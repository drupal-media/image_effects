<?php

namespace Drupal\image_effects\Tests;

use Drupal\image\Entity\ImageStyle;

/**
 * Text overlay effect test.
 *
 * @group Image Effects
 */
class ImageEffectsTextOverlayTest extends ImageEffectsTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->toolkits = ['gd', 'imagemagick'];
  }

  /**
   * Text overlay effect test.
   */
  public function testTextOverlayEffect() {
    // Add Text overlay effect to the test image style.
    $effect_config = [
      'id' => 'image_effects_text_overlay',
      'data' => [
        'text_default][text_string' => 'the quick brown fox jumps over the lazy dog',
        'font][uri' => drupal_get_path('module', 'image_effects') . '/tests/fonts/LinLibertineTTF_5.3.0_2012_07_02/LinLibertine_Rah.ttf',
        'font][size' => 40,
        'layout][position][extended_color][container][transparent' => FALSE,
        'layout][position][extended_color][container][hex' => '#FF00FF',
        'layout][position][extended_color][container][opacity' => 100,
      ],
    ];
    $uuid = $this->addEffectToTestStyle($effect_config);

    // Test operations on toolkits.
    $this->executeTestOnToolkits([$this, 'doTestTextOverlayOperations']);

    // Test text and HTML tags and entities.
    $this->changeToolkit('gd');
    $style_name = 'image_effects_test';
    $style_path = 'admin/config/media/image-styles/manage/' . $style_name;
    $image_style = ImageStyle::load($style_name);
    $effect = $image_style->getEffect($uuid);
    $this->assertEqual('the quick brown fox jumps over the lazy dog', $effect->getAlteredText($effect->getConfiguration()['data']['text_string']));
    $this->assertEqual('Para1 Para2', $effect->getAlteredText('<p>Para1</p><!-- Comment --> Para2'));
    $this->assertEqual('"Title" One …', $effect->getAlteredText('&quot;Title&quot; One &hellip;'));
    $effect_config = [
      'data[text_default][strip_tags]' => FALSE,
      'data[text_default][decode_entities]' => FALSE,
    ];
    $this->drupalPostForm($style_path . '/effects/' . $uuid, $effect_config, t('Update effect'));
    $image_style = ImageStyle::load($style_name);
    $effect = $image_style->getEffect($uuid);
    $this->assertEqual('<p>Para1</p><!-- Comment --> Para2', $effect->getAlteredText('<p>Para1</p><!-- Comment --> Para2'));
    $this->assertEqual('&quot;Title&quot; One &hellip;', $effect->getAlteredText('&quot;Title&quot; One &hellip;'));

    // Test converting to uppercase and trimming text.
    $effect_config = [
      'data[text][maximum_chars]' => 9,
      'data[text][case_format]' => 'upper',
    ];
    $this->drupalPostForm($style_path . '/effects/' . $uuid, $effect_config, t('Update effect'));
    $image_style = ImageStyle::load($style_name);
    $effect = $image_style->getEffect($uuid);
    $this->assertEqual('THE QUICK…', $effect->getAlteredText($effect->getConfiguration()['data']['text_string']));
  }

  /**
   * Text overlay operations test.
   */
  public function doTestTextOverlayOperations() {
    $image_factory = $this->container->get('image.factory');
    $test_data = [
      [
        'test_file' => drupal_get_path('module', 'simpletest') . '/files/image-test.png',
        'derivative_width' => 984,
        'derivative_height' => 61,
      ],
    ];

    foreach ($test_data as $data) {
      // Get expected URIs.
      $original_uri = file_unmanaged_copy($data['test_file'], 'public://', FILE_EXISTS_RENAME);
      $generated_uri = 'public://styles/image_effects_test/public/'. \Drupal::service('file_system')->basename($original_uri);

      // Source image.
      $image = $image_factory->get($original_uri);

      // Load Image Style and get expected derivative URL.
      $image_style = ImageStyle::load('image_effects_test');
      $url = file_url_transform_relative($image_style->buildUrl($original_uri));

      // Check that ::applyEffect generates image with expected dimensions
      // and colors at corners.
      $image_style->createDerivative($original_uri, $image_style->buildUri($original_uri));
      $derivative_image = $image_factory->get($generated_uri, 'gd');
      $this->assertTextOverlay($derivative_image, $data['derivative_width'], $data['derivative_height']);
      $this->assertTrue($this->colorsAreEqual($this->fuchsia, $this->getPixelColor($derivative_image, 0, 0)));
      $this->assertTrue($this->colorsAreEqual($this->fuchsia, $this->getPixelColor($derivative_image, $derivative_image->getWidth() - 1, 0)));
      $this->assertTrue($this->colorsAreEqual($this->fuchsia, $this->getPixelColor($derivative_image, 0, $derivative_image->getHeight() - 1)));
      $this->assertTrue($this->colorsAreEqual($this->fuchsia, $this->getPixelColor($derivative_image, $derivative_image->getWidth() - 1, $derivative_image->getHeight() - 1)));

      // Check that ::transformDimensions returns expected dimensions.
      $variables = array(
        '#theme' => 'image_style',
        '#style_name' => 'image_effects_test',
        '#uri' => $original_uri,
        '#width' => $image->getWidth(),
        '#height' => $image->getHeight(),
      );
      $this->assertEqual('<img src="' . $url . '" width="' . $derivative_image->getWidth() . '" height="' . $derivative_image->getHeight() . '" alt="" class="image-style-image-effects-test" />', $this->getImageTag($variables));
    }
  }
}
