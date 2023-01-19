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
class CHDProcess extends CHDObject {
  /** @var integer Primary key */
  public $hd_process_id;

  public $annee;
  public $ip_dms_med;
  public $ip_dms_chir;
  public $ip_dms_obs;
  public $poids_cout_personnel_non_med;
  public $poids_cout_personnel_med;
  public $poids_cout_personnel_services_medico_technique;
  public $pct_depenses_admin_logistique_technique;
  public $nb_examens_bio_par_technicien;
  public $nb_icr_par_salle;
  public $taux_cesarienne;
  public $taux_peridurale;
  public $taux_chir_ambu;
  public $taux_gestes_marqueurs_chir_ambu;
  public $taux_utilisation_places_chir_ambu;
  public $indice_facturation;
  public $niveau_prerequis_hopital_numerique;

  static public $fields = array(
    'P1'    => 'ip_dms_med',
    'P2'    => 'ip_dms_chir',
    'P3'    => 'ip_dms_obs',
    'P4'    => 'poids_cout_personnel_non_med',
    'P5'    => 'poids_cout_personnel_med',
    'P6'    => 'poids_cout_personnel_services_medico_technique',
    'P7'    => 'pct_depenses_admin_logistique_techniques',
    'P8new' => 'nb_examens_bio_par_technicien',
    'P9'    => 'nb_icr_par_salle',
    'P10'   => 'taux_cesarienne',
    'P11'   => 'taux_peridurale',
    'P12'   => 'taux_chir_ambu',
    'P13'   => 'taux_gestes_marqueurs_chir_ambu',
    'P14'   => 'taux_utilisation_places_chir_ambu',
    'P15'   => 'indice_facturation',
    'P16'   => 'niveau_prerequis_hopital_numerique',
  );

  static public $field_page = array(
    'ip_dms_med'                                     => '33',
    'ip_dms_chir'                                    => '34',
    'ip_dms_obs'                                     => '35',
    'poids_cout_personnel_non_med'                   => '36',
    'poids_cout_personnel_med'                       => '37',
    'poids_cout_personnel_services_medico_technique' => '38',
    'pct_depenses_admin_logistique_technique'        => '39',
    'nb_examens_bio_par_technicien'                  => '41',
    'nb_icr_par_salle'                               => '42',
    'taux_cesarienne'                                => '43',
    'taux_peridurale'                                => '44',
    'taux_chir_ambu'                                 => '45',
    'taux_gestes_marqueurs_chir_ambu'                => '46',
    'taux_utilisation_places_chir_ambu'              => '47',
    'indice_facturation'                             => '48',
    'niveau_prerequis_hopital_numerique'             => '49',
  );

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "hd_process";
    $spec->key   = "hd_process_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props['annee']                                          = 'num notNull';
    $props['ip_dms_med']                                     = 'num';
    $props['ip_dms_chir']                                    = 'num';
    $props['ip_dms_obs']                                     = 'num';
    $props['poids_cout_personnel_non_med']                   = 'num';
    $props['poids_cout_personnel_med']                       = 'num';
    $props['poids_cout_personnel_services_medico_technique'] = 'num';
    $props['pct_depenses_admin_logistique_technique']        = 'num';
    $props['nb_examens_bio_par_technicien']                  = 'num';
    $props['nb_icr_par_salle']                               = 'num';
    $props['taux_cesarienne']                                = 'num';
    $props['taux_peridurale']                                = 'num';
    $props['taux_chir_ambu']                                 = 'num';
    $props['taux_gestes_marqueurs_chir_ambu']                = 'num';
    $props['taux_utilisation_places_chir_ambu']              = 'num';
    $props['indice_facturation']                             = 'num';
    $props['niveau_prerequis_hopital_numerique']             = 'num';
    $props['hd_etablissement_id']                            .= ' back|process';

    return $props;
  }
}
