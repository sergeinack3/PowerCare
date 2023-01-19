<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/////////////////
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\CUserLog;

CApp::rip(); // Pour eviter les mauvaises surprises, supprimer cette ligne pour utiliser le script
/////////////////

CCanDo::checkAdmin();

$query = <<<SQL
SELECT user_log_id, object_id
FROM user_log
LEFT JOIN sejour ON sejour.sejour_id = user_log.object_id
WHERE
  sejour.mode_entree != '7' AND
  user_log.object_class = 'CSejour' AND
  user_log.extra     LIKE '%"entree_reelle":""%' AND
  user_log.extra     LIKE '%"etablissement_entree_id":%'AND
  user_log.extra NOT LIKE '%"etablissement_entree_id":""%'
  LIMIT 50
SQL;

$ds = CSQLDataSource::get("std");
$logs = $ds->loadList($query);

foreach ($logs as $_log) {
  $user_log = new CUserLog();
  $user_log->load($_log["user_log_id"]);

  $values = $user_log->getOldValues();

  /** @var CSejour $sejour */
  $sejour = $user_log->loadTargetObject();
  $sejour->etablissement_entree_id = $values["etablissement_entree_id"];
  $sejour->mode_entree = "7"; // transfert

  if ($msg = $sejour->store()) {
    CAppUI::setMsg($msg, UI_MSG_WARNING);
  }
  else {
    CAppUI::setMsg("Etablissement d'entrée rétabli", UI_MSG_OK);
  }
}

echo CAppUI::getMsg();
