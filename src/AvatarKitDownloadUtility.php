<?php

namespace Drupal\avatars;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;

/**
 * Utility for creating Drupal files from responses.
 */
class AvatarKitDownloadUtility implements AvatarKitDownloadUtilityInterface {

  /**
   * Storage for 'file' entity.
   *
   * @var \Drupal\Core\Entity\ContentEntityStorageInterface
   */
  protected $fileStorage;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The file usage service.
   *
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  protected $fileUsage;

  /**
   * The Guzzle HTTP middleware.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * The logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Creates a new AvatarKitDownloadUtility instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system service.
   * @param \Drupal\file\FileUsage\FileUsageInterface $fileUsage
   *   The file usage service.
   * @param \GuzzleHttp\Client $httpClient
   *   The Guzzle HTTP middleware.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, FileSystemInterface $fileSystem, FileUsageInterface $fileUsage, Client $httpClient, LoggerInterface $logger) {
    $this->fileStorage = $entityTypeManager->getStorage('file');
    $this->fileSystem = $fileSystem;
    $this->fileUsage = $fileUsage;
    $this->httpClient = $httpClient;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function get(string $uri) : ResponseInterface {
    $valid_url = !empty($uri) && parse_url($uri) !== FALSE;
    if (!$valid_url) {
      throw new \InvalidArgumentException('Malformed Url');
    }
    $response = $this->httpClient->get($uri);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function createFile(ResponseInterface $response, string $default_filename) : ?FileInterface {
    $stream = $response->getBody();

    // Save stream to temporary file.
    // Following block is file_unmanaged_save_data() but without the Drupal
    // message.
    $temp_filepath = $this->fileSystem
      ->tempnam('temporary://', 'temp');
    if (\file_put_contents($temp_filepath, $stream) === FALSE) {
      $this->logger->notice('Unable to create temporary file: %filename.', ['%filename' => $temp_filepath]);
      return NULL;
    }

    $path_info = pathinfo($default_filename);
    $final_filename = $path_info['filename'] . '.' . $this->getExtension($response);

    // Create destination directory if it doesn't exist.
    $directory = dirname($final_filename);
    if (file_prepare_directory($directory, FILE_CREATE_DIRECTORY) !== TRUE) {
      $this->logger->notice('Unable to create directory: %directory.', ['%directory' => $directory]);
      return NULL;
    }

    // Move the temporary file to the final destination.
    try {
      $final_filepath = $this->moveFile($temp_filepath, $final_filename, FILE_EXISTS_REPLACE);
    }
    catch (\Exception $e) {
      // file_unmanaged_move() logs its own errors.
      return NULL;
    }

    // Create file entity.
    // file_save_data() used as inspiration.
    /** @var \Drupal\file\FileInterface $file */
    $file = $this->fileStorage->create();
    $file->setFileUri($final_filepath);
    $file->setPermanent();

    $violation_count = $this->logViolations($file);
    if ($violation_count) {
      return NULL;
    }

    $file->save();
    return $file;
  }

  /**
   * Abstraction of file_unmanaged_move.
   *
   * @param array ...
   *   Arguments to pass to file_unmanaged_move().
   *
   * @see \file_unmanaged_move();
   *
   * @return string
   *   The path to the new file.
   *
   * @throws \Exception
   *   Throws exception in the event of an error.
   */
  protected function moveFile(...$args) : string {
    $result = \file_unmanaged_move(...$args);
    if ($result === FALSE) {
      throw new \Exception('Failed to move file.');
    }
    return $result;
  }

  /**
   * Guess file extension for a response.
   *
   * @param \Psr\Http\Message\ResponseInterface $response
   *   A response object.
   *
   * @return string
   *   A file extension. 'file' is returned if no guess could be made.
   */
  protected function getExtension(ResponseInterface $response) : string {
    $guesser = ExtensionGuesser::getInstance();
    $mime = $response->getHeaderLine('Content-Type');
    return $guesser->guess($mime) ?? 'file';
  }

  /**
   * Validates a file entity and logs any violations.
   *
   * @param \Drupal\file\FileInterface $entity
   *   A file entity to validate.
   *
   * @return int
   *   Number of violations logged.
   */
  protected function logViolations(FileInterface $entity) : int {
    $violations = $entity->validate();
    if ($violations) {
      foreach ($violations as $violation) {
        $message = $violation->getMessage();
        $this->logger->notice('Unable to save file %file: %message.', [
          '%file' => $entity->getFileUri(),
          '%message' => $message,
        ]);
      }
    }
    return $violations->count();
  }

}
