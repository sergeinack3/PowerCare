<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CEntity;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Eai\CInteropActorFactory;
use Ox\Interop\Hl7\handlers\CHL7DelegatedHandler;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Etablissement\CLegalEntity;
use Ox\Mediboard\Hospi\CChambre;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;

CCanDo::checkAdmin();

$objects = null;
switch (CValue::get('value_entity')) {
    case 'M':
        $entity  = new CLegalEntity();
        $objects = $entity->loadList(null, null);
        break;

    case 'ETBL_GRPQ':
        $etab    = new CGroups();
        $objects = $etab->loadList(null, null);
        break;

    case 'D':
        $service = new CService();
        $objects = $service->loadList(null, null);
        break;

    case 'N':
        $unite_hebergement = new CUniteFonctionnelle();
        $where["type"]     = "= 'hebergement'";
        $objects           = $unite_hebergement->loadList($where, null);
        break;

    case 'H':
        $where["type"]     = "= 'medicale'";
        $unite_hebergement = new CUniteFonctionnelle();
        $objects           = $unite_hebergement->loadList($where, null);
        break;

    case 'B':
        $lit     = new CLit();
        $objects = $lit->loadList(null, null);
        break;

    case 'CLR':
        $chambre = new CChambre();
        $objects = $chambre->loadList(null, null);
        break;

    case 'R':
        $chambre = new CChambre();
        $objects = $chambre->loadList(null, null);
        break;

    case 'BX':
        $chambre = new CChambre();
        $objects = $chambre->loadList(null, null);
        break;

    case 'SL_ATNT':
        $chambre = new CChambre();
        $objects = $chambre->loadList(null, null);
        break;

    case 'PL':
    case 'UNT_MDCL':
    case 'UAC':
    case 'BTMNT':
    case 'L':
    case 'ETG':
    case 'AL':
    case 'PNT_CLCT':
    case 'PNT_LVRSN':
    case 'SL_RVL':
        break;

    default:
}


$entity           = new CEntity();
$entity->_objects = $objects;

if ($cn_receiver_guid = CValue::sessionAbs("cn_receiver_guid")) {
    $receiver_hl7v2 = CMbObject::loadFromGuid($cn_receiver_guid);
    $receivers      = [$receiver_hl7v2];
} else {
    $receiver_hl7v2           = (new CInteropActorFactory())->receiver()->makeHL7v2();
    $receiver_hl7v2->actif    = 1;
    $receiver_hl7v2->group_id = CGroups::loadCurrent()->_id;
    $receivers                = $receiver_hl7v2->loadMatchingList();
}

$message  = "MFN";
$code     = "M05";
$profil   = "CHL7MFN";
$ack_data = null;

$hl7_handler = new CHL7DelegatedHandler();
foreach ($receivers as $_receiver) {
    if (!$hl7_handler->isMessageSupported($message, $code, $_receiver, $profil)) {
        continue;
    }
    $entity->_receiver = $_receiver;

    $ack_data = $hl7_handler->sendEvent($message, $code, $entity);
}

$smarty = new CSmartyDP();
$smarty->assign('objects', $objects);
$smarty->display("inc_vw_message_MFN.tpl");

