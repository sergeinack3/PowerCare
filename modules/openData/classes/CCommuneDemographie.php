<?php
/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\OpenData;

use Ox\Core\CMbObject;

/**
 * Description
 */
class CCommuneDemographie extends CMbObject {
  /** @var integer Primary key */
  public $communes_demographie_id;

  public $commune_id;
  public $annee;
  public $age_min;
  public $age_max;
  public $sexe;
  public $nationalite;
  public $population;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->dsn      = 'INSEE';
    $spec->loggable = false;
    $spec->table    = "communes_demographie";
    $spec->key      = "communes_demographie_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props['commune_id'] = 'ref class|CCommuneFrance notNull back|demographies';
    $props['annee']      = 'num notNull';
    $props['age_min']    = 'num';
    $props['age_max']    = 'num';
    $props['sexe']       = 'enum list|m|f';
    $props['population'] = 'num';
    $props['nationalite'] = 'enum list|francais|etranger';

    return $props;
  }
}
