<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;


CCanDo::checkRead();

$directory = CView::get('directory', 'str notNull');
$group_id  = CView::get('group_id', 'ref class|CGroups notNull');

CView::checkin();

$root_dir = CAppUI::gconf("importTools export root_path", $group_id);
$root_dir = rtrim($root_dir, "/\\");

$integrity_file = "$root_dir/$directory/export.integrity";
if (!is_file($integrity_file)) {
  CAppUI::commonError("$integrity_file does not exists");
}

$json_stats = file_get_contents($integrity_file);
$stats      = json_decode($json_stats, true);

$xml = array(
  "CSejour"       => $stats['CSejour'],
  "CConsultation" => $stats['CConsultation'],
  "COperation"    => $stats['COperation'],
  "CFile"         => $stats['CFile'],
  "CCompteRendu"  => $stats['CCompteRendu'],
);

$sql = $stats['SQL'];

$diffs = CMbArray::diffRecursive($sql, $xml);

$counts = array_fill_keys(array_keys($xml), 0);
foreach ($diffs as $_class => $_val) {
  $counts[$_class] = count($_val);
}

$smarty = new CSmartyDP();
$smarty->assign('diffs', $diffs);
$smarty->assign('counts', $counts);
$smarty->display("vw_compare_integrity");