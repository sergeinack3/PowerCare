<?php

/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CModeleToPack;
use Ox\Mediboard\CompteRendu\Controllers\Legacy\CCompteRenduController;
use Ox\Mediboard\CompteRendu\CPack;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Pack multiple docs aed
 */
// Génération d'un document pour chaque modèle du pack
$pack_id         = CView::post("pack_id", "ref class|CPack");
$object_class    = CView::post("object_class", "str");
$object_id       = CView::post("object_id", "ref class|$object_class");
$ext_cabinet_id  = CView::post("_ext_cabinet_id", "num");
$callback        = CView::post("callback", "str");
$liste_cr        = CView::post("liste_cr", "str");

CView::checkin();
CView::reset();

$user_id = CMediusers::get()->_id;

$pack = new CPack();
$pack->load($pack_id);

$modele_to_pack  = new CModeleToPack();
$modeles_to_pack = $modele_to_pack->loadAllModelesFor($pack_id);

/** @var $object CMbObject */
$object = new $object_class();
$object->load($object_id);

$cr_to_push = null;

// Sauvegarde du premier compte-rendu pour
// l'afficher dans la popup d'édition de compte-rendu
if (!$pack->is_eligible_selection_document) {
    $array_modeles = $modeles_to_pack;
    $first         = reset($modeles_to_pack);
    $modeles       = CMbObject::massLoadFwdRef($modeles_to_pack, "modele_id");
    CMbObject::massLoadFwdRef($modeles, "content_id");
} else {
    $array_modeles = $liste_cr;
    $first         = $liste_cr[0];
}
foreach ($array_modeles as $array_modele) {
    (!$pack->is_eligible_selection_document) ? $modele = $array_modele->loadRefModele(
    ) : $modele = CCompteRendu::findOrFail($array_modele);

    $params = [
        'modele_id'       => $modele->_id,
        'praticien_id'    => $user_id,
        'target_class'    => $object->_class,
        'target_id'       => $object->_id,
        'store_headless'  => 1,
        'suppressHeaders' => 1,
    ];

    CView::reset();
    $cr_id = (new CCompteRenduController())->edit($params);

    if ($array_modele === $first) {
        $cr_to_push = CCompteRendu::findOrFail($cr_id);
    }
}

if ($callback && $cr_to_push) {
    $fields = $cr_to_push->getProperties();
    CAppUI::callbackAjax($callback, $cr_to_push->_id, $fields);
}

CAppUI::setMsg(CAppUI::tr("CPack-msg-create"), UI_MSG_OK);

echo CAppUI::getMsg();

CApp::rip();
