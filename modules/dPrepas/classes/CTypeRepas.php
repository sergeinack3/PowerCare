<?php
/**
 * @package Mediboard\Repas
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Repas;

use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * The CTypeRepas class
 */
class CTypeRepas extends CMbObject {
  // DB Table key
  public $typerepas_id;

  // DB Fields
  public $group_id;
  public $nom;
  public $debut;
  public $fin;

  // Form fields
  public $_debut;
  public $_fin;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'repas_type';
    $spec->key   = 'typerepas_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $specs             = parent::getProps();
    $specs["nom"]      = "str notNull";
    $specs["group_id"] = "ref notNull class|CGroups back|types_repas";
    $specs["debut"]    = "time notNull";
    $specs["fin"]      = "time notNull moreThan|debut";
    $specs["_debut"]   = "time notNull";
    $specs["_fin"]     = "time notNull moreThan|_debut";

    return $specs;
  }

  function updatePlainFields() {
    if ($this->_debut !== "") {
      $this->debut = $this->_debut . ":00";
    }
    if ($this->_fin) {
      $this->fin = $this->_fin . ":00";
    }
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view  = $this->nom;
    $this->_debut = substr($this->debut, 0, 2);
    $this->_fin   = substr($this->fin, 0, 2);
  }
}