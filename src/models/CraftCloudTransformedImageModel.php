<?php
/**
 * Craft Cloud transformer for Imager X
 *
 * @link      https://www.spacecat.ninja
 * @copyright Copyright (c) 2024 André Elvan
 */

namespace spacecatninja\craftcloudtransformer\models;

use craft\elements\Asset;
use spacecatninja\imagerx\models\BaseTransformedImageModel;
use spacecatninja\imagerx\models\LocalSourceImageModel;
use spacecatninja\imagerx\models\TransformedImageInterface;

class CraftCloudTransformedImageModel extends BaseTransformedImageModel implements TransformedImageInterface
{

    public function __construct(string $imageUrl = null, Asset $source = null, array $transform = [])
    {
        if ($imageUrl !== null) {
            $this->url = $imageUrl;
        }
        
        $mode = $transform['mode'] ?? 'crop';
        
        if (isset($transform['width'], $transform['height'])) {
            $this->width = (int)$transform['width'];
            $this->height = (int)$transform['height'];

            if ($source !== null && $mode === 'fit') {
                [$sourceWidth, $sourceHeight] = $this->getSourceImageDimensions($source);
                
                $transformW = (int)$transform['width'];
                $transformH = (int)$transform['height'];

                if ($sourceWidth !== 0 && $sourceHeight !== 0) {
                    if ($sourceWidth / $sourceHeight > $transformW / $transformH) {
                        $useW = min($transformW, $sourceWidth);
                        $this->width = $useW;
                        $this->height = round($useW * ($sourceHeight / $sourceWidth));
                    } else {
                        $useH = min($transformH, $sourceHeight);
                        $this->width = round($useH * ($sourceWidth / $sourceHeight));
                        $this->height = $useH;
                    }
                }
            }
        } else if (isset($transform['width']) || isset($transform['height'])) {
            if ($source !== null && $transform !== null) {
                [$sourceWidth, $sourceHeight] = $this->getSourceImageDimensions($source);
                [$w, $h] = $this->calculateTargetSize($transform, $sourceWidth, $sourceHeight);

                $this->width = $w;
                $this->height = $h;
            }
        } else {
            // Neither is set, image is not resized. Just get dimensions and return.
            [$sourceWidth, $sourceHeight] = $this->getSourceImageDimensions($source);

            $this->width = $sourceWidth;
            $this->height = $sourceHeight;
        }
    }

    /**
     * Get source dimensions, either from an asset, or an external image.
     */
    protected function getSourceImageDimensions($source): array
    {
        if ($source instanceof Asset) {
            return [$source->getWidth(), $source->getHeight()];
        }

        return [0, 0];
    }

    /**
     * Calculate target size
     */
    protected function calculateTargetSize(array $transform, int $sourceWidth, int $sourceHeight): array
    {
        $ratio = $sourceWidth / $sourceHeight;

        $w = $transform['width'] ?? null;
        $h = $transform['height'] ?? null;

        if ($w) {
            return [$w, round($w / $ratio)];
        }
        if ($h) {
            return [round($h * $ratio), $h];
        }

        return [0, 0];
    }
    
}
