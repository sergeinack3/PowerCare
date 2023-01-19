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
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();
$grossesse_id     = CView::get("grossesse_id", "ref class|CGrossesse");
$show_header      = CView::get("show_header", "bool default|0");
$is_tdb_maternite = CView::get("is_tdb_maternite", "bool default|1");
$with_buttons     = CView::get("with_buttons", "bool default|1");
CView::checkin();

/**
 * Modification de grossesse
 */
$user = CMediusers::get();
$user->isProfessionnelDeSante();

$grossesse = CGrossesse::findOrNew($grossesse_id);

$patient = $grossesse->loadRefParturiente();
$patient->loadIPP($grossesse->group_id);
$patient->loadRefsCorrespondants();
$patient->loadRefsCorrespondantsPatient();

$listPrat = CConsultation::loadPraticiens(PERM_EDIT);

$smarty = new CSmartyDP();
$smarty->assign("grossesse", $grossesse);
$smarty->assign("prats", $listPrat);
$smarty->assign("user", $user);
$smarty->assign("show_header", $show_header);
$smarty->assign("is_tdb_maternite", $is_tdb_maternite);
$smarty->assign("with_buttons", $with_buttons);
$smarty->display("inc_vw_tdb_grossesse");
