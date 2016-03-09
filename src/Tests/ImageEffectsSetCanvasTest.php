<?php

namespace Drupal\image_effects\Tests;

use Drupal\image\Entity\ImageStyle;

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
    $this->toolkits = ['gd', 'imagemagick'];
  }

  /**
   * Set canvas effect test.
   */
  public function testColorShiftEffect() {
    // Test operations on toolkits.
    $this->executeTestOnToolkits([$this, 'doTestSetCanvasOperations']);
  }

  /**
   * Set canvas operations test.
   */
  public function doTestSetCanvasOperations() {
    $image_factory = $this->container->get('image.factory');

    $test_file = drupal_get_path('module', 'simpletest') . '/files/image-test.png';
    $original_uri = file_unmanaged_copy($test_file, 'public://', FILE_EXISTS_RENAME);
    $generated_uri = 'public://styles/image_effects_test/public/' . \Drupal::service('file_system')->basename($original_uri);

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

    // Load Image Style.
    $image_style = ImageStyle::load('image_effects_test');

    // Check that ::transformDimensions returns expected dimensions.
    $image = $image_factory->get($original_uri);
    $this->assertEqual(40, $image->getWidth());
    $this->assertEqual(20, $image->getHeight());
    $url = file_url_transform_relative($image_style->buildUrl($original_uri));
    $variables = array(
      '#theme' => 'image_style',
      '#style_name' => 'image_effects_test',
      '#uri' => $original_uri,
      '#width' => $image->getWidth(),
      '#height' => $image->getHeight(),
    );
    $this->assertEqual('<img src="' . $url . '" width="80" height="40" alt="" class="image-style-image-effects-test" />', $this->getImageTag($variables));

    // Check that ::applyEffect generates image with expected canvas.
    $image_style->createDerivative($original_uri, $image_style->buildUri($original_uri));
    $image = $image_factory->get($generated_uri, 'gd');
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

    // Load Image Style.
    $image_style = ImageStyle::load('image_effects_test');

    // Check that ::transformDimensions returns expected dimensions.
    $image = $image_factory->get($original_uri);
    $this->assertEqual(40, $image->getWidth());
    $this->assertEqual(20, $image->getHeight());
    $url = file_url_transform_relative($image_style->buildUrl($original_uri));
    $variables = array(
      '#theme' => 'image_style',
      '#style_name' => 'image_effects_test',
      '#uri' => $original_uri,
      '#width' => $image->getWidth(),
      '#height' => $image->getHeight(),
    );
    $this->assertEqual('<img src="' . $url . '" width="70" height="90" alt="" class="image-style-image-effects-test" />', $this->getImageTag($variables));

    // Check that ::applyEffect generates image with expected canvas.
    $image_style->createDerivative($original_uri, $image_style->buildUri($original_uri));
    $image = $image_factory->get($generated_uri, 'gd');
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
