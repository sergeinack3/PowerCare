<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Soins\CObjectifSoinCategorie;

CCanDo::checkEdit();

$where = array(
  "group_id = '" . CGroups::loadCurrent()->_id . "' OR group_id IS NULL",
  "actif" => "= '0'",
);

$categorie     = new CObjectifSoinCategorie();
$countInactive = $categorie->countList($where);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("countInactive", $countInactive);

$smarty->display("vw_categories_objectif_soin");