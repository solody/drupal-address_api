<?php

namespace Drupal\address_api\Plugin\rest\resource;

use CommerceGuys\Addressing\Subdivision\SubdivisionRepositoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "address_api_query_subdivision_values_by_name",
 *   label = @Translation("Query subdivision values by name"),
 *   uri_paths = {
 *     "create" = "/api/rest/address-api/query-subdivision-values-by-name"
 *   }
 * )
 */
class QuerySubdivisionValuesByName extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $currentUser;

  /**
   * The SubdivisionRepository service instance.
   *
   * @var \CommerceGuys\Addressing\Subdivision\SubdivisionRepositoryInterface
   */
  protected SubdivisionRepositoryInterface $subdivisionRepository;

  /**
   * Constructs a new QuerySubdivisions object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   * @param \CommerceGuys\Addressing\Subdivision\SubdivisionRepositoryInterface $subdivision_repository
   *   The SubdivisionRepository service instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user,
    SubdivisionRepositoryInterface $subdivision_repository,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
    $this->subdivisionRepository = $subdivision_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('address_api'),
      $container->get('current_user'),
      $container->get('address.subdivision_repository')
    );
  }

  /**
   * Responds to POST requests.
   *
   * @param array $data
   *   The entity object.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post(array $data): ModifiedResourceResponse {

    $parents = [$data['country']];
    $subdivisions = $this->subdivisionRepository->getList($parents, $data['locale']);

    $values = [];

    foreach ($data['names'] as $name) {
      foreach ($subdivisions as $subdivision_code => $subdivision_name) {
        if ($subdivision_name === $name) {
          $values[] = $subdivision_code;
          $parents[] = $subdivision_code;
          $subdivisions = $this->subdivisionRepository->getList($parents, $data['locale']);
          break;
        }
      }
    }

    return new ModifiedResourceResponse($values, 200);
  }

}
