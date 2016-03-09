<?php

namespace Drupal\image_effects\Tests;

use Drupal\image\Entity\ImageStyle;

/**
 * Strip metadata effect test.
 *
 * @group Image Effects
 */
class ImageEffectsStripMetadataTest extends ImageEffectsTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->toolkits = ['gd', 'imagemagick'];
  }

  /**
   * Strip metadata effect test.
   */
  public function testStripMetadataEffect() {
    // Add Strip metadata effect to the test image style.
    $effect = [
      'id' => 'image_effects_strip_metadata',
    ];
    $this->addEffectToTestStyle($effect);

    // Test operations on toolkits.
    $this->executeTestOnToolkits([$this, 'doTestStripMetadataOperations']);
  }

  /**
   * Strip metadata operations test.
   */
  public function doTestStripMetadataOperations() {
    $image_factory = $this->container->get('image.factory');
    $test_data = [
      // Test a JPEG image with EXIF data.
      [
        'test_file' => drupal_get_path('module', 'image_effects') . '/tests/images/portrait-painting.jpg',
        'original_orientation' => 8,
      ],
      // Test a JPEG image without EXIF data.
      [
        'test_file' => drupal_get_path('module', 'simpletest') . '/files/image-test.jpg',
        'original_orientation' => NULL,
      ],
      // Test a non-EXIF image.
      [
        'test_file' => drupal_get_path('module', 'simpletest') . '/files/image-1.png',
        'original_orientation' => NULL,
      ],
    ];

    foreach ($test_data as $data) {
      // Get expected URIs.
      $test_file = drupal_get_path('module', 'image_effects') . '/tests/images/portrait-painting.jpg';
      $original_uri = file_unmanaged_copy($data['test_file'], 'public://', FILE_EXISTS_RENAME);
      $generated_uri = 'public://styles/image_effects_test/public/'. \Drupal::service('file_system')->basename($original_uri);

      // Test source image EXIF data.
      $exif = @exif_read_data(\Drupal::service('file_system')->realpath($original_uri));
      $this->assertEqual($data['original_orientation'], isset($exif['Orientation']) ? $exif['Orientation'] : NULL);

      // Load Image Style and process source image.
      $image_style = ImageStyle::load('image_effects_test');
      $image_style->createDerivative($original_uri, $image_style->buildUri($original_uri));

      // Check that ::applyEffect strips EXIF metadata.
      $exif = @exif_read_data(\Drupal::service('file_system')->realpath($generated_uri));
      $this->assertEqual(NULL, isset($exif['Orientation']) ? $exif['Orientation'] : NULL);
    }
  }
}
