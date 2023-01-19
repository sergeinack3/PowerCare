<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\Module\CModule;

global $can;
$can = CModule::getCanDo('dPsalleOp');

CAppUI::requireModuleFile("dPbloc", "vw_timeline_salles");
