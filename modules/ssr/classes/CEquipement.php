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
 * Equipement de SSR, fait parti d'un plateau technique
 */
class CEquipement extends CMbObject {
  // DB Table key
  public $equipement_id;

  // References
  public $plateau_id;

  // DB Fields
  public $nom;
  public $visualisable;
  public $actif;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'equipement';
    $spec->key   = 'equipement_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                 = parent::getProps();
    $props["plateau_id"]   = "ref notNull class|CPlateauTechnique back|equipements";
    $props["nom"]          = "str notNull";
    $props["visualisable"] = "bool notNull default|1";
    $props["actif"]        = "bool notNull default|1";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->nom;
  }
}
