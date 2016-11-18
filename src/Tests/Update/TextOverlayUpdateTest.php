<?php

namespace Drupal\image_effects\Tests\Update;

use Drupal\system\Tests\Update\UpdatePathTestBase;

/**
 * Tests the upgrade path for 'Text overlay' effect.
 *
 * @see image_effects_post_update_text_overlay_maximum_chars()
 *
 * @group Image Effects
 */
class TextOverlayUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['image_effects'];

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../tests/fixtures/update/d_820_ie_810a2.php.gz',
    ];
  }

  /**
   * Tests that 'Text overlay' effects are updated properly.
   */
  public function testTextOverlayUpdate() {
    // Test that Text overlay effect does not have 'maximum_chars' and 'excess_chars_text' parameters set.
    $effect_data = $this->config('image.style.test_text_overlay')->get('effects.8287f632-3b1f-4a6f-926f-119550cc0948.data');
    $this->assertFalse(array_key_exists('maximum_chars', $effect_data['text']));
    $this->assertFalse(array_key_exists('excess_chars_text', $effect_data['text']));

    // Run updates.
    $this->runUpdates();

    // Test that Text overlay effect has 'maximum_chars' and 'excess_chars_text' parameters set.
    $effect_data = $this->config('image.style.test_text_overlay')->get('effects.8287f632-3b1f-4a6f-926f-119550cc0948.data');
    $this->assertTrue(array_key_exists('maximum_chars', $effect_data['text']));
    $this->assertNull($effect_data['text']['maximum_chars']);
    $this->assertTrue(array_key_exists('excess_chars_text', $effect_data['text']));
    $this->assertEqual('…', $effect_data['text']['excess_chars_text']);
  }

}
