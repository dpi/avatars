<?php

namespace Drupal\avatars\Event;

/**
 * Defines events for Avatar Kit.
 */
final class AvatarKitEvents {

  /**
   * Used to determine the preference order of avatar services for an entity.
   *
   * Grants the opportunity to modify the order of in which avatar services
   * are attempted for an entity.
   *
   * @Event
   *
   * @see \Drupal\avatars\Event\EntityServicePreferenceEvent
   */
  const ENTITY_SERVICE_PREFERENCE = 'avatars.service_preference.entity';

  /**
   * Used to alter avatar service plugin definitions.
   *
   * @Event
   *
   * @see \Drupal\avatars\Event\AvatarKitServiceDefinitionAlterEvent
   */
  const PLUGIN_SERVICE_ALTER = 'avatars.service_plugin.info_alter';

}
