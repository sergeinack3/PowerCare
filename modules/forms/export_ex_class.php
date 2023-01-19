<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CValue;
use Ox\Mediboard\Forms\CExClassExport;

$ex_class_id = CValue::get("ex_class_id");

$export = new CExClassExport($ex_class_id);
$export->stream();
