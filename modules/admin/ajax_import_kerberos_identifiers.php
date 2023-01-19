<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCando;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;

CCanDo::checkAdmin();

$uid = preg_replace('/[^\d]/', '', CView::get('uid', 'str'));

CView::checkin();

$temp = CAppUI::getTmpPath('kerberos_import');
$file = "{$temp}/{$uid}";

$csv = new CCSVFile($file, CCSVFile::PROFILE_AUTO);
$csv->setColumnNames(['mediboard_identifier', 'domain_identifier']);

$sample = [];

$line = $csv->readLine(true, true);
$i    = 1;

do {
  $sample[$i] = $line;
  $i++;
} while (($i <= 5) && ($line = $csv->readLine(true, true)));

$smarty = new CSmartyDP();
$smarty->assign('uid', $uid);
$smarty->assign('sample', $sample);
$smarty->display('inc_import_kerberos_identifiers');
