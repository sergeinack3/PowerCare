<?php
/**
 * @package Mediboard\urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Urgences\CRPUCategorie;

CCanDo::checkAdmin();

$categorie_rpu_id = CView::get("categorie_rpu_id", "ref class|CRPUCategorie");

CView::checkin();

$categorie_rpu = new CRPUCategorie();
$categorie_rpu->load($categorie_rpu_id);
$categorie_rpu->loadRefIcone();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("categorie_rpu", $categorie_rpu);

$smarty->display("inc_edit_categorie_rpu");
