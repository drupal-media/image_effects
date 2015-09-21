<?php

/**
 * @file
 * Color Shift effect test case script.
 */

namespace Drupal\image_effects\Tests;

use Drupal\image\Entity\ImageStyle;

/**
 * Auto Orientation effect test.
 *
 * @group Image Effects
 */
class ImageEffectsColorShiftTest extends ImageEffectsTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // @todo the 'colorshift' operation yields to different results in
    // ImageMagick vs. GD. Once an argument sequence can be determined
    // for ImageMagick to be equal to GD, imagemagick can be tested as
    // well.
    $this->toolkits = ['gd'];
  }

  /**
   * Auto Orientation effect test.
   */
  public function testColorShiftEffect() {
    // Add Color Shift effect to the test image style.
    $effect = [
      'id' => 'image_effects_color_shift',
      'data' => [
        'RGB][hex' => '#FF0000',
      ],
    ];
    $this->addEffectToTestStyle($effect);

    // Test operations on toolkits.
    $this->executeTestOnToolkits([$this, 'doTestColorshiftOperations']);
  }

  /**
   * Auto Orientation operations test.
   */
  public function doTestColorshiftOperations() {
    $image_factory = $this->container->get('image.factory');
    $test_data = [
      // Test on the PNG test image.
      [
        'test_file' => drupal_get_path('module', 'simpletest') . '/files/image-test.png',
        'expected_colors' => [$this->red, $this->yellow, $this->transparent, $this->fuchsia],
      ],
    ];

    foreach ($test_data as $data) {
      // Get expected URIs.
      $original_uri = file_unmanaged_copy($data['test_file'], 'public://', FILE_EXISTS_RENAME);
      $generated_uri = 'public://styles/image_effects_test/public/'. \Drupal::service('file_system')->basename($original_uri);

      // Load Image Style.
      $image_style = ImageStyle::load('image_effects_test');

      // Check that ::applyEffect generates image with expected color shift.
      $image_style->createDerivative($original_uri, $image_style->buildUri($original_uri));
      $image = $image_factory->get($generated_uri, 'gd');
      $this->assertTrue($this->colorsAreEqual($data['expected_colors'][0], $this->getPixelColor($image, 0, 0)));
      $this->assertTrue($this->colorsAreEqual($data['expected_colors'][1], $this->getPixelColor($image, 39, 0)));
      $this->assertTrue($this->colorsAreEqual($data['expected_colors'][2], $this->getPixelColor($image, 0, 19)));
      $this->assertTrue($this->colorsAreEqual($data['expected_colors'][3], $this->getPixelColor($image, 39, 19)));
    }
  }
}
