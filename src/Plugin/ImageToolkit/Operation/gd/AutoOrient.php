<?php

namespace Drupal\image_effects\Plugin\ImageToolkit\Operation\gd;

use Drupal\system\Plugin\ImageToolkit\Operation\gd\GDImageToolkitOperationBase;

/**
 * Defines GD AutoOrient operation.
 *
 * @ImageToolkitOperation(
 *   id = "image_effects_gd_auto_orient",
 *   toolkit = "gd",
 *   operation = "auto_orient",
 *   label = @Translation("Auto orient image"),
 *   description = @Translation("Automatically adjusts the orientation of an image.")
 * )
 */
class AutoOrient extends GDImageToolkitOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    // This operation does not use any parameters.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(array $arguments) {
    // If image has been created in memory, this will not apply.
    if (!$source_path = $this->getToolkit()->getSource()) {
      return TRUE;
    }

    // Will not work without EXIF extension installed.
    if (!function_exists('exif_read_data')) {
      $this->logger->notice('The image %file could not be auto-rotated because the exif_read_data() function is not available in this PHP installation. Check if the PHP EXIF extension is enabled.', array('%file' => $this->getToolkit()->getSource()));
      return FALSE;
    }

    // Read EXIF data.
    $exif = @exif_read_data(\Drupal::service('file_system')->realpath($source_path));
    if (isset($exif['Orientation'])) {
      // http://www.sno.phy.queensu.ca/~phil/exiftool/TagNames/EXIF.html:
      // 1 = Horizontal (normal).
      // 2 = Mirror horizontal.
      // 3 = Rotate 180.
      // 4 = Mirror vertical.
      // 5 = Mirror horizontal and rotate 270 CW.
      // 6 = Rotate 90 CW.
      // 7 = Mirror horizontal and rotate 90 CW.
      // 8 = Rotate 270 CW.
      // @todo: Add horizontal and vertical flips etc.
      // imagecopy seems to be able to mirror, see conmments on
      // http://php.net/manual/en/function.imagecopy.php
      // @todo: Create sample set for tests.
      switch ($exif['Orientation']) {
        case 3:
          $degrees = 180;
          break;

        case 6:
          $degrees = 90;
          break;

        case 8:
          $degrees = 270;
          break;

        default:
          $degrees = 0;
      }
      if ($degrees != 0) {
        return $this->getToolkit()->apply('rotate', ['degrees' => $degrees]);
      }
    }
    return TRUE;
  }

}
