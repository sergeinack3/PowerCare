<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CPermObject;

CCanDo::checkEdit();

$user_id  = CView::get("user_id", "ref class|CUser");
$mod_name = CView::get("mod_name", "str");
CView::checkin();

// DROITS SUR LES OBJETS
$permObject = new CPermObject();

$classes        = CApp::getInstalledClasses(array(), true);
$module_classes = CApp::groupClassesByModule($classes);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("module_classes"  , $module_classes  );
$smarty->assign("mod_name"        , $mod_name  );
$smarty->assign("user_id"         , $user_id  );
$smarty->assign("permObject"      , $permObject  );

$smarty->display("vw_objects_class.tpl");
