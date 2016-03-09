<?php

namespace Drupal\image_effects\Tests;

use Drupal\image\Entity\ImageStyle;

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
    $this->toolkits = ['gd', 'imagemagick'];
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
    $image_factory = $this->container->get('image.factory');

    // Test on the PNG test image.
    $test_file = drupal_get_path('module', 'simpletest') . '/files/image-test.png';
    $original_uri = file_unmanaged_copy($test_file, 'public://', FILE_EXISTS_RENAME);
    $generated_uri = 'public://styles/image_effects_test/public/'. \Drupal::service('file_system')->basename($original_uri);

    // Test data.
    $test_data = [
      '#FF0000' => [$this->red, $this->yellow, $this->transparent, $this->fuchsia],  // Shift to red.
      '#00FF00' => [$this->yellow, $this->green, $this->transparent, $this->cyan],   // Shift to green.
      '#0000FF' => [$this->fuchsia, $this->cyan, $this->transparent, $this->blue],   // Shift to blue.
      '#929BEF'  => [  // Arbitrary shift.
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

      // Load Image Style.
      $image_style = ImageStyle::load('image_effects_test');

      // Check that ::applyEffect generates image with expected color shift.
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
