<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

/**
 * Apportes des informations de référence sur les activités CsARR
 */
class CReferenceActiviteCsARR extends CCsARRObject {

  public $code;
  public $libelle;
  public $dedie;
  public $non_dedie;
  public $collectif;
  public $pluripro;
  public $appareillage;
  public $seance;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'activite_reference';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();

    // DB Fields
    $props["code"]         = "str notNull length|7";
    $props["libelle"]      = "str notNull";
    $props["dedie"]        = "enum list|oui|non";
    $props["non_dedie"]    = "enum list|possible|non";
    $props["collectif"]    = "enum list|oui";
    $props["pluripro"]     = "enum list|oui";
    $props["appareillage"] = "enum list|oui";
    $props["seance"]       = "enum list|oui|non";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view      = $this->code;
    $this->_shortview = $this->code;
  }
}
