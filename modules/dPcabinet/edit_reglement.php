<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CBanque;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Facturation\CReglement;

CCanDo::checkEdit();

$reglement_id     = CView::get("reglement_id", "ref class|CReglement");
$force_regle_acte = CView::get("force_regle_acte", "bool");
$emetteur         = CView::get("emetteur", "str");
$mode             = CView::get("mode", "str");
$montant          = CView::get("montant", "str");

if ($reglement_id) {
    $object = null;
} else {
    $object_class = CView::get("object_class", "str");
    $object_id    = CView::get("object_id", "ref class|$object_class");
    $object_guid  = CView::get("object_guid", "str default|" . "$object_class-$object_id");

    $object = CMbObject::loadFromGuid($object_guid);

    if (!$object || !$object->_id) {
        CAppUI::notFound($object_guid);
    }
}

CView::checkin();

// Chargement du reglement
$reglement = new CReglement();
$reglement->load($reglement_id);
if ($reglement->_id) {
    $reglement->loadRefsNotes();
    $object = $reglement->loadTargetObject(true);
} // Préparation du nouveau règlement
else {
    $reglement->setObject($object);
    $reglement->date     = "now";
    $reglement->emetteur = $emetteur;
    $reglement->mode     = $mode ? $mode : CAppUI::gconf("dPfacturation CReglement use_mode_default");
    $reglement->montant  = $montant;
}

/** @var CFactureCabinet $facture */
$facture = $object;

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("reglement", $reglement);
$smarty->assign("object", $object);
$smarty->assign("facture", $facture);
$smarty->assign("banques", CBanque::loadAllBanques());
$smarty->assign("force_regle_acte", $force_regle_acte);

$smarty->display("edit_reglement.tpl");
