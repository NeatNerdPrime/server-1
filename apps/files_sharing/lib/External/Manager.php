<?php

/**
 * SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\Files_Sharing\External;

use Doctrine\DBAL\Driver\Exception;
use OC\Files\Filesystem;
use OCA\FederatedFileSharing\Events\FederatedShareAddedEvent;
use OCA\Files_Sharing\Helper;
use OCA\Files_Sharing\ResponseDefinitions;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\Federation\ICloudFederationFactory;
use OCP\Federation\ICloudFederationProviderManager;
use OCP\Files;
use OCP\Files\Events\InvalidateMountCacheEvent;
use OCP\Files\NotFoundException;
use OCP\Files\Storage\IStorageFactory;
use OCP\Http\Client\IClientService;
use OCP\IDBConnection;
use OCP\IGroupManager;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\Notification\IManager;
use OCP\OCS\IDiscoveryService;
use OCP\Share;
use OCP\Share\IShare;
use Psr\Log\LoggerInterface;

/**
 * @psalm-import-type Files_SharingRemoteShare from ResponseDefinitions
 */
class Manager {
	public const STORAGE = '\OCA\Files_Sharing\External\Storage';

	/** @var string|null */
	private $uid;

	public function __construct(
		private IDBConnection $connection,
		private \OC\Files\Mount\Manager $mountManager,
		private IStorageFactory $storageLoader,
		private IClientService $clientService,
		private IManager $notificationManager,
		private IDiscoveryService $discoveryService,
		private ICloudFederationProviderManager $cloudFederationProviderManager,
		private ICloudFederationFactory $cloudFederationFactory,
		private IGroupManager $groupManager,
		private IUserManager $userManager,
		IUserSession $userSession,
		private IEventDispatcher $eventDispatcher,
		private LoggerInterface $logger,
	) {
		$user = $userSession->getUser();
		$this->uid = $user ? $user->getUID() : null;
	}

	/**
	 * add new server-to-server share
	 *
	 * @param string $remote
	 * @param string $token
	 * @param string $password
	 * @param string $name
	 * @param string $owner
	 * @param int $shareType
	 * @param boolean $accepted
	 * @param string $user
	 * @param string $remoteId
	 * @param int $parent
	 * @return Mount|null
	 * @throws \Doctrine\DBAL\Exception
	 */
	public function addShare($remote, $token, $password, $name, $owner, $shareType, $accepted = false, $user = null, $remoteId = '', $parent = -1) {
		$user = $user ?? $this->uid;
		$accepted = $accepted ? IShare::STATUS_ACCEPTED : IShare::STATUS_PENDING;
		$name = Filesystem::normalizePath('/' . $name);

		if ($accepted !== IShare::STATUS_ACCEPTED) {
			// To avoid conflicts with the mount point generation later,
			// we only use a temporary mount point name here. The real
			// mount point name will be generated when accepting the share,
			// using the original share item name.
			$tmpMountPointName = '{{TemporaryMountPointName#' . $name . '}}';
			$mountPoint = $tmpMountPointName;
			$hash = md5($tmpMountPointName);
			$data = [
				'remote' => $remote,
				'share_token' => $token,
				'password' => $password,
				'name' => $name,
				'owner' => $owner,
				'user' => $user,
				'mountpoint' => $mountPoint,
				'mountpoint_hash' => $hash,
				'accepted' => $accepted,
				'remote_id' => $remoteId,
				'share_type' => $shareType,
			];

			$i = 1;
			while (!$this->connection->insertIfNotExist('*PREFIX*share_external', $data, ['user', 'mountpoint_hash'])) {
				// The external share already exists for the user
				$data['mountpoint'] = $tmpMountPointName . '-' . $i;
				$data['mountpoint_hash'] = md5($data['mountpoint']);
				$i++;
			}
			return null;
		}

		$mountPoint = Files::buildNotExistingFileName('/', $name);
		$mountPoint = Filesystem::normalizePath('/' . $mountPoint);
		$hash = md5($mountPoint);

		$this->writeShareToDb($remote, $token, $password, $name, $owner, $user, $mountPoint, $hash, $accepted, $remoteId, $parent, $shareType);

		$options = [
			'remote' => $remote,
			'token' => $token,
			'password' => $password,
			'mountpoint' => $mountPoint,
			'owner' => $owner
		];
		return $this->mountShare($options, $user);
	}

