<?php

namespace Drupal\image_effects\Tests;

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
    // @todo This effect does not work on GraphicsMagick.
    $this->imagemagickPackages['graphicsmagick'] = FALSE;
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
    $test_data = [
      // Test on the PNG test image.
      [
        'test_file' => $this->getTestImageCopyUri('/files/image-test.png', 'simpletest'),
      ],
    ];

    // Add interlace effect to the test image style.
    $effect = [
      'id' => 'image_effects_interlace',
      'data' => [
        'type' => 'Plane',
      ],
    ];
    $uuid = $this->addEffectToTestStyle($effect);

    foreach ($test_data as $data) {
      $original_uri = $data['test_file'];

      // Check that ::applyEffect generates interlaced PNG or GIF or
      // progressive JPEG image.
      $derivative_uri = $this->testImageStyle->buildUri($original_uri);
      $this->testImageStyle->createDerivative($original_uri, $derivative_uri);
      $image = $this->imageFactory->get($derivative_uri, 'gd');

      $this->assertTrue($this->isPngInterlaced($image));
    }

    // Remove effect.
    $uuid = $this->removeEffectFromTestStyle($uuid);
  }

  /**
   * Checks if this is an interlaced PNG.
   *
   * @param \Drupal\Core\Image\ImageInterface $image
   *   An image object that need to be checked.
   *
   * @return bool
   *   Returns TRUE on success or FALSE on failure.
   *
   * @see http://stackoverflow.com/questions/14235600/php-test-if-image-is-interlaced
   */
  protected function isPngInterlaced(ImageInterface $image) {
    $source = $image->getSource();

    $real_path = $this->container->get('file_system')->realpath($source);

    $handle = fopen($real_path, "r");
    $contents = fread($handle, 32);
    fclose($handle);
    return ord($contents[28]) != 0;
  }

}
