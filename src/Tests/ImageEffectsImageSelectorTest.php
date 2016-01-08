<?php

/**
 * @file
 * Image selector test case script.
 */

namespace Drupal\image_effects\Tests;

use Drupal\image\Entity\ImageStyle;

/**
 * Image selector test.
 *
 * @group Image Effects
 */
class ImageEffectsImageSelectorTest extends ImageEffectsTestBase {

  public static $modules = ['image', 'image_effects', 'simpletest', 'image_effects_module_test'];

  /**
   * Image selector test.
   */
  public function testImageSelector() {
    $image_path = drupal_get_path('module', 'image_effects') . '/misc';
    $image_file = 'portrait-painting.jpg';

    // Test the Basic plugin.
    // Add an effect with the image selector.
    $effect = [
      'id' => 'image_effects_module_test_image_selection',
      'data' => [
        'image_uri' => $image_path . '/' . $image_file,
      ],
    ];
    $uuid = $this->addEffectToTestStyle($effect);

    // Check that the full image URI is in the effect summary.
    $this->assertText($image_path . '/' . $image_file);

    // Test the Dropdown plugin.
    // Remove the effect.
    $this->removeEffectFromTestStyle($uuid);

    // Change the settings.
    $config = \Drupal::configFactory()->getEditable('image_effects.settings');
    $config
      ->set('image_selector.plugin_id', 'dropdown')
      ->set('image_selector.plugin_settings.dropdown.path', $image_path)
      ->save();

    // Add an effect with the image selector.
    $effect = [
      'id' => 'image_effects_module_test_image_selection',
      'data' => [
        'image_uri' => $image_file,
      ],
    ];
    $this->addEffectToTestStyle($effect);

    // Check that the full image URI is in the effect summary.
    $this->assertText($image_path . '/' . $image_file);
  }

}
