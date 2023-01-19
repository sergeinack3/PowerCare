<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\Stock\CProductCategoryXMLImport;

CCanDo::checkAdmin();

CApp::setTimeLimit(600);

$file     = CValue::read($_FILES, "import");
$filename = $file["tmp_name"];

$importer = new CProductCategoryXMLImport($filename);
$importer->import(array(), array());
