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
use Ox\Mediboard\Hospi\CUniteFonctionnelle;

CCanDo::checkAdmin();

CView::enforceSlave();

$group = CGroups::loadCurrent();

if (!$group->getPerm(PERM_READ)) {
  CAppUI::stepAjax('access-forbidden', UI_MSG_ERROR);
}

$header = array(
  'code', 'libelle', 'type', 'type_sejour'
);

$date      = CMbDT::date();
$file_name = "export-uf-{$group->text}-{$date}";

$filepath = rtrim(CAppUI::conf('root_dir'), '/\\') . "/tmp/$file_name";

$fp  = fopen($filepath, 'w+');
$csv = new CCSVFile($fp);
$csv->setColumnNames($header);
$csv->writeLine($header);

$uf           = new CUniteFonctionnelle();
$uf->group_id = $group->_id;

$ufs = $uf->loadMatchingListEsc();

/** @var CUniteFonctionnelle $_uf */
foreach ($ufs as $_uf) {
  $line = array(
    'code'        => $_uf->code,
    'libelle'     => $_uf->libelle,
    'type'        => $_uf->type,
    'type_sejour' => $_uf->type_sejour,
  );

  $csv->writeLine($line);
}

$csv->stream($file_name, true);

$csv->close();
unlink($filepath);