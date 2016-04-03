<?php

namespace Drupal\image_effects\Tests;

use Drupal\image\Entity\ImageStyle;

/**
 * Background effect test.
 *
 * @group Image Effects
 */
class ImageEffectsBackgroundTest extends ImageEffectsTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->toolkits = ['gd', 'imagemagick'];
  }

  /**
   * Background effect test.
   */
  public function testBackgroundEffect() {
    // Test operations on toolkits.
    $this->executeTestOnToolkits([$this, 'doTestBackgroundOperations']);
  }

  /**
   * Background operations test.
   */
  public function doTestBackgroundOperations() {
    $image_factory = $this->container->get('image.factory');

    $test_file = drupal_get_path('module', 'simpletest') . '/files/image-test.png';
    $original_uri = file_unmanaged_copy($test_file, 'public://', FILE_EXISTS_RENAME);
    $generated_uri = 'public://styles/image_effects_test/public/' . \Drupal::service('file_system')->basename($original_uri);

    $background_file = drupal_get_path('module', 'simpletest') . '/files/image-1.png';
    $background_uri = file_unmanaged_copy($background_file, 'public://', FILE_EXISTS_RENAME);

    $effect = [
      'id' => 'image_effects_background',
      'data' => [
        'placement' => 'left-top',
        'x_offset' => 0,
        'y_offset' => 0,
        'opacity' => 100,
        'background_image' => $background_uri,
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
    $variables = [
      '#theme' => 'image_style',
      '#style_name' => 'image_effects_test',
      '#uri' => $original_uri,
      '#width' => $image->getWidth(),
      '#height' => $image->getHeight(),
    ];
    $this->assertEqual('<img src="' . $url . '" width="360" height="240" alt="" class="image-style-image-effects-test" />', $this->getImageTag($variables));

    // Check that ::applyEffect generates image with expected canvas.
    $image_style->createDerivative($original_uri, $image_style->buildUri($original_uri));
    $image = $image_factory->get($generated_uri, 'gd');
    $this->assertEqual(360, $image->getWidth());
    $this->assertEqual(240, $image->getHeight());
    $this->assertTrue($this->colorsAreEqual($this->red, $this->getPixelColor($image, 0, 0)));
    $this->assertTrue($this->colorsAreEqual($this->green, $this->getPixelColor($image, 39, 0)));
    $this->assertTrue($this->colorsAreEqual([185, 185, 185, 0], $this->getPixelColor($image, 0, 19)));
    $this->assertTrue($this->colorsAreEqual($this->blue, $this->getPixelColor($image, 39, 19)));

    // Remove effect.
    $this->removeEffectFromTestStyle($uuid);

    // Toolkit-specific tests.
    switch ($image_factory->getToolkitId()) {
      case 'gd':
        // For the GD toolkit, test we are not left with orphan resource after
        // applying the operation.
        $image = $image_factory->get($original_uri);
        // Store the original GD resource.
        $old_res = $image->getToolkit()->getResource();
        // Apply the operation.
        $image->apply('background', [
          'x_offset' => 0,
          'y_offset' => 0,
          'opacity' => 100,
          'background_image' => $image_factory->get($background_uri),
        ]);
        // The operation replaced the resource, check that the old one has
        // been destroyed.
        $new_res = $image->getToolkit()->getResource();
        $this->assertTrue(is_resource($new_res));
        $this->assertNotEqual($new_res, $old_res);
        $this->assertFalse(is_resource($old_res));
        break;

      case 'imagemagick':
        // For the Imagemagick toolkit, toolkit should return backround
        // image dimensions after applying the operation, but before
        // saving.
        $image = $image_factory->get($original_uri);
        // Apply the operation.
        $image->apply('background', [
          'x_offset' => 0,
          'y_offset' => 0,
          'opacity' => 100,
          'background_image' => $image_factory->get($background_uri),
        ]);
        $this->assertEqual(360, $image->getToolkit()->getWidth());
        $this->assertEqual(240, $image->getToolkit()->getHeight());
        break;

    }
  }

}
