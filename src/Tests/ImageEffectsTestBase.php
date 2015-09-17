<?php

/**
 * @file
 * image_effects test case script.
 */

namespace Drupal\image_effects\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Base test class for image_effects tests.
 */
abstract class ImageEffectsTestBase extends WebTestBase {

  public static $modules = ['image', 'image_effects'];

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

    // Create a test image style.
    $style_name = 'image_effects_test';
    $style_label = 'Image Effects Test';
    $style_path = 'admin/config/media/image-styles/manage/' . $style_name;
    $edit = [
      'name' => $style_name,
      'label' => $style_label,
    ];
    $this->drupalPostForm('admin/config/media/image-styles/add', $edit, t('Create new style'));
    $this->assertRaw(t('Style %name was created.', ['%name' => $style_label]));
  }

  /**
   * Add an image effect to the image test style.
   *
   * @param array $effect
   *   An array of effect data, with following keys:
   *   - id: the image effect plugin
   *   - data: an array of fields for the image effect edit form, with
   *     their values.
   */
  protected function addEffectToTestStyle($effect) {
    $style_name = 'image_effects_test';
    $style_path = 'admin/config/media/image-styles/manage/' . $style_name;
    // Add the effect.
    $this->drupalPostForm($style_path, array('new' => $effect['id']), t('Add'));
    if (!empty($effect['data'])) {
      $effect_edit = [];
      foreach($effect['data'] as $field => $value) {
        $effect_edit['data[' . $field . ']'] = $value;
      }
      $this->drupalPostForm(NULL, $effect_edit, t('Add effect'));
    }
  }

  /**
   * Render an image style element.
   *
   * drupal_render() alters the passed $variables array by adding a new key
   * '#printed' => TRUE. This prevents next call to re-render the element. We
   * wrap drupal_render() in a helper protected method and pass each time a
   * fresh array so that $variables won't get altered and the element is
   * re-rendered each time.
   */
  protected function getImageTag($variables) {
    return str_replace("\n", NULL, \Drupal::service('renderer')->renderRoot($variables));
  }

}
