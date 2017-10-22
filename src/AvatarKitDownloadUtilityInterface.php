<?php

namespace Drupal\avatars;

use Drupal\file\FileInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface for download utility.
 */
interface AvatarKitDownloadUtilityInterface {

  /**
   * Get stream for a file.
   *
   * @param string $uri
   *   The URL of the file to download.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response stream.
   *
   * @throws \InvalidArgumentException
   *   If the URI is defective.
   * @throws \Exception
   *   Various other exceptions thrown from HTTP client.
   */
  public function get(string $uri) : ResponseInterface;

  /**
   * Creates a file entity from a PSR response.
   *
   * @param \Psr\Http\Message\ResponseInterface $response
   *   File to download.
   * @param string $default_filename
   *   The desirable file name. Extension may be substituted.
   *
   * @return \Drupal\file\FileInterface|null
   *   The new file entity, or null if the file could not be downloaded.
   */
  public function createFile(ResponseInterface $response, string $default_filename) : ?FileInterface;

}