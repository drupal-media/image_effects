<?php

namespace Drupal\image_effects\Tests;

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
    // @todo This effect does not work on GraphicsMagick.
    $this->imagemagickPackages['graphicsmagick'] = FALSE;
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
    $effect = $this->testImageStyle->getEffect($uuid);
    $this->assertEqual('the quick brown fox jumps over the lazy dog', $effect->getAlteredText($effect->getConfiguration()['data']['text_string']));
    $this->assertEqual('Para1 Para2', $effect->getAlteredText('<p>Para1</p><!-- Comment --> Para2'));
    $this->assertEqual('"Title" One …', $effect->getAlteredText('&quot;Title&quot; One &hellip;'));
    $this->removeEffectFromTestStyle($uuid);
    $effect_config['data'] += [
      'text_default][strip_tags' => FALSE,
      'text_default][decode_entities' => FALSE,
    ];
    $uuid = $this->addEffectToTestStyle($effect_config);
    $effect = $this->testImageStyle->getEffect($uuid);
    $this->assertEqual('<p>Para1</p><!-- Comment --> Para2', $effect->getAlteredText('<p>Para1</p><!-- Comment --> Para2'));
    $this->assertEqual('&quot;Title&quot; One &hellip;', $effect->getAlteredText('&quot;Title&quot; One &hellip;'));

    // Test converting to uppercase and trimming text.
    $this->removeEffectFromTestStyle($uuid);
    $effect_config['data'] += [
      'text][maximum_chars' => 9,
      'text][case_format' => 'upper',
    ];
    $uuid = $this->addEffectToTestStyle($effect_config);
    $effect = $this->testImageStyle->getEffect($uuid);
    $this->assertEqual('THE QUICK…', $effect->getAlteredText($effect->getConfiguration()['data']['text_string']));
  }

  /**
   * Text overlay operations test.
   */
  public function doTestTextOverlayOperations() {
    $test_data = [
      [
        'test_file' => $this->getTestImageCopyUri('/files/image-test.png', 'simpletest'),
        'derivative_width' => 984,
        'derivative_height' => 61,
      ],
    ];

    foreach ($test_data as $data) {
      // Get expected URIs.
      $original_uri = $data['test_file'];
      $derivative_uri = $this->testImageStyle->buildUri($original_uri);

      // Source image.
      $image = $this->imageFactory->get($original_uri);

      // Load Image Style and get expected derivative URL.
      $derivative_url = file_url_transform_relative($this->testImageStyle->buildUrl($original_uri));

      // Check that ::applyEffect generates image with expected dimensions
      // and colors at corners.
      $this->testImageStyle->createDerivative($original_uri, $derivative_uri);
      $derivative_image = $this->imageFactory->get($derivative_uri, 'gd');
      $this->assertTextOverlay($derivative_image, $data['derivative_width'], $data['derivative_height']);
      $this->assertTrue($this->colorsAreEqual($this->fuchsia, $this->getPixelColor($derivative_image, 0, 0)));
      $this->assertTrue($this->colorsAreEqual($this->fuchsia, $this->getPixelColor($derivative_image, $derivative_image->getWidth() - 1, 0)));
      $this->assertTrue($this->colorsAreEqual($this->fuchsia, $this->getPixelColor($derivative_image, 0, $derivative_image->getHeight() - 1)));
      $this->assertTrue($this->colorsAreEqual($this->fuchsia, $this->getPixelColor($derivative_image, $derivative_image->getWidth() - 1, $derivative_image->getHeight() - 1)));

      // Check that ::transformDimensions returns expected dimensions.
      $variables = [
        '#theme' => 'image_style',
        '#style_name' => 'image_effects_test',
        '#uri' => $original_uri,
        '#width' => $image->getWidth(),
        '#height' => $image->getHeight(),
      ];
      $this->assertEqual('<img src="' . $derivative_url . '" width="' . $derivative_image->getWidth() . '" height="' . $derivative_image->getHeight() . '" alt="" class="image-style-image-effects-test" />', $this->getImageTag($variables));
    }
  }

}
