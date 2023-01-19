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

$sspi_id  = CView::get("sspi_id", "ref class|CSSPI");

CView::checkin();

$curr_group = CGroups::loadCurrent();

// R�cup�ration des sspis de l'�tablissement
$sspis_list = $curr_group->loadSSPIs(PERM_EDIT);

// R�cup�ration de la sspi � ajouter / modifier
$sspi = new CSSPI();
$sspi->load($sspi_id);
$sspi->loadRefsBlocs();
$sspi->loadRefsPostes();

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("sspi_id"      , $sspi_id);
$smarty->assign("sspi"         , $sspi);
$smarty->assign("sspis_list"   , $sspis_list);

$smarty->display("vw_list_sspis");