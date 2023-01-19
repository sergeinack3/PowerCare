<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Interop\Eai\CInteropActorFactory;
use Ox\Interop\Hl7\handlers\CHL7DelegatedHandler;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkAdmin();

$id_file        = CValue::get("id_file");
$value_type_R01 = CValue::get("value_type_R01");
$link_file      = CValue::get("link_file");

$file = new CFile();
$file->load($id_file);
$file->getBinaryContent();
$file->_value_type = $value_type_R01;

if (!class_exists($file->object_class)) {
    return;
}

$object = new $file->object_class();

if ($object instanceof CSejour) {
    $object->load($file->object_id);
    $object->loadRefPatient();
    $object->loadRefPraticien();

    $file->_sejour        = $object;
    $file->_patient       = $object->_ref_patient;
    $file->_ref_praticien = $object->_ref_praticien;
    $file->_ref_object    = $object;
}

if ($value_type_R01 == "RP") {
    $file->_link_file = $link_file;
}

if ($cn_receiver_guid = CValue::sessionAbs("cn_receiver_guid")) {
    $receiver_hl7v2 = CMbObject::loadFromGuid($cn_receiver_guid);
    $receivers      = [$receiver_hl7v2];
} else {
    $receiver_hl7v2           = (new CInteropActorFactory())->receiver()->makeHL7v2();
    $receiver_hl7v2->actif    = 1;
    $receiver_hl7v2->group_id = CGroups::loadCurrent()->_id;
    $receivers                = $receiver_hl7v2->loadMatchingList();
}

$message  = "ORU";
$code     = "R01";
$profil   = "CDEC";
$ack_data = null;

$hl7_handler = new CHL7DelegatedHandler();
foreach ($receivers as $_receiver) {
    if (!$hl7_handler->isMessageSupported($message, $code, $_receiver, $profil)) {
        continue;
    }

    $file->_receiver = $_receiver;

    $ack_data = $hl7_handler->sendEvent($message, $code, $file);
}

CApp::log("Message envoyé. Voir dans l'EAI l'échange");
