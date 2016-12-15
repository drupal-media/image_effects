<?php

namespace Drupal\image_effects\Tests;

/**
 * Strip metadata effect test.
 *
 * @group Image Effects
 */
class ImageEffectsStripMetadataTest extends ImageEffectsTestBase {

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
    $test_data = [
      // Test a JPEG image with EXIF data.
      [
        'test_file' => $this->getTestImageCopyUri('/tests/images/portrait-painting.jpg', 'image_effects'),
        'original_orientation' => 8,
      ],
      // Test a JPEG image without EXIF data.
      [
        'test_file' => $this->getTestImageCopyUri('/files/image-test.jpg', 'simpletest'),
        'original_orientation' => NULL,
      ],
      // Test a non-EXIF image.
      [
        'test_file' => $this->getTestImageCopyUri('/files/image-1.png', 'simpletest'),
        'original_orientation' => NULL,
      ],
    ];

    foreach ($test_data as $data) {
      // Get expected URIs.
      $original_uri = $data['test_file'];
      $derivative_uri = $this->testImageStyle->buildUri($original_uri);

      // Test source image EXIF data.
      $exif = @exif_read_data(\Drupal::service('file_system')->realpath($original_uri));
      $this->assertEqual($data['original_orientation'], isset($exif['Orientation']) ? $exif['Orientation'] : NULL);

      // Process source image.
      $this->testImageStyle->createDerivative($original_uri, $derivative_uri);

      // Check that ::applyEffect strips EXIF metadata.
      $exif = @exif_read_data(\Drupal::service('file_system')->realpath($derivative_uri));
      $this->assertEqual(NULL, isset($exif['Orientation']) ? $exif['Orientation'] : NULL);
    }
  }

}
