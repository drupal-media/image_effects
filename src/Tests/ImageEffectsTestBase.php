<?php

namespace Drupal\image_effects\Tests;

use Drupal\Core\Image\ImageInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\simpletest\WebTestBase;

/**
 * Base test class for image_effects tests.
 */
abstract class ImageEffectsTestBase extends WebTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = [
    'image',
    'image_effects',
    'simpletest',
    'imagemagick',
  ];

  /**
   * Toolkits to be tested.
   *
   * @var array
   */
  protected $toolkits = ['gd', 'imagemagick'];

  /**
   * ImageMagick toolkit: packages to be tested.
   *
   * @var array
   */
  protected $imagemagickPackages = [
    'imagemagick' => TRUE,
    'graphicsmagick' => TRUE,
  ];

  /**
   * Test image style.
   *
   * @var \Drupal\image\Entity\ImageStyle
   */
  protected $testImageStyle;

  /**
   * Test image style name.
   *
   * @var string
   */
  protected $testImageStyleName = 'image_effects_test';

  /**
   * Test image style label.
   *
   * @var string
   */
  protected $testImageStyleLabel = 'Image Effects Test';

  /**
   * Image factory.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  // Colors that are used in testing.
  // @codingStandardsIgnoreStart
  protected $black       = [  0,   0,   0,   0];
  protected $red         = [255,   0,   0,   0];
  protected $green       = [  0, 255,   0,   0];
  protected $blue        = [  0,   0, 255,   0];
  protected $yellow      = [255, 255,   0,   0];
  protected $fuchsia     = [255,   0, 255,   0];
  protected $cyan        = [  0, 255, 255,   0];
  protected $white       = [255, 255, 255,   0];
  protected $grey        = [128, 128, 128,   0];
  protected $transparent = [  0,   0,   0, 127];
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Set the image factory.
    $this->imageFactory = $this->container->get('image.factory');

    // Create a user and log it in.
    $this->adminUser = $this->drupalCreateUser([
      'administer site configuration',
      'administer image styles',
    ]);
    $this->drupalLogin($this->adminUser);

    // Create the test image style.
    $this->testImageStyle = ImageStyle::create([
      'name' => $this->testImageStyleName,
      'label' => $this->testImageStyleLabel,
    ]);
    $this->assertEqual(SAVED_NEW, $this->testImageStyle->save());
  }

  /**
   * Add an image effect to the image test style.
   *
   * Uses the image effect configuration forms, and not API directly, to ensure
   * forms work correctly.
   *
   * @param array $effect
   *   An array of effect data, with following keys:
   *   - id: the image effect plugin
   *   - data: an array of fields for the image effect edit form, with
   *     their values.
   *
   * @return string
   *   The UUID of the newly added effect.
   */
  protected function addEffectToTestStyle(array $effect) {
    // Get image style prior to adding the new effect.
    $image_style_pre = ImageStyle::load($this->testImageStyleName);

    // Add the effect.
    $this->drupalPostForm('admin/config/media/image-styles/manage/' . $this->testImageStyleName, ['new' => $effect['id']], t('Add'));
    if (!empty($effect['data'])) {
      $effect_edit = [];
      foreach ($effect['data'] as $field => $value) {
        $effect_edit['data[' . $field . ']'] = $value;
      }
      $this->drupalPostForm(NULL, $effect_edit, t('Add effect'));
    }

    // Get UUID of newly added effect.
    $this->testImageStyle = ImageStyle::load($this->testImageStyleName);
    foreach ($this->testImageStyle->getEffects() as $uuid => $effect) {
      if (!$image_style_pre->getEffects()->has($uuid)) {
        return $uuid;
      }
    }
    return NULL;
  }

  /**
   * Remove an image effect from the image test style.
   *
   * @param string $uuid
   *   The UUID of the effect to remove.
   */
  protected function removeEffectFromTestStyle($uuid) {
    $effect = $this->testImageStyle->getEffect($uuid);
    $this->testImageStyle->deleteImageEffect($effect);
    $this->assertEqual(SAVED_UPDATED, $this->testImageStyle->save());
  }

  /**
   * Render an image style element.
   *
   * The ::renderRoot method alters the passed $variables array by adding a new
   * key '#printed' => TRUE. This prevents next call to re-render the element.
   * We wrap ::renderRoot() in a helper protected method and pass each time a
   * fresh array so that $variables won't get altered and the element is
   * re-rendered each time.
   */
  protected function getImageTag($variables) {
    return str_replace("\n", NULL, \Drupal::service('renderer')->renderRoot($variables));
  }

  /**
   * Change toolkit.
   *
   * @param string $toolkit_id
   *   The id of the toolkit to set up.
   */
  protected function changeToolkit($toolkit_id) {
    \Drupal::configFactory()->getEditable('system.image')
      ->set('toolkit', $toolkit_id)
      ->save();
    $this->container->get('image.factory')->setToolkitId($toolkit_id);
  }

  /**
   * Executes a test method on requested toolkits.
   */
  protected function executeTestOnToolkits($method) {
    foreach ($this->toolkits as $toolkit_id) {
      // Manage toolkit specific configuration.
      switch ($toolkit_id) {
        case 'gd':
          $this->changeToolkit($toolkit_id);
          call_user_func($method);
          $this->testImageStyle->flush();
          break;

        case 'imagemagick':
          $this->changeToolkit($toolkit_id);

          // Execute tests with ImageMagick.
          // The test can only be executed if ImageMagick's 'convert' is
          // available on the shell path.
          if ($this->imagemagickPackages['imagemagick'] === TRUE) {
            \Drupal::configFactory()->getEditable('imagemagick.settings')
              ->set('binaries', 'imagemagick')
              ->set('debug', TRUE)
              ->save();
            $status = \Drupal::service('image.toolkit.manager')->createInstance('imagemagick')->checkPath('');
            if (!empty($status['errors'])) {
              // Bots running automated test on d.o. do not have ImageMagick
              // installed, so there's no purpose to try and run this test
              // there; it can be run locally where ImageMagick is installed.
              debug('Tests for ImageMagick cannot run because the \'convert\' binary is not available on the shell path.');
            }
            else {
              call_user_func($method);
              $this->testImageStyle->flush();
            }
          }

          // Execute tests with GraphicsMagick.
          // The test can only be executed if GraphicsMagick's 'gm' is available
          // on the shell path.
          if ($this->imagemagickPackages['graphicsmagick'] === TRUE) {
            \Drupal::configFactory()->getEditable('imagemagick.settings')
              ->set('binaries', 'graphicsmagick')
              ->set('debug', TRUE)
              ->save();
            $status = \Drupal::service('image.toolkit.manager')->createInstance('imagemagick')->checkPath('');
            if (!empty($status['errors'])) {
              // Bots running automated test on d.o. do not have GraphicsMagick
              // installed, so there's no purpose to try and run this test
              // there; it can be run locally where GraphicsMagick is installed.
              debug('Tests for GraphicsMagick cannot run because the \'gm\' binary is not available on the shell path.');
            }
            else {
              call_user_func($method);
              $this->testImageStyle->flush();
            }
          }

          break;

      }
    }
  }

  /**
   * Get the URI of the test image file copied to a safe location.
   *
   * @param string $path
   *   The path to the test image file.
   * @param string $name
   *   (optional) The name of the item for which the path is requested.
   *   Ignored for $type 'core'. If null, $path is returned. Defaults
   *   to null.
   * @param string $type
   *   (optional) The type of the item; one of 'core', 'profile', 'module',
   *   'theme', or 'theme_engine'. Defaults to 'module'.
   */
  protected function getTestImageCopyUri($path, $name = NULL, $type = 'module') {
    $test_directory = 'public://test-images/';
    file_prepare_directory($test_directory, FILE_CREATE_DIRECTORY);
    $source_uri = $name ? drupal_get_path($type, $name) : '';
    $source_uri .= $path;
    $target_uri = $test_directory . \Drupal::service('file_system')->basename($source_uri);
    return file_unmanaged_copy($source_uri, $target_uri, FILE_EXISTS_RENAME);
  }

  /**
   * Function to compare two colors by RGBa.
   */
  protected function colorsAreEqual($color_a, $color_b) {
    // Fully transparent pixels are equal, regardless of RGB.
    if ($color_a[3] == 127 && $color_b[3] == 127) {
      return TRUE;
    }

    foreach ($color_a as $key => $value) {
      if ($color_b[$key] != $value) {
        debug("Color A: {" . implode(',', $color_a) . "}, Color B: {" . implode(',', $color_b) . "}");
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Function to compare two colors by RGBa, within a tolerance.
   *
   * Very basic, just compares the sum of the squared differences for each of
   * the R, G, B, a components of two colors against a 'tolerance' value.
   *
   * @param int[] $color_a
   *   An RGBa array.
   * @param int[] $color_b
   *   An RGBa array.
   * @param int $tolerance
   *   The accepteable difference between the colors.
   *
   * @return bool
   *   TRUE if the colors differences are within tolerance, FALSE otherwise.
   */
  protected function colorsAreClose(array $color_a, array $color_b, $tolerance) {
    // Fully transparent colors are equal, regardless of RGB.
    if ($color_a[3] == 127 && $color_b[3] == 127) {
      return TRUE;
    }
    $distance = pow(($color_a[0] - $color_b[0]), 2) + pow(($color_a[1] - $color_b[1]), 2) + pow(($color_a[2] - $color_b[2]), 2) + pow(($color_a[3] - $color_b[3]), 2);
    if ($distance > $tolerance) {
      debug("Color A: {" . implode(',', $color_a) . "}, Color B: {" . implode(',', $color_b) . "}, Distance: " . $distance . ", Tolerance: " . $tolerance);
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Function for finding a pixel's RGBa values.
   */
  protected function getPixelColor(ImageInterface $image, $x, $y) {
    $toolkit = $image->getToolkit();
    $color_index = imagecolorat($toolkit->getResource(), $x, $y);

    $transparent_index = imagecolortransparent($toolkit->getResource());
    if ($color_index == $transparent_index) {
      return [0, 0, 0, 127];
    }

    return array_values(imagecolorsforindex($toolkit->getResource(), $color_index));
  }

  /**
   * Asserts a Text overlay image.
   */
  protected function assertTextOverlay($image, $width, $height) {
    $w_error = abs($image->getWidth() - $width);
    $h_error = abs($image->getHeight() - $height);
    $tolerance = 0.1;
    $this->assertTrue($w_error < $width * $tolerance && $h_error < $height * $tolerance, "Width and height ({$image->getWidth()}x{$image->getHeight()}) approximate expected results ({$width}x{$height})");
  }

}
