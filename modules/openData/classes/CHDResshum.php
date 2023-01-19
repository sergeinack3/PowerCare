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
class CHDResshum extends CHDObject {
  /** @var integer Primary key */
  public $hd_resshum_id;

  public $annee;
  public $nb_accouchement_par_obs_sage_femme;
  public $nb_icr_anesth_par_anesth;
  public $nb_icr_par_chir;
  public $nb_ide_as_par_cadre;
  public $nb_iade_par_anesth;
  public $nb_sage_femme_par_obs;
  public $taux_absenteisme_pnm;
  public $turn_over_global;
  public $interim_med;

  static public $fields = array(
    'RH1'  => 'nb_accouchement_par_obs_sage_femme',
    'RH2'  => 'nb_icr_anesth_par_anesth',
    'RH3'  => 'nb_icr_par_chir',
    'RH4'  => 'nb_ide_as_par_cadre',
    'RH5'  => 'nb_iade_par_anesth',
    'RH6'  => 'nb_sage_femme_par_obs',
    'RH8'  => 'taux_absenteisme_pnm',
    'RH9'  => 'turn_over_global',
    'RH10' => 'interim_med',
  );

  static public $field_page = array(
    'nb_accouchement_par_obs_sage_femme' => '50',
    'nb_icr_anesth_par_anesth'           => '51',
    'nb_icr_par_chir'                    => '52',
    'nb_ide_as_par_cadre'                => '53',
    'nb_iade_par_anesth'                 => '54',
    'nb_sage_femme_par_obs'              => '55',
    'taux_absenteisme_pnm'               => '56',
    'turn_over_global'                   => '58',
    'interim_med'                        => '59',
  );

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "hd_resshum";
    $spec->key   = "hd_resshum_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props['annee']                              = 'num notNull';
    $props['nb_accouchement_par_obs_sage_femme'] = 'num';
    $props['nb_icr_anesth_par_anesth']           = 'num';
    $props['nb_icr_par_chir']                    = 'num';
    $props['nb_ide_as_par_cadre']                = 'num';
    $props['nb_iade_par_anesth']                 = 'num';
    $props['nb_sage_femme_par_obs']              = 'num';
    $props['taux_absenteisme_pnm']               = 'num';
    $props['turn_over_global']                   = 'num';
    $props['interim_med']                        = 'num';
    $props['hd_etablissement_id']                .= ' back|resshums';

    return $props;
  }
}
