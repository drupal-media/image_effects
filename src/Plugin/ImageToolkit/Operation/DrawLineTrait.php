<?php

namespace Drupal\image_effects\Plugin\ImageToolkit\Operation;

/**
 * Base trait for image_effects DrawLine operations.
 */
trait DrawLineTrait {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'x1' => array(
        'description' => 'x-coordinate for first point.',
      ),
      'y1' => array(
        'description' => 'y-coordinate for first point.',
      ),
      'x2' => array(
        'description' => 'x-coordinate for second point.',
      ),
      'y2' => array(
        'description' => 'y-coordinate for second point.',
      ),
      'color' => array(
        'description' => 'The line color, in RGBA format.',
      ),
    );
  }

}
