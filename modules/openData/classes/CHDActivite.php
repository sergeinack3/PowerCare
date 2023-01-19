<?php
/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\OpenData;

/**
 * Description
 */
class CHDActivite extends CHDObject {
  /** @var integer Primary key */
  public $hd_activite_id;

  public $annee;
  public $zone_reg;
  public $pm_med_reg;
  public $pm_chir_reg;
  public $pm_obs_reg;
  public $pm_chir_ambu_reg;
  public $pm_hospi_cancer_reg;
  public $pct_hospi_cancer;
  public $pct_ghm;
  public $pct_sejours_severite_3_4;
  public $indice_enseignement;
  public $indice_recherche;
  public $pct_entree_prov_urgence;
  public $taux_util_lits_med;
  public $taux_util_lits_chir;
  public $taux_util_lits_obs;

  static public $fields = array(
    'A7'  => 'pct_hospi_cancer',
    'A8'  => 'pct_ghm',
    'A9'  => 'pct_sejours_severite_3_4',
    'A10' => 'indice_enseignement',
    'A11' => 'indice_recherche',
    'A12' => 'pct_entree_prov_urgence',
    'A13' => 'taux_util_lits_med',
    'A14' => 'taux_util_lits_chir',
    'A15' => 'taux_util_lits_obs',
  );

  static public $fields_reg = array(
    'zone'  => 'zone_reg',
    'A1bis' => 'pm_med_reg',
    'A2bis' => 'pm_chir_reg',
    'A3bis' => 'pm_obs_reg',
    'A4bis' => 'pm_chir_ambu_reg',
    'A5bis' => 'pm_hospi_cancer_reg',
  );

  static public $field_page = array(
    'pm_med_reg'               => '2',
    'pm_chir_reg'              => '4',
    'pm_obs_reg'               => '6',
    'pm_chir_ambu_reg'         => '8',
    'pm_hospi_cancer_reg'      => '10',
    'pct_hospi_cancer'         => '12',
    'pct_ghm'                  => '13',
    'pct_sejours_severite_3_4' => '14',
    'indice_enseignement'      => '15',
    'indice_recherche'         => '16',
    'pct_entree_prov_urgence'  => '17',
    'taux_util_lits_med'       => '18',
    'taux_util_lits_chir'      => '19',
    'taux_util_lits_obs'       => '20',
  );

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "hd_activite";
    $spec->key   = "hd_activite_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props['annee']                    = 'num notNull';
    $props['zone_reg']                 = 'str';
    $props['pm_med_reg']               = 'num';
    $props['pm_chir_reg']              = 'num';
    $props['pm_obs_reg']               = 'num';
    $props['pm_chir_ambu_reg']         = 'num';
    $props['pm_hospi_cancer_reg']      = 'num';
    $props['pct_hospi_cancer']         = 'num';
    $props['pct_ghm']                  = 'num';
    $props['pct_sejours_severite_3_4'] = 'num';
    $props['indice_enseignement']      = 'num';
    $props['indice_recherche']         = 'num';
    $props['pct_entree_prov_urgence']  = 'num';
    $props['taux_util_lits_med']       = 'num';
    $props['taux_util_lits_chir']      = 'num';
    $props['taux_util_lits_obs']       = 'num';
    $props['hd_etablissement_id']      .= ' back|activites';

    return $props;
  }
}
