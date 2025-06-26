<?php

namespace onstuimig\seomatichelper;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use nystudio107\seomatic\base\Container;
use nystudio107\seomatic\events\IncludeContainerEvent;
use nystudio107\seomatic\models\MetaJsonLdContainer;
use onstuimig\seomatichelper\models\Settings;
use yii\base\Event;

/**
 * SEOmatic Helper plugin
 *
 * @method static SeomaticHelper getInstance()
 * @method Settings getSettings()
 * @author Onstuimig
 * @copyright Onstuimig
 * @license https://craftcms.github.io/license/ Craft License
 */
class SeomaticHelper extends Plugin
{
	public string $schemaVersion = '1.0.0';
	public bool $hasCpSettings = true;

	public static function config(): array
	{
		return [
			'components' => [
				// Define component configs here...
			],
		];
	}

	public function init(): void
	{
		parent::init();

		$this->attachEventHandlers();

		// Any code that creates an element query or loads Twig should be deferred until
		// after Craft is fully initialized, to avoid conflicts with other plugins/modules
		Craft::$app->onInit(function() {
			// ...
		});
	}

	protected function createSettingsModel(): ?Model
	{
		return Craft::createObject(Settings::class);
	}

	protected function settingsHtml(): ?string
	{
		return Craft::$app->view->renderTemplate('seomatic-helper/_settings.twig', [
			'plugin' => $this,
			'settings' => $this->getSettings(),
		]);
	}

	private function attachEventHandlers(): void
	{
		Event::on(
			Container::class,
			Container::EVENT_INCLUDE_CONTAINER,
			function(IncludeContainerEvent $event) {
				if ($event->sender instanceof MetaJsonLdContainer) {
					$settings = $this->getSettings();

					if ($settings->removeCreatorFromJsonLd) {
						$creator = $event->sender->data['creator'] ?? null;
						$mainEntityOfPage = $event->sender->data['mainEntityOfPage'] ?? null;
						if ($creator) {
							$creator->include = false;
						}
						if ($mainEntityOfPage) {
							if (property_exists($mainEntityOfPage, 'publisher')) {
								$mainEntityOfPage->publisher = null;
							}
							if (property_exists($mainEntityOfPage, 'creator')) {
								$mainEntityOfPage->creator = null;
							}
						}
					}
				}
			}
		);
	}
}
