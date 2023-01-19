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
use Ox\Mediboard\Maternite\CSurvEchoGrossesse;

CCanDo::checkEdit();

$grossesse_id = CView::get("grossesse_id", "ref class|CGrossesse");
$print        = CView::get("print", "bool default|0");

CView::checkin();

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);
$grossesse->loadRefGroup();
$grossesse->loadRefPere();
$grossesse->getDateAccouchement();
$grossesse->loadRefsNaissances();

$patient = $grossesse->loadRefParturiente();
$patient->loadIPP($grossesse->group_id);
$patient->loadRefsCorrespondants();
$patient->loadRefsCorrespondantsPatient();

$dossier = $grossesse->loadRefDossierPerinat();

// grossesse multiple
$list_children  = array();
$count_children = 0;

if ($grossesse->multiple) {
  $test = 0;

  /** @var CSurvEchoGrossesse[] $depistages */
  $depistages     = $grossesse->loadBackRefs("echographies", "date ASC");
  $count_children = count($grossesse->loadBackRefs("echographies", "date ASC", null, "num_enfant"));

  foreach ($depistages as $depistage) {
    $list_children[$depistage->num_enfant][$depistage->_id] = $depistage;
  }
}

$smarty = new CSmartyDP();

$smarty->assign("grossesse", $grossesse);
$smarty->assign("list_children", $list_children);
$smarty->assign("count_children", $count_children);
$smarty->assign("print", $print);

$smarty->display("dossier_mater_debut_grossesse.tpl");

