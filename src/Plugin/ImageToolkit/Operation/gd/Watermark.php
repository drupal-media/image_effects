<?php

namespace Drupal\image_effects\Plugin\ImageToolkit\Operation\gd;

use Drupal\system\Plugin\ImageToolkit\Operation\gd\GDImageToolkitOperationBase;
use Drupal\image_effects\Plugin\ImageToolkit\Operation\WatermarkTrait;

/**
 * Defines GD Watermark operation.
 *
 * @ImageToolkitOperation(
 *   id = "image_effects_gd_watermark",
 *   toolkit = "gd",
 *   operation = "watermark",
 *   label = @Translation("Watermark"),
 *   description = @Translation("Add watermark image effect.")
 * )
 */
class Watermark extends GDImageToolkitOperationBase {

  use GDOperationTrait;
  use WatermarkTrait;

  /**
   * {@inheritdoc}
   */
  protected function execute(array $arguments) {
    return $this->imageCopyMergeAlpha(
      $this->getToolkit()->getResource(),
      $arguments['watermark_image']->getToolkit()->getResource(),
      $arguments['x_offset'],
      $arguments['y_offset'],
      0,
      0,
      $arguments['watermark_image']->getToolkit()->getWidth(),
      $arguments['watermark_image']->getToolkit()->getHeight(),
      $arguments['opacity']
    );
  }

}
