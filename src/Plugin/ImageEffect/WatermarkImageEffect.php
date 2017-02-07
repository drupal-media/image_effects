<?php

namespace Drupal\image_effects\Plugin\ImageEffect;

use Drupal\Core\Image\ImageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\image\ConfigurableImageEffectBase;
use Drupal\image_effects\Plugin\ImageEffectsPluginBaseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Image\ImageFactory;

/**
 * Class WatermarkImageEffect.
 *
 * @ImageEffect(
 *   id = "image_effects_watermark",
 *   label = @Translation("Watermark"),
 *   description = @Translation("Add watermark image effect.")
 * )
 */
class WatermarkImageEffect extends ConfigurableImageEffectBase implements ContainerFactoryPluginInterface {

  /**
   * The image factory service.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * The image selector plugin.
   *
   * @var \Drupal\image_effects\Plugin\ImageEffectsPluginBaseInterface
   */
  protected $imageSelector;

  /**
   * Constructs an WatermarkImageEffect object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Image\ImageFactory $image_factory
   *   The image factory service.
   * @param \Drupal\image_effects\Plugin\ImageEffectsPluginBaseInterface $image_selector
   *   The image selector plugin.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, LoggerInterface $logger, ImageFactory $image_factory, ImageEffectsPluginBaseInterface $image_selector) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $logger);
    $this->imageFactory = $image_factory;
    $this->imageSelector = $image_selector;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')->get('image'),
      $container->get('image.factory'),
      $container->get('plugin.manager.image_effects.image_selector')->getPlugin()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'placement' => 'center-center',
      'x_offset' => 0,
      'y_offset' => 0,
      'opacity' => 100,
      'watermark_image' => '',
      'watermark_scale' => NULL,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $summary = array(
      '#theme' => 'image_effects_watermark_summary',
      '#data' => $this->configuration,
    );
    $summary += parent::getSummary();

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['placement'] = [
      '#type' => 'radios',
      '#title' => $this->t('Placement'),
      '#options' => [
        'left-top' => $this->t('Top left'),
        'center-top' => $this->t('Top center'),
        'right-top' => $this->t('Top right'),
        'left-center' => $this->t('Center left'),
        'center-center' => $this->t('Center'),
        'right-center' => $this->t('Center right'),
        'left-bottom' => $this->t('Bottom left'),
        'center-bottom' => $this->t('Bottom center'),
        'right-bottom' => $this->t('Bottom right'),
      ],
      '#theme' => 'image_anchor',
      '#default_value' => $this->configuration['placement'],
      '#description' => $this->t('Position of the watermark on the underlying image.'),
    ];
    $form['x_offset'] = [
      '#type'  => 'number',
      '#title' => $this->t('Horizontal offset'),
      '#field_suffix'  => 'px',
      '#description'   => $this->t('Additional horizontal offset from placement.'),
      '#default_value' => $this->configuration['x_offset'],
      '#maxlength' => 4,
      '#size' => 4,
    ];
    $form['y_offset'] = [
      '#type'  => 'number',
      '#title' => $this->t('Vertical offset'),
      '#field_suffix'  => 'px',
      '#description'   => $this->t('Additional vertical offset from placement.'),
      '#default_value' => $this->configuration['y_offset'],
      '#maxlength' => 4,
      '#size' => 4,
    ];
    $form['opacity'] = [
      '#type' => 'number',
      '#title' => $this->t('Opacity'),
      '#field_suffix' => '%',
      '#description' => $this->t('Opacity: 0 - 100'),
      '#default_value' => $this->configuration['opacity'],
      '#min' => 0,
      '#max' => 100,
      '#maxlength' => 3,
      '#size' => 3,
    ];
    $options = [
      '#title' => $this->t('File name'),
      '#description' => $this->t('Image to use for watermark effect.'),
      '#default_value' => $this->configuration['watermark_image'],
    ];
    $form['watermark_image'] = $this->imageSelector->selectionElement($options);
    $form['watermark_scale'] = [
      '#type' => 'number',
      '#title' => $this->t('Scale'),
      '#field_suffix' => '%',
      '#description' => $this->t('Scales the overlay image with respect to the source image. Leave empty to use the actual size of the overlay image.'),
      '#default_value' => $this->configuration['watermark_scale'],
      '#min' => 1,
      '#maxlength' => 4,
      '#size' => 4,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['placement'] = $form_state->getValue('placement');
    $this->configuration['x_offset'] = $form_state->getValue('x_offset');
    $this->configuration['y_offset'] = $form_state->getValue('y_offset');
    $this->configuration['opacity'] = $form_state->getValue('opacity');
    $this->configuration['watermark_image'] = $form_state->getValue('watermark_image');
    $this->configuration['watermark_scale'] = $form_state->getValue('watermark_scale');
  }

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    $watermark_image = $this->imageFactory->get($this->configuration['watermark_image']);
    if (!$watermark_image->isValid()) {
      $this->logger->error('Image watermark failed using the %toolkit toolkit on %path', ['%toolkit' => $image->getToolkitId(), '%path' => $this->configuration['watermark_image']]);
      return FALSE;
    }
    if ($this->configuration['watermark_scale'] !== NULL && $this->configuration['watermark_scale'] > 0) {
      // Scale the overlay with respect to the dimensions of the source being
      // overlaid. To maintain the aspect ratio, only the width of the overlay
      // is scaled like that, the height of the overlay follows the aspect
      // ratio.
      $overlay_w = round($image->getWidth() * $this->configuration['watermark_scale'] / 100);
      if (!$watermark_image->scale($overlay_w, NULL, TRUE)) {
        return FALSE;
      }
    }
    list($x, $y) = explode('-', $this->configuration['placement']);
    $x_pos = round(image_filter_keyword($x, $image->getWidth(), $watermark_image->getWidth()));
    $y_pos = round(image_filter_keyword($y, $image->getHeight(), $watermark_image->getHeight()));

    return $image->apply('watermark', [
      'x_offset' => $x_pos + $this->configuration['x_offset'],
      'y_offset' => $y_pos + $this->configuration['y_offset'],
      'opacity' => $this->configuration['opacity'],
      'watermark_image' => $watermark_image,
    ]);
  }

}
