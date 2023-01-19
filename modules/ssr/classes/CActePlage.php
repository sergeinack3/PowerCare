<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\CMbObject;

/**
 * Actes SSR abstraits
 */
class CActePlage extends CMbObject {
  public $acte_id;
  // DB Fields
  public $plage_id;
  public $code;
  public $type;
  public $quantite;

  /** @var CActiviteCsARR|CPrestaSSR */
  public $_ref_activite;
  /** @var CPlageSeanceCollective */
  public $_ref_plage;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'acte_plage_collective';
    $spec->key   = 'acte_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props             = parent::getProps();
    $props["plage_id"] = "ref notNull class|CPlageSeanceCollective cascade back|actes_plage";
    $props["code"]     = "str notNull show|0";
    $props["type"]     = "enum list|csarr|presta";
    $props["quantite"] = "float default|1 min|0";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->getActivite();
    $this->_view = $this->code . ": " . $this->_ref_activite->libelle;
  }

  /**
   * Chargement de l'activite
   *
   * @return CActiviteCsARR|CPrestaSSR
   */
  function getActivite() {
    if ($this->type == "csarr") {
      return $this->_ref_activite = CActiviteCsARR::get($this->code);
    }

    return $this->_ref_activite = CPrestaSSR::get($this->code);
  }

  /**
   * Chargement de la plage collective
   *
   * @return CPlageSeanceCollective
   */
  function loadRefPlage() {
    return $this->_ref_plage = $this->loadFwdRef("plage_id", true);
  }
}
