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
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

$salle_id = CView::get("salle_id", "ref class|CSalle");

CView::checkin();

$curr_group = CGroups::loadCurrent();

// Récupération des blocs de l'etablissement
$blocs_list = $curr_group->loadBlocs(PERM_EDIT);

// Récupération de la salle à ajouter / modifier
$salle = new CSalle();
$salle->load($salle_id);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("salle", $salle);
$smarty->assign("blocs_list", $blocs_list);

$smarty->display("inc_edit_salle");