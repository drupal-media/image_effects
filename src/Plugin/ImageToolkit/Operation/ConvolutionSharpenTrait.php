<?php

namespace Drupal\image_effects\Plugin\ImageToolkit\Operation;

/**
 * Base trait for image_effects ConvolutionSharpen operations.
 */
trait ConvolutionSharpenTrait {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return [
      'level' => [
        'description' => 'The sharpen level.',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function validateArguments(array $arguments) {
    // Assure sharpen level is valid.
    if (!is_numeric($arguments['level']) || ($arguments['level'] < 0)) {
      throw new \InvalidArgumentException("Invalid level ('{$arguments['level']}') specified for the image 'convolution_sharpen' operation");
    }
    return $arguments;
  }

}
