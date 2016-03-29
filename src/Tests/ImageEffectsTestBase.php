<?php

namespace Drupal\image_effects\Tests;

use Drupal\Core\Extension\MissingDependencyException;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\simpletest\WebTestBase;

/**
 * Base test class for image_effects tests.
 */
abstract class ImageEffectsTestBase extends WebTestBase {

  public static $modules = ['image', 'image_effects', 'simpletest'];

  /**
   * Toolkits to be tested.
   *
   * @var array
   */
  protected $toolkits = [];

  // Colors that are used in testing.
  protected $black       = array(0, 0, 0, 0);
  protected $red         = array(255, 0, 0, 0);
  protected $green       = array(0, 255, 0, 0);
  protected $blue        = array(0, 0, 255, 0);
  protected $yellow      = array(255, 255, 0, 0);
  protected $fuchsia     = array(255, 0, 255, 0);
  protected $cyan        = array(0, 255, 255, 0);
  protected $white       = array(255, 255, 255, 0);
  protected $grey        = array(128, 128, 128, 0);
  protected $transparent = array(0, 0, 0, 127);

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Try installing additional toolkit modules.
    $toolkit_modules = ['imagemagick'];
    try {
      $this->container->get('module_installer')->install($toolkit_modules, TRUE);
      $this->rebuildAll();
    }
    catch (MissingDependencyException $e) {
      // The exception message has all the details. We just print out a debug
      // since we do not want to fail tests if contrib toolkits are not
      // available.
      debug($e->getMessage());
    }

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
   *
   * @return string
   *   The UUID of the newly added effect.
   */
  protected function addEffectToTestStyle($effect) {
    $style_name = 'image_effects_test';
    $style_path = 'admin/config/media/image-styles/manage/' . $style_name;

    // Get image style prior to adding a new effect.
    $image_style_pre = ImageStyle::load($style_name);

    // Add the effect.
    $this->drupalPostForm($style_path, array('new' => $effect['id']), t('Add'));
    if (!empty($effect['data'])) {
      $effect_edit = [];
      foreach($effect['data'] as $field => $value) {
        $effect_edit['data[' . $field . ']'] = $value;
      }
      $this->drupalPostForm(NULL, $effect_edit, t('Add effect'));
    }

    // Get UUID of newly added effect.
    $image_style_post = ImageStyle::load($style_name);
    foreach ($image_style_post->getEffects() as $uuid => $effect) {
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
    $style_name = 'image_effects_test';
    $style_path = 'admin/config/media/image-styles/manage/' . $style_name;
    $this->drupalPostForm($style_path . '/effects/' . $uuid . '/delete', [], t('Delete'));
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
          break;

        case 'imagemagick':
          if ($this->container->get('module_handler')->moduleExists('imagemagick')) {
            $this->changeToolkit($toolkit_id);
            \Drupal::configFactory()->getEditable('imagemagick.settings')
              ->set('debug', TRUE)
              ->save();
            // The test can only be executed if the ImageMagick 'convert' is
            // available on the shell path.
            $status = \Drupal::service('image.toolkit.manager')->createInstance('imagemagick')->checkPath('');
            if (empty($status['errors'])) {
              call_user_func($method);
            }
            else {
              debug('Tests for the Imagemagick toolkit cannot run because the \'convert\' executable is not available on the shell path.');
            }
          }
          break;

      }
    }
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
      return array(0, 0, 0, 127);
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
