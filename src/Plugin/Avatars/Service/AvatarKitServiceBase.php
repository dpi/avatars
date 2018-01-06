<?php

namespace Drupal\avatars\Plugin\Avatars\Service;

use dpi\ak\AvatarIdentifier;
use dpi\ak\AvatarIdentifierInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Abstract class for Avatar Kit service plugins.
 */
abstract class AvatarKitServiceBase extends PluginBase implements AvatarKitServiceInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() : array {
    $configuration = [];
    if ($this->pluginIsDynamic()) {
      $configuration['lifetime'] = 3600 * 24;
    }
    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() : array {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) : void {
    $this->configuration = NestedArray::mergeDeep(
      $this->defaultConfiguration(),
      $configuration
    );
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() : array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) : array {
    if ($this->pluginIsDynamic()) {
      $lifetime = $this->configuration['lifetime'];
      $form['lifetime'] = [
        '#type' => 'unlimited_number',
        '#title' => $this->t('Lifetime'),
        '#description' => $this->t('How long avatars are cached.'),
        '#default_value' => isset($lifetime) ? $lifetime : 'unlimited',
        '#step' => 60,
        '#min' => 60,
        '#field_suffix' => $this->t('seconds'),
        '#options' => [
          'unlimited' => $this->t('Never delete'),
          'limited' => $this->t('Delete after'),
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) : void {}

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) : void {
    if ($this->pluginIsDynamic()) {
      $lifetime = $form_state->getValue('lifetime');
      $this->configuration['lifetime'] = $lifetime == 'unlimited' ? NULL : $lifetime;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createIdentifier(): AvatarIdentifierInterface {
    return new AvatarIdentifier();
  }

  /**
   * Determine whether the plugin is dynamic.
   *
   * @return bool
   *   Whether the plugin is dynamic.
   */
  protected function pluginIsDynamic(): bool {
    return $this->getPluginDefinition()['dynamic'] ?? FALSE;
  }

}
