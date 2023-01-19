<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbSecurity;
use Ox\Core\CMbString;
use Ox\Mediboard\Mediusers\Api\CAPICMediusers;
use Ox\Mediboard\Mediusers\Api\CAPITools;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$post = file_get_contents('php://input');
$input = json_decode($post, true);

$user = CMediusers::get();

if (!$input) {
    CAPITools::response('common-error-Invalid data', 400);
}

$prettify = (isset($input['prettify'])) ? $input['prettify'] : false;

if (!isset($input['data']) || !$input['data']) {
    CAPITools::response('common-error-Missing parameter: %s', 400, $prettify, null, null, 'DATA');
}

$data = $input['data'];

if (isset($data['id'])) {
    unset($data['id']);
}

if (!isset($data['mot_de_passe']) || !$data['mot_de_passe']) {
    $data['mot_de_passe'] = CMbSecurity::getRandomPassword();
}

$api_object = new CAPICMediusers();

// Todo: Remove all this API
//if (isset($data['ldap_guid']) && $data['ldap_guid']) {
//    if (CAppUI::conf('admin LDAP object_guid_mode') == 'registry') {
//        $data['ldap_guid'] = CLDAP::convertHexaToRegistry($data['ldap_guid']);
//    }
//
//    if ($data['ldap_guid']) {
//        $idex               = new CIdSante400();
//        $idex->tag          = CAppUI::conf('admin LDAP ldap_tag');
//        $idex->object_class = 'CUser';
//        $idex->object_id    = $api_object->_ref_object->_id;
//        $idex->id400        = $data['ldap_guid'];
//
//        if (!$idex->loadMatchingObjectEsc()) {
//            LdapCache::invalidateCache($api_object->_ref_object->_id);
//
//            if ($msg = $idex->store()) {
//                CAPITools::response(CMbString::removeHtml($msg), 500);
//            }
//        }
//    }
//}

try {
    $api_object->storeMbObject($data);
} catch (Exception $e) {
    CAPITools::response(CMbString::removeHtml($e->getMessage()), 500, $prettify);
}

CAPITools::response(
    "{$api_object->_ref_object->_class}-msg-create", 201, $prettify, $api_object->_ref_object->_id,
    [
        'object' => $api_object::mbObjectToAPI($api_object->_ref_object),
    ]
);
