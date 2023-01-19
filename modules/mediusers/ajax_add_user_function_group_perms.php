<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Color selector
 */
CCanDo::checkAdmin();

$mediuser = new CMediusers();
$mediusers = $mediuser->loadGroupList();
foreach ($mediusers as $mediuser) {
  $mediuser->insFunctionPermission();
  $mediuser->insGroupPermission();
}

CAppUI::stepAjax(count($mediusers)." utilisateurs vérifiés");
