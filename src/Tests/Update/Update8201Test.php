<?php

namespace Drupal\image_effects\Tests\Update;

use Drupal\system\Tests\Update\UpdatePathTestBase;

/**
 * Tests the upgrade path for enabling file_mdm and its submodules.
 *
 * @group Image Effects
 */
class Update8201Test extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../tests/fixtures/update/d834_ie810.php.gz',
    ];
  }

  /**
   * Tests that the file_mdm module enable is executed correctly.
   */
  public function testModuleWeightUpdate() {
    $this->assertFalse(\Drupal::moduleHandler()->moduleExists('file_mdm'));
    $this->assertFalse(\Drupal::moduleHandler()->moduleExists('file_mdm_exif'));
    $this->assertFalse(\Drupal::moduleHandler()->moduleExists('file_mdm_font'));

    $this->runUpdates();
    // @todo Fix https://www.drupal.org/node/2021959 so that module
    // enable/disable changes are immediately reflected in
    // \Drupal::getContainer(). Until then, tests can invoke this workaround
    // when requiring services from newly enabled modules to be immediately
    // available in the same request.
    $this->rebuildContainer();

    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('file_mdm'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('file_mdm_exif'));
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('file_mdm_font'));
  }

}
