<?php

/**
 * @file
 * Auto Orientation effect test case script.
 */

namespace Drupal\image_effects\Tests;

use Drupal\image\Entity\ImageStyle;

/**
 * Auto Orientation effect test.
 *
 * @group Image Effects
 */
class ImageEffectsAutoOrientTest extends ImageEffectsTestBase {

  public static $modules = array('image_effects');

  /**
   * Auto Orientation effect test.
   */
  public function testAutoOrientEffect() {
    $image_factory = $this->container->get('image.factory');

    // Add Auto Orient effect to the test image style.
    $effect = [
      'id' => 'image_effects_auto_orient',
      'data' => [
        'scan_exif' => TRUE,
      ],
    ];
    $this->addEffectToTestStyle($effect);

    // Get expected URIs.
    $test_file = drupal_get_path('module', 'image_effects') . '/misc/portrait-painting.jpg';
    $original_uri = file_unmanaged_copy($test_file, 'public://', FILE_EXISTS_RENAME);
    $generated_uri = 'public://styles/image_effects_test/public/'. \Drupal::service('file_system')->basename($original_uri);

    // Test source image dimensions..
    $image = $image_factory->get($original_uri);
    $this->assertEqual($image->getWidth(), 640);
    $this->assertEqual($image->getHeight(), 480);

    // Load Image Style and get expected derivative URL.
    $image_style = ImageStyle::load('image_effects_test');
    $url = $image_style->buildUrl($original_uri);

    // Check that ::transformDimensions returns expected dimensions.
    $variables = array(
      '#theme' => 'image_style',
      '#style_name' => 'image_effects_test',
      '#uri' => $original_uri,
      '#width' => $image->getWidth(),
      '#height' => $image->getHeight(),
    );
    $this->assertEqual($this->getImageTag($variables), '<img src="' . $url . '" width="480" height="640" alt="" class="image-style-image-effects-test" />');

    // Check that ::applyEffect generates image with expected dimensions.
    $image_style->createDerivative($original_uri, $image_style->buildUri($original_uri));
    $image = $image_factory->get($generated_uri);
    $this->assertEqual($image->getWidth(), 480);
    $this->assertEqual($image->getHeight(), 640);
  }

}
