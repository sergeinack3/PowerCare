<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CCSVImportFilesCategory;
use Ox\Mediboard\Files\CFilesCategory;

CCanDo::checkEdit();

CView::enforceSlave();

CApp::setTimeLimit(600);
CApp::setMemoryLimit("1024M");

$files_category = new CFilesCategory();
$files_categories = $files_category->loadList(null, 'group_id');

$file = tempnam(rtrim(CAppUI::conf('root_dir'), '/\\') . "/tmp", 'export-patients');

if ($files_categories) {
  $header = CCSVImportFilesCategory::$headers;

  $fp  = fopen($file, 'w+');
  $csv = new CCSVFile($fp);
  $csv->setColumnNames($header);
  $csv->writeLine($header);

  foreach ($files_categories as $_category) {
    $line = array();
    foreach ($header as $_field) {
      if ($_field == 'etablissement') {
        $etab = '';
        if ($_category->group_id) {
          $group = CGroups::get($_category->group_id);
          $etab = $group->text;
        }

        $line[] = $etab;
        continue;
      }

      $line[] = $_category->$_field;
    }

    $csv->writeLine($line);
  }

  $csv->close();
}

// Direct download of the file
// BEGIN extra headers to resolve IE caching bug (JRP 9 Feb 2003)
// [http://bugs.php.net/bug.php?id=16173]
header("Pragma: ");
header("Cache-Control: ");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");  //HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
// END extra headers to resolve IE caching bug

header("MIME-Version: 1.0");

header("Content-disposition: attachment; filename=\"CategoriesFichiers.csv\";");
header("Content-type: text/csv");
header("Content-length: " . filesize($file));

readfile($file);
unlink($file);