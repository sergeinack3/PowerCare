<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CBarcodeParser;
use Ox\Core\CValue;

$barcode = CValue::get("barcode");

$parsed = CBarcodeParser::parse($barcode);

CApp::json($parsed);
