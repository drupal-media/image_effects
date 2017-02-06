<?php

namespace Drupal\image_effects\Tests;

/**
 * Opacity effect test.
 *
 * @group Image Effects
 */
class ImageEffectsOpacityTest extends ImageEffectsTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // @todo This effect does not work on GraphicsMagick.
    $this->imagemagickPackages['graphicsmagick'] = FALSE;
  }

  /**
   * Opacity effect test.
   */
  public function testOpacityEffect() {
    // Test operations on toolkits.
    $this->executeTestOnToolkits([$this, 'doTestOpacityOperations']);
  }

  /**
   * Opacity operations test.
   */
  public function doTestOpacityOperations() {
    // Test on the PNG test image.
    $original_uri = $this->getTestImageCopyUri('/files/image-test.png', 'simpletest');

    // Test data.
    $test_data = [
      // No transparency change.
      '100' => [
        $this->red,
        $this->green,
        $this->transparent,
        $this->blue,
      ],
      // 50% transparency.
      '50' => [
        [255, 0, 0, 63],
        [0, 255, 0, 63],
        $this->transparent,
        [0, 0, 255, 63],
      ],
      // 100% transparency.
      '0' => [
        $this->transparent,
        $this->transparent,
        $this->transparent,
        $this->transparent,
      ],
    ];

    foreach ($test_data as $opacity => $colors) {
      // Add Opacity effect to the test image style.
      $effect = [
        'id' => 'image_effects_opacity',
        'data' => [
          'opacity' => $opacity,
        ],
      ];
      $uuid = $this->addEffectToTestStyle($effect);

      // Check that ::applyEffect generates image with expected opacity.
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
