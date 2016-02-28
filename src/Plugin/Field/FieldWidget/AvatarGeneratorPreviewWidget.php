<?php

/**
 * @file
 * Contains \Drupal\avatars\Plugin\Field\FieldWidget\AvatarGeneratorPreviewWidget.
 */

namespace Drupal\avatars\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsButtonsWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\avatars\Entity\AvatarGenerator;

/**
 * Widget for avatar generator selection fields.
 *
 * @FieldWidget(
 *   id = "avatars_generator_preview",
 *   label = @Translation("Avatar Generator Previews"),
 *   field_types = {
 *     "list_string",
 *   },
 *   multiple_values = TRUE
 * )
 */
class AvatarGeneratorPreviewWidget extends OptionsButtonsWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'preview_image_style' => 'thumbnail',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['preview_image_style'] = array(
      '#title' => t('Preview image style'),
      '#type' => 'select',
      '#options' => image_style_options(FALSE),
      '#empty_option' => '<' . t('Original') . '>',
      '#default_value' => $this->getSetting('preview_image_style'),
      '#description' => t('A preview of the avatar will be shown in this size.'),
    );

    return $element;
  }

  /**
   * Get label of preview image style.
   *
   * @return string|NULL
   *   Label of image style, or NULL if not set.
   */
  private function getPreviewImageStyle() {
    $image_styles = image_style_options(FALSE);
    unset($image_styles['']);
    $image_style_setting = $this->getSetting('preview_image_style');
    return isset($image_styles[$image_style_setting]) ? $image_styles[$image_style_setting] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $image_style = $this->getPreviewImageStyle() ?: $this->t('Original');
    return [t('Preview image style: @style', ['@style' => $image_style])];
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    if ($this->isDefaultValueWidget($form_state)) {
      return parent::formElement($items, $delta, $element, $form, $form_state);
    }

    $value = isset($items[$delta]->value) ? $items[$delta]->value : NULL;
    if (isset($value)) {
      $default_value = !empty($value) ? $value : '_none';
    }
    else {
      $default_value = '_none';
    }

    /** @var \Drupal\user\UserInterface $user */
    $user = $items->getEntity();

    /** @var \Drupal\avatars\AvatarManager $avatar_manager */
    $avatar_manager = \Drupal::service('avatars.avatar_manager');

    $options = [];
    $thumbs = [];

    foreach ($avatar_manager->refreshAllAvatars($user) as $preview) {
      if ($file = $preview->getAvatar()) {
        $instance_id = $preview->getAvatarGeneratorId();
        $avatar_generator = AvatarGenerator::load($instance_id);
        $options[$instance_id] = $avatar_generator->label();
        $thumbs[$instance_id] = [
          'label' => $avatar_generator->label(),
          'uri' => $file->getFileUri(),
        ];
      }
    }

    $thumbs['_none'] = ['label' => $this->t('Let the site determine which avatar to use.')];

    $element = $element + [
      '#type' => 'avatars_image_radios',
      '#thumbs' => $thumbs,
      '#style_name' => $this->getSetting('preview_image_style'),
      '#default_value' => $default_value,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    if ($this->isDefaultValueWidget($form_state)) {
      return parent::massageFormValues($values, $form, $form_state);
    }
    $new_values = [];
    foreach ($values as $value) {
      $new_values[]['value'] = $value;
    }
    return $new_values;
  }

}
