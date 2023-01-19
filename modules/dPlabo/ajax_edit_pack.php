<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Labo\CPackExamensLabo;
use Ox\Mediboard\Mediusers\CFunctions;

CCanDo::checkEdit();

$pack_examens_labo_id = CView::get("pack_examens_labo_id", "ref class|CPackExamensLabo", true);

CView::checkin();

// Chargement du pack demandé
$pack = new CPackExamensLabo();
$pack->load($pack_examens_labo_id);
$pack->loadRefsItemExamenLabo();

// Chargement des fontions
$function = new CFunctions();
$listFunctions = $function->loadListWithPerms(PERM_EDIT);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("pack"         , $pack);
$smarty->assign("listFunctions", $listFunctions);

$smarty->display("inc_edit_pack");