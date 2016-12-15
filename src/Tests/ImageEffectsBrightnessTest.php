<?php

namespace Drupal\image_effects\Tests;

/**
 * Brightness effect test.
 *
 * @group Image Effects
 */
class ImageEffectsBrightnessTest extends ImageEffectsTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // @todo This effect does not work on GraphicsMagick.
    $this->imagemagickPackages['graphicsmagick'] = FALSE;
  }

  /**
   * Brightness effect test.
   */
  public function testBrightnessEffect() {
    // Test operations on toolkits.
    $this->executeTestOnToolkits([$this, 'doTestBrightnessOperations']);
  }

  /**
   * Brightness operations test.
   */
  public function doTestBrightnessOperations() {
    // Test on the PNG test image.
    $original_uri = $this->getTestImageCopyUri('/files/image-test.png', 'simpletest');

    // Test data.
    $test_data = [
      // No brightness change.
      '0' => [$this->red, $this->green, $this->transparent, $this->blue],
      // Adjust brightness by -100%.
      '-100' => [$this->black, $this->black, $this->transparent, $this->black],
      // Adjust brightness by 100%.
      '100' => [$this->white, $this->white, $this->transparent, $this->white],
    ];

    foreach ($test_data as $key => $colors) {
      // Add Brightness effect to the test image style.
      $effect = [
        'id' => 'image_effects_brightness',
        'data' => [
          'level' => $key,
        ],
      ];
      $uuid = $this->addEffectToTestStyle($effect);

      // Check that ::applyEffect generates image with expected brightness.
      $derivative_uri = $this->testImageStyle->buildUri($original_uri);
      $this->testImageStyle->createDerivative($original_uri, $derivative_uri);
      $image = $this->imageFactory->get($derivative_uri, 'gd');
      $this->assertTrue($this->colorsAreEqual($colors[0], $this->getPixelColor($image, 0, 0)));
      $this->assertTrue($this->colorsAreEqual($colors[1], $this->getPixelColor($image, 39, 0)));
      $this->assertTrue($this->colorsAreEqual($colors[2], $this->getPixelColor($image, 0, 19)));
      $this->assertTrue($this->colorsAreEqual($colors[3], $this->getPixelColor($image, 39, 19)));

      // Remove effect.
      $uuid = $this->removeEffectFromTestStyle($uuid);
    }
  }

}
