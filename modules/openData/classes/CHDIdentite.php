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
class CHDIdentite extends CHDObject {
  /** @var integer Primary key */
  public $hd_identite_id;

  public $annee;
  public $nb_rsa_med;
  public $nb_rsa_chir;
  public $nb_rsa_obs;
  public $nb_rsa_med_ambu;
  public $nb_rsa_chir_ambu;
  public $nb_rsa_obs_ambu;
  public $nb_chimio;
  public $nb_radio;
  public $nb_hemo;
  public $nb_autre;
  public $nb_accouchement;
  public $nb_actes_chir;
  public $nb_atu;
  public $nb_actes_endo;
  public $nb_racine_ghm;
  public $nb_chir_sein;
  public $nb_cataracte;
  public $nb_arthro_genou;
  public $nb_hernie_enfant;
  public $nb_rtu_prostate;
  public $nb_hernie_adulte;
  public $group_chir_1_libelle;
  public $group_chir_1;
  public $group_chir_2_libelle;
  public $group_chir_2;
  public $group_chir_3_libelle;
  public $group_chir_3;
  public $group_chir_4_libelle;
  public $group_chir_4;
  public $group_chir_5_libelle;
  public $group_chir_5;
  public $group_med_1_libelle;
  public $group_med_1;
  public $group_med_2_libelle;
  public $group_med_2;
  public $group_med_3_libelle;
  public $group_med_3;
  public $group_med_4_libelle;
  public $group_med_4;
  public $group_med_5_libelle;
  public $group_med_5;
  public $nb_lits_med;
  public $nb_lits_soins_intensifs;
  public $nb_lits_surv_continue;
  public $nb_lits_reanimation;
  public $nb_places_med;
  public $nb_lits_chir;
  public $nb_places_chir;
  public $nb_lits_obs;
  public $nb_places_obs;
  public $taux_info_ima_bio_ana;
  public $taux_info_dpii;
  public $taux_info_presc;
  public $taux_info_agenda_patient;
  public $taux_info_med;
  public $nb_scanners;
  public $nb_irm;
  public $nb_tep_scan;
  public $nb_table_corona;
  public $nb_salles_radio_vasc;
  public $nb_salles_interv_chir;
  public $niveau_mater;
  public $nb_b;
  public $nb_examens;
  public $total_prod;
  public $prod_taa;
  public $prod_migac;
  public $prod_merri;
  public $prod_ac;
  public $prod_recette_daf;
  public $prod_recette_mco;
  public $total_charges;
  public $total_charges_mco;
  public $resultat_consolide;
  public $resultat_net;
  public $resultat_consolide_budget_principal;
  public $caf;
  public $total_bilan;
  public $encours_dette;
  public $fond_roulement_net_global;
  public $fond_roulement_besoin;
  public $tresorerie;
  public $coeff_transition;
  public $nb_etp_medicaux;
  public $nb_etp_medicaux_med;
  public $nb_etp_medicaux_chir;
  public $nb_etp_medicaux_anesth;
  public $nb_etp_medicaux_gyneco_obs;
  public $nb_etp_non_med;
  public $nb_etp_non_med_direction_administratif;
  public $nb_etp_non_med_services_soins;
  public $nb_etp_non_med_educatifs_sociaux;
  public $nb_etp_non_med_medico_technique;
  public $nb_etp_non_med_techniques_ouvriers;

