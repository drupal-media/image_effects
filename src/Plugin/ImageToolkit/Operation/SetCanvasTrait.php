<?php

namespace Drupal\image_effects\Plugin\ImageToolkit\Operation;

/**
 * Base trait for set canvas operations.
 */
trait SetCanvasTrait {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'canvas_color' => array(
        'description' => 'Color',
        'required' => FALSE,
        'default' => NULL,
      ),
      'width' => array(
        'description' => 'The width of the canvas image, in pixels',
      ),
      'height' => array(
        'description' => 'The height of the canvas image, in pixels',
      ),
      'x_pos' => array(
        'description' => 'The left offset of the original image on the canvas, in pixels',
      ),
      'y_pos' => array(
        'description' => 'The top offset of the original image on the canvas, in pixels',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function validateArguments(array $arguments) {
    return $arguments;
  }

}
