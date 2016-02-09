<?php

namespace Drupal\image_effects\Plugin;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * image_effects base plugin interface.
 */
interface ImageEffectsPluginBaseInterface extends ConfigurablePluginInterface, ContainerFactoryPluginInterface, PluginFormInterface {
  /**
   * Return a form element to select the plugin content.
   *
   * @param array $options
   *   (Optional) An array of additional Form API keys and values.
   *
   * @return array
   *   Render array of the form element.
   */
  public function selectionElement(array $options = array());

  /**
   * Get the image_effects plugin type.
   *
   * @return string
   *   The plugin type.
   */
  public function getType();

  /**
   * Determines if plugin can be used.
   *
   * @return boolean
   *   TRUE if the plugin is available.
   */
  public static function isAvailable();
}
