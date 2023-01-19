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
class CHDQualite extends CHDObject {
  /** @var integer Primary key */
  public $hd_qualite_id;

  public $annee;
  public $score_naso;
  public $conformite_dossier_patient;
  public $conformite_delais_envoie;
  public $depistage_nutri;
  public $tracabilite_eval_douleur;
  public $conformite_dossier_anesth;
  public $rcp_cancer;
  public $niveau_certif;
  public $pep_bloc_op;
  public $pep_urg;
  public $pep_med;

  static public $fields = array(
    'Q1'  => 'score_naso',
    'Q2'  => 'conformite_dossier_patient',
    'Q3'  => 'conformite_delais_envoie',
    'Q4'  => 'depistage_nutri',
    'Q5'  => 'tracabilite_eval_douleur',
    'Q6'  => 'conformite_dossier_anesth',
    'Q7'  => 'rcp_cancer',
    'Q8'  => 'niveau_certif',
    'Q9'  => 'pep_bloc_op',
    'Q10' => 'pep_urg',
    'Q11' => 'pep_med',
  );

  static public $field_page = array(
    'score_naso'                 => '21',
    'conformite_dossier_patient' => '22',
    'conformite_delais_envoie'   => '23',
    'depistage_nutri'            => '24',
    'tracabilite_eval_douleur'   => '25',
    'conformite_dossier_anesth'  => '26|27',
    'rcp_cancer'                 => '28',
    'niveau_certif'              => '29',
    'pep_bloc_op'                => '30',
    'pep_urg'                    => '31',
    'pep_med'                    => '32',
  );


  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "hd_qualite";
    $spec->key   = "hd_qualite_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props['annee']                      = 'num notNull';
    $props['score_naso']                 = 'str';
    $props['conformite_dossier_patient'] = 'str';
    $props['conformite_delais_envoie']   = 'str';
    $props['depistage_nutri']            = 'str';
    $props['tracabilite_eval_douleur']   = 'str';
    $props['conformite_dossier_anesth']  = 'str';
    $props['rcp_cancer']                 = 'str';
    $props['niveau_certif']              = 'str';
    $props['pep_bloc_op']                = 'str';
    $props['pep_urg']                    = 'str';
    $props['pep_med']                    = 'str';
    $props['hd_etablissement_id']        .= ' back|qualites';

    return $props;
  }
}
