<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Ligne d'activités RHS
 */
class CLigneActivitesRHS extends CMbObject {
  // DB Table key
  public $ligne_id;

  // DB Fields
  public $rhs_id;
  public $executant_id;
  public $auto;
  public $code_activite_cdarr;
  public $code_activite_csarr;
  public $code_activite;
  public $type_activite;
  public $code_intervenant_cdarr;
  public $modulateurs;
  public $phases;
  public $nb_patient_seance;
  public $nb_intervenant_seance;
  public $commentaire;
  public $extension;

  public $qty_mon;
  public $qty_tue;
  public $qty_wed;
  public $qty_thu;
  public $qty_fri;
  public $qty_sat;
  public $qty_sun;

  // Form fields
  public $_qty_total;
  public $_executant;
  public $_modulateurs;

  // References
  /** @var CRHS */
  public $_ref_rhs;
  /** @var CIntervenantCdARR */
  public $_ref_intervenant_cdarr;
  /** @var CActiviteCdARR */
  public $_ref_activite_cdarr;
  /** @var CActiviteCsARR */
  public $_ref_activite_csarr;
  /** @var CMediusers */
  public $_ref_executant;

  public $_ref_presta_ssr = array();

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec                   = parent::getSpec();
    $spec->table            = "ligne_activites_rhs";
    $spec->key              = "ligne_id";
    $spec->uniques["ligne"] = array(
      "rhs_id",
      "executant_id",
      "code_activite_cdarr"
    );
    $spec->xor["code"]      = array(
      "code_activite_cdarr",
      "code_activite_csarr",
      "code_activite"
    );

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();

    // DB Fields
    $props["rhs_id"]                 = "ref notNull class|CRHS back|lines";
    $props["executant_id"]           = "ref notNull class|CMediusers back|activites_rhs";
    $props["auto"]                   = "bool";
    $props["code_activite_cdarr"]    = "str length|4";
    $props["code_activite_csarr"]    = "str length|7";
    $props["code_activite"]          = "str";
    $props["type_activite"]          = "enum list|cdarr|csarr|presta_ssr default|csarr";
    $props["code_intervenant_cdarr"] = "str length|2";
    $props["modulateurs"]            = "str maxLength|20";
    $props["phases"]                 = "str maxLength|3";
    $props["nb_patient_seance"]      = "num";
    $props["nb_intervenant_seance"]  = "num";
    $props["commentaire"]            = "text";
    $props["extension"]              = "str length|2";

    $props["qty_mon"] = "float min|0 default|0";
    $props["qty_tue"] = "float min|0 default|0";
    $props["qty_wed"] = "float min|0 default|0";
    $props["qty_thu"] = "float min|0 default|0";
    $props["qty_fri"] = "float min|0 default|0";
    $props["qty_sat"] = "float min|0 default|0";
    $props["qty_sun"] = "float min|0 default|0";

    // Form fields
    $props["_qty_total"] = "num min|0 max|99";
    $props["_executant"] = "str maxLength|50";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_qty_total =
      (int)$this->qty_mon +
      (int)$this->qty_tue +
      (int)$this->qty_wed +
      (int)$this->qty_thu +
      (int)$this->qty_fri +
      (int)$this->qty_sat +
      (int)$this->qty_sun;

    if (!$this->qty_mon) {
      $this->qty_mon = "";
    }

    if (!$this->qty_tue) {
      $this->qty_tue = "";
    }

    if (!$this->qty_wed) {
      $this->qty_wed = "";
    }

    if (!$this->qty_thu) {
      $this->qty_thu = "";
    }

    if (!$this->qty_fri) {
      $this->qty_fri = "";
    }

    if (!$this->qty_sat) {
      $this->qty_sat = "";
    }

    if (!$this->qty_sun) {
      $this->qty_sun = "";
    }

    if ($this->modulateurs) {
      $this->_modulateurs = explode("-", $this->modulateurs);
    }
  }

  /**
   * Charge l'activité CdARR associée
   *
   * @return CActiviteCdARR
   */
  function loadRefActiviteCdARR() {
    $activite    = CActiviteCdARR::get($this->code_activite_cdarr);
    $this->_view = $activite->_view;

    return $this->_ref_activite_cdarr = $activite;
  }

  /**
   * Charge l'activité CsARR associée
   *
   * @return CActiviteCsARR
   */
  function loadRefActiviteCsARR() {
    $activite    = CActiviteCsARR::get($this->code_activite_csarr);
    $this->_view = $activite->_view;

    return $this->_ref_activite_csarr = $activite;
  }

  /**
   * Charge la prestation SSR associée
   *
   * @return CPrestaSSR
   */
  function loadRefPrestationSSR() {
    $presta = CPrestaSSR::get($this->code_activite);

    return $this->_ref_presta_ssr = $presta;
  }

  /**
   * Chargement l'intervenant CdARR associé
   *
   * @return CIntervenantCdARR
   */
  function loadRefIntervenantCdARR() {
    return $this->_ref_intervenant_cdarr = CIntervenantCdARR::get($this->code_intervenant_cdarr);
  }

  /**
   * Chargement de l'executant
   *
   * @return CMediusers
   */
  function loadRefExecutant() {
    return $this->_ref_executant = $this->loadFwdRef("executant_id");
  }

  /**
   * Load holding RHS
   *
   * @return CRHS
   */
  function loadRefRHS() {
    return $this->_ref_rhs = $this->loadFwdRef("rhs_id");
  }

  /**
   * Incremente ou décrement le compteur journalier de la ligne
   *
   * @param string $datetime Moment
   * @param string $action   Soit inc soit dec
   * @param float  $quantite Quantité
   *
   * @return void
   */
  function crementDay($datetime, $action, $quantite = 1) {
    $day        = CMbDT::transform($datetime, null, "%w");
    $days       = array(
      "0" => "qty_sun",
      "1" => "qty_mon",
      "2" => "qty_tue",
      "3" => "qty_wed",
      "4" => "qty_thu",
      "5" => "qty_fri",
      "6" => "qty_sat",
    );
    $day        = $days[$day];
    $crement    = $action == "inc" ? $quantite : $quantite*-1;
    if (!$this->$day) {
      $this->$day = 0;
    }
    $this->$day += $crement;
  }

  /**
   * @see parent::check()
   */
  function check() {
    $this->completeField("code_activite_csarr");

    if ($this->code_activite_csarr) {
      $activite = CActiviteCsARR::get($this->code_activite_csarr);
      if (!$activite->_id) {
        return CAppUI::tr("CActiviteCsARR.code_invalide");
      }
    }

    return parent::check();
  }

  /**
   * @see parent::store()
   */
  function store() {
    // RHS already charged
    $this->completeField("rhs_id");
    $rhs = $this->loadRefRHS();
    if ($rhs->facture) {
      return "$this->_class-failed-rhs-facture";
    }

    // Delete if total is 0
    $this->completeField(
      "qty_mon",
      "qty_tue",
      "qty_wed",
      "qty_thu",
      "qty_thu",
      "qty_fri",
      "qty_sat",
      "qty_sun"
    );
    $this->updateFormFields();
    if ($this->_id && $this->_qty_total == 0) {
      return $this->delete();
    }

    return parent::store();
  }
}
