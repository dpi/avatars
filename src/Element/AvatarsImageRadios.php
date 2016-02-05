<?php

/**
 * @file
 * Contains \Drupal\avatars\Element\AvatarsImageRadios.
 */

namespace Drupal\avatars\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\Radios;

/**
 * Provides a form element for a set of radio buttons with images.
 *
 * Properties:
 * - #thumbs: An associative array, where the keys are the returned values for
 *   each radio button, and the values are an array containing:
 *   - 'label': A label for the radio element.
 *   - 'uri': (optional) path to an image.
 *
 * Usage example:
 * @code
 * $thumbs['my_generator] = [
 *   'label' => t('My Generator'),
 *   'uri' => 'public://path/to.image',
 * ];
 * $form['avatar_generators'] = array(
 *   '#type' => 'avatars_image_radios',
 *   '#thumbs' => $thumbs,
 *   '#style_name' => $this->getSetting('preview_image_style'),
 *   '#default_value' => $default_value,
 * );
 * @endcode
 *
 * @FormElement("avatars_image_radios")
 */
class AvatarsImageRadios extends Radios {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processRadios'],
      ],
      '#theme_wrappers' => ['radios'],
      '#pre_render' => [
        [$class, 'preRenderCompositeFormElement'],
      ],
      '#attached' => [
        'library' => ['avatars/avatars.avatars_image_radios'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function processRadios(&$element, \Drupal\Core\Form\FormStateInterface $form_state, &$complete_form) {
    static::setAttributes($element, ['avatar_preview_radios']);

    $parent = $element;
    $parent['#options'] = [];
    foreach ($parent['#thumbs'] as $id => $thumb) {
      $parent['#options'][$id] = (string) $thumb['label'];
    }
    $parent = parent::processRadios($parent, $form_state, $complete_form);

    foreach (Element::children($parent) as $key) {
      $thumb = $element['#thumbs'][$key];
      $element[$key]['#theme'] = 'avatar_preview_radio';
      static::setAttributes($element[$key], [
        'avatar_preview_radio',
        'avatar_preview_radio__' . $key,
      ]);

      // Image.
      if (isset($thumb['uri'])) {
        if ($element['#style_name']) {
          $element[$key]['image']['#theme'] = 'image_style';
          $element[$key]['image']['#style_name'] = $element['#style_name'];
        }
        else {
          $element[$key]['image']['#theme'] = 'image';
        }
        $element[$key]['image']['#uri'] = $thumb['uri'];
      }

      // Radio.
      $element[$key]['radio'] = &$parent[$key];
      $element[$key]['radio']['#parents'][] = 'radio';
    }

    return $element;
  }

}
