<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CItemPrestation;
use Ox\Mediboard\Hospi\CPrestationExpert;
use Ox\Mediboard\Hospi\CPrestationJournaliere;
use Ox\Mediboard\Hospi\CPrestationPonctuelle;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::checkAdmin();

CCanDo::checkAdmin();

CView::enforceSlave();

$group = CGroups::loadCurrent();

if (!$group->getPerm(PERM_READ)) {
  CAppUI::stepAjax('access-forbidden', UI_MSG_ERROR);
}

$header = array(
  'prestation', 'type', 'type_admission', 'M', 'C', 'O', 'SSR', 'item', 'rang', 'identifiant_externe'
);

$date      = CMbDT::date();
$file_name = "export-prestations-{$group->text}-{$date}";

$filepath = rtrim(CAppUI::conf('root_dir'), '/\\') . "/tmp/$file_name";

$fp  = fopen($filepath, 'w+');
$csv = new CCSVFile($fp);
$csv->setColumnNames($header);
$csv->writeLine($header);

$prestatation_journaliere           = new CPrestationJournaliere();
$prestatation_journaliere->group_id = $group->_id;
$prestations_journalieres           = $prestatation_journaliere->loadMatchingListEsc();

/** @var CPrestationJournaliere $_presta */
foreach ($prestations_journalieres as $_presta) {
  $csv = exportPresta($_presta, $csv, 'journaliere');
}

$prestatation_ponctuelle           = new CPrestationPonctuelle();
$prestatation_ponctuelle->group_id = $group->_id;
$prestatations_ponctuelle          = $prestatation_ponctuelle->loadMatchingListEsc();

foreach ($prestatations_ponctuelle as $_presta) {
  $csv = exportPresta($_presta, $csv, 'ponctuelle');
}

$csv->stream($file_name, true);

$csv->close();
unlink($filepath);

/**
 * @param CPrestationExpert $presta Prestation to export
 * @param CCSVFile          $csv    CSV to write to
 * @param string            $type   Ponctuelle ou journalière
 *
 * @return mixed
 */
function exportPresta($presta, $csv, $type) {
  $items = $presta->loadRefsItems();
  /** @var CItemPrestation $_item */
  foreach ($items as $_item) {
    $idex       = $_item->loadBackRefs('identifiants');
    $idex_field = array();
    /** @var CIdSante400 $_idx */
    foreach ($idex as $_idx) {
      $idex_field[] = $_idx->id400 . '|' . $_idx->tag;
    }
    $idex_field = implode('||', $idex_field);

    $line = array(
      'prestation'          => $presta->nom,
      'type'                => $type,
      'type_admission'      => $presta->type_hospi,
      'M'                   => $presta->M,
      'C'                   => $presta->C,
      'O'                   => $presta->O,
      'SSR'                 => $presta->SSR,
      'item'                => $_item->nom,
      'rang'                => $_item->rank,
      'identifiant_externe' => $idex_field,
      'price'               => $_item->price,
    );

    $csv->writeLine($line);
  }

  return $csv;
}