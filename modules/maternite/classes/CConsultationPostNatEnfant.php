<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite;

use Ox\Core\CMbObject;

/**
 * Conusltation post natale
 */
class CConsultationPostNatEnfant extends CMbObject {
  // DB Table key
  public $consultation_post_enfant_id;

  // DB Fields
  public $consultation_post_natale_id;
  public $naissance_id;
  public $enfant_present;
  public $etat_enfant;
  public $rehospitalisation;
  public $motif_rehospitalisation;
  public $poids;
  public $date_deces;
  public $allaitement;
  public $arret_allaitement;
  public $nb_semaines_allaitement;
  public $motif_arret_allaitement;
  public $complement_eau;
  public $complement_eau_sucree;
  public $complement_prepa_lactee;
  public $complement_tasse;
  public $complement_cuillere;
  public $complement_biberon;
  public $indication_complement;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "consultation_enfant";
    $spec->key   = "consultation_post_enfant_id";

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                                = parent::getProps();
    $props["consultation_post_natale_id"] = "ref notNull class|CConsultationPostNatale cascade back|consult_mater_enfants";
    $props["naissance_id"]                = "ref notNull class|CNaissance cascade back|consult_postnat_bebe";
    $props["enfant_present"]              = "bool";
    $props["etat_enfant"]                 = "enum list|ok|surv|hosp|deces";
    $props["rehospitalisation"]           = "bool";
    $props["motif_rehospitalisation"]     = "str";
    $props["poids"]                       = "num";
    $props["date_deces"]                  = "date";
    $props["allaitement"]                 = "enum list|amexclu|ampart|art";
    $props["arret_allaitement"]           = "date";
    $props["nb_semaines_allaitement"]     = "num";
    $props["motif_arret_allaitement"]     = "str";
    $props["complement_eau"]              = "bool";
    $props["complement_eau_sucree"]       = "bool";
    $props["complement_prepa_lactee"]     = "bool";
    $props["complement_tasse"]            = "bool";
    $props["complement_cuillere"]         = "bool";
    $props["complement_biberon"]          = "bool";
    $props["indication_complement"]       = "str";

    return $props;
  }
}