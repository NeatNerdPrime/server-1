<?php

/**
 * SPDX-FileCopyrightText: 2019-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */
namespace OCA\Files_External\Tests\Command;

use OCA\Files_External\Lib\StorageConfig;
use OCA\Files_External\NotFoundException;
use OCA\Files_External\Service\GlobalStoragesService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\BufferedOutput;
use Test\TestCase;

abstract class CommandTestCase extends TestCase {
	/**
	 * @param StorageConfig[] $mounts
	 * @return GlobalStoragesService&MockObject
	 */
	protected function getGlobalStorageService(array $mounts = []) {
		$mock = $this->createMock(GlobalStoragesService::class);

		$this->bindMounts($mock, $mounts);

		return $mock;
	}

	/**
	 * @param MockObject $mock
	 * @param StorageConfig[] $mounts
	 */
	protected function bindMounts(MockObject $mock, array $mounts) {
		$mock->expects($this->any())
			->method('getStorage')
			->willReturnCallback(function ($id) use ($mounts) {
				foreach ($mounts as $mount) {
					if ($mount->getId() === $id) {
						return $mount;
					}
				}
				throw new NotFoundException();
			});
	}

	/**
	 * @param $id
	 * @param $mountPoint
	 * @param $backendClass
	 * @param string $applicableIdentifier
	 * @param array $config
	 * @param array $options
	 * @param array $users
	 * @param array $groups
	 * @return StorageConfig
	 */
	protected function getMount($id, $mountPoint, $backendClass, $applicableIdentifier = 'password::password', $config = [], $options = [], $users = [], $groups = []) {
		$mount = new StorageConfig($id);

		$mount->setMountPoint($mountPoint);
		$mount->setBackendOptions($config);
		$mount->setMountOptions($options);
		$mount->setApplicableUsers($users);
		$mount->setApplicableGroups($groups);

		return $mount;
	}

	protected function getInput(Command $command, array $arguments = [], array $options = []): ArrayInput {
		$input = new ArrayInput([]);
		$input->bind($command->getDefinition());
		foreach ($arguments as $key => $value) {
			$input->setArgument($key, $value);
		}
		foreach ($options as $key => $value) {
			$input->setOption($key, $value);
		}
		return $input;
	}

	protected function executeCommand(Command $command, Input $input): string {
		$output = new BufferedOutput();
		$this->invokePrivate($command, 'execute', [$input, $output]);
		return $output->fetch();
	}
}
