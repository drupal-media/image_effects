<?php

namespace Drupal\image_effects\Tests;

use Drupal\image\Entity\ImageStyle;
use Drupal\Core\Image\ImageInterface;

/**
 * Interlace effect test.
 *
 * @group Image Effects
 */
class ImageEffectsInterlaceTest extends ImageEffectsTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->toolkits = ['gd', 'imagemagick'];
  }

  /**
   * Interlace effect test.
   */
  public function testInterlaceEffect() {
    // Test operations on toolkits.
    $this->executeTestOnToolkits([$this, 'doTestInterlaceOperations']);
  }

  /**
   * Interlace operations test.
   */
  public function doTestInterlaceOperations() {
    $image_factory = $this->container->get('image.factory');

    $test_data = [
      // Test on the PNG test image.
      [
        'test_file' => drupal_get_path('module', 'simpletest') . '/files/image-test.png',
      ],
    ];

    foreach ($test_data as $data) {
      $original_uri = file_unmanaged_copy($data['test_file'], 'public://', FILE_EXISTS_RENAME);
      $generated_uri = 'public://styles/image_effects_test/public/'. $this->container->get('file_system')->basename($original_uri);

      // Add interlace effect to the test image style.
      $effect = [
        'id' => 'image_effects_interlace',
        'data' => [
          'type' => 'Plane',
        ],
      ];
      $uuid = $this->addEffectToTestStyle($effect);

      // Load Image Style.
      $image_style = ImageStyle::load('image_effects_test');

      // Check that ::applyEffect generates interlaced PNG or GIF or
      // progressive JPEG image.
      $image_style->createDerivative($original_uri, $image_style->buildUri($original_uri));
      $image = $image_factory->get($generated_uri, 'gd');

      $this->assertTrue($this->isPNGInterlaced($image));

      // Remove effect.
      $uuid = $this->removeEffectFromTestStyle($uuid);
    }
  }

  /**
   * Checks if this is an interlaced PNG.
   *
   * @param \Drupal\Core\Image\ImageInterface $image
   *   An image object that need to be checked.
   * @return bool
   *   Returns TRUE on success or FALSE on failure.
   *
   * @see http://stackoverflow.com/questions/14235600/php-test-if-image-is-interlaced
   */
  private function isPNGInterlaced(ImageInterface $image) {
    $source = $image->getSource();

    $real_path = $this->container->get('file_system')->realpath($source);

    $handle = fopen($real_path, "r");
    $contents = fread($handle, 32);
    fclose($handle);
    return( ord($contents[28]) != 0 );
  }

}
