<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CFileParser;
use Ox\Core\CValue;
use Ox\Core\CView;

CCanDo::checkAdmin();

$file = CValue::files('formfile');


CView::checkin();

if (!$file) {
  CAppUI::stepAjax('CFile-not-exists', UI_MSG_ERROR, $file_path);
}

try {
  $parser = new CFileParser();
}
catch (Exception $e) {
  CAppUI::stepAjax($e->getMessage(), UI_MSG_ERROR);
}


foreach ($file['tmp_name'] as $key => $_file) {
  echo "<h3>" . $file["name"][$key] . " - " . $_file . "</h3>";
  dump($parser->getMetadata($_file));
  dump($parser->getContent($_file));
}
