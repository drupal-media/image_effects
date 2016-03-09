<?php

namespace Drupal\image_effects\Tests;

use Drupal\image\Entity\ImageStyle;

/**
 * Auto Orientation effect test.
 *
 * @group Image Effects
 */
class ImageEffectsAutoOrientTest extends ImageEffectsTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->toolkits = ['gd', 'imagemagick'];
  }

  /**
   * Auto Orientation effect test.
   */
  public function testAutoOrientEffect() {
    // Add Auto Orient effect to the test image style.
    $effect = [
      'id' => 'image_effects_auto_orient',
      'data' => [
        'scan_exif' => TRUE,
      ],
    ];
    $this->addEffectToTestStyle($effect);

    // Add a scale effect too.
    $effect = [
      'id' => 'image_scale',
      'data' => [
        'width' => 200,
        'upscale' => TRUE,
      ],
    ];
    $this->addEffectToTestStyle($effect);

    // Test operations on toolkits.
    $this->executeTestOnToolkits([$this, 'doTestAutoOrientOperations']);
  }

  /**
   * Auto Orientation operations test.
   */
  public function doTestAutoOrientOperations() {
    $image_factory = $this->container->get('image.factory');
    $test_data = [
      // Test a JPEG image with EXIF data.
      [
        'test_file' => drupal_get_path('module', 'image_effects') . '/tests/images/portrait-painting.jpg',
        'original_width' => 640,
        'original_height' => 480,
        'derivative_width' => 200,
        'derivative_height' => 267,
      ],
      // Test a JPEG image without EXIF data.
      [
        'test_file' => drupal_get_path('module', 'simpletest') . '/files/image-test.jpg',
        'original_width' => 40,
        'original_height' => 20,
        'derivative_width' => 200,
        'derivative_height' => 100,
      ],
      // Test a non-EXIF image.
      [
        'test_file' => drupal_get_path('module', 'simpletest') . '/files/image-1.png',
        'original_width' => 360,
        'original_height' => 240,
        'derivative_width' => 200,
        'derivative_height' => 133,
      ],
    ];

    foreach ($test_data as $data) {
      // Get expected URIs.
      $original_uri = file_unmanaged_copy($data['test_file'], 'public://', FILE_EXISTS_RENAME);
      $generated_uri = 'public://styles/image_effects_test/public/'. \Drupal::service('file_system')->basename($original_uri);

      // Test source image dimensions.
      $image = $image_factory->get($original_uri);
      $this->assertEqual($data['original_width'], $image->getWidth());
      $this->assertEqual($data['original_height'], $image->getHeight());

      // Load Image Style and get expected derivative URL.
      $image_style = ImageStyle::load('image_effects_test');
      $url = file_url_transform_relative($image_style->buildUrl($original_uri));

      // Check that ::transformDimensions returns expected dimensions.
      $variables = array(
        '#theme' => 'image_style',
        '#style_name' => 'image_effects_test',
        '#uri' => $original_uri,
        '#width' => $image->getWidth(),
        '#height' => $image->getHeight(),
      );
      $this->assertEqual('<img src="' . $url . '" width="' . $data['derivative_width'] . '" height="' . $data['derivative_height'] . '" alt="" class="image-style-image-effects-test" />', $this->getImageTag($variables));

      // Check that ::applyEffect generates image with expected dimensions.
      $image_style->createDerivative($original_uri, $image_style->buildUri($original_uri));
      $image = $image_factory->get($generated_uri);
      $this->assertEqual($data['derivative_width'], $image->getWidth());
      $this->assertEqual($data['derivative_height'], $image->getHeight());
    }
  }
}
