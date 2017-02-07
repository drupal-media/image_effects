<?php

namespace Drupal\image_effects\Tests;

/**
 * Color Shift effect test.
 *
 * @group Image Effects
 */
class ImageEffectsColorShiftTest extends ImageEffectsTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // @todo This effect does not work on GraphicsMagick.
    $this->imagemagickPackages['graphicsmagick'] = FALSE;
  }

  /**
   * Color Shift effect test.
   */
  public function testColorShiftEffect() {
    // Test operations on toolkits.
    $this->executeTestOnToolkits([$this, 'doTestColorshiftOperations']);
  }

  /**
   * Color Shift operations test.
   */
  public function doTestColorshiftOperations() {
    // Test on the PNG test image.
    $original_uri = $this->getTestImageCopyUri('/files/image-test.png', 'simpletest');

    // Test data.
    $test_data = [
      // Shift to red.
      '#FF0000' => [
        $this->red,
        $this->yellow,
        $this->transparent,
        $this->fuchsia,
      ],
      // Shift to green.
      '#00FF00' => [
        $this->yellow,
        $this->green,
        $this->transparent,
        $this->cyan,
      ],
      // Shift to blue.
      '#0000FF' => [
        $this->fuchsia,
        $this->cyan,
        $this->transparent,
        $this->blue,
      ],
      // Arbitrary shift.
      '#929BEF'  => [
        [255, 155, 239, 0],
        [146, 255, 239, 0],
        $this->transparent,
        [146, 155, 255, 0],
      ],
    ];

    foreach ($test_data as $key => $colors) {
      // Add Color Shift effect to the test image style.
      $effect = [
        'id' => 'image_effects_color_shift',
        'data' => [
          'RGB][hex' => $key,
        ],
      ];
      $uuid = $this->addEffectToTestStyle($effect);

      // Check that ::applyEffect generates image with expected color shift.
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
