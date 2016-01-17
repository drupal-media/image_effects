<?php

/**
 * @file
 * Contains \Drupal\image_effects\Plugin\ImageToolkit\Operation\imagemagick\Watermark.
 */

namespace Drupal\image_effects\Plugin\ImageToolkit\Operation\imagemagick;

use Drupal\imagemagick\Plugin\ImageToolkit\Operation\imagemagick\ImagemagickImageToolkitOperationBase;
use Drupal\image_effects\Plugin\ImageToolkit\Operation\WatermarkTrait;

/**
 * Defines ImageMagick Watermark operation.
 *
 * @ImageToolkitOperation(
 *   id = "image_effects_imagemagick_watermark",
 *   toolkit = "imagemagick",
 *   operation = "watermark",
 *   label = @Translation("Watermark"),
 *   description = @Translation("Add watermark image efect.")
 * )
 */
class Watermark extends ImagemagickImageToolkitOperationBase {

  use WatermarkTrait;

  /**
   * {@inheritdoc}
   */
  protected function execute(array $arguments) {
    // Reset any gravity settings from earlier effects.
    $op = '-gravity None ';

    // Add the overlay image.
    $op .= $this->getToolkit()->escapeShellArg($arguments['watermark_image']->getToolkit()->getSourceLocalPath());

    // Set offset. Offset arguments require a sign in front.
    $x = $arguments['x_offset'] >= 0 ? ('+' . $arguments['x_offset']) : $arguments['x_offset'];
    $y = $arguments['y_offset'] >= 0 ? ('+' . $arguments['y_offset']) : $arguments['y_offset'];
    $op .= " -geometry $x$y";

    // Compose it with the destination.
    if ($arguments['opacity'] == 100) {
      $op .= ' -compose src-over -composite';
    }
    else {
      $op .= " -compose blend -define compose:args={$arguments['opacity']} -composite";
    }

    $this->getToolkit()->addArgument($op);
    return TRUE;
  }

}
