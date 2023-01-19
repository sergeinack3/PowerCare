<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation;

use Ox\Core\CMbObject;

/**
 * Permet de choisir un coefficient spécifique pour une facture
 */
class CFactureCoeff extends CMbObject {
  // DB Table key
  public $facture_coeff_id;

  // DB Fields
  public $praticien_id;
  public $group_id;
  public $coeff;
  public $nom;
  public $description;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'facture_coeff';
    $spec->key   = 'facture_coeff_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["praticien_id"]= "ref class|CMediusers back|coeff_fact_prat";
    $props["group_id"]    = "ref notNull class|CGroups back|coeff_bill_group";
    $props["coeff"]       = "float notNull";
    $props["nom"]         = "str notNull";
    $props["description"] = "text";
    return $props;
  }
}
