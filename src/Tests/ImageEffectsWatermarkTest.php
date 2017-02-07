<?php

namespace Drupal\image_effects\Tests;

/**
 * Watermark effect test.
 *
 * @group Image Effects
 */
class ImageEffectsWatermarkTest extends ImageEffectsTestBase {

  /**
   * Watermark effect test.
   */
  public function testWatermarkEffect() {
    // Test operations on toolkits.
    $this->executeTestOnToolkits([$this, 'doTestWatermarkOperations']);
  }

  /**
   * Watermark operations test.
   */
  public function doTestWatermarkOperations() {

    // 1. Basic test.
    $original_uri = $this->getTestImageCopyUri('/files/image-1.png', 'simpletest');
    $derivative_uri = $this->testImageStyle->buildUri($original_uri);

    $watermark_uri = $this->getTestImageCopyUri('/files/image-test.png', 'simpletest');

    $effect = [
      'id' => 'image_effects_watermark',
      'data' => [
        'placement' => 'left-top',
        'x_offset' => 1,
        'y_offset' => 1,
        'opacity' => 100,
        'watermark_image' => $watermark_uri,
      ],
    ];
    $uuid = $this->addEffectToTestStyle($effect);

    // Check that ::applyEffect generates image with expected watermark.
    $this->testImageStyle->createDerivative($original_uri, $derivative_uri);
    $image = $this->imageFactory->get($derivative_uri, 'gd');
    $watermark = $this->imageFactory->get($watermark_uri, 'gd');
    $this->assertFalse($this->colorsAreEqual($this->getPixelColor($watermark, 0, 0), $this->getPixelColor($image, 0, 0)));
    $this->assertTrue($this->colorsAreEqual($this->getPixelColor($watermark, 0, 0), $this->getPixelColor($image, 1, 1)));
    $this->assertTrue($this->colorsAreEqual($this->getPixelColor($watermark, 0, 1), $this->getPixelColor($image, 1, 2)));
    $this->assertTrue($this->colorsAreEqual($this->getPixelColor($watermark, 0, 3), $this->getPixelColor($image, 1, 4)));

    // Remove effect.
    $this->removeEffectFromTestStyle($uuid);

    // 2. Test for scaled watermark. Place a fuchsia watermark scaled to 5%
    // over a sample image and check the color of pixels inside/outside the
    // watermark to see that it was scaled properly.
    $original_uri = $this->getTestImageCopyUri('/files/image-1.png', 'simpletest');
    $derivative_uri = $this->testImageStyle->buildUri($original_uri);

    $watermark_uri = $this->getTestImageCopyUri('/tests/images/fuchsia.png', 'image_effects');

    $effect = [
      'id' => 'image_effects_watermark',
      'data' => [
        'placement' => 'left-top',
        'x_offset' => 0,
        'y_offset' => 0,
        'opacity' => 100,
        'watermark_image' => $watermark_uri,
        'watermark_scale' => 5,
      ],
    ];
    $uuid = $this->addEffectToTestStyle($effect);

    // Check that ::applyEffect generates image with expected watermark.
    $this->testImageStyle->createDerivative($original_uri, $derivative_uri);
    $image = $this->imageFactory->get($derivative_uri, 'gd');
    // GD slightly compresses fuchsia while resampling, so checking color
    // in and out the watermark needs a tolerance.
    $this->assertTrue($this->colorsAreClose($this->getPixelColor($image, 17, 0), $this->fuchsia, 4));
    $this->assertFalse($this->colorsAreClose($this->getPixelColor($image, 19, 0), $this->fuchsia, 4));
    $this->assertTrue($this->colorsAreClose($this->getPixelColor($image, 0, 13), $this->fuchsia, 4));
    $this->assertFalse($this->colorsAreClose($this->getPixelColor($image, 0, 15), $this->fuchsia, 4));

    // Remove effect.
    $this->removeEffectFromTestStyle($uuid);

    // 3. Test for watermark PNG image with full transparency set, 100% opacity
    // watermark.
    $original_uri = $this->getTestImageCopyUri('/tests/images/fuchsia.png', 'image_effects');
    $derivative_uri = $this->testImageStyle->buildUri($original_uri);

    $watermark_uri = $this->getTestImageCopyUri('/files/image-test.png', 'simpletest');

    $effect = [
      'id' => 'image_effects_watermark',
      'data' => [
        'placement' => 'left-top',
        'x_offset' => 0,
        'y_offset' => 0,
        'opacity' => 100,
        'watermark_image' => $watermark_uri,
      ],
    ];
    $uuid = $this->addEffectToTestStyle($effect);

    // Check that ::applyEffect generates image with expected transparency.
    $this->testImageStyle->createDerivative($original_uri, $derivative_uri);
    $image = $this->imageFactory->get($derivative_uri, 'gd');
    $this->assertTrue($this->colorsAreEqual($this->getPixelColor($image, 0, 19), $this->fuchsia));

    // Remove effect.
    $this->removeEffectFromTestStyle($uuid);

    // 4. Test for watermark PNG image with full transparency set, 50% opacity
    // watermark.
    // -----------------------------------------------------------------------
    // Skip on ImageMagick toolkit with GraphicsMagick package selected.
    // @todo see if GraphicsMagick can support opacity setting.
    if ($this->imageFactory->getToolkitId() === 'imagemagick' && \Drupal::configFactory()->get('imagemagick.settings')->get('binaries') === 'graphicsmagick') {
      return;
    }

    $original_uri = $this->getTestImageCopyUri('/tests/images/fuchsia.png', 'image_effects');
    $derivative_uri = $this->testImageStyle->buildUri($original_uri);

    $watermark_uri = $this->getTestImageCopyUri('/files/image-test.png', 'simpletest');

    $effect = [
      'id' => 'image_effects_watermark',
      'data' => [
        'placement' => 'left-top',
        'x_offset' => 0,
        'y_offset' => 0,
        'opacity' => 50,
        'watermark_image' => $watermark_uri,
      ],
    ];
    $uuid = $this->addEffectToTestStyle($effect);

    // Check that ::applyEffect generates image with expected alpha.
    $this->testImageStyle->createDerivative($original_uri, $derivative_uri);
    $image = $this->imageFactory->get($derivative_uri, 'gd');
    $this->assertTrue($this->colorsAreEqual($this->getPixelColor($image, 0, 19), $this->fuchsia));
    // GD and ImageMagick return slightly different colors, use the
    // ::colorsAreClose method.
    $this->assertTrue($this->colorsAreClose($this->getPixelColor($image, 39, 0), $this->grey, 4));

    // Remove effect.
    $this->removeEffectFromTestStyle($uuid);
  }

}
