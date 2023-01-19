<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbPath;
use Ox\Core\CMbString;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$user = CMediusers::get();
$function_name = $user->loadRefFunction()->text;

$tempdir = CAppUI::getTmpPath("cabinet_backup_$user->_id");
CMbPath::forceDir($tempdir);

$files = array();

// Programme de consult
$parameters = array(
  "m"           => "cabinet",
  "dialog"      => "offline_programme_consult",
  "_aio"        => 1,
  "function_id" => $user->function_id,
);
$title = CMbString::removeAccents(CAppUI::tr("mod-dPcabinet-tab-offline_programme_consult"));
$filename = "$tempdir/$title.html";
$content = CApp::fetchQuery($parameters);
file_put_contents($filename, $content);
$files[] = $filename;

// Vue journalière hors ligne avec résumé patient
$parameters = array(
  "m"           => "cabinet",
  "dialog"      => "vw_offline_consult_patients",
  "_aio"        => 1,
  "function_id" => $user->function_id,
  "date"        => CMbDT::date(),
);

// Aujourd'hui
$title = CMbString::removeAccents(CAppUI::tr("mod-dPcabinet-tab-vw_offline_consult_patients")." - ".CAppUI::tr("Today"));
$filename = "$tempdir/$title.html";
$content = CApp::fetchQuery($parameters);
file_put_contents($filename, $content);
$files[] = $filename;

// Demain
$parameters["date"] = CMbDT::date("+1 DAY");
$title = CMbString::removeAccents(CAppUI::tr("mod-dPcabinet-tab-vw_offline_consult_patients")." - ".CAppUI::tr("Tomorrow"));
$filename = "$tempdir/$title.html";
$content = CApp::fetchQuery($parameters);
file_put_contents($filename, $content);
$files[] = $filename;

// Make archive
$zipname = "$tempdir/archive.zip";
$archive = new ZipArchive();
$archive->open($zipname, ZipArchive::CREATE);
foreach ($files as $_file) {
  $_filename = basename($_file);
  $archive->addFile($_file, $_filename);
}
$archive->close();

$output_name = sprintf("Sauvegarde du %s - %s.zip", CMbDT::date(), strtr($function_name, './\\"\'', ''));

// Output
ob_end_clean();

header('Content-Type: application/zip');
header(sprintf('Content-Disposition: attachment; filename="%s"', $output_name));
readfile($zipname);

// Cleanup
foreach ($files as $_file) {
  unlink($_file);
}
unlink($zipname);
rmdir($tempdir);

CApp::rip();
