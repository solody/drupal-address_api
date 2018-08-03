<?php

namespace Drupal\address_api\Repository;

use Drupal\address\Repository\SubdivisionRepository;
use CommerceGuys\Addressing\Locale;
/**
 * Provides subdivisions.
 *
 * Subdivisions are stored on disk in JSON and cached inside Drupal.
 */
class ISOCodeOrderSubdivisionRepository extends SubdivisionRepository {

  /**
   * {@inheritdoc}
   */
  public function getList(array $parents, $locale = null)
  {
    $definitions = $this->loadDefinitions($parents);
    if (empty($definitions)) {
      return [];
    }

    $definitionLocale = isset($definitions['locale']) ? $definitions['locale'] : '';
    $useLocalName = Locale::matchCandidates($locale, $definitionLocale);
    $list = [];

    // order by ios_code
    uasort($definitions['subdivisions'], function ($a, $b) {
      if (isset($a['iso_code']) && $b['iso_code']) {
        if ($a['iso_code'] === $b['iso_code']) return 0;
        elseif ($a['iso_code'] > $b['iso_code']) return 1;
        else return -1;
      } else {
        return 0;
      }
    });

    foreach ($definitions['subdivisions'] as $code => $definition) {
      $list[$code] = $useLocalName ? $definition['local_name'] : $definition['name'];
    }

    return $list;
  }
}
