<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Admin\CUser;

CCanDo::checkAdmin();

$user = new CUser();

// Find duplicates
$query = "SELECT `user_username`, COUNT(*) AS `user_count`
  FROM `users`
  GROUP BY `user_username`
  ORDER BY `user_count` DESC";
$ds = $user->getDS();
$user_counts = $ds->loadHashList($query);
$siblings = array();

foreach ($user_counts as $user_name => $user_count) {
  // Only duplicates
  if ($user_count == 1) {
    break;
  }

  $user->user_username = $user_name;
  $siblings[$user_name] = $user->loadMatchingList();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("siblings", $siblings);

$smarty->display("check_siblings.tpl");
