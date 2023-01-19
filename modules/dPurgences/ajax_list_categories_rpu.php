<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Urgences\CRPUCategorie;

CCanDo::checkAdmin();

$categorie_rpu  = new CRPUCategorie();
$categories_rpu = $categorie_rpu->loadGroupList(null, "motif");

foreach ($categories_rpu as $_categorie_rpu) {
  $_categorie_rpu->loadRefIcone();
}

$smarty = new CSmartyDP();

$smarty->assign("categories_rpu", $categories_rpu);

$smarty->display("inc_list_categories_rpu");