<?php

namespace Drupal\image_effects\Tests;
use Drupal\system\Tests\Image\ToolkitTestBase;

/**
 * Tests that the image effects pass parameters to the toolkit correctly.
 *
 * @group image
 */
class ImageEffectsConvolutionTest extends ToolkitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('image', 'image_effects');

  /**
   * The image effect manager.
   *
   * @var \Drupal\image\ImageEffectManager
   */
  protected $manager;

  protected function setUp() {
    parent::setUp();
    $this->manager = $this->container->get('plugin.manager.image.effect');
  }

  /**
   * Test the image_effects_convolution effect parameters.
   */
  function testConvolutionEffectParameters() {
    $this->assertImageEffect('image_effects_convolution', array(
      'kernel' => [[1, 1, 1], [1, 1, 1], [1, 1, 1]],
      'divisor' => 9,
      'offset' => 0,
    ));
    // @todo: uncomment the following instruction when
    // assertToolkitOperationsCalled() will take into account non core effects
    // $this->assertToolkitOperationsCalled(array('convolution'));

    // Check the parameters.
    $calls = $this->imageTestGetAllCalls();
    $this->assertEqual($calls['convolution'][0][0], [[1, 1, 1], [1, 1, 1], [1, 1, 1]], 'Kernel matrix was passed correctly');
    $this->assertEqual($calls['convolution'][0][1], 9, 'Divisor was passed correctly');
    $this->assertEqual($calls['convolution'][0][2], 0, 'Offset was passed correctly');
  }

  /**
   * Test the image_effects_convolution_sharpen effect parameters.
   */
  function testConvolutionSharpenEffectParameters() {
    $this->assertImageEffect('image_effects_convolution_sharpen', array(
      'level' => 25,
    ));
    // @todo: uncomment the following instruction when
    // assertToolkitOperationsCalled() will take into account non core effects
    // $this->assertToolkitOperationsCalled(array('convolution_sharpen'));

    // Check the parameters.
    $sharpenlevel = 25 / 100;
    $kernel = [
      [-$sharpenlevel, -$sharpenlevel, -$sharpenlevel],
      [-$sharpenlevel, 8 * $sharpenlevel + 1, -$sharpenlevel],
      [-$sharpenlevel, -$sharpenlevel, -$sharpenlevel]
    ];
    $calls = $this->imageTestGetAllCalls();
    $this->assertEqual($calls['convolution'][0][0], $kernel, 'Kernel was passed correctly');
    $this->assertEqual($calls['convolution'][0][1], 1, 'Divisor was passed correctly');
    $this->assertEqual($calls['convolution'][0][2], 0, 'Offset was passed correctly');
  }

  /**
   * Asserts the effect processing of an image effect plugin.
   *
   * Note that this method was coppied from class
   * Drupal\image\Tests\assertImageEffect.
   *
   * @param string $effect_name
   *   The name of the image effect to test.
   * @param array $data
   *   The data to pass to the image effect.
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  protected function assertImageEffect($effect_name, array $data) {
    $effect = $this->manager->createInstance($effect_name, array('data' => $data));
    return $this->assertTrue($effect->applyEffect($this->image), 'Function returned the expected value.');
  }
}
