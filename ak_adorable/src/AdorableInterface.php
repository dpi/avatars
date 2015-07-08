<?php

/**
 * @file
 * Contains \Drupal\ak_adorable\AdorableInterface.
 */

namespace Drupal\ak_adorable;

/**
 * Interface for the Adorable.io API.
 */
interface AdorableInterface {

  /*
   * URL for insecure requests.
   *
   * @var string
   */
  const ADORABLE_HOSTNAME = 'api.adorable.io';

  /*
   * Maximum width images output by the endpoint.
   *
   * @var int
   */
  const DIMENSION_MAXIMUM_WIDTH = 400;

  /*
   * Minimum width images output by the endpoint.
   *
   * @var int
   */
  const DIMENSION_MINIMUM_WIDTH = 40;

}
