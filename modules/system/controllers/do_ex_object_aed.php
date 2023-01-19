<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CMbArray;
use Ox\Core\Module\CModule;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Prescription\CPrescriptionProtocoleToConcept;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Custom controller for CExObject
 *
 * @property CExObject _obj
 * @property CExObject _old
 */
class CDoExObjectAddEdit extends CDoObjectAddEdit {
  /**
   * @see parent::onAfterInstanciation()
   */
  function onAfterInstanciation(){
    $_ex_class_id = CValue::read($this->request, "_ex_class_id");

    $this->_obj->setExClass($_ex_class_id);
    $this->_old->setExClass($_ex_class_id);
  }

  function doStore() {
    /** @var CExObject $ex_object */
    $ex_object = $this->_obj;

    // Change dynamically the notNull property, of the fields hidden in the form
    $hidden_fields = CValue::post("_hidden_fields");
    if ($hidden_fields) {
      $hidden = explode("|", $hidden_fields);
      foreach ($hidden as $_hidden) {
        $ex_object->_specs[$_hidden]->notNull = false;
      }
    }

    parent::doStore();

    if (CModule::getActive("dPprescription") && !$this->_old->_id) {
      $p_to_c = new CPrescriptionProtocoleToConcept();
      $count_p_to_c = $p_to_c->countList();

      if ($count_p_to_c > 0) {
        $all_fields = $ex_object->loadRefExClass()->loadRefsAllFields();
        $bool_concept_ids = array();
        foreach ($all_fields as $_field) {
          if (strpos($_field->prop, "bool") === 0 && $_field->concept_id && $ex_object->{$_field->name} == "1") {
            $bool_concept_ids[] = $_field->concept_id;
          }
        }

        $bool_concept_ids = array_unique($bool_concept_ids);

        $where = array(
          "concept_id" => $p_to_c->getDS()->prepareIn($bool_concept_ids)
        );
        $protocole_ids = array_values(CMbArray::pluck($p_to_c->loadList($where), "protocole_id"));

        if (count($protocole_ids)) {
          /** @var CSejour $sejour */
          $sejour = $ex_object->getReferenceObject("CSejour");
          if ($sejour && $sejour->_id) {
            $prescription = $sejour->loadRefPrescriptionSejour();

            if (!$prescription->_id) {
              $prescription = new CPrescription();
              $prescription->object_id = $sejour->_id;
              $prescription->object_class = $sejour->_class;
              $prescription->type = "sejour";

              if ($msg = $prescription->store()) {
                CAppUI::setMsg($msg, UI_MSG_WARNING);
              }
            }

            $role_propre = CAppUI::gconf("mpm general role_propre");

            $ops_ids = implode("-", CMbArray::pluck($sejour->loadRefsOperations(array("annulee" => "= '0'")), "operation_id"));
            CAppUI::callbackAjax(
              "(window.opener ? window.opener.ExObject.checkOpsBeforeProtocole : window.ExObject.checkOpsBeforeProtocole)",
              $protocole_ids,
              $prescription->_id,
              $sejour->_id,
              $ops_ids,
              $role_propre ? CMediusers::get()->_id : $sejour->praticien_id,
              $role_propre ? null : $sejour->praticien_id
            );
          }
        }
      }
    }

    if (CModule::getActive("soins")) {
      CAppUI::callbackAjax("((window.opener ? window.opener.updateInfosPatient : window.updateInfosPatient) || function(){})");
    }
  }
}

$do = new CDoExObjectAddEdit("CExObject");
$do->doIt();
