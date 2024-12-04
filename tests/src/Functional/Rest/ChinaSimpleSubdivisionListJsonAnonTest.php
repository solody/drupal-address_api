<?php

namespace Drupal\Tests\address_api\Functional\Rest;

use Drupal\Tests\rest\Functional\AnonResourceTestTrait;

/**
 * Test for BindPhone rest resource.
 */
class ChinaSimpleSubdivisionListJsonAnonTest extends ChinaSimpleSubdivisionListTestBase {

  use AnonResourceTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $format = 'json';

  /**
   * {@inheritdoc}
   */
  protected static $mimeType = 'application/json';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

}
