<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Files\CFile;

CCanDo::checkRead();

$keywords      = CView::get("keywords", "str", true);
$selClass      = CView::get("selClass", "str", true);
$selKey        = CView::get("selKey", "ref class|$selClass", true);
$typeVue       = CView::get("typeVue", "num default|0", true);
$file_id       = CView::get("file_id", "ref class|CFile", true);
$accordDossier = CView::get("accordDossier", "bool default|0", true);

CView::checkin();

$object = new CMbObject();

$file = new CFile();
$file->load($file_id);

// Chargement de l'objet
if ($selClass && $selKey) {
  /** @var CMbObject $object */
  $object = new $selClass;
  $object->load($selKey);
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("selClass"     , $selClass);
$smarty->assign("selKey"       , $selKey);
$smarty->assign("selView"      , $object->_view);
$smarty->assign("keywords"     , $keywords);
$smarty->assign("object"       , $object);
$smarty->assign("file"         , $file);
$smarty->assign("typeVue"      , $typeVue);
$smarty->assign("accordDossier", $accordDossier);

$smarty->display("vw_files.tpl");

