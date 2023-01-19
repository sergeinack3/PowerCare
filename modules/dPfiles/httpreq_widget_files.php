<?php

/**
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
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\OxLaboClient\OxLaboClientHandler;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceHTTP;

CCanDo::check();
$object_class = CView::get("object_class", "str");
$object_id    = CView::get("object_id", "ref class|$object_class");
$object_guid  = CView::get("object_guid", "str default|" . "$object_class-$object_id");

$object = CMbObject::loadFromGuid($object_guid);

if (!$object || !$object->_id) {
    CAppUI::notFound($object_guid);
}

$only_files     = CView::get("only_files", "bool default|0");
$name_readonly  = CView::get("name_readonly", "bool default|0");
$use_mozaic     = CView::get("mozaic", "bool default|0");
$show_actions   = CView::get("show_actions", "bool default|1");
$category_id    = CView::get("category_id", "ref class|CFilesCategory");
$show_widget    = CView::get('show_widget', "bool default|1");
$show_only      = CView::get('show_only', "bool default|0");
$show_img       = CView::get('show_img', "bool default|0");
$show_hyperlink = CView::get("show_hyperlink", "bool default|1");
$link_readonly  = CView::get("link_readonly", "bool");
$with_div       = CView::get("with_div", "bool default|1");

CView::checkin();

// Chargement des fichiers
$object->loadRefsFiles();
$object->loadRefsHyperTextLink();
$object->canDo();

$appFineClient_active = CModule::getActive("appFineClient");

//Récupération des alertes OxLabo
$source_labo           = CExchangeSource::get(
    "OxLabo" . CGroups::loadCurrent()->_id,
    CSourceHTTP::TYPE,
    false,
    "OxLaboExchange",
    false
);
$alerts_new_result     = [];
$alerts_anormal_result = [];

if ($object->_class == "CSejour" && CModule::getActive("OxLaboClient") && $source_labo->active) {
    $labo_handler          = new OxLaboClientHandler();
    $alerts_anormal_result = $labo_handler->getAlerteAnormalForSejours([$object], "file_id");

    $alerts_new_result = $labo_handler->getAlertNewResultForSejours([$object], "file_id");
}


$affichageFile = [];
if ($object->_ref_files) {
    foreach ($object->_ref_files as $_k => $_file) {
        if ($category_id && $_file->file_category_id != $category_id) {
            unset($object->_ref_files[$_k]);
            continue;
        }

        $_file->countSynchronizedRecipients();
        $_file->canDo();

        $cat_id                                                                = $_file->file_category_id ?: 0;
        $affichageFile[$cat_id]["items"][$_file->file_name . "-$_file->_guid"] = $_file;
        if (!isset($affichageFile[$cat_id]["name"])) {
            $affichageFile[$cat_id]["name"] = $cat_id ? $_file->_ref_category->nom : CAppUI::tr("CFilesCategory.none");
        }

        if ($appFineClient_active) {
            CAppFineClient::loadBackRefOrderItem($_file);
        }
    }
}

foreach ($affichageFile as $categorie => $files) {
    CMbArray::pluckSort($affichageFile[$categorie]['items'], SORT_DESC, "file_date");
}

if ($appFineClient_active) {
    CAppFineClient::loadIdex($object);
}

$file = new CFile();

$smarty = new CSmartyDP();
$smarty->assign("object", $object);
$smarty->assign("can_files", $file->canClass());
$smarty->assign("name_readonly", $name_readonly);
$smarty->assign("mozaic", $use_mozaic);
$smarty->assign("show_actions", $show_actions);
$smarty->assign("category_id", $category_id);
$smarty->assign("show_widget", $show_widget);
$smarty->assign('show_only', $show_only);
$smarty->assign('show_img', $show_img);
$smarty->assign('show_hyperlink', $show_hyperlink);
$smarty->assign('link_readonly', $link_readonly);
$smarty->assign('affichageFile', $affichageFile);
$smarty->assign("with_div", $with_div);
$smarty->assign("alerts_anormal_result", $alerts_anormal_result);
$smarty->assign("alerts_new_result", $alerts_new_result);

$smarty->display($only_files ? "inc_widget_list_files" : "inc_widget_vw_files");
