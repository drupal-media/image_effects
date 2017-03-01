<?php

namespace Drupal\image_effects\Tests;

/**
 * Mirror effect test.
 *
 * @group Image Effects
 */
class ImageEffectsMirrorTest extends ImageEffectsTestBase {

  /**
   * Mirror effect test.
   */
  public function testMirrorEffect() {
    // Test operations on toolkits.
    $this->executeTestOnToolkits([$this, 'doTestMirrorOperations']);
  }

  /**
   * Mirror operations test.
   */
  public function doTestMirrorOperations() {
    // Test on the PNG test image.
    $original_uri = $this->getTestImageCopyUri('/files/image-test.png', 'simpletest');

    // Test data.
    $test_data = [
      // Horizontal mirror.
      'horizontal' => [
        'effect' => [
          'x_axis' => TRUE,
          'y_axis' => FALSE,
        ],
        'expected_text' => 'Mirror horizontal',
        'expected_colors' => [
          $this->green,
          $this->red,
          $this->blue,
          $this->transparent,
        ],
      ],
      // Vertical mirror.
      'vertical' => [
        'effect' => [
          'x_axis' => FALSE,
          'y_axis' => TRUE,
        ],
        'expected_text' => 'Mirror vertical',
        'expected_colors' => [
          $this->transparent,
          $this->blue,
          $this->red,
          $this->green,
        ],
      ],
      // Both horizontal and vertical mirror.
      'both' => [
        'effect' => [
          'x_axis' => TRUE,
          'y_axis' => TRUE,
        ],
        'expected_text' => 'Mirror both horizontal and vertical',
        'expected_colors' => [
          $this->blue,
          $this->transparent,
          $this->green,
          $this->red,
        ],
      ],
    ];

    foreach ($test_data as $data) {
      // Add Mirror effect to the test image style.
      $effect = [
        'id' => 'image_effects_mirror',
        'data' => $data['effect'],
      ];
      $uuid = $this->addEffectToTestStyle($effect);

      // Assert effect summary text.
      $this->assertText($data['expected_text']);

      // Check that ::applyEffect generates image with expected mirror. Colors
      // of the derivative image should be swapped according to the mirror
      // direction.
      $derivative_uri = $this->testImageStyle->buildUri($original_uri);
      $this->testImageStyle->createDerivative($original_uri, $derivative_uri);
      $image = $this->imageFactory->get($derivative_uri, 'gd');
      $this->assertTrue($this->colorsAreEqual($data['expected_colors'][0], $this->getPixelColor($image, 0, 0)));
      $this->assertTrue($this->colorsAreEqual($data['expected_colors'][1], $this->getPixelColor($image, 39, 0)));
      $this->assertTrue($this->colorsAreEqual($data['expected_colors'][2], $this->getPixelColor($image, 0, 19)));
      $this->assertTrue($this->colorsAreEqual($data['expected_colors'][3], $this->getPixelColor($image, 39, 19)));

      // Remove effect.
      $uuid = $this->removeEffectFromTestStyle($uuid);
    }
  }

}
