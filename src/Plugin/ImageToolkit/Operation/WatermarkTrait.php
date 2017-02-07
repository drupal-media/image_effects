<?php

namespace Drupal\image_effects\Plugin\ImageToolkit\Operation;

use Drupal\Core\Image\ImageInterface;

/**
 * Base trait for image_effects Watermark operations.
 */
trait WatermarkTrait {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return [
      'x_offset' => [
        'description' => 'X offset for watermark image.',
      ],
      'y_offset' => [
        'description' => 'Y offset for watermark image.',
      ],
      'opacity' => [
        'description' => 'Opacity for watermark image.',
        'required' => FALSE,
        'default' => 100,
      ],
      'watermark_image' => [
        'description' => 'Image to use for watermark effect.',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function validateArguments(array $arguments) {
    // Ensure watermark_image opacity is in the range 0-100.
    if ($arguments['opacity'] > 100 || $arguments['opacity'] < 0) {
      throw new \InvalidArgumentException("Invalid opacity ('{$arguments['opacity']}') specified for the image 'watermark' operation");
    }
    // Ensure watermark_image is an expected ImageInterface object.
    if (!$arguments['watermark_image'] instanceof ImageInterface) {
      throw new \InvalidArgumentException("Watermark image passed to the 'watermark' operation is invalid");
    }
    // Ensure watermark_image is a valid image.
    if (!$arguments['watermark_image']->isValid()) {
      $source = $arguments['watermark_image']->getSource();
      throw new \InvalidArgumentException("Invalid image at {$source}");
    }
    return $arguments;
  }

}