  public static $fields = array(
    'volumetrie'      => array(
      'CI_A1'  => 'nb_rsa_med',
      'CI_A2'  => 'nb_rsa_chir',
      'CI_A3'  => 'nb_rsa_obs',
      'CI_A4'  => 'nb_rsa_med_ambu',
      'CI_A5'  => 'nb_rsa_chir_ambu',
      'CI_A6'  => 'nb_rsa_obs_ambu',
      'CI_A7'  => 'nb_chimio',
      'CI_A8'  => 'nb_radio',
      'CI_A9'  => 'nb_hemo',
      'CI_A10' => 'nb_autre',
      'CI_A11' => 'nb_accouchement',
      'CI_A12' => 'nb_actes_chir',
      'CI_A13' => 'nb_atu',
      'CI_A14' => 'nb_actes_endo',
      'CI_A15' => 'nb_racine_ghm',
    ),
    'infrastructure'  => array(
      'CI_AC1'   => 'nb_lits_med',
      'CI_AC2'   => 'nb_lits_soins_intensifs',
      'CI_AC3'   => 'nb_lits_surv_continue',
      'CI_AC4'   => 'nb_lits_reanimation',
      'CI_AC5'   => 'nb_places_med',
      'CI_AC6'   => 'nb_lits_chir',
      'CI_AC7'   => 'nb_places_chir',
      'CI_AC8'   => 'nb_lits_obs',
      'CI_AC9'   => 'nb_places_obs',
      'CI_E1'    => 'nb_scanners',
      'CI_E2'    => 'nb_irm',
      'CI_E3'    => 'nb_tep_scan',
      'CI_E4'    => 'nb_table_corona',
      'CI_E4_V2' => 'nb_salles_radio_vasc',
      'CI_E5'    => 'nb_salles_interv_chir',
      'CI_E6'    => 'niveau_mater',
      'CI_E7'    => 'nb_b',
      'CI_E7_V2' => 'nb_examens',
    ),
    'informatisation' => array(
      'CI_DF1' => 'taux_info_ima_bio_ana',
      'CI_DF2' => 'taux_info_dpii',
      'CI_DF3' => 'taux_info_presc',
      'CI_DF4' => 'taux_info_agenda_patient',
      'CI_DF5' => 'taux_info_med',
    ),
    'rh'              => array(
      'CI_RH1'  => 'nb_etp_medicaux',
      'CI_RH2'  => 'nb_etp_medicaux_med',
      'CI_RH3'  => 'nb_etp_medicaux_chir',
      'CI_RH4'  => 'nb_etp_medicaux_anesth',
      'CI_RH5'  => 'nb_etp_medicaux_gyneco_obs',
      'CI_RH6'  => 'nb_etp_non_med',
      'CI_RH7'  => 'nb_etp_non_med_direction_administratif',
      'CI_RH8'  => 'nb_etp_non_med_services_soins',
      'CI_RH9'  => 'nb_etp_non_med_educatifs_sociaux',
      'CI_RH10' => 'nb_etp_non_med_medico_technique',
      'CI_RH11' => 'nb_etp_non_med_techniques_ouvriers',
    ),
  );

  static public $fields_group_activite = array(
    'CI_A17_1' => 'group_chir_1',
    'CI_A17_2' => 'group_chir_2',
    'CI_A17_3' => 'group_chir_3',
    'CI_A17_4' => 'group_chir_4',
    'CI_A17_5' => 'group_chir_5',
    'CI_A18_1' => 'group_med_1',
    'CI_A18_2' => 'group_med_2',
    'CI_A18_3' => 'group_med_3',
    'CI_A18_4' => 'group_med_4',
    'CI_A18_5' => 'group_med_5',
  );

  static public $fields_activite_realisees = array(
    'CI_A16_1' => 'nb_chir_sein',
    'CI_A16_2' => 'nb_cataracte',
    'CI_A16_3' => 'nb_arthro_genou',
    'CI_A16_4' => 'nb_hernie_enfant',
    'CI_A16_5' => 'nb_rtu_prostate',
    'CI_A16_6' => 'nb_hernie_adulte',
  );

