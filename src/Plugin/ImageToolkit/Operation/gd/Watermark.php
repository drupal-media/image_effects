<?php

/**
 * @file
 * Contains \Drupal\image_effects\Plugin\ImageToolkit\Operation\gd\Watermark.
 */

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
 *   description = @Translation("Add watermark image efect.")
 * )
 */
class Watermark extends GDImageToolkitOperationBase {

  use WatermarkTrait;

  /**
   * {@inheritdoc}
   */
  protected function execute(array $arguments) {
    if ($arguments['opacity'] === 100) {
      // Use imagecopy() if watermark opacity is 100%.
      return imagecopy(
        $this->getToolkit()->getResource(),
        $arguments['watermark_image']->getToolkit()->getResource(),
        $arguments['x_offset'],
        $arguments['y_offset'],
        0,
        0,
        $arguments['watermark_image']->getToolkit()->getWidth(),
        $arguments['watermark_image']->getToolkit()->getHeight()
      );
    }
    else {
      // If opacity is below 100%, use the approach described in
      // http://php.net/manual/it/function.imagecopymerge.php#92787
      // to preserve watermark alpha.

      // Create a cut resource.
      // @todo when #2583041 is committed, add a check for memory
      // availability before crating the resource.
      $cut = imagecreatetruecolor(
        $arguments['watermark_image']->getToolkit()->getWidth(),
        $arguments['watermark_image']->getToolkit()->getHeight()
      );
      if (!is_resource($cut)) {
        return FALSE;
      }

      // Copy relevant section from destination image to the cut resource.
      $success = imagecopy(
        $cut,
        $this->getToolkit()->getResource(),
        0,
        0,
        $arguments['x_offset'],
        $arguments['y_offset'],
        $arguments['watermark_image']->getToolkit()->getWidth(),
        $arguments['watermark_image']->getToolkit()->getHeight()
      );
      if (!$success) {
        imagedestroy($cut);
        return FALSE;
      }

      // Copy relevant section from watermark image to the cut resource.
      $success = imagecopy(
        $cut,
        $arguments['watermark_image']->getToolkit()->getResource(),
        0,
        0,
        0,
        0,
        $arguments['watermark_image']->getToolkit()->getWidth(),
        $arguments['watermark_image']->getToolkit()->getHeight()
      );
      if (!$success) {
        imagedestroy($cut);
        return FALSE;
      }

      // Insert cut resource to destination image.
      $success = imagecopymerge(
        $this->getToolkit()->getResource(),
        $cut,
        $arguments['x_offset'],
        $arguments['y_offset'],
        0,
        0,
        $arguments['watermark_image']->getToolkit()->getWidth(),
        $arguments['watermark_image']->getToolkit()->getHeight(),
        $arguments['opacity']
      );
      imagedestroy($cut);
      return $success;
    }
  }

}
