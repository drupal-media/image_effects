<?php

namespace Drupal\image_effects\Tests;

/**
 * Set canvas effect test.
 *
 * @group Image Effects
 */
class ImageEffectsSetCanvasTest extends ImageEffectsTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // @todo This effect does not work on GraphicsMagick.
    $this->imagemagickPackages['graphicsmagick'] = FALSE;
  }

  /**
   * Set canvas effect test.
   */
  public function testSetCanvasEffect() {
    // Test operations on toolkits.
    $this->executeTestOnToolkits([$this, 'doTestSetCanvasOperations']);
  }

  /**
   * Set canvas operations test.
   */
  public function doTestSetCanvasOperations() {
    $original_uri = $this->getTestImageCopyUri('/files/image-test.png', 'simpletest');
    $derivative_uri = $this->testImageStyle->buildUri($original_uri);

    // Test EXACT size canvas.
    $effect = [
      'id' => 'image_effects_set_canvas',
      'data' => [
        'canvas_size' => 'exact',
        'canvas_color][container][transparent' => FALSE,
        'canvas_color][container][hex' => '#FF00FF',
        'canvas_color][container][opacity' => 100,
        'exact][width][c0][c1][value' => 200,
        'exact][width][c0][c1][uom' => 'perc',
        'exact][height][c0][c1][value' => 200,
        'exact][height][c0][c1][uom' => 'perc',
      ],
    ];
    $uuid = $this->addEffectToTestStyle($effect);

    // Check that ::transformDimensions returns expected dimensions.
    $image = $this->imageFactory->get($original_uri);
    $this->assertEqual(40, $image->getWidth());
    $this->assertEqual(20, $image->getHeight());
    $derivative_url = file_url_transform_relative($this->testImageStyle->buildUrl($original_uri));
    $variables = [
      '#theme' => 'image_style',
      '#style_name' => 'image_effects_test',
      '#uri' => $original_uri,
      '#width' => $image->getWidth(),
      '#height' => $image->getHeight(),
    ];
    $this->assertEqual('<img src="' . $derivative_url . '" width="80" height="40" alt="" class="image-style-image-effects-test" />', $this->getImageTag($variables));

    // Check that ::applyEffect generates image with expected canvas.
    $this->testImageStyle->createDerivative($original_uri, $derivative_uri);
    $image = $this->imageFactory->get($derivative_uri, 'gd');
    $this->assertEqual(80, $image->getWidth());
    $this->assertEqual(40, $image->getHeight());
    $this->assertTrue($this->colorsAreEqual($this->fuchsia, $this->getPixelColor($image, 0, 0)));
    $this->assertTrue($this->colorsAreEqual($this->fuchsia, $this->getPixelColor($image, 79, 0)));
    $this->assertTrue($this->colorsAreEqual($this->fuchsia, $this->getPixelColor($image, 0, 39)));
    $this->assertTrue($this->colorsAreEqual($this->fuchsia, $this->getPixelColor($image, 79, 39)));

    // Remove effect.
    $this->removeEffectFromTestStyle($uuid);

    // Test RELATIVE size canvas.
    $effect = [
      'id' => 'image_effects_set_canvas',
      'data' => [
        'canvas_size' => 'relative',
        'canvas_color][container][transparent' => FALSE,
        'canvas_color][container][hex' => '#FFFF00',
        'canvas_color][container][opacity' => 100,
        'relative][right' => 10,
        'relative][left' => 20,
        'relative][top' => 30,
        'relative][bottom' => 40,
      ],
    ];
    $uuid = $this->addEffectToTestStyle($effect);

    // Check that ::transformDimensions returns expected dimensions.
    $image = $this->imageFactory->get($original_uri);
    $this->assertEqual(40, $image->getWidth());
    $this->assertEqual(20, $image->getHeight());
    $derivative_url = file_url_transform_relative($this->testImageStyle->buildUrl($original_uri));
    $variables = [
      '#theme' => 'image_style',
      '#style_name' => 'image_effects_test',
      '#uri' => $original_uri,
      '#width' => $image->getWidth(),
      '#height' => $image->getHeight(),
    ];
    $this->assertEqual('<img src="' . $derivative_url . '" width="70" height="90" alt="" class="image-style-image-effects-test" />', $this->getImageTag($variables));

    // Check that ::applyEffect generates image with expected canvas.
    $this->testImageStyle->createDerivative($original_uri, $derivative_uri);
    $image = $this->imageFactory->get($derivative_uri, 'gd');
    $this->assertEqual(70, $image->getWidth());
    $this->assertEqual(90, $image->getHeight());
    $this->assertTrue($this->colorsAreEqual($this->yellow, $this->getPixelColor($image, 0, 0)));
    $this->assertTrue($this->colorsAreEqual($this->yellow, $this->getPixelColor($image, 69, 0)));
    $this->assertTrue($this->colorsAreEqual($this->yellow, $this->getPixelColor($image, 0, 89)));
    $this->assertTrue($this->colorsAreEqual($this->yellow, $this->getPixelColor($image, 69, 89)));

    // Remove effect.
    $this->removeEffectFromTestStyle($uuid);
  }

}
