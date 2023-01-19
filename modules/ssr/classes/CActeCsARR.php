<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

/**
 * Actes SSR de la nomenclature CsARR
 */
class CActeCsARR extends CActeSSR {
  public $acte_csarr_id;

  // DB Fields
  public $modulateurs;
  public $phases;
  public $commentaire;
  public $extension;

  // Derived feilds
  public $_modulateurs = array();
  public $_phases = array();
  public $_fabrication;

  /** @var CActiviteCsARR */
  public $_ref_activite_csarr;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'acte_csarr';
    $spec->key   = 'acte_csarr_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                = parent::getProps();
    $props["sejour_id"] .= " back|actes_csarr";
    $props["administration_id"] .= " back|actes_csarr";
    $props["evenement_ssr_id"] .= " back|actes_csarr";
    $props["code"]        = "str notNull length|7 show|0";
    $props["modulateurs"] = "str maxLength|20";
    $props["phases"]      = "str maxLength|3";
    $props["commentaire"] = "text";
    $props["extension"]   = "str length|2";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    if ($this->modulateurs) {
      $this->_view        .= "-$this->modulateurs";
      $this->_modulateurs = explode("-", $this->modulateurs);
    }

    if ($this->phases) {
      $this->_view   .= ".$this->phases";
      $this->_phases = str_split($this->phases);
    }
  }

  /**
   * @see parent::updatePlainFields()
   */
  function updatePlainFields() {
    parent::updatePlainFields();
    $this->modulateurs = $this->_modulateurs ? implode("-", $this->_modulateurs) : "";
    $this->phases      = $this->_phases ? implode("", $this->_phases) : "";
  }

  /**
   * @see parent::store()
   */
  function store() {
    $this->completeField("commentaire");

    $recalculate = $this->_id && $this->fieldModified("commentaire") ? true : false;
    if ($this->_id) {
      $rhs = $this->loadRefEvenementSSR()->getRHS();
      if ($rhs->_id && $rhs->facture) {
        return "CRHS-failed-rhs-facture";
      }
    }
    else {
      $this->loadRefEvenementSSR();
      /* Reset the cache of the most used codes uppon the creation of a new CsArr act */
      if ($this->_ref_evenement_ssr) {
        CActiviteCsARR::resetUsedCodesCache($this->_ref_evenement_ssr->therapeute_id);
      }
    }
    // Standard store
    if ($msg = parent::store()) {
      return $msg;
    }
    if ($recalculate) {
      $this->loadRefEvenementSSR()->getRHS()->recalculate();
    }

    return null;
  }

  /**
   * Chargement de l'activité associée
   *
   * @return CActiviteCsARR
   */
  function loadRefActiviteCsarr() {
    $activite           = CActiviteCsARR::get($this->code);
    $this->_fabrication = strpos($activite->hierarchie, "09.02.02.") === 0;
    $activite->loadRefHierarchie();

    return $this->_ref_activite_csarr = $activite;
  }

  /**
   * @see parent::loadView()
   */
  function loadView() {
    parent::loadView();
    $this->loadRefActiviteCsARR();
  }
}