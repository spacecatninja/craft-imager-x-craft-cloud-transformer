<?php

/**
 * Craft Cloud transformer for Imager X
 *
 * @link      https://www.spacecat.ninja
 * @copyright Copyright (c) 2024 André Elvan
 */

namespace spacecatninja\craftcloudtransformer;

use craft\base\Model;
use craft\base\Plugin;

use craft\services\Assets;
use spacecatninja\craftcloudtransformer\models\Settings;
use spacecatninja\craftcloudtransformer\transformers\CraftCloud;
use yii\base\Event;

class CraftCloudTransformer extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var CraftCloudTransformer
     */
    public static $plugin;

    // Public Methods
    // =========================================================================

    public function init(): void
    {
        parent::init();

        self::$plugin = $this;

        // Register services
        $this->setComponents([

        ]);

        // Register transformer with Imager
        Event::on(\spacecatninja\imagerx\ImagerX::class,
            \spacecatninja\imagerx\ImagerX::EVENT_REGISTER_TRANSFORMERS,
            static function(\spacecatninja\imagerx\events\RegisterTransformersEvent $event) {
                $event->transformers['craftcloud'] = CraftCloud::class;
            }
        );
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

}
