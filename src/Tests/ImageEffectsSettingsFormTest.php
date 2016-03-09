<?php

namespace Drupal\image_effects\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Settings form test.
 *
 * @group Image Effects
 */
class ImageEffectsSettingsFormTest extends WebTestBase {

  public static $modules = ['image_effects'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // Create a user and log it in.
    $this->adminUser = $this->drupalCreateUser([
      'administer site configuration',
      'administer image styles',
    ]);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Settings form test.
   */
  public function testSettingsForm() {
    $admin_path = '/admin/config/media/image_effects';

    // Get the settings form.
    $this->drupalGet($admin_path);

    // Change the default color selector.
    $edit = [
      'settings[color_selector][plugin_id]' => 'farbtastic',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save configuration'));

    // Check config changed.
    $this->assertEqual('farbtastic', \Drupal::config('image_effects.settings')->get('color_selector.plugin_id'));

    // Change the default image selector.
    $config = \Drupal::configFactory()->getEditable('image_effects.settings');
    $config->set('image_selector.plugin_id', 'dropdown')->save();
    $this->drupalGet($admin_path);
    $edit = [
      'settings[image_selector][plugin_settings][path]' => 'private://',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save configuration'));

    // Check config changed.
    $this->assertEqual(['path' => 'private://'], \Drupal::config('image_effects.settings')->get('image_selector.plugin_settings.dropdown'));

    // Change the default font selector.
    $config = \Drupal::configFactory()->getEditable('image_effects.settings');
    $config->set('font_selector.plugin_id', 'dropdown')->save();
    $this->drupalGet($admin_path);
    $edit = [
      'settings[font_selector][plugin_settings][path]' => 'public://',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save configuration'));

    // Check config changed.
    $this->assertEqual(['path' => 'public://'], \Drupal::config('image_effects.settings')->get('font_selector.plugin_settings.dropdown'));
  }

}
