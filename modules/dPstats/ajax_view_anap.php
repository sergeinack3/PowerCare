<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Stats\CBlocStatistics;

CCanDo::checkRead();

$date_min       = CView::get('date_min', 'date');
$date_max       = CView::get('date_max', 'date');
$discipline_ids = CView::get('discipline_ids', "str", true);
$prat_ids       = CView::get('prat_ids', "str", true);
$bloc_ids       = CView::get('bloc_ids', "str", true);
$salle_ids      = CView::get('salle_ids', "str", true);

CView::checkin();
CView::enforceSlave();

$stats = new CBlocStatistics(
  array(
    'date_min'         => $date_min,
    'date_max'         => $date_max,
    '_disciplines_ids' => $discipline_ids,
    '_prats_ids'       => $prat_ids,
    '_blocs_ids'       => $bloc_ids,
    '_salles_ids'      => $salle_ids,
  )
);

$discipline  = new CDiscipline();
$disciplines = $discipline->loadUsedDisciplines();

$bloc  = new CBlocOperatoire();
$blocs = CGroups::get()->loadBlocs();

$function  = new CFunctions();
$functions = $function->loadSpecialites(PERM_READ, 1);

$smarty = new CSmartyDP();
$smarty->assign('stats', $stats);
$smarty->assign('disciplines', $disciplines);
$smarty->assign('blocs', $blocs);
$smarty->assign('functions', $functions);
$smarty->display('inc_anap_stats.tpl');