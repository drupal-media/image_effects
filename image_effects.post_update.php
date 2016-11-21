<?php

/**
 * @file
 * Post-update functions for Image Effects.
 */

use Drupal\image\Entity\ImageStyle;

/**
 * @addtogroup updates-8.x-1.0-alpha
 * @{
 */

/**
 * Add 'maximum_chars' and 'excess_chars_text' parameters to 'Text Overlay' effects.
 */
function image_effects_post_update_text_overlay_maximum_chars() {
  foreach (ImageStyle::loadMultiple() as $image_style) {
    $edited = FALSE;
    foreach ($image_style->getEffects() as $effect) {
      if ($effect->getPluginId() === "image_effects_text_overlay") {
        $configuration = $effect->getConfiguration();
        $configuration['data']['text']['maximum_chars'] = NULL;
        $configuration['data']['text']['excess_chars_text'] = t('…');
        unset($configuration['data']['preview_bar']);
        $effect->setConfiguration($configuration);
        $edited = TRUE;
      }
    }
    if ($edited) {
      $image_style->save();
    }
  }
}

/**
 * Add 'strip_tags' and 'decode_entities' parameters to 'Text Overlay' effects.
 */
function image_effects_post_update_text_overlay_strip_tags() {
  foreach (ImageStyle::loadMultiple() as $image_style) {
    $edited = FALSE;
    foreach ($image_style->getEffects() as $effect) {
      if ($effect->getPluginId() === "image_effects_text_overlay") {
        $configuration = $effect->getConfiguration();
        $configuration['data']['text']['strip_tags'] = TRUE;
        $configuration['data']['text']['decode_entities'] = TRUE;
        unset($configuration['data']['preview_bar']);
        $effect->setConfiguration($configuration);
        $edited = TRUE;
      }
    }
    if ($edited) {
      $image_style->save();
    }
  }
}

/**
 * @} End of "addtogroup updates-8.x-1.0-alpha".
 */
