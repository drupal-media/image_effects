<?php

namespace Drupal\image_effects\Tests;

/**
 * Invert effect test.
 *
 * @group Image Effects
 */
class ImageEffectsInvertTest extends ImageEffectsTestBase {

  /**
   * Invert effect test.
   */
  public function testInvertEffect() {
    // Add Invert effect to the test image style.
    $effect = [
      'id' => 'image_effects_invert',
    ];
    $this->addEffectToTestStyle($effect);

    // Test operations on toolkits.
    $this->executeTestOnToolkits([$this, 'doTestInvertOperations']);
  }

  /**
   * Invert operations test.
   */
  public function doTestInvertOperations() {
    // Test on the PNG test image.
    $original_uri = $this->getTestImageCopyUri('/files/image-test.png', 'simpletest');

    // Expected colors after negate.
    $colors = [
      // Red is converted to cyan.
      $this->cyan,
      // Green is converted to fuchsia.
      $this->fuchsia,
      // Transparent remains transparent.
      $this->transparent,
      // Blue is converted to yellow.
      $this->yellow,
    ];

    // Check that ::applyEffect generates image with inverted colors.
    $derivative_uri = $this->testImageStyle->buildUri($original_uri);
    $this->testImageStyle->createDerivative($original_uri, $derivative_uri);
    $image = $this->imageFactory->get($derivative_uri, 'gd');
    $this->assertTrue($this->colorsAreEqual($colors[0], $this->getPixelColor($image, 0, 0)));
    $this->assertTrue($this->colorsAreEqual($colors[1], $this->getPixelColor($image, 39, 0)));
    $this->assertTrue($this->colorsAreEqual($colors[2], $this->getPixelColor($image, 0, 19)));
    $this->assertTrue($this->colorsAreEqual($colors[3], $this->getPixelColor($image, 39, 19)));
  }

}
