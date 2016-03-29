<?php

namespace Drupal\image_effects\Plugin\ImageToolkit\Operation;

/**
 * Base trait for image_effects DrawEllipse operations.
 */
trait DrawEllipseTrait {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return array(
      'cx' => array(
        'description' => 'x-coordinate of the center.',
      ),
      'cy' => array(
        'description' => 'y-coordinate of the center.',
      ),
      'width' => array(
        'description' => 'The ellipse width.',
      ),
      'height' => array(
        'description' => 'The ellipse height.',
      ),
      'color' => array(
        'description' => 'The fill color, in RGBA format.',
      ),
    );
  }

}
