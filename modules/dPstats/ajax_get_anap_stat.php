<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Stats\CBlocStatistics;

CCanDo::checkRead();

$date_min              = CView::get('date_min', 'date', true);
$date_max              = CView::get('date_max', 'date', true);
$discipline_ids        = CView::get('discipline_ids', "str", true);
$prat_ids              = CView::get('prat_ids', "str", true);
$bloc_ids              = CView::get('bloc_ids', "str", true);
$salle_ids             = CView::get('salle_ids', "str", true);
$plages_to_display     = CView::get('plages_to_display', "str", true);
$operations_to_display = CView::get('operations_to_display', "str", true);
$grouping              = CView::get('grouping', "str", true);
$export                = CView::get('export', "str");

CView::checkin();
CView::enforceSlave();

$stats = new CBlocStatistics(
  array(
    'date_min'              => $date_min,
    'date_max'              => $date_max,
    '_disciplines_ids'      => $discipline_ids,
    '_prats_ids'            => $prat_ids,
    '_blocs_ids'            => $bloc_ids,
    '_salles_ids'           => $salle_ids,
    'plages_to_display'     => $plages_to_display,
    'operations_to_display' => $operations_to_display,
    'grouping'              => $grouping
  )
);

$results = $stats->getANAPStatistics();

if ($export) {
  $file = new CCSVFile();

  $file->writeLine(
    array(
      'Praticien',
      'Salle',
      'Date',
      'TVO',
      'TPOS',
      'TROSjour',
      'TROV',
      'Tx occupation',
      'Potentiel salle',
      'Débordement',
      'Tx débordement',
      'Tx performance',
      'Tx démarrage tardifs',
      'Tx fins précoces',
      'Tx urgences',
      'Tx utilisation potentiel salle',
      'Evaluation TVO'
    )
  );

  foreach ($results['vacations'] as $_vacation => $_results) {
    // No grouping array
    if (!$grouping) {
      $_results = array($_results);
    }
    foreach ($_results as $_result) {
      $_plage = $_result['plage'];
      $file->writeLine(
        array(
          $_plage->_ref_chir->_view,
          $_plage->_ref_salle->_view,
          $_plage->getFormattedValue('date') . ' ' . $_plage->getFormattedValue('debut') . ' - ' . $_plage->getFormattedValue('fin'),
          $_result['tvo'],
          $_result['tpos'],
          $_result['tros'],
          $_result['trov'],
          $_result['txoc'],
          $_result['pot'],
          $_result['deb'],
          $_result['txdeb'],
          $_result['txper'],
          $_result['txbeg'],
          $_result['txend'],
          $_result['txurg'],
          $_result['txpot'],
          $_result['evtvo']
        )
      );
    }
  }

  $file->stream(str_replace(' ', '_', CAppUI::tr('CBlocStatistics-stats-anap')) . "_{$stats->date_min}_{$stats->date_max}");
  CApp::rip();
}

$smarty = new CSmartyDP();
$smarty->assign('stats', $stats);
$smarty->assign('results', $results);
$smarty->display('inc_anap_results.tpl');
