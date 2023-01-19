<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\CSpecCPAM;

CCanDo::check();

$mediuser = CMediusers::get();
$mediuser->loadRefFunction();
$mediuser->loadRefSpecCPAM();
$mediuser->loadRefDiscipline();
$mediuser->loadRefBanque();
$mediuser->loadRefsSecondaryUsers();
$mediuser->_ref_user->isLDAPLinked();
$mediuser->loadNamedFile("identite.jpg");
$mediuser->loadNamedFile("signature.jpg");

// Récupération des disciplines
$disciplines = new CDiscipline();
$disciplines = $disciplines->loadList();

$affiche_nom = CValue::get("affiche_nom", 0);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("b2g"        , CAppUI::gconf("admin CBrisDeGlace enable_bris_de_glace"));
$smarty->assign("disciplines", $disciplines);
$smarty->assign("spec_cpam"  , CSpecCPAM::getList());
$smarty->assign("fonction"   , $mediuser->_ref_function);
$smarty->assign("user"       , $mediuser);
$smarty->assign("affiche_nom", $affiche_nom);

$smarty->assign("dPboard_name", CAppUI::tr('module-dPboard-court'));

$smarty->display("edit_infos.tpl");
