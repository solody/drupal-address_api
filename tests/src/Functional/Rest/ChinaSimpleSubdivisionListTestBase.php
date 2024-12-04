<?php

namespace Drupal\Tests\address_api\Functional\Rest;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Url;
use Drupal\Tests\rest\Functional\ResourceTestBase;

/**
 * Test for ChinaSimpleSubdivisionList rest resource.
 *
 * @coversDefaultClass \Drupal\address_api\Plugin\rest\resource\ChinaSimpleSubdivisionList
 */
abstract class ChinaSimpleSubdivisionListTestBase extends ResourceTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['address_api', 'basic_auth'];

  /**
   * {@inheritdoc}
   */
  protected static $resourceConfigId = 'address_api_china_simple_subdivision_list';

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $auth = isset(static::$auth) ? [static::$auth] : [];
    $this->provisionResource([static::$format], $auth, ['GET']);
  }

  /**
   * Do testing.
   *
   * @covers ::get
   */
  public function testGet() {

    // Login in if necessary.
    $this->initAuthentication();
    // Add permissions to role if necessary.
    $this->setUpAuthorization('GET');

    // Append credential headers for authentication.
    $request_options = $this->getAuthenticationRequestOptions('GET');

    // Make request.
    $response = $this->request('GET', $this->getRequestUrl('GET'), $request_options);

    if (static::$auth) {
      // Authenticated access.
      $this->assertResourceResponse(
        200,
        FALSE,
        $response
      );
    }
    else {
      // Anonymous access.
      $this->assertResourceResponse(
        200,
        FALSE,
        $response
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function setUpAuthorization($method) {
    switch ($method) {
      case 'GET':
        $this->grantPermissionsToTestedRole(['restful get ' . static::$resourceConfigId]);
        break;

      default:
        throw new \UnexpectedValueException();
    }
  }

  /**
   * Get request url of specific method.
   *
   * @param string $method
   *   The method.
   *
   * @return \Drupal\Core\Url
   *   The url to request.
   */
  protected function getRequestUrl(string $method): Url {
    return Url::fromRoute('rest.' . static::$resourceConfigId . '.' . $method, [
      '_format' => static::$format,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function getExpectedUnauthorizedAccessCacheability() {
    return (new CacheableMetadata())
      ->setCacheTags([
        '4xx-response',
        'config:user.role.anonymous',
        'http_response',
      ])
      ->setCacheContexts(['user.permissions']);
  }

  /**
   * {@inheritdoc}
   */
  protected function assertNormalizationEdgeCases($method, Url $url, array $request_options): void {}

}
