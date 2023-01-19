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
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$lastname       = CView::get("lastname", "str");

CView::checkin();

// Liste des praticiens
$curr_user = CMediusers::get();
$prats     = $curr_user->loadPraticiens();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("lastname", $lastname);
$smarty->assign("grossesse", new CGrossesse());
$smarty->assign("patient", new CPatient());
$smarty->assign("prats", $prats);

$smarty->display("inc_search_grossesse.tpl");