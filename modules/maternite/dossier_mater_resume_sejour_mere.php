<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Maternite\CGrossesse;

CCanDo::checkEdit();

$grossesse_id = CView::get("grossesse_id", "ref class|CGrossesse");
$print        = CView::get("print", "bool default|0");

CView::checkin();

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);
$patient = $grossesse->loadRefParturiente();
$dossier = $grossesse->loadRefDossierPerinat();
$sejour  = $dossier->loadRefSejourAccouchement();
$sejour->loadRefPraticien();

$smarty = new CSmartyDP();

$smarty->assign("grossesse", $grossesse);
$smarty->assign("print", $print);

$smarty->display("dossier_mater_resume_sejour_mere.tpl");

