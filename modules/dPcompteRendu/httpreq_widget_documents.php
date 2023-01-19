<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Ameli\CAvisArretTravail;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CModeleEtiquette;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Widget des documents
 */
CCanDo::check();

$object_class = CView::get("object_class", "str", true);
$object_id    = CView::get("object_id", "ref class|$object_class", true);
$user_id      = CView::get("praticien_id", "ref class|CMediusers", true);
$only_docs    = CView::get("only_docs", "bool default|0");
$category_id  = CView::get("category_id", "ref class|CFilesCategory");
$mode         = CView::get("mode", "str");
$unique_id    = CView::get("unique_id", "str");

CView::checkin();

// Chargement de l'objet cible
$object = new $object_class;
if (!$object instanceof CMbObject) {
    trigger_error("object_class should be an CMbObject", E_USER_WARNING);

    return;
}

$object->load($object_id);
if (!$object->_id) {
    trigger_error("object of class '$object_class' could not be loaded with id '$object_id'", E_USER_WARNING);

    return;
}

$object->canDo();

// Voir si un arret de travail existe
$avis_arret_travail = 0;
if (CModule::getActive("ameli")) {
    if ($object instanceof CConsultation) {
        $arret_travail = new CAvisArretTravail();
        $where         = [];

        $where['object_id']    = " = '$object_id'";
        $where['object_class'] = " = 'CConsultation'";

        $avis_arret_travail = $arret_travail->countList($where);
    } elseif ($object instanceof CSejour) {
        $object->loadRefsConsultations();

        foreach ($object->_ref_consultations as $_consult) {
            /** @var CConsultation $_consult */
            $praticien = $_consult->loadRefPraticien();
            $praticien->loadRefFunction();
            $_consult->canDo();
            if ($praticien->isUrgentiste() && ($object->countBackRefs("rpu") > 0)) {
                $list_avis = $_consult->loadBackRefs("arret_travail");

                $avis_arret_travail = count($list_avis);
            }
        }
    }
}

$user = CMediusers::get();

// Praticien concerné
if (!$user->isPraticien() && $user_id) {
    $user = CMediusers::get($user_id);
}

$user->loadRefFunction();
$user->_ref_function->loadRefGroup();
$user->canDo();

$appFineClient_active = CModule::getActive("appFineClient");

$affichageDocs = [];

if ($object->loadRefsDocs()) {
    foreach ($object->_ref_documents as $_doc) {
        if ($category_id && $_doc->file_category_id != $category_id) {
            unset($object->_ref_documents[$_doc->_id]);
            continue;
        }
        $_doc->countSynchronizedRecipients();
        $_doc->checkSendDocument();
        $_doc->isLocked();
        $_doc->canDo();

        $cat_id                                                        = $_doc->file_category_id ?: 0;
        $affichageDocs[$cat_id]["items"][$_doc->nom . "-$_doc->_guid"] = $_doc;
        if (!isset($affichageDocs[$cat_id]["name"])) {
            $affichageDocs[$cat_id]["name"] = $cat_id ? $_doc->_ref_category->nom : CAppUI::tr("CFilesCategory.none");
        }
    }
}

foreach ($affichageDocs as $categorie => $docs) {
    CMbArray::pluckSort($affichageDocs[$categorie]['items'], SORT_DESC, "creation_date");
}

// Compter les modèles d'étiquettes
$modele_etiquette               = new CModeleEtiquette();
$modele_etiquette->object_class = $object_class;
$modele_etiquette->group_id     = CGroups::loadCurrent()->_id;
$nb_modeles_etiquettes          = $modele_etiquette->countMatchingList();

$nb_printers = 0;

if (CModule::getActive("printing")) {
    // Chargement des imprimantes pour l'impression d'étiquettes
    $user_printers = CMediusers::get();
    $function      = $user_printers->loadRefFunction();
    $nb_printers   = $function->countBackRefs("printers");
}

$compte_rendu = new CCompteRendu();

// Création du template
$smarty = new CSmartyDP();

if ($appFineClient_active) {
    CAppFineClient::loadIdex($object);
}

$smarty->assign("praticien", $user);
$smarty->assign("object", $object);
$smarty->assign("avis_arret_travail", $avis_arret_travail);
$smarty->assign("mode", $mode);
$smarty->assign("notext", "notext");
$smarty->assign("nb_printers", $nb_printers);
$smarty->assign("nb_modeles_etiquettes", $nb_modeles_etiquettes);
$smarty->assign("can_create_docs", $compte_rendu->canCreate($object));
$smarty->assign("affichageDocs", $affichageDocs);
$smarty->assign("unique_id", $unique_id);

$smarty->display($only_docs ? "inc_widget_list_documents" : "inc_widget_documents");
