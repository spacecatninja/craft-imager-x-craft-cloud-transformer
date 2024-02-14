<?php
/**
 * Craft Cloud transformer for Imager X
 *
 * @link      https://www.spacecat.ninja
 * @copyright Copyright (c) 2024 André Elvan
 */

namespace spacecatninja\craftcloudtransformer\transformers;

use Craft;
use craft\base\Component;
use craft\elements\Asset;

use craft\helpers\App;
use spacecatninja\craftcloudtransformer\CraftCloudTransformer;
use spacecatninja\craftcloudtransformer\models\Settings;
use spacecatninja\craftcloudtransformer\models\CraftCloudTransformedImageModel;

use spacecatninja\imagerx\models\ConfigModel;
use spacecatninja\imagerx\services\ImagerService;
use spacecatninja\imagerx\transformers\TransformerInterface;
use spacecatninja\imagerx\exceptions\ImagerException;

class CraftCloud extends Component implements TransformerInterface
{

    public function transform(Asset|string $image, array $transforms): ?array
    {
        $transformedImages = [];

        foreach ($transforms as $transform) {
            $transformedImages[] = $this->getTransformedImage($image, $transform);
        }

        return $transformedImages;
    }

    /**
     * @throws \spacecatninja\imagerx\exceptions\ImagerException
     */
    private function getTransformedImage(Asset $image, array $transform): ?CraftCloudTransformedImageModel
    {
        $config = ImagerService::getConfig();
        /** @var Settings $settings */
        $settings = CraftCloudTransformer::$plugin->getSettings();

        if ($settings === null) {
            return null;
        }

        $params = $this->createParams($transform, $image);

        $url = $image->getUrl($params);

        return new CraftCloudTransformedImageModel($url, $image, $params);
    }

    private function createParams(array $transform, Asset $image): array
    {
        $config = ImagerService::getConfig();

        $r = [];

        $r['upscale'] = $config->allowUpscale;

        if ($config->interlace !== false) {
            if (\is_string($config->interlace)) {
                $r['interlace'] = $config->interlace;
            } else {
                $r['interlace'] = 'line';
            }
        }

        // Set width and height in the return object
        if (isset($transform['width'])) {
            $r['width'] = $transform['width'];
        }

        if (isset($transform['height'])) {
            $r['height'] = $transform['height'];
        }

        // set format
        if (isset($transform['format'])) {
            $r['format'] = $transform['format'];
        }

        // Set quality 
        if (!isset($r['quality'])) {
            if (isset($r['format'])) {
                $r['quality'] = $this->getQualityFromExtension($r['format'], $transform);
            } else {
                $ext = null;

                if ($image instanceof Asset) {
                    $ext = $image->getExtension();
                }

                if (\is_string($image)) {
                    $pathParts = pathinfo($image);
                    $ext = $pathParts['extension'];
                }

                $r['quality'] = $this->getQualityFromExtension($ext, $transform);
            }
        }

        if (isset($transform['mode'])) {
            $mode = $transform['mode'];

            switch ($mode) {
                case 'croponly':
                    Craft::error('The Craft Cloud Transformer does not support mode `croponly`, reverting to `crop`.', __METHOD__);
                    $r['mode'] = 'crop';
                    break;
                case 'letterbox':
                    $r['mode'] = 'letterbox';
                    $letterboxDef = $config->getSetting('letterbox', $transform);
                    $r['fill'] = $this->getLetterboxColor($letterboxDef);
                    break;
                default:
                    $r['mode'] = $mode;
                    break;
            }
        }

        return $r;
    }

    /**
     * Gets the quality setting based on the extension.
     *
     * @param string     $ext
     * @param array|null $transform
     *
     * @return string
     */
    private function getQualityFromExtension(string $ext, array $transform = null): string
    {
        /** @var ConfigModel $settings */
        $config = ImagerService::getConfig();

        switch ($ext) {
            case 'png':
                $pngCompression = $config->getSetting('pngCompressionLevel', $transform);

                return max(100 - ($pngCompression * 10), 1);
            case 'webp':
                return $config->getSetting('webpQuality', $transform);
            case 'avif':
                return $config->getSetting('avifQuality', $transform);
            case 'jxl':
                return $config->getSetting('jxlQuality', $transform);
        }

        return $config->getSetting('jpegQuality', $transform);
    }

    /**
     * Translate letterbox params to correct format.
     *
     * ImageKit uses a weird RGBA veriant where the last two digits should be
     * the opacity between 00 and 99.
     */
    private function getLetterboxColor($letterboxDef): string
    {
        $color = $letterboxDef['color'] ?? '#000000';

        // Craft does not support opacity

        return $color;
    }

}
