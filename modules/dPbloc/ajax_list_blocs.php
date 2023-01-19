<?php

/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

$bloc_id = CView::get("bloc_id", "ref class|CBlocOperatoire");

CView::checkin();

$curr_group = CGroups::loadCurrent();

// Récupération des blocs de l'etablissement
$blocs_list = $curr_group->loadBlocs(PERM_EDIT);

// Récupération des sspis de l'établissement
$sspis_list = $curr_group->loadSSPIs(PERM_EDIT);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("blocs_list", $blocs_list);
$smarty->assign("bloc_id"   , $bloc_id);

$smarty->display("inc_list_blocs");
