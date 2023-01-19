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
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Stats\CBlocStatistics;

CCanDo::checkRead();

$plage_id              = CView::get('plage_id', 'ref class|CPlageOp');
$plages_to_display     = CView::get('plages_to_display', "str");
$operations_to_display = CView::get('operations_to_display', "str");
$export                = CView::get('export', "str");

CView::checkin();
CView::enforceSlave();

$stats = new CBlocStatistics(
  array(
    'plages_to_display'     => $plages_to_display,
    'operations_to_display' => $operations_to_display
  )
);

$stats->date_max = null;
$stats->date_min = null;

$results = $stats->getVacationDetails($plage_id);
$plage   = new CPlageOp();
$plage->load($plage_id);

if ($export) {
  $file = new CCSVFile();

  $file->writeLine(
    array(
      'interv date',
      'interv libellé',
      'salle',
      'praticien',
      'anesthésiste',
      'patient',
      'age',
      'poids',
      'taille',
      'sejour ndos',
      'sejour type',
      'sejour duree',
      'sejour entree',
      'sejour sortie',
      'type anesth',
      'ASA',
      'entrée SSPI',
      'sortie SSPI',
      'entrée salle',
      'sortie salle'
    )
  );

  foreach ($results as $_operation) {
    $_sejour  = $_operation->_ref_sejour;
    $_patient = $_operation->_ref_patient;

    $file->writeLine(
      array(
        $_operation->date,
        $_operation->libelle,
        $_operation->_ref_salle->_view,
        $_operation->_ref_chir->_view,
        $_operation->_ref_anesth->_view,
        $_patient->_view,
        $_patient->_age,
        $_patient->_poids,
        $_patient->_taille,
        $_sejour->_NDA,
        CAppUI::tr("CSejour.type.$_sejour->type"),
        $_sejour->_duree . $_sejour->_duree > 1 ? CAppUI::tr('days') : CAppUI::tr('day'),
        CMbDT::format($_sejour->entree_reelle ? $_sejour->entree_reelle : $_sejour->entree_prevue, CAppUI::conf('datetime')),
        CMbDT::format($_sejour->sortie_reelle ? $_sejour->sortie_reelle : $_sejour->sortie_prevue, CAppUI::conf('datetime')),
        $_operation->type_anesth,
        $_operation->ASA,
        CMbDT::format($_operation->entree_reveil, CAppUI::conf('time')),
        CMbDT::format($_operation->sortie_reveil_reel ?: $_operation->sortie_reveil_possible, CAppUI::conf('time')),
        CMbDT::format($_operation->entree_salle, CAppUI::conf('time')),
        CMbDT::format($_operation->sortie_salle, CAppUI::conf('time'))
      )
    );
  }

  $file->stream('details_operations' . "_{$plage->_view}");
  CApp::rip();
}

$smarty = new CSmartyDP();
$smarty->assign('results', $results);
$smarty->assign('stats', $stats);
$smarty->assign('plage', $plage);
$smarty->display('inc_vacation_details.tpl');