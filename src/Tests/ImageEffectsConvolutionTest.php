<?php

namespace Drupal\image_effects\Tests;

/**
 * Convolution effect test.
 *
 * @group Image Effects
 */
class ImageEffectsConvolutionTest extends ImageEffectsTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // @todo This effect does not work on GraphicsMagick.
    $this->imagemagickPackages['graphicsmagick'] = FALSE;
  }

  /**
   * Convolution effect test.
   */
  public function testConvolutionEffect() {
    // Add convolution effect to the test image style.
    $effect = [
      'id' => 'image_effects_convolution',
      'data' => [
        'kernel][entries][0][0' => 0,
        'kernel][entries][0][1' => 1,
        'kernel][entries][0][2' => 2,
        'kernel][entries][1][0' => 3,
        'kernel][entries][1][1' => 4,
        'kernel][entries][1][2' => 5,
        'kernel][entries][2][0' => 6,
        'kernel][entries][2][1' => 7,
        'kernel][entries][2][2' => 8,
        'divisor' => 9,
        'offset' => 0,
        'label' => 'test_convolution',
      ],
    ];
    $uuid = $this->addEffectToTestStyle($effect);

    // Assert that effect is configured as expected.
    $effect_configuration_data = $this->testImageStyle->getEffect($uuid)->getConfiguration()['data'];
    $this->assertEqual([[0, 1, 2], [3, 4, 5], [6, 7, 8]], $effect_configuration_data['kernel']);
    $this->assertEqual(9, $effect_configuration_data['divisor']);
    $this->assertEqual(0, $effect_configuration_data['offset']);
    $this->assertEqual('test_convolution', $effect_configuration_data['label']);

    // Remove effect.
    $this->removeEffectFromTestStyle($uuid);

    // Test operations on toolkits.
    $this->executeTestOnToolkits([$this, 'doTestConvolutionOperations']);
  }

  /**
   * Convolution operations test.
   */
  public function doTestConvolutionOperations() {
    $original_uri = $this->getTestImageCopyUri('/files/image-test.png', 'simpletest');
    $derivative_uri = 'public://test-images/image-test-derived.png';

    // Add the effect for operation test.
    $effect = [
      'id' => 'image_effects_convolution',
      'data' => [
        'kernel][entries][0][0' => 9,
        'kernel][entries][0][1' => 9,
        'kernel][entries][0][2' => 9,
        'kernel][entries][1][0' => 9,
        'kernel][entries][1][1' => 9,
        'kernel][entries][1][2' => 9,
        'kernel][entries][2][0' => 9,
        'kernel][entries][2][1' => 9,
        'kernel][entries][2][2' => 9,
        'divisor' => 9,
        'offset' => 0,
        'label' => 'test_convolution',
      ],
    ];
    $uuid = $this->addEffectToTestStyle($effect);

    // Apply the operation, via the effect.
    $image = $this->imageFactory->get($original_uri);
    $effect = $this->testImageStyle->getEffect($uuid);
    $effect->applyEffect($image);

    // Toolkit-specific tests.
    switch ($this->imageFactory->getToolkitId()) {
      case 'gd':
        // For the GD toolkit, just test derivative image is valid.
        $image->save($derivative_uri);
        $derivative_image = $this->imageFactory->get($derivative_uri);
        $this->assertTrue($derivative_image->isValid());
        break;

      case 'imagemagick':
        // For the Imagemagick toolkit, check the command line argument has
        // been formatted properly.
        $argument = $image->getToolkit()->getArguments()[$image->getToolkit()->findArgument('-morphology')];
        $this->assertEqual("-morphology Convolve '3x3:1,1,1 1,1,1 1,1,1'", $argument);
        break;

    }

    // Remove effect.
    $this->removeEffectFromTestStyle($uuid);

  }

}
