<?php
/**
 * @package Mediboard\Reservation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Reservation;

use Ox\Core\CMbObject;

/**
 * Classe CExamenOperation
 * @ gère des questions sur la partie examens d'une intervention
 */
class CExamenOperation extends CMbObject {
  // DB Table key
  var $examen_operation_id = null;

  // DB Fields
  var $completed = null;
  var $acheminement = null;
  var $labo = null;
  var $groupe_rh = null;
  var $flacons = null;
  var $auto_transfusion = null;
  var $ecg = null;
  var $radio_thorax = null;
  var $radios_autres = null;
  var $physio_preop = null;
  var $physio_postop = null;

  // Refs
  var $_ref_operation = null;

  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'examen_operation';
    $spec->key   = 'examen_operation_id';

    return $spec;
  }

  function getProps() {
    $specs                     = parent::getProps();
    $specs["completed"]        = "bool default|0";
    $specs["acheminement"]     = "enum list|courrier|fax|autre";
    $specs["labo"]             = "text helped";
    $specs["groupe_rh"]        = "bool default|0";
    $specs["flacons"]          = "num pos";
    $specs["auto_transfusion"] = "bool";
    $specs["ecg"]              = "bool";
    $specs["radio_thorax"]     = "bool";
    $specs["radios_autres"]    = "text helped";
    $specs["physio_preop"]     = "text helped";
    $specs["physio_postop"]    = "text helped";

    return $specs;
  }

  function loadRefOperation() {
    return $this->_ref_operation = $this->loadUniqueBackRef("operation");
  }
}
