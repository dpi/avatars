<?php

namespace Drupal\avatars\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the annotation for AvatarKitService plugins.
 *
 * @Annotation
 */
class AvatarKitService extends Plugin {

  /**
   * Unique identifier of the service.
   *
   * @var string
   * @Required
   */
  public $id;

  /**
   * Description of the service.
   *
   * @var string
   * @Required
   */
  public $description;

  /**
   * Whether the service uses File entities as a source.
   *
   * @var bool
   */
  public $files = FALSE;

}