  static public $field_page = array(
    'nb_rsa_med'                             => '84',
    'nb_rsa_chir'                            => '85',
    'nb_rsa_obs'                             => '86',
    'nb_rsa_med_ambu'                        => '87',
    'nb_rsa_chir_ambu'                       => '88',
    'nb_rsa_obs_ambu'                        => '89',
    'nb_chimio'                              => '90',
    'nb_radio'                               => '91',
    'nb_hemo'                                => '92',
    'nb_autre'                               => '93',
    'nb_accouchement'                        => '94',
    'nb_actes_chir'                          => '95',
    'nb_atu'                                 => '96',
    'nb_actes_endo'                          => '97',
    'nb_racine_ghm'                          => '98',
    'nb_chir_sein'                           => '99',
    'nb_cataracte'                           => '99',
    'nb_arthro_genou'                        => '99',
    'nb_hernie_enfant'                       => '99',
    'nb_rtu_prostate'                        => '99',
    'nb_hernie_adulte'                       => '99',
    'group_chir_1_libelle'                   => '101',
    'group_chir_1'                           => '101',
    'group_chir_2_libelle'                   => '101',
    'group_chir_2'                           => '101',
    'group_chir_3_libelle'                   => '101',
    'group_chir_3'                           => '101',
    'group_chir_4_libelle'                   => '101',
    'group_chir_4'                           => '101',
    'group_chir_5_libelle'                   => '101',
    'group_chir_5'                           => '101',
    'group_med_1_libelle'                    => '100',
    'group_med_1'                            => '100',
    'group_med_2_libelle'                    => '100',
    'group_med_2'                            => '100',
    'group_med_3_libelle'                    => '100',
    'group_med_3'                            => '100',
    'group_med_4_libelle'                    => '100',
    'group_med_4'                            => '100',
    'group_med_5_libelle'                    => '100',
    'group_med_5'                            => '100',
    'nb_lits_med'                            => '156',
    'nb_lits_soins_intensifs'                => '157',
    'nb_lits_surv_continue'                  => '158',
    'nb_lits_reanimation'                    => '159',
    'nb_places_med'                          => '160',
    'nb_lits_chir'                           => '161',
    'nb_places_chir'                         => '162',
    'nb_lits_obs'                            => '163',
    'nb_places_obs'                          => '164',
    'taux_info_ima_bio_ana'                  => '165',
    'taux_info_dpii'                         => '166',
    'taux_info_presc'                        => '167',
    'taux_info_agenda_patient'               => '168',
    'taux_info_med'                          => '169',
    'nb_scanners'                            => '102',
    'nb_irm'                                 => '103',
    'nb_tep_scan'                            => '104',
    'nb_table_corona'                        => '105',
    'nb_salles_radio_vasc'                   => '106',
    'nb_salles_interv_chir'                  => '107',
    'niveau_mater'                           => '108',
    'nb_b'                                   => '108',
    'nb_examens'                             => '110',
    'total_prod'                             => '111',
    'prod_taa'                               => '112',
    'prod_migac'                             => '113',
    'prod_merri'                             => '114',
    'prod_ac'                                => '115',
    'prod_recette_daf'                       => '116',
    'prod_recette_mco'                       => '133',
    'total_charges'                          => '117',
    'total_charges_mco'                      => '118',
    'resultat_consolide'                     => '119',
    'resultat_net'                           => '136',
    'resultat_consolide_budget_principal'    => '120',
    'caf'                                    => '121',
    'total_bilan'                            => '122',
    'encours_dette'                          => '123',
    'fond_roulement_net_global'              => '124',
    'fond_roulement_besoin'                  => '125',
    'tresorerie'                             => '126',
    'coeff_transition'                       => '127',
    'nb_etp_medicaux'                        => '145',
    'nb_etp_medicaux_med'                    => '146',
    'nb_etp_medicaux_chir'                   => '147',
    'nb_etp_medicaux_anesth'                 => '148',
    'nb_etp_medicaux_gyneco_obs'             => '149',
    'nb_etp_non_med'                         => '150',
    'nb_etp_non_med_direction_administratif' => '151',
    'nb_etp_non_med_educatifs_sociaux'       => '153',
    'nb_etp_non_med_services_soins'          => '152',
    'nb_etp_non_med_medico_technique'        => '154',
    'nb_etp_non_med_techniques_ouvriers'     => '155',
  );

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "hd_identite";
    $spec->key   = "hd_identite_id";

