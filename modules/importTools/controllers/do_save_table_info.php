<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Import\ImportTools\CImportTools;

CCanDo::checkAdmin();

$dsn        = CValue::post("dsn");
$table_name = CValue::post("table");
$type       = CValue::post("type");
$value      = CValue::post("value");

if (CImportTools::checkDSN($dsn)) {
  CAppUI::stepAjax('mod-importTools-no-std', UI_MSG_ERROR);
}

$info = CImportTools::getDatabaseStructure($dsn);

/** @var DOMDocument $dom */
$dom               = $info["description"];
$dom->formatOutput = true;

/** @var DOMXPath $xpath */
$xpath = $dom->_xpath;

/** @var DOMElement $table */
$table = $xpath->query("//tables/table[@name='$table_name']")->item(0);

if (!$table) {
  $tables = $xpath->query('//tables')->item(0);

  $table = $dom->createElement('table');
  $table->setAttribute('name', $table_name);
  $table->setAttribute('display', 'yes');

  $tables->appendChild($table);
}

$table->setAttribute($type, utf8_encode($value));

$dom->save($info["description_file"]);

CAppUI::stepAjax("Fichier de description sauvegardé", UI_MSG_OK);

CApp::rip();
