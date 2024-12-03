<?php

namespace Drupal\Tests\address_api\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Simple test to ensure that main page loads with module enabled.
 *
 * @group address_api
 */
class LoadTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'address',
    'address_api',
  ];

  /**
   * A user with permission to administer site configuration.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['address']);
  }

  /**
   * Tests that the home page loads with a 200 response.
   */
  public function testLoad() {
    /** @var \Drupal\address\Repository\CountryRepository $country_repository */
    $country_repository = $this->container->get('address.country_repository');
    $country_names = $country_repository->getList('zh-hant');
    $countrys = $country_repository->getAll('zh-hans');

    /** @var \Drupal\address\Repository\SubdivisionRepository $subdivision_repository */
    $subdivision_repository = $this->container->get('address.subdivision_repository');
    $rs = $subdivision_repository->getAll(['CN', 'GD', 'Guangzhou Shi']);

  }

}
