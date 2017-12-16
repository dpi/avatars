<?php

namespace Drupal\avatars\Plugin\Avatars\Service;

use dpi\ak\AvatarKit\AvatarServices\AvatarServiceInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\DerivativeInspectionInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;

/**
 * Interface for a Avatar Kit service plugins.
 */
interface AvatarKitServiceInterface extends AvatarServiceInterface, PluginInspectionInterface, DerivativeInspectionInterface, PluginFormInterface, ConfigurablePluginInterface {}
