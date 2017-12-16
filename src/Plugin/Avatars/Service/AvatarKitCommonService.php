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
   *
   * @return \dpi\ak\AvatarKit\AvatarServices\AvatarServiceInterface
   *   A new service instance
   */
  protected function getService() {
    $configuration = new AvatarConfiguration();

    $width = $this->configuration['width'] ?? NULL;
    if (is_int($width)) {
      $configuration->setWidth($width);
    }

    $height = $this->configuration['height'] ?? NULL;
    if (is_int($height)) {
      $configuration->setHeight($height);
    }

    $configuration->setProtocol('https');
    return $this->createService($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) : array {
    $metadata = $this->getMetadata();

    $protocols = $metadata->protocols ?? [];
    $protocol_options = array_map(
      function (string $protocol) : string {
        return $protocol;
      },
      $protocols
    );

    $form['protocol'] = [
      '#title' => $this->t('Protocol'),
      '#options' => $protocol_options,
      '#type' => 'select',
    ];

    $form['width'] = [
      '#title' => $this->t('Source width'),
      '#type' => 'number',
    ];

    $form['height'] = [
      '#title' => $this->t('Source height'),
      '#type' => 'number',
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
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() : array {
    $configuration = parent::defaultConfiguration();
    $configuration['width'] = NULL;
    $configuration['height'] = NULL;
    return $configuration;
  }

}
