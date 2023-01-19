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

CCanDo::checkAdmin();

$sspi_id = CView::get("sspi_id", "ref class|CSSPI");

CView::checkin();

$sspi = new CSSPI();
$sspi->load($sspi_id);
$sspi->loadRefsPostes();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sspi", $sspi);

$smarty->display("inc_list_sspi_postes");