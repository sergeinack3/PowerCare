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
class CHDActiviteZone extends CMbObject {
  /** @var integer Primary key */
  public $hd_activite_zone_id;

  public $hd_etablissement_id;
  public $annee;
  public $zone;
  public $pm_med;
  public $pm_chir;
  public $pm_obs;
  public $pm_chir_ambu;
  public $pm_hospi_cancer;
  public $pm_chimio;

  static public $fields = array(
    'A1' => 'pm_med',
    'A2' => 'pm_chir',
    'A3' => 'pm_obs',
    'A4' => 'pm_chir_ambu',
    'A5' => 'pm_hospi_cancer',
    'A6' => 'pm_chimio',
  );

  static public $field_page = array(
    'pm_med'          => '1',
    'pm_chir'         => '3',
    'pm_obs'          => '5',
    'pm_chir_ambu'    => '7',
    'pm_hospi_cancer' => '9',
    'pm_chimio'       => '11',
  );

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->dsn      = 'hospi_diag';
    $spec->table    = "hd_activite_zone";
    $spec->key      = "hd_activite_zone_id";
    $spec->loggable = false;

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props['hd_etablissement_id'] = 'ref class|CHDEtablissement notNull back|activites_zones';
    $props['annee']               = 'num notNull';
    $props['zone']                = 'str notNull';
    $props['pm_med']              = 'num';
    $props['pm_chir']             = 'num';
    $props['pm_obs']              = 'num';
    $props['pm_chir_ambu']        = 'num';
    $props['pm_hospi_cancer']     = 'num';
    $props['pm_chimio']           = 'num';

    return $props;
  }
}