    return $spec;
  }


  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props['annee']                                  = 'num notNull';
    $props['nb_rsa_med']                             = 'num';
    $props['nb_rsa_chir']                            = 'num';
    $props['nb_rsa_obs']                             = 'num';
    $props['nb_rsa_med_ambu']                        = 'num';
    $props['nb_rsa_chir_ambu']                       = 'num';
    $props['nb_rsa_obs_ambu']                        = 'num';
    $props['nb_chimio']                              = 'num';
    $props['nb_radio']                               = 'num';
    $props['nb_hemo']                                = 'num';
    $props['nb_autre']                               = 'num';
    $props['nb_accouchement']                        = 'num';
    $props['nb_actes_chir']                          = 'num';
    $props['nb_atu']                                 = 'num';
    $props['nb_actes_endo']                          = 'num';
    $props['nb_racine_ghm']                          = 'num';
    $props['nb_chir_sein']                           = 'num';
    $props['nb_cataracte']                           = 'num';
    $props['nb_arthro_genou']                        = 'num';
    $props['nb_hernie_enfant']                       = 'num';
    $props['nb_rtu_prostate']                        = 'num';
    $props['nb_hernie_adulte']                       = 'num';
    $props['group_chir_1_libelle']                   = 'str';
    $props['group_chir_1']                           = 'num';
    $props['group_chir_2_libelle']                   = 'str';
    $props['group_chir_2']                           = 'num';
    $props['group_chir_3_libelle']                   = 'str';
    $props['group_chir_3']                           = 'num';
    $props['group_chir_4_libelle']                   = 'str';
    $props['group_chir_4']                           = 'num';
    $props['group_chir_5_libelle']                   = 'str';
    $props['group_chir_5']                           = 'num';
    $props['group_med_1_libelle']                    = 'str';
    $props['group_med_1']                            = 'num';
    $props['group_med_2_libelle']                    = 'str';
    $props['group_med_2']                            = 'num';
    $props['group_med_3_libelle']                    = 'str';
    $props['group_med_3']                            = 'num';
    $props['group_med_4_libelle']                    = 'str';
    $props['group_med_4']                            = 'num';
    $props['group_med_5_libelle']                    = 'str';
    $props['group_med_5']                            = 'num';
    $props['nb_lits_med']                            = 'num';
    $props['nb_lits_soins_intensifs']                = 'num';
    $props['nb_lits_surv_continue']                  = 'num';
    $props['nb_lits_reanimation']                    = 'num';
    $props['nb_places_med']                          = 'num';
    $props['nb_lits_chir']                           = 'num';
    $props['nb_places_chir']                         = 'num';
    $props['nb_lits_obs']                            = 'num';
    $props['nb_places_obs']                          = 'num';
    $props['taux_info_ima_bio_ana']                  = 'num';
    $props['taux_info_dpii']                         = 'num';
    $props['taux_info_presc']                        = 'num';
    $props['taux_info_agenda_patient']               = 'num';
    $props['taux_info_med']                          = 'num';
    $props['nb_scanners']                            = 'num';
    $props['nb_irm']                                 = 'num';
    $props['nb_tep_scan']                            = 'num';
    $props['nb_table_corona']                        = 'num';
    $props['nb_salles_radio_vasc']                   = 'num';
    $props['nb_salles_interv_chir']                  = 'num';
    $props['niveau_mater']                           = 'num';
    $props['nb_b']                                   = 'num';
    $props['nb_examens']                             = 'num';
    $props['total_prod']                             = 'num';
    $props['prod_taa']                               = 'num';
    $props['prod_migac']                             = 'num';
    $props['prod_merri']                             = 'num';
    $props['prod_ac']                                = 'num';
    $props['prod_recette_daf']                       = 'num';
    $props['prod_recette_mco']                       = 'num';
    $props['total_charges']                          = 'num';
    $props['total_charges_mco']                      = 'num';
    $props['resultat_consolide']                     = 'num';
    $props['resultat_net']                           = 'num';
    $props['resultat_consolide_budget_principal']    = 'num';
    $props['caf']                                    = 'num';
    $props['total_bilan']                            = 'num';
    $props['encours_dette']                          = 'num';
    $props['fond_roulement_net_global']              = 'num';
    $props['fond_roulement_besoin']                  = 'num';
    $props['tresorerie']                             = 'num';
    $props['coeff_transition']                       = 'num';
    $props['nb_etp_medicaux']                        = 'num';
    $props['nb_etp_medicaux_med']                    = 'num';
    $props['nb_etp_medicaux_chir']                   = 'num';
    $props['nb_etp_medicaux_anesth']                 = 'num';
    $props['nb_etp_medicaux_gyneco_obs']             = 'num';
    $props['nb_etp_non_med']                         = 'num';
    $props['nb_etp_non_med_direction_administratif'] = 'num';
    $props['nb_etp_non_med_educatifs_sociaux']       = 'num';
    $props['nb_etp_non_med_services_soins']          = 'num';
    $props['nb_etp_non_med_medico_technique']        = 'num';
    $props['nb_etp_non_med_techniques_ouvriers']     = 'num';
    $props['hd_etablissement_id']                    .= ' back|identites';

    return $props;
  }

  /**
   * @inheritdoc
   */
  function getDisplayFields() {
    $fields = array(
      'volumetrie'      => array(),
      'interv'          => array(),
      'actes'           => array(),
      'infrastructure'  => array(),
      'informatisation' => array(),
      'finances'        => array(),
      'rh'              => array(),
    );

    // Volumétrie
    $fields['volumetrie'] = $this->getFields(self::$fields['volumetrie'], true);

    // Interventions
    $fields['interv'] = $this->getFields(self::$fields_activite_realisees, true);

    // Actes
    $fields['actes'] = array_merge(
      $this->getFields(self::$fields_group_activite, true),
      $this->getFields(
        array(
          'group_chir_1_libelle', 'group_chir_2_libelle', 'group_chir_3_libelle', 'group_chir_4_libelle', 'group_chir_5_libelle',
          'group_med_1_libelle', 'group_med_2_libelle', 'group_med_3_libelle', 'group_med_4_libelle', 'group_med_5_libelle',
        )
      )
    );

    // Infrastructure
    $fields['infrastructure'] = $this->getFields(self::$fields['infrastructure'], true);

    // Informatisation
    $fields['informatisation'] = $this->getFields(self::$fields['informatisation'], true);

    // Finances
    $fields['finances'] = $this->getFieldsFinance();

    // Ressources humaines
    $fields['rh'] = $this->getFields(self::$fields['rh'], true);

    return $fields;
  }

  /**
   * @param array $labels Fields name to get
   * @param bool  $annee  Add $this->annee field or not
   *
   * @return array
   */
  function getFields($labels = array(), $annee = false) {
    $fields = array();
    if ($annee) {
      $fields['annee'] = $this->annee;
    }

    foreach ($labels as $_field_name) {
      $fields[$_field_name] = $this->$_field_name;
    }

    return $fields;
  }

  /**
   * Get the Finance Fields from the object
   *
   * @return array
   */
  function getFieldsFinance() {
    return array(
      'annee'                               => $this->annee,
      'total_prod'                          => $this->total_prod,
      'prod_taa'                            => $this->prod_taa,
      'prod_migac'                          => $this->prod_migac,
      'prod_merri'                          => $this->prod_merri,
      'prod_ac'                             => $this->prod_ac,
      'prod_recette_daf'                    => $this->prod_recette_daf,
      'prod_recette_mco'                    => $this->prod_recette_mco,
      'total_charges'                       => $this->total_charges,
      'total_charges_mco'                   => $this->total_charges_mco,
      'resultat_consolide'                  => $this->resultat_consolide,
      'resultat_net'                        => $this->resultat_net,
      'resultat_consolide_budget_principal' => $this->resultat_consolide_budget_principal,
      'caf'                                 => $this->caf,
      'total_bilan'                         => $this->total_bilan,
      'encours_dette'                       => $this->encours_dette,
      'fond_roulement_net_global'           => $this->fond_roulement_net_global,
      'fond_roulement_besoin'               => $this->fond_roulement_besoin,
      'tresorerie'                          => $this->tresorerie,
      'coeff_transition'                    => $this->coeff_transition,
    );
  }
}
