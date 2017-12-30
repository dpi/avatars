<?php

namespace Drupal\avatars\Plugin\Avatars\Service;

use dpi\ak\Annotation\AvatarService;
use dpi\ak\AvatarConfiguration;
use dpi\ak\AvatarConfigurationInterface;
use dpi\ak\AvatarIdentifierInterface;
use dpi\ak\AvatarKit\AvatarServices\AvatarServiceInterface;
use dpi\ak\AvatarServiceDiscoveryInterface;
use dpi\ak\AvatarServiceFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Automatically creates services based on plugins from Avatar Kit library.
 *
 * @AvatarKitService(
 *   id = "avatars_ak_common",
 *   deriver = "Drupal\avatars\Plugin\Derivative\AvatarKitCommonService"
 * )
 */
abstract class AvatarKitCommonService extends AvatarKitServiceBase implements ContainerFactoryPluginInterface {

  /**
   * Avatar Kit service discovery.
   *
   * @var \dpi\ak\AvatarServiceDiscoveryInterface
   */
  protected $avatarKitServiceDiscovery;

  /**
   * Avatar Kit service factory.
   *
   * @var \dpi\ak\AvatarServiceFactoryInterface
   */
  protected $avatarKitServiceFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AvatarServiceDiscoveryInterface $avatarKitServiceDiscovery, AvatarServiceFactoryInterface $avatarKitServiceFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->avatarKitServiceDiscovery = $avatarKitServiceDiscovery;
    $this->avatarKitServiceFactory = $avatarKitServiceFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('avatars.avatar_kit.discovery.services'),
      $container->get('avatars.avatar_kit.factory.services')
    );
  }

  use StringTranslationTrait;

  /**
   * Creates a service from Avatar Kit library.
   *
   * @param \dpi\ak\AvatarConfigurationInterface $configuration
   *   Avatar service configuration.
   *
   * @return \dpi\ak\AvatarKit\AvatarServices\AvatarServiceInterface
   *   A new service instance
   */
  protected function createService(AvatarConfigurationInterface $configuration) : AvatarServiceInterface {
    $id = $this->getDerivativeId();
    return $this->avatarKitServiceFactory->createService($id, $configuration);
  }

  /**
   * Create a new configuration object.
   *
   * @return \dpi\ak\AvatarConfigurationInterface
   *   A new configuration object.
   */
  protected function newAvatarConfiguration(): AvatarConfigurationInterface {
    return new AvatarConfiguration();
  }

  /**
   * Gets metadata for a service from Avatar Kit library.
   *
   * @return \dpi\ak\Annotation\AvatarService
   *   Metadata for a service from Avatar Kit library.
   */
  protected function getMetadata() : AvatarService {
    $id = $this->getDerivativeId();
    return $this->avatarKitServiceDiscovery->getMetadata($id);
  }

  /**
   * Create a service instance.
   *
   * @return \dpi\ak\AvatarKit\AvatarServices\AvatarServiceInterface
   *   A new service instance
   */
  protected function getService() {
    $configuration = $this->newAvatarConfiguration();

    $width = $this->configuration['width'] ?? NULL;
    if (is_int($width)) {
      $configuration->setWidth($width);
    }

    $height = $this->configuration['height'] ?? NULL;
    if (is_int($height)) {
      $configuration->setHeight($height);
    }

    $protocol = $this->configuration['protocol'] ?? NULL;
    if (!empty($protocol)) {
      $configuration->setProtocol($protocol);
    }

    return $this->createService($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) : array {
    $metadata = $this->getMetadata();

    $protocols = $metadata->protocols ?? [];
    $protocol_options = array_combine($protocols, $protocols);

    $form['protocol'] = [
      '#title' => $this->t('Protocol'),
      '#options' => $protocol_options,
      '#default_value' => $this->configuration['protocol'] ?? NULL,
      '#required' => TRUE,
      '#type' => 'select',
    ];

    $form['width'] = [
      '#title' => $this->t('Source width'),
      '#type' => 'number',
      '#default_value' => $this->configuration['width'] ?? NULL,
    ];

    $form['height'] = [
      '#title' => $this->t('Source height'),
      '#type' => 'number',
      '#default_value' => $this->configuration['height'] ?? NULL,
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['width'] = $form_state->getValue('width');
    $this->configuration['height'] = $form_state->getValue('height');
    $this->configuration['protocol'] = $form_state->getValue('protocol');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() : array {
    $configuration = parent::defaultConfiguration();
    $configuration['width'] = NULL;
    $configuration['height'] = NULL;
    $configuration['protocol'] = NULL;
    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function getAvatar(AvatarIdentifierInterface $identifier): ?string {
    return $this->getService()
      ->getAvatar($identifier);
  }

  /**
   * {@inheritdoc}
   */
  public function createIdentifier(): AvatarIdentifierInterface {
    return $this->getService()
      ->createIdentifier();
  }

}
