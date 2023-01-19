<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Chronometer;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatientExportHm;

CCanDo::checkAdmin();

$count    = CView::post('count', 'num default|100');
$start    = CView::post('start', 'num default|0');
$continue = CView::post('continue', 'bool default|0');
$max      = CView::post('max', 'num default|0');

CView::checkin();

$group = CGroups::loadCurrent();

$file_path = rtrim(CAppUI::conf('root_dir'), '/\\') . "/tmp/export-hm-$group->text";

$chrono = new Chronometer();
$chrono->start();

$export      = new CPatientExportHm();
$nb_patients = $export->doExport($start, $count);

$chrono->step('Récupération et formatage des patients');

$create = false;
if (!file_exists($file_path)) {
  $create = true;
}

$fp = fopen($file_path, 'a+');

if ($create) {
  $header = $export->createHeader();
  if ($header) {
    $export->writeLine($header, $fp);
  }
}

foreach ($export->getPatientsCsv() as $_values) {
  $export->writeLine($_values, $fp);
}

$chrono->step('Écriture du fichier');
$chrono->report();

CAppUI::stepAjax('CPatient-export-hm-nb', UI_MSG_OK, $nb_patients);

fclose($fp);

$next = $count + $start;
CAppUI::js("\$V(getForm('do-export-patients-hm').elements.start, '$next')");

if ($next <= $max && $continue) {
  CAppUI::js('ExportPatientsHm.nextImport()');
  CApp::rip();
}