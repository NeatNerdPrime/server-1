<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\Theming\Themes;

use OCA\Theming\ITheme;

class HighContrastTheme extends DefaultTheme implements ITheme {

	public function getId(): string {
		return 'light-highcontrast';
	}

	public function getTitle(): string {
		return $this->l->t('High contrast mode');
	}

	public function getEnableLabel(): string {
		return $this->l->t('Enable high contrast mode');
	}

	public function getDescription(): string {
		return $this->l->t('A high contrast mode to ease your navigation. Visual quality will be reduced but clarity will be increased.');
	}

	public function getMediaQuery(): string {
		return '(prefers-contrast: more)';
	}

	/**
	 * Keep this consistent with other HighContrast Themes
	 */
	public function getCSSVariables(): array {
		$defaultVariables = parent::getCSSVariables();

		$colorMainText = '#000000';
		$colorMainBackground = '#ffffff';
		$colorMainBackgroundRGB = join(',', $this->util->hexToRGB($colorMainBackground));

		$colorError = '#D10000';
		$colorWarning = '#995900';
		$colorSuccess = '#207830';
		$colorInfo = '#006DA8';

		$primaryVariables = $this->generatePrimaryVariables($colorMainBackground, $colorMainText, true);
		return array_merge(
			$defaultVariables,
			$primaryVariables,
			[
				'--color-primary-element-text-dark' => $primaryVariables['--color-primary-element-text'],

				'--color-main-background' => $colorMainBackground,
				'--color-main-background-rgb' => $colorMainBackgroundRGB,
				'--color-main-background-translucent' => 'rgba(var(--color-main-background-rgb), 1)',
				'--color-main-text' => $colorMainText,

				'--color-background-dark' => $this->util->darken($colorMainBackground, 20),
				'--color-background-darker' => $this->util->darken($colorMainBackground, 20),

				'--color-main-background-blur' => $colorMainBackground,
				'--filter-background-blur' => 'none',

				'--color-placeholder-light' => $this->util->darken($colorMainBackground, 30),
				'--color-placeholder-dark' => $this->util->darken($colorMainBackground, 45),

				'--color-text-maxcontrast' => $colorMainText,
				'--color-text-maxcontrast-background-blur' => $colorMainText,
				'--color-text-light' => $colorMainText,
				'--color-text-lighter' => $colorMainText,

				'--color-error' => $colorError,
				'--color-error-rgb' => join(',', $this->util->hexToRGB($colorError)),
				'--color-error-hover' => $this->util->darken($colorError, 8),
				'--color-error-text' => $this->util->darken($colorError, 17),

				'--color-warning' => $colorWarning,
				'--color-warning-rgb' => join(',', $this->util->hexToRGB($colorWarning)),
				'--color-warning-hover' => $this->util->darken($colorWarning, 7),
				'--color-warning-text' => $this->util->darken($colorWarning, 13),

				'--color-info' => $colorInfo,
				'--color-info-rgb' => join(',', $this->util->hexToRGB($colorInfo)),
				'--color-info-hover' => $this->util->darken($colorInfo, 7),
				'--color-info-text' => $this->util->darken($colorInfo, 15),

				'--color-success' => $colorSuccess,
				'--color-success-rgb' => join(',', $this->util->hexToRGB($colorSuccess)),
				'--color-success-hover' => $this->util->darken($colorSuccess, 7),
				'--color-success-text' => $this->util->darken($colorSuccess, 14),

				'--color-favorite' => '#936B06',

				'--color-scrollbar' => 'auto transparent',

				// used for the icon loading animation
				'--color-loading-light' => '#dddddd',
				'--color-loading-dark' => '#000000',

				'--color-box-shadow-rgb' => $colorMainText,
				'--color-box-shadow' => $colorMainText,

				'--color-border' => $this->util->darken($colorMainBackground, 50),
				'--color-border-dark' => $this->util->darken($colorMainBackground, 50),
				'--color-border-maxcontrast' => $this->util->darken($colorMainBackground, 56),

				// remove the gradient from the app icons
				'--header-menu-icon-mask' => 'none',
			]
		);
	}

	public function getCustomCss(): string {
		return "
			[class^='icon-'], [class*=' icon-'],
			.action,
			#appmenu li a,
			.menutoggle {
				opacity: 1 !important;
			}
			#app-navigation {
				border-right: 1px solid var(--color-border);
			}
		";
	}
}
