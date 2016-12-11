<?php

namespace Drupal\image_effects\Tests;

use Drupal\image\Entity\ImageStyle;

/**
 * ImageMagick arguments effect test.
 *
 * @group Image Effects
 */
class ImageEffectsImagemagickArgumentsTest extends ImageEffectsTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->toolkits = ['imagemagick'];
  }

  /**
   * ImageMagick arguments effect test.
   */
  public function testImagemagickArgumentsEffect() {
    // Test operations on toolkits.
    $this->executeTestOnToolkits([$this, 'doTestImagemagickArgumentsOperations']);
  }

  /**
   * ImageMagick arguments operations test.
   */
  public function doTestImagemagickArgumentsOperations() {
    $image_factory = $this->container->get('image.factory');

    $test_file = drupal_get_path('module', 'image_effects') . '/tests/images/portrait-painting.jpg';
    $original_uri = file_unmanaged_copy($test_file, 'public://', FILE_EXISTS_RENAME);
    $generated_uri = 'public://styles/image_effects_test/public/' . \Drupal::service('file_system')->basename($original_uri);

    // Test source image EXIF data.
    $exif = @exif_read_data(\Drupal::service('file_system')->realpath($original_uri));
    $this->assertEqual(8, isset($exif['Orientation']) ? $exif['Orientation'] : NULL);

    // 1. Test effect with 'keep' dimensions.
    $effect = [
      'id' => 'image_effects_imagemagick_arguments',
      'data' => [
        'command_line' => '-strip',
        'dimensions_method' => 'keep',
      ],
    ];
    $uuid = $this->addEffectToTestStyle($effect);

    // Load Image Style.
    $image_style = ImageStyle::load('image_effects_test');

    // Check that ::transformDimensions returns expected dimensions.
    $image = $image_factory->get($original_uri);
    $url = file_url_transform_relative($image_style->buildUrl($original_uri));
    $variables = [
      '#theme' => 'image_style',
      '#style_name' => 'image_effects_test',
      '#uri' => $original_uri,
      '#width' => $image->getWidth(),
      '#height' => $image->getHeight(),
    ];
    $this->assertEqual('<img src="' . $url . '" width="640" height="480" alt="" class="image-style-image-effects-test" />', $this->getImageTag($variables));

    // Create derivative image.
    $image_style->createDerivative($original_uri, $image_style->buildUri($original_uri));

    // Check that ::applyEffect stripped EXIF metadata.
    $image = $image_factory->get($generated_uri);
    $this->assertEqual(640, $image->getWidth());
    $this->assertEqual(480, $image->getHeight());
    $exif = @exif_read_data(\Drupal::service('file_system')->realpath($generated_uri));
    $this->assertEqual(NULL, isset($exif['Orientation']) ? $exif['Orientation'] : NULL);

    // Remove effect.
    $this->removeEffectFromTestStyle($uuid);

    // 2. Test effect with 'strip' dimensions.
    $effect = [
      'id' => 'image_effects_imagemagick_arguments',
      'data' => [
        'command_line' => '-strip',
        'dimensions_method' => 'strip',
      ],
    ];
    $uuid = $this->addEffectToTestStyle($effect);

    // Load Image Style.
    $image_style = ImageStyle::load('image_effects_test');

    // Check that ::transformDimensions does not provide dimension
    // attributes.
    $image = $image_factory->get($original_uri);
    $url = file_url_transform_relative($image_style->buildUrl($original_uri));
    $variables = [
      '#theme' => 'image_style',
      '#style_name' => 'image_effects_test',
      '#uri' => $original_uri,
      '#width' => $image->getWidth(),
      '#height' => $image->getHeight(),
    ];
    $this->assertEqual('<img src="' . $url . '" alt="" class="image-style-image-effects-test" />', $this->getImageTag($variables));

    // Create derivative image.
    $image_style->createDerivative($original_uri, $image_style->buildUri($original_uri));

    // Check that ::applyEffect generated a derivative with same size as
    // original.
    $image = $image_factory->get($generated_uri);
    $this->assertEqual(640, $image->getWidth());
    $this->assertEqual(480, $image->getHeight());

    // Remove effect.
    $this->removeEffectFromTestStyle($uuid);

    // 3. Test effect with 'value' dimensions as percentages.
    $effect = [
      'id' => 'image_effects_imagemagick_arguments',
      'data' => [
        'command_line' => '-strip',
        'dimensions_method' => 'value',
        'dimensions][width][c0][c1][value' => 50,
        'dimensions][width][c0][c1][uom' => 'perc',
        'dimensions][height][c0][c1][value' => 25,
        'dimensions][height][c0][c1][uom' => 'perc',
      ],
    ];
    $uuid = $this->addEffectToTestStyle($effect);

    // Load Image Style.
    $image_style = ImageStyle::load('image_effects_test');

    // Check that ::transformDimensions returns expected dimensions.
    $image = $image_factory->get($original_uri);
    $url = file_url_transform_relative($image_style->buildUrl($original_uri));
    $variables = [
      '#theme' => 'image_style',
      '#style_name' => 'image_effects_test',
      '#uri' => $original_uri,
      '#width' => $image->getWidth(),
      '#height' => $image->getHeight(),
    ];
    $this->assertEqual('<img src="' . $url . '" width="320" height="120" alt="" class="image-style-image-effects-test" />', $this->getImageTag($variables));

    // Remove effect.
    $this->removeEffectFromTestStyle($uuid);

    // 4. Test effect with 'value' dimensions as pixels.
    $effect = [
      'id' => 'image_effects_imagemagick_arguments',
      'data' => [
        'command_line' => '-strip',
        'dimensions_method' => 'value',
        'dimensions][width][c0][c1][value' => 64,
        'dimensions][width][c0][c1][uom' => 'px',
        'dimensions][height][c0][c1][value' => 48,
        'dimensions][height][c0][c1][uom' => 'px',
      ],
    ];
    $uuid = $this->addEffectToTestStyle($effect);

    // Load Image Style.
    $image_style = ImageStyle::load('image_effects_test');

    // Check that ::transformDimensions returns expected dimensions.
    $image = $image_factory->get($original_uri);
    $url = file_url_transform_relative($image_style->buildUrl($original_uri));
    $variables = [
      '#theme' => 'image_style',
      '#style_name' => 'image_effects_test',
      '#uri' => $original_uri,
      '#width' => $image->getWidth(),
      '#height' => $image->getHeight(),
    ];
    $this->assertEqual('<img src="' . $url . '" width="64" height="48" alt="" class="image-style-image-effects-test" />', $this->getImageTag($variables));

    // Remove effect.
    $this->removeEffectFromTestStyle($uuid);
  }

}
