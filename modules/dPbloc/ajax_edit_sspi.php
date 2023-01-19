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
use Ox\Mediboard\Bloc\CSSPI;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

$sspi_id = CView::get("sspi_id", "ref class|CSSPI");

CView::checkin();

$curr_group = CGroups::loadCurrent();

// Récupération des blocs de l'etablissement
$blocs_list = $curr_group->loadBlocs(PERM_EDIT);

// Récupération de la sspi à ajouter / modifier
$sspi = new CSSPI();
$sspi->load($sspi_id);
$sspi->loadRefsBlocs();
$sspi->loadRefsPostes();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sspi", $sspi);
$smarty->assign("blocs_list", $blocs_list);

$smarty->display("inc_edit_sspi");