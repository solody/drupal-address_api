<?php

namespace Drupal\Tests\address_api\Functional\Rest;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Url;
use Drupal\Tests\rest\Functional\ResourceTestBase;
use GuzzleHttp\RequestOptions;

/**
 * Test for QuerySubdivisionValuesByName rest resource.
 *
 * @coversDefaultClass \Drupal\address_api\Plugin\rest\resource\QuerySubdivisionValuesByName
 */
abstract class QuerySubdivisionValuesByNameTestBase extends ResourceTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['address_api', 'basic_auth'];

  /**
   * {@inheritdoc}
   */
  protected static $resourceConfigId = 'address_api_query_subdivision_values_by_name';

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $auth = isset(static::$auth) ? [static::$auth] : [];
    $this->provisionResource([static::$format], $auth, ['POST']);
  }

  /**
   * Do testing.
   *
   * @covers ::post
   */
  public function testPost() {

    // Login in if necessary.
    $this->initAuthentication();
    // Add permissions to role if necessary.
    $this->setUpAuthorization('POST');

    // Append credential headers for authentication.
    $request_options = $this->getAuthenticationRequestOptions('POST');
    // Send json text as body.
    $request_options[RequestOptions::JSON] = [
      'country' => 'CN',
      'names' => ['广东省', '深圳市', '福田区'],
      'locale' => 'zh-hans',
    ];
    // Set Content-Type of the body to send.
    $request_options[RequestOptions::HEADERS]['Content-Type'] = static::$mimeType;

    // Make request.
    $response = $this->request('POST', $this->getRequestUrl('POST'), $request_options);

    if (static::$auth) {
      // Authenticated access.
      $this->assertResourceResponse(
        200,
        '',
        $response
      );
    }
    else {
      // Anonymous access.
      $this->assertResourceResponse(
        200,
        '',
        $response
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function setUpAuthorization($method) {
    switch ($method) {
      case 'POST':
        $this->grantPermissionsToTestedRole(['restful post ' . static::$resourceConfigId]);
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
    return Url::fromRoute('rest.' . static::$resourceConfigId . '.POST', [
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
