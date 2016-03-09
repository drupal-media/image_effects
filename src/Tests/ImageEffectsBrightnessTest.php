<?php

namespace Drupal\image_effects\Tests;

use Drupal\image\Entity\ImageStyle;

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
    $this->toolkits = ['gd', 'imagemagick'];
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
    $image_factory = $this->container->get('image.factory');

    // Test on the PNG test image.
    $test_file = drupal_get_path('module', 'simpletest') . '/files/image-test.png';
    $original_uri = file_unmanaged_copy($test_file, 'public://', FILE_EXISTS_RENAME);
    $generated_uri = 'public://styles/image_effects_test/public/'. \Drupal::service('file_system')->basename($original_uri);

    // Test data.
    $test_data = [
      '0' => [$this->red, $this->green, $this->transparent, $this->blue],        // No brightness change.
      '-100' => [$this->black, $this->black, $this->transparent, $this->black],  // Adjust brightness by -100%.
      '100' => [$this->white, $this->white, $this->transparent, $this->white],   // Adjust brightness by 100%.
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

      // Load Image Style.
      $image_style = ImageStyle::load('image_effects_test');

      // Check that ::applyEffect generates image with expected brightness.
      $image_style->createDerivative($original_uri, $image_style->buildUri($original_uri));
      $image = $image_factory->get($generated_uri, 'gd');
      $this->assertTrue($this->colorsAreEqual($colors[0], $this->getPixelColor($image, 0, 0)));
      $this->assertTrue($this->colorsAreEqual($colors[1], $this->getPixelColor($image, 39, 0)));
      $this->assertTrue($this->colorsAreEqual($colors[2], $this->getPixelColor($image, 0, 19)));
      $this->assertTrue($this->colorsAreEqual($colors[3], $this->getPixelColor($image, 39, 19)));

      // Remove effect.
      $uuid = $this->removeEffectFromTestStyle($uuid);
    }
  }
}