	/**
	 * write remote share to the database
	 *
	 * @param $remote
	 * @param $token
	 * @param $password
	 * @param $name
	 * @param $owner
	 * @param $user
	 * @param $mountPoint
	 * @param $hash
	 * @param $accepted
	 * @param $remoteId
	 * @param $parent
	 * @param $shareType
	 *
	 * @return void
	 * @throws \Doctrine\DBAL\Driver\Exception
	 */
	private function writeShareToDb($remote, $token, $password, $name, $owner, $user, $mountPoint, $hash, $accepted, $remoteId, $parent, $shareType): void {
		$query = $this->connection->prepare('
				INSERT INTO `*PREFIX*share_external`
					(`remote`, `share_token`, `password`, `name`, `owner`, `user`, `mountpoint`, `mountpoint_hash`, `accepted`, `remote_id`, `parent`, `share_type`)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
			');
		$query->execute([$remote, $token, $password, $name, $owner, $user, $mountPoint, $hash, $accepted, $remoteId, $parent, $shareType]);
	}

	private function fetchShare(int $id): array|false {
		$getShare = $this->connection->prepare('
			SELECT `id`, `remote`, `remote_id`, `share_token`, `name`, `owner`, `user`, `mountpoint`, `accepted`, `parent`, `share_type`, `password`, `mountpoint_hash`
			FROM  `*PREFIX*share_external`
			WHERE `id` = ?');
		$result = $getShare->execute([$id]);
		$share = $result->fetch();
		$result->closeCursor();
		return $share;
	}

	/**
	 * get share by token
	 *
	 * @param string $token
	 * @return mixed share of false
	 */
	private function fetchShareByToken($token) {
		$getShare = $this->connection->prepare('
			SELECT `id`, `remote`, `remote_id`, `share_token`, `name`, `owner`, `user`, `mountpoint`, `accepted`, `parent`, `share_type`, `password`, `mountpoint_hash`
			FROM  `*PREFIX*share_external`
			WHERE `share_token` = ?');
		$result = $getShare->execute([$token]);
		$share = $result->fetch();
		$result->closeCursor();
		return $share;
	}

	private function fetchUserShare($parentId, $uid) {
		$getShare = $this->connection->prepare('
			SELECT `id`, `remote`, `remote_id`, `share_token`, `name`, `owner`, `user`, `mountpoint`, `accepted`, `parent`, `share_type`, `password`, `mountpoint_hash`
			FROM  `*PREFIX*share_external`
			WHERE `parent` = ? AND `user` = ?');
		$result = $getShare->execute([$parentId, $uid]);
		$share = $result->fetch();
		$result->closeCursor();
		if ($share !== false) {
			return $share;
		}
		return null;
	}

	public function getShare(int $id, ?string $user = null): array|false {
		$user = $user ?? $this->uid;
		$share = $this->fetchShare($id);
		if ($share === false) {
			return false;
		}

		// check if the user is allowed to access it
		if ($this->canAccessShare($share, $user)) {
			return $share;
		}

		return false;
	}

	/**
	 * Get share by token
	 *
	 * @param string $token
	 * @return array|false
	 */
	public function getShareByToken(string $token): array|false {
		$share = $this->fetchShareByToken($token);

		// We do not check if the user is allowed to access it here,
		// as this is not used from a user context.
		if ($share === false) {
			return false;
		}

		return $share;
	}

	private function canAccessShare(array $share, string $user): bool {
		$validShare = isset($share['share_type']) && isset($share['user']);

		if (!$validShare) {
			return false;
		}

		// If the share is a user share, check if the user is the recipient
		if ((int)$share['share_type'] === IShare::TYPE_USER
			&& $share['user'] === $user) {
			return true;
		}

		// If the share is a group share, check if the user is in the group
		if ((int)$share['share_type'] === IShare::TYPE_GROUP) {
			$parentId = (int)$share['parent'];
			if ($parentId !== -1) {
				// we just retrieved a sub-share, switch to the parent entry for verification
				$groupShare = $this->fetchShare($parentId);
			} else {
				$groupShare = $share;
			}

			$user = $this->userManager->get($user);
			if ($this->groupManager->get($groupShare['user'])->inGroup($user)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Updates accepted flag in the database
	 *
	 * @param int $id
	 */
	private function updateAccepted(int $shareId, bool $accepted) : void {
		$query = $this->connection->prepare('
			UPDATE `*PREFIX*share_external`
			SET `accepted` = ?
			WHERE `id` = ?');
		$updateResult = $query->execute([$accepted ? 1 : 0, $shareId]);
		$updateResult->closeCursor();
	}

	/**
	 * accept server-to-server share
	 *
	 * @param int $id
	 * @return bool True if the share could be accepted, false otherwise
	 */
	public function acceptShare(int $id, ?string $user = null) {
		// If we're auto-accepting a share, we need to know the user id
		// as there is no session available while processing the share
		// from the remote server request.
		$user = $user ?? $this->uid;
		if ($user === null) {
			$this->logger->error('No user specified for accepting share');
			return false;
		}

		$share = $this->getShare($id, $user);
		$result = false;

		if ($share) {
			\OC_Util::setupFS($user);
			$shareFolder = Helper::getShareFolder(null, $user);
			$mountPoint = Files::buildNotExistingFileName($shareFolder, $share['name']);
			$mountPoint = Filesystem::normalizePath($mountPoint);
			$hash = md5($mountPoint);
			$userShareAccepted = false;

			if ((int)$share['share_type'] === IShare::TYPE_USER) {
				$acceptShare = $this->connection->prepare('
				UPDATE `*PREFIX*share_external`
				SET `accepted` = ?,
					`mountpoint` = ?,
					`mountpoint_hash` = ?
				WHERE `id` = ? AND `user` = ?');
				$userShareAccepted = $acceptShare->execute([1, $mountPoint, $hash, $id, $user]);
			} else {
				$parentId = (int)$share['parent'];
				if ($parentId !== -1) {
					// this is the sub-share
					$subshare = $share;
				} else {
					$subshare = $this->fetchUserShare($id, $user);
				}

				if ($subshare !== null) {
					try {
						$acceptShare = $this->connection->prepare('
						UPDATE `*PREFIX*share_external`
						SET `accepted` = ?,
							`mountpoint` = ?,
							`mountpoint_hash` = ?
						WHERE `id` = ? AND `user` = ?');
						$acceptShare->execute([1, $mountPoint, $hash, $subshare['id'], $user]);
						$result = true;
					} catch (Exception $e) {
						$this->logger->emergency('Could not update share', ['exception' => $e]);
						$result = false;
					}
				} else {
					try {
						$this->writeShareToDb(
							$share['remote'],
							$share['share_token'],
							$share['password'],
							$share['name'],
							$share['owner'],
							$user,
							$mountPoint, $hash, 1,
							$share['remote_id'],
							$id,
							$share['share_type']);
						$result = true;
					} catch (Exception $e) {
						$this->logger->emergency('Could not create share', ['exception' => $e]);
						$result = false;
					}
				}
			}

			if ($userShareAccepted !== false) {
				$this->sendFeedbackToRemote($share['remote'], $share['share_token'], $share['remote_id'], 'accept');
				$event = new FederatedShareAddedEvent($share['remote']);
				$this->eventDispatcher->dispatchTyped($event);
				$this->eventDispatcher->dispatchTyped(new InvalidateMountCacheEvent($this->userManager->get($user)));
				$result = true;
			}
		}

		// Make sure the user has no notification for something that does not exist anymore.
		$this->processNotification($id, $user);

		return $result;
	}

	/**
	 * decline server-to-server share
	 *
	 * @param int $id
	 * @return bool True if the share could be declined, false otherwise
	 */
	public function declineShare(int $id, ?string $user = null) {
		$user = $user ?? $this->uid;
		if ($user === null) {
			$this->logger->error('No user specified for declining share');
			return false;
		}

		$share = $this->getShare($id, $user);
		$result = false;

		if ($share && (int)$share['share_type'] === IShare::TYPE_USER) {
			$removeShare = $this->connection->prepare('
				DELETE FROM `*PREFIX*share_external` WHERE `id` = ? AND `user` = ?');
			$removeShare->execute([$id, $user]);
			$this->sendFeedbackToRemote($share['remote'], $share['share_token'], $share['remote_id'], 'decline');

			$this->processNotification($id, $user);
			$result = true;
		} elseif ($share && (int)$share['share_type'] === IShare::TYPE_GROUP) {
			$parentId = (int)$share['parent'];
			if ($parentId !== -1) {
				// this is the sub-share
				$subshare = $share;
			} else {
				$subshare = $this->fetchUserShare($id, $user);
			}

			if ($subshare !== null) {
				try {
					$this->updateAccepted((int)$subshare['id'], false);
					$result = true;
				} catch (Exception $e) {
					$this->logger->emergency('Could not update share', ['exception' => $e]);
					$result = false;
				}
			} else {
				try {
					$this->writeShareToDb(
						$share['remote'],
						$share['share_token'],
						$share['password'],
						$share['name'],
						$share['owner'],
						$user,
						$share['mountpoint'],
						$share['mountpoint_hash'],
						0,
						$share['remote_id'],
						$id,
						$share['share_type']);
					$result = true;
				} catch (Exception $e) {
					$this->logger->emergency('Could not create share', ['exception' => $e]);
					$result = false;
				}
			}
			$this->processNotification($id, $user);
		}

		return $result;
	}

	public function processNotification(int $remoteShare, ?string $user = null): void {
		$user = $user ?? $this->uid;
		if ($user === null) {
			$this->logger->error('No user specified for processing notification');
			return;
		}

		$share = $this->fetchShare($remoteShare);
		if ($share === false) {
			return;
		}

		$filter = $this->notificationManager->createNotification();
		$filter->setApp('files_sharing')
			->setUser($user)
			->setObject('remote_share', (string)$remoteShare);
		$this->notificationManager->markProcessed($filter);
	}

	/**
	 * inform remote server whether server-to-server share was accepted/declined
	 *
	 * @param string $remote
	 * @param string $token
	 * @param string $remoteId Share id on the remote host
	 * @param string $feedback
	 * @return boolean
	 */
	private function sendFeedbackToRemote($remote, $token, $remoteId, $feedback) {
		$result = $this->tryOCMEndPoint($remote, $token, $remoteId, $feedback);

		if (is_array($result)) {
			return true;
		}

		$federationEndpoints = $this->discoveryService->discover($remote, 'FEDERATED_SHARING');
		$endpoint = $federationEndpoints['share'] ?? '/ocs/v2.php/cloud/shares';

		$url = rtrim($remote, '/') . $endpoint . '/' . $remoteId . '/' . $feedback . '?format=' . Share::RESPONSE_FORMAT;
		$fields = ['token' => $token];

		$client = $this->clientService->newClient();

		try {
			$response = $client->post(
				$url,
				[
					'body' => $fields,
					'connect_timeout' => 10,
				]
			);
		} catch (\Exception $e) {
			return false;
		}

		$status = json_decode($response->getBody(), true);

		return ($status['ocs']['meta']['statuscode'] === 100 || $status['ocs']['meta']['statuscode'] === 200);
	}

	/**
	 * try send accept message to ocm end-point
	 *
	 * @param string $remoteDomain
	 * @param string $token
	 * @param string $remoteId id of the share
	 * @param string $feedback
	 * @return array|false
	 */
	protected function tryOCMEndPoint($remoteDomain, $token, $remoteId, $feedback) {
		switch ($feedback) {
			case 'accept':
				$notification = $this->cloudFederationFactory->getCloudFederationNotification();
				$notification->setMessage(
					'SHARE_ACCEPTED',
					'file',
					$remoteId,
					[
						'sharedSecret' => $token,
						'message' => 'Recipient accept the share'
					]

				);
				return $this->cloudFederationProviderManager->sendNotification($remoteDomain, $notification);
			case 'decline':
				$notification = $this->cloudFederationFactory->getCloudFederationNotification();
				$notification->setMessage(
					'SHARE_DECLINED',
					'file',
					$remoteId,
					[
						'sharedSecret' => $token,
						'message' => 'Recipient declined the share'
					]

				);
				return $this->cloudFederationProviderManager->sendNotification($remoteDomain, $notification);
		}

		return false;
	}


	/**
	 * remove '/user/files' from the path and trailing slashes
	 *
	 * @param string $path
	 * @return string
	 */
	protected function stripPath($path) {
		$prefix = '/' . $this->uid . '/files';
		return rtrim(substr($path, strlen($prefix)), '/');
	}

	public function getMount($data, ?string $user = null) {
		$user = $user ?? $this->uid;
		$data['manager'] = $this;
		$mountPoint = '/' . $user . '/files' . $data['mountpoint'];
		$data['mountpoint'] = $mountPoint;
		$data['certificateManager'] = \OC::$server->getCertificateManager();
		return new Mount(self::STORAGE, $mountPoint, $data, $this, $this->storageLoader);
	}

	/**
	 * @param array $data
	 * @return Mount
	 */
	protected function mountShare($data, ?string $user = null) {
		$mount = $this->getMount($data, $user);
		$this->mountManager->addMount($mount);
		return $mount;
	}

	/**
	 * @return \OC\Files\Mount\Manager
	 */
	public function getMountManager() {
		return $this->mountManager;
	}

	/**
	 * @param string $source
	 * @param string $target
	 * @return bool
	 */
	public function setMountPoint($source, $target) {
		$source = $this->stripPath($source);
		$target = $this->stripPath($target);
		$sourceHash = md5($source);
		$targetHash = md5($target);

		$query = $this->connection->prepare('
			UPDATE `*PREFIX*share_external`
			SET `mountpoint` = ?, `mountpoint_hash` = ?
			WHERE `mountpoint_hash` = ?
			AND `user` = ?
		');
		$result = (bool)$query->execute([$target, $targetHash, $sourceHash, $this->uid]);

		$this->eventDispatcher->dispatchTyped(new InvalidateMountCacheEvent($this->userManager->get($this->uid)));

		return $result;
	}

	public function removeShare($mountPoint): bool {
		try {
			$mountPointObj = $this->mountManager->find($mountPoint);
		} catch (NotFoundException $e) {
			$this->logger->error('Mount point to remove share not found', ['mountPoint' => $mountPoint]);
			return false;
		}
		if (!$mountPointObj instanceof Mount) {
			$this->logger->error('Mount point to remove share is not an external share, share probably doesn\'t exist', ['mountPoint' => $mountPoint]);
			return false;
		}
		$id = $mountPointObj->getStorage()->getCache()->getId('');

		$mountPoint = $this->stripPath($mountPoint);
		$hash = md5($mountPoint);

		try {
			$getShare = $this->connection->prepare('
				SELECT `remote`, `share_token`, `remote_id`, `share_type`, `id`
				FROM  `*PREFIX*share_external`
				WHERE `mountpoint_hash` = ? AND `user` = ?');
			$result = $getShare->execute([$hash, $this->uid]);
			$share = $result->fetch();
			$result->closeCursor();
			if ($share !== false && (int)$share['share_type'] === IShare::TYPE_USER) {
				try {
					$this->sendFeedbackToRemote($share['remote'], $share['share_token'], $share['remote_id'], 'decline');
				} catch (\Throwable $e) {
					// if we fail to notify the remote (probably cause the remote is down)
					// we still want the share to be gone to prevent undeletable remotes
				}

				$query = $this->connection->prepare('
					DELETE FROM `*PREFIX*share_external`
					WHERE `id` = ?
				');
				$deleteResult = $query->execute([(int)$share['id']]);
				$deleteResult->closeCursor();
			} elseif ($share !== false && (int)$share['share_type'] === IShare::TYPE_GROUP) {
				$this->updateAccepted((int)$share['id'], false);
			}

			$this->removeReShares($id);
		} catch (\Doctrine\DBAL\Exception $ex) {
			$this->logger->emergency('Could not update share', ['exception' => $ex]);
			return false;
		}

		return true;
	}

	/**
	 * remove re-shares from share table and mapping in the federated_reshares table
	 *
	 * @param $mountPointId
	 */
	protected function removeReShares($mountPointId) {
		$selectQuery = $this->connection->getQueryBuilder();
		$query = $this->connection->getQueryBuilder();
		$selectQuery->select('id')->from('share')
			->where($selectQuery->expr()->eq('file_source', $query->createNamedParameter($mountPointId)));
		$select = $selectQuery->getSQL();


		$query->delete('federated_reshares')
			->where($query->expr()->in('share_id', $query->createFunction($select)));
		$query->execute();

		$deleteReShares = $this->connection->getQueryBuilder();
		$deleteReShares->delete('share')
			->where($deleteReShares->expr()->eq('file_source', $deleteReShares->createNamedParameter($mountPointId)));
		$deleteReShares->execute();
	}

	/**
	 * remove all shares for user $uid if the user was deleted
	 *
	 * @param string $uid
	 */
	public function removeUserShares($uid): bool {
		try {
			// TODO: use query builder
			$getShare = $this->connection->prepare('
				SELECT `id`, `remote`, `share_type`, `share_token`, `remote_id`
				FROM  `*PREFIX*share_external`
				WHERE `user` = ?
				AND `share_type` = ?');
			$result = $getShare->execute([$uid, IShare::TYPE_USER]);
			$shares = $result->fetchAll();
			$result->closeCursor();

			foreach ($shares as $share) {
				$this->sendFeedbackToRemote($share['remote'], $share['share_token'], $share['remote_id'], 'decline');
			}

			$qb = $this->connection->getQueryBuilder();
			$qb->delete('share_external')
				// user field can specify a user or a group
				->where($qb->expr()->eq('user', $qb->createNamedParameter($uid)))
				->andWhere(
					$qb->expr()->orX(
						// delete direct shares
						$qb->expr()->eq('share_type', $qb->expr()->literal(IShare::TYPE_USER)),
						// delete sub-shares of group shares for that user
						$qb->expr()->andX(
							$qb->expr()->eq('share_type', $qb->expr()->literal(IShare::TYPE_GROUP)),
							$qb->expr()->neq('parent', $qb->expr()->literal(-1)),
						)
					)
				);
			$qb->execute();
		} catch (\Doctrine\DBAL\Exception $ex) {
			$this->logger->emergency('Could not delete user shares', ['exception' => $ex]);
			return false;
		}

		return true;
	}

	public function removeGroupShares($gid): bool {
		try {
			$getShare = $this->connection->prepare('
				SELECT `id`, `remote`, `share_type`, `share_token`, `remote_id`
				FROM  `*PREFIX*share_external`
				WHERE `user` = ?
				AND `share_type` = ?');
			$result = $getShare->execute([$gid, IShare::TYPE_GROUP]);
			$shares = $result->fetchAll();
			$result->closeCursor();

			$deletedGroupShares = [];
			$qb = $this->connection->getQueryBuilder();
			// delete group share entry and matching sub-entries
			$qb->delete('share_external')
				->where(
					$qb->expr()->orX(
						$qb->expr()->eq('id', $qb->createParameter('share_id')),
						$qb->expr()->eq('parent', $qb->createParameter('share_parent_id'))
					)
				);

			foreach ($shares as $share) {
				$qb->setParameter('share_id', $share['id']);
				$qb->setParameter('share_parent_id', $share['id']);
				$qb->execute();
			}
		} catch (\Doctrine\DBAL\Exception $ex) {
			$this->logger->emergency('Could not delete user shares', ['exception' => $ex]);
			return false;
		}

		return true;
	}

	/**
	 * return a list of shares which are not yet accepted by the user
	 *
	 * @return list<Files_SharingRemoteShare> list of open server-to-server shares
	 */
	public function getOpenShares() {
		return $this->getShares(false);
	}

	/**
	 * return a list of shares which are accepted by the user
	 *
	 * @return list<Files_SharingRemoteShare> list of accepted server-to-server shares
	 */
	public function getAcceptedShares() {
		return $this->getShares(true);
	}

	/**
	 * return a list of shares for the user
	 *
	 * @param bool|null $accepted True for accepted only,
	 *                            false for not accepted,
	 *                            null for all shares of the user
	 * @return list<Files_SharingRemoteShare> list of open server-to-server shares
	 */
	private function getShares($accepted) {
		// Not allowing providing a user here,
		// as we only want to retrieve shares for the current user.
		$user = $this->userManager->get($this->uid);
		$groups = $this->groupManager->getUserGroups($user);
		$userGroups = [];
		foreach ($groups as $group) {
			$userGroups[] = $group->getGID();
		}

		$qb = $this->connection->getQueryBuilder();
		$qb->select('id', 'share_type', 'parent', 'remote', 'remote_id', 'share_token', 'name', 'owner', 'user', 'mountpoint', 'accepted')
			->from('share_external')
			->where(
				$qb->expr()->orX(
					$qb->expr()->eq('user', $qb->createNamedParameter($this->uid)),
					$qb->expr()->in(
						'user',
						$qb->createNamedParameter($userGroups, IQueryBuilder::PARAM_STR_ARRAY)
					)
				)
			)
			->orderBy('id', 'ASC');

		try {
			$result = $qb->execute();
			$shares = $result->fetchAll();
			$result->closeCursor();

			// remove parent group share entry if we have a specific user share entry for the user
			$toRemove = [];
			foreach ($shares as $share) {
				if ((int)$share['share_type'] === IShare::TYPE_GROUP && (int)$share['parent'] > 0) {
					$toRemove[] = $share['parent'];
				}
			}
			$shares = array_filter($shares, function ($share) use ($toRemove) {
				return !in_array($share['id'], $toRemove, true);
			});

			if (!is_null($accepted)) {
				$shares = array_filter($shares, function ($share) use ($accepted) {
					return (bool)$share['accepted'] === $accepted;
				});
			}
			return array_values($shares);
		} catch (\Doctrine\DBAL\Exception $e) {
			$this->logger->emergency('Error when retrieving shares', ['exception' => $e]);
			return [];
		}
	}
}
