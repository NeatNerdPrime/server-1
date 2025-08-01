<?php

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OC\Collaboration\Collaborators;

use OCP\Collaboration\Collaborators\ISearch;
use OCP\Collaboration\Collaborators\ISearchPlugin;
use OCP\Collaboration\Collaborators\ISearchResult;
use OCP\Collaboration\Collaborators\SearchResultType;
use OCP\IContainer;
use OCP\Share\IShare;

class Search implements ISearch {
	protected array $pluginList = [];

	public function __construct(
		private IContainer $container,
	) {
	}

	/**
	 * @param string $search
	 * @param bool $lookup
	 * @param int|null $limit
	 * @param int|null $offset
	 * @throws \OCP\AppFramework\QueryException
	 */
	public function search($search, array $shareTypes, $lookup, $limit, $offset): array {
		$hasMoreResults = false;

		// Trim leading and trailing whitespace characters, e.g. when query is copy-pasted
		$search = trim($search);

		/** @var ISearchResult $searchResult */
		$searchResult = $this->container->resolve(SearchResult::class);

		foreach ($shareTypes as $type) {
			if (!isset($this->pluginList[$type])) {
				continue;
			}
			foreach ($this->pluginList[$type] as $plugin) {
				/** @var ISearchPlugin $searchPlugin */
				$searchPlugin = $this->container->resolve($plugin);
				$hasMoreResults = $searchPlugin->search($search, $limit, $offset, $searchResult) || $hasMoreResults;
			}
		}

		// Get from lookup server, not a separate share type
		if ($lookup) {
			$searchPlugin = $this->container->resolve(LookupPlugin::class);
			$hasMoreResults = $searchPlugin->search($search, $limit, $offset, $searchResult) || $hasMoreResults;
		}

		// sanitizing, could go into the plugins as well

		// if we have an exact match, either for the federated cloud id or for the
		// email address, we only return the exact match. It is highly unlikely
		// that the exact same email address and federated cloud id exists
		$emailType = new SearchResultType('emails');
		$remoteType = new SearchResultType('remotes');
		if ($searchResult->hasExactIdMatch($emailType) && !$searchResult->hasExactIdMatch($remoteType)) {
			$searchResult->unsetResult($remoteType);
		} elseif (!$searchResult->hasExactIdMatch($emailType) && $searchResult->hasExactIdMatch($remoteType)) {
			$searchResult->unsetResult($emailType);
		}

		$this->dropMailSharesWhereRemoteShareIsPossible($searchResult);

		// if we have an exact local user match with an email-a-like query,
		// there is no need to show the remote and email matches.
		$userType = new SearchResultType('users');
		if (str_contains($search, '@') && $searchResult->hasExactIdMatch($userType)) {
			$searchResult->unsetResult($remoteType);
			$searchResult->unsetResult($emailType);
		}

		return [$searchResult->asArray(), $hasMoreResults];
	}

	public function registerPlugin(array $pluginInfo): void {
		$shareType = constant(IShare::class . '::' . substr($pluginInfo['shareType'], strlen('SHARE_')));
		if ($shareType === null) {
			throw new \InvalidArgumentException('Provided ShareType is invalid');
		}
		$this->pluginList[$shareType][] = $pluginInfo['class'];
	}

	protected function dropMailSharesWhereRemoteShareIsPossible(ISearchResult $searchResult): void {
		$allResults = $searchResult->asArray();

		$emailType = new SearchResultType('emails');
		$remoteType = new SearchResultType('remotes');

		if (!isset($allResults[$remoteType->getLabel()])
			|| !isset($allResults[$emailType->getLabel()])) {
			return;
		}

		$mailIdMap = [];
		foreach ($allResults[$emailType->getLabel()] as $mailRow) {
			// sure, array_reduce looks nicer, but foreach needs less resources and is faster
			if (!isset($mailRow['uuid'])) {
				continue;
			}
			$mailIdMap[$mailRow['uuid']] = $mailRow['value']['shareWith'];
		}

		foreach ($allResults[$remoteType->getLabel()] as $resultRow) {
			if (!isset($resultRow['uuid'])) {
				continue;
			}
			if (isset($mailIdMap[$resultRow['uuid']])) {
				$searchResult->removeCollaboratorResult($emailType, $mailIdMap[$resultRow['uuid']]);
			}
		}
	}
}
