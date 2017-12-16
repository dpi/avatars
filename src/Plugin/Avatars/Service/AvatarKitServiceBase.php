<?php

namespace Drupal\avatars\Plugin\Avatars\Service;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;

/**
 * Abstract class for Avatar Kit service plugins.
 */
abstract class AvatarKitServiceBase extends PluginBase implements AvatarKitServiceInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() : array {
    return [];
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
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) : void {}

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) : void {}

}
