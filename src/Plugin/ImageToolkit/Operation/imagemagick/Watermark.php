<?php

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
    // Overlay image path.
    $image_path = $this->getToolkit()->escapeShellArg($arguments['watermark_image']->getToolkit()->getSourceLocalPath());

    // Set the dimensions of the overlay. Use of the scale option means that we
    // need to change the dimensions: always set them, they don't harm when the
    // scale option is not used.
    $w = $arguments['watermark_image']->getWidth();
    $h = $arguments['watermark_image']->getHeight();

    // Set offset. Offset arguments require a sign in front.
    $x = $arguments['x_offset'] >= 0 ? ('+' . $arguments['x_offset']) : $arguments['x_offset'];
    $y = $arguments['y_offset'] >= 0 ? ('+' . $arguments['y_offset']) : $arguments['y_offset'];

    // Compose it with the destination.
    switch ($this->getToolkit()->getPackage()) {
      case 'imagemagick':
        if ($arguments['opacity'] == 100) {
          $op = "-gravity None {$image_path} -geometry {$w}x{$h}{$x}{$y} -compose src-over -composite";
        }
        else {
          $op = "-gravity None {$image_path} -geometry {$w}x{$h}{$x}{$y} -compose blend -define compose:args={$arguments['opacity']} -composite";
        }
        break;

      case 'graphicsmagick':
        // @todo see if GraphicsMagick can support opacity setting.
        $op = "-draw 'image Over {$arguments['x_offset']},{$arguments['y_offset']} {$w},{$h} {$image_path}'";
        break;

    }

    $this->getToolkit()->addArgument($op);
    return TRUE;
  }

}
