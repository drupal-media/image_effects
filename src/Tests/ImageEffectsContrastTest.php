<?php

/**
 * @file
 * Contrast effect test case script.
 */

namespace Drupal\image_effects\Tests;

use Drupal\image\Entity\ImageStyle;

/**
 * Contrast effect test.
 *
 * @group Image Effects
 */
class ImageEffectsContrastTest extends ImageEffectsTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->toolkits = ['gd', 'imagemagick'];
  }

  /**
   * Contrast effect test.
   */
  public function testContrastEffect() {
    // Test operations on toolkits.
    $this->executeTestOnToolkits([$this, 'doTestContrastOperations']);
  }

  /**
   * Contrast operations test.
   */
  public function doTestContrastOperations() {
    $image_factory = $this->container->get('image.factory');
    $image_toolkit_id = $image_factory->getToolkitId();

    // Test on the PNG test image.
    $test_file = drupal_get_path('module', 'simpletest') . '/files/image-test.png';
    $original_uri = file_unmanaged_copy($test_file, 'public://', FILE_EXISTS_RENAME);
    $generated_uri = 'public://styles/image_effects_test/public/'. \Drupal::service('file_system')->basename($original_uri);

    // Test data.
    $test_data = [
      // No contrast change.
      '0' => [
        $this->red,
        $this->green,
        $this->transparent,
        $this->blue,
      ],

      // Adjust contrast by -50%.
      '-50' => [
        $image_toolkit_id === 'imagemagick' ? array(180, 75, 75, 0) : array(159, 95, 95, 0),
        $image_toolkit_id === 'imagemagick' ? array(75, 180, 75, 0) : array(95, 159, 95, 0),
        $this->transparent,
        $image_toolkit_id === 'imagemagick' ? array(75, 75, 180, 0) : array(95, 95, 159, 0),
      ],

      // Adjust contrast by -100%.
      '-100' => [
        $image_toolkit_id === 'imagemagick' ? array(128, 128, 128, 0) : array(127, 127, 127, 0),
        $image_toolkit_id === 'imagemagick' ? array(128, 128, 128, 0) : array(127, 127, 127, 0),
        $this->transparent,
        $image_toolkit_id === 'imagemagick' ? array(128, 128, 128, 0) : array(127, 127, 127, 0),
      ],

      // Adjust contrast by 50%.
      '50' => [
        array(255, 0, 0, 0),
        array(0, 255, 0, 0),
        $this->transparent,
        array(0, 0, 255, 0),
      ],

      // Adjust contrast by 100%.
      '100' => [
        array(255, 0, 0, 0),
        array(0, 255, 0, 0),
        $this->transparent,
        array(0, 0, 255, 0),
      ],
    ];

    foreach ($test_data as $key => $colors) {
      // Add contrast effect to the test image style.
      $effect = [
        'id' => 'image_effects_contrast',
        'data' => [
          'level' => $key,
        ],
      ];
      $uuid = $this->addEffectToTestStyle($effect);

      // Load Image Style.
      $image_style = ImageStyle::load('image_effects_test');

      // Check that ::applyEffect generates image with expected contrast.
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
