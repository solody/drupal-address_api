<?php

namespace Drupal\address_api\Plugin\rest\resource;

use CommerceGuys\Addressing\Subdivision\SubdivisionRepositoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "address_api_china_simple_subdivision_list",
 *   label = @Translation("China simple subdivision list"),
 *   uri_paths = {
 *     "canonical" = "/api/rest/address/china-simple-subdivision-list"
 *   }
 * )
 */
class ChinaSimpleSubdivisionList extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;


  /**
   * @var SubdivisionRepositoryInterface
   */
  protected $subdivisionRepository;

  /**
   * Constructs a new ChinaSimpleSubdivisionList object.
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
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user,
    SubdivisionRepositoryInterface $subdivision_repository) {
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
      $container->get('address_api.iso_code_order_subdivision_repository')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get() {

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    // 生成数据
    $data = [];
    $provinces = $this->subdivisionRepository->getList(['CN'], 'zh-hans');
    foreach ($provinces as $province_code => $province_name) {
      $data[] = [
        'name' => $province_name,
        'value' => $province_code
      ];
    }

    foreach ($provinces as $province_code => $province_name) {
      $cities = $this->subdivisionRepository->getList(['CN', $province_code], 'zh-hans');

      foreach ($cities as $city_code => $city_name) {
        $data[] = [
          'name' => $city_name,
          'value' => $city_code,
          'parent' => $province_code
        ];

        $districts = $this->subdivisionRepository->getList(['CN', $province_code, $city_code], 'zh-hans');
        if (count($districts)) {
          foreach ($districts as $district_code => $district_name) {
            $data[] = [
              'name' => $district_name,
              'value' => $district_code,
              'parent' => $city_code
            ];
          }
        } else {
          $data[] = [
            'name' => '',
            'value' => '',
            'parent' => $city_code
          ];
        }
      }
    }

    $response = new ResourceResponse($data, 200);
    return $response;
  }

}
