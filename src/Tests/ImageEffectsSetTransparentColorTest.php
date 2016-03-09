<?php

namespace Drupal\image_effects\Tests;

use Drupal\image\Entity\ImageStyle;

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
    $this->toolkits = ['gd', 'imagemagick'];
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
    $image_factory = $this->container->get('image.factory');

    // Test on the GIF test image.
    $test_file = drupal_get_path('module', 'simpletest') . '/files/image-test.gif';
    $original_uri = file_unmanaged_copy($test_file, 'public://', FILE_EXISTS_RENAME);
    $generated_uri = 'public://styles/image_effects_test/public/'. \Drupal::service('file_system')->basename($original_uri);

    // Test data.
    $test_data = [
      '#FF0000' => [$this->transparent, $this->green, $this->yellow, $this->blue],
      '#00FF00' => [$this->red, $this->transparent, $this->yellow, $this->blue],
      '#0000FF' => [$this->red, $this->green, $this->yellow, $this->transparent],
      ''  => [$this->red, $this->green, $this->transparent, $this->blue],
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

      // Load Image Style.
      $image_style = ImageStyle::load('image_effects_test');

      // Check that ::applyEffect generates image with expected transparent
      // color. GD slightly compresses GIF colors so we use the
      // ::colorsAreClose method for testing.
      $image_style->createDerivative($original_uri, $image_style->buildUri($original_uri));
      $image = $image_factory->get($generated_uri, 'gd');
      $this->assertTrue($this->colorsAreClose($colors[0], $this->getPixelColor($image, 0, 0), 40));
      $this->assertTrue($this->colorsAreClose($colors[1], $this->getPixelColor($image, 39, 0), 40));
      $this->assertTrue($this->colorsAreClose($colors[2], $this->getPixelColor($image, 0, 19), 40));
      $this->assertTrue($this->colorsAreClose($colors[3], $this->getPixelColor($image, 39, 19), 40));

      // Remove effect.
      $uuid = $this->removeEffectFromTestStyle($uuid);
    }
  }
}
