<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */


use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Interop\Eai\CMessageSupported;
use Ox\Interop\Ihe\CIHE;

$profil_name      = CView::get("family_name", "str");
$transaction_name = CView::get("category_name", "str");
$actor_guid       = CView::get("actor_guid", "guid class|CInteropReceiver");
$toggle           = CView::get("toggle", "str");
CView::checkin();

$actor = CMbObject::loadFromGuid($actor_guid);

/** @var CIHE $profil */
$profil = new $profil_name;

if ($profil->_categories) {
  foreach ($profil->_categories as $transaction_key => $_category) {
    // Si la catégorie passée en paramètre est différente de celle qu'on parcourt on passe à la suivante
    if ($transaction_name && $transaction_key != $transaction_name) {
      continue;
    }

    foreach ($profil->_categories[$transaction_key] as $_transaction_name) {
      $message_supported = new CMessageSupported();
      $message_supported->setObject($actor);
      $message_supported->message = CMbArray::get($profil::$evenements, $_transaction_name);
      $message_supported->profil = $profil_name;
      $message_supported->transaction = $transaction_key;
      $message_supported->loadMatchingObject();

      if (!$message_supported->_id) {
        $message_supported->active = 1;
      }
      else {
        $message_supported->active = $toggle;
      }
      $message_supported->store();
    }
  }
}
else {
  foreach ($profil->getEvenements() as $evenement) {
    $message_supported = new CMessageSupported();
    $message_supported->setObject($actor);
    $message_supported->message = $evenement;
    $message_supported->profil  = $profil_name;
    $message_supported->loadMatchingObject();

    if (!$message_supported->_id) {
      $message_supported->active = 1;
    }
    else {
      $message_supported->active = $toggle;
    }
    $message_supported->store();
  }
}

CAppUI::setMsg("CMessageSupported-msg-modify");
echo CAppUI::getMsg();