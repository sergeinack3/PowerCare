<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;

global $current_m;

if (!$current_m) {
  $current_m = "ssr";
}

CAppUI::requireModuleFile("dPadmissions", "vw_sejours_validation");
