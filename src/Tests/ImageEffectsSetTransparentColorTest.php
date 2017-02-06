<?php

namespace Drupal\image_effects\Tests;

/**
 * Set transparent color effect test.
 *
 * @group Image Effects
 */
class ImageEffectsSetTransparentColorTest extends ImageEffectsTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // @todo This effect does not work on GraphicsMagick.
    $this->imagemagickPackages['graphicsmagick'] = FALSE;
  }

  /**
   * Set transparent color effect test.
   */
  public function testSetTransparentColorEffect() {
    // Test operations on toolkits.
    $this->executeTestOnToolkits([$this, 'doTestColorshiftOperations']);
  }

  /**
   * Set transparent color operations test.
   */
  public function doTestColorshiftOperations() {
    // Test on the GIF test image.
    $original_uri = $this->getTestImageCopyUri('/files/image-test.gif', 'simpletest');
    $derivative_uri = $this->testImageStyle->buildUri($original_uri);

    // Test data.
    $test_data = [
      '#FF0000' => [
        $this->transparent,
        $this->green,
        $this->yellow,
        $this->blue,
      ],
      '#00FF00' => [
        $this->red,
        $this->transparent,
        $this->yellow,
        $this->blue,
      ],
      '#0000FF' => [
        $this->red,
        $this->green,
        $this->yellow,
        $this->transparent,
      ],
      ''  => [
        $this->red,
        $this->green,
        $this->transparent,
        $this->blue,
      ],
    ];

    foreach ($test_data as $key => $colors) {
      // Add Set transparent color effect to the test image style.
      $effect = [
        'id' => 'image_effects_set_transparent_color',
        'data' => [
          'transparent_color][container][transparent' => empty($key) ? TRUE : FALSE,
          'transparent_color][container][hex' => $key,
        ],
      ];
      $uuid = $this->addEffectToTestStyle($effect);

      // Check that ::applyEffect generates image with expected transparent
      // color. GD slightly compresses GIF colors so we use the
      // ::colorsAreClose method for testing.
      $this->testImageStyle->createDerivative($original_uri, $derivative_uri);
      $image = $this->imageFactory->get($derivative_uri, 'gd');
      $this->assertTrue($this->colorsAreClose($colors[0], $this->getPixelColor($image, 0, 0), 40));
      $this->assertTrue($this->colorsAreClose($colors[1], $this->getPixelColor($image, 39, 0), 40));
      $this->assertTrue($this->colorsAreClose($colors[2], $this->getPixelColor($image, 0, 19), 40));
      $this->assertTrue($this->colorsAreClose($colors[3], $this->getPixelColor($image, 39, 19), 40));

      // Remove effect.
      $uuid = $this->removeEffectFromTestStyle($uuid);
    }
  }

}
