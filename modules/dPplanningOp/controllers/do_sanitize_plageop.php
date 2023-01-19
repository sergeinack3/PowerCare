<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSQLDataSource;

CCanDo::checkAdmin();

$ds = CSQLDataSource::get("std");

$query = "UPDATE `operations`
            LEFT JOIN plagesop ON plagesop.plageop_id = `operations`.`plageop_id`
            SET `operations`.`date` = plagesop.date
            WHERE `operations`.plageop_id IS NOT NULL";

if (!$ds->exec($query)) {
  return CAppUI::stepAjax("Sanitize-failed", UI_MSG_ERROR, $ds->error());
}

CAppUI::stepAjax("Sanitize-ok");

CApp::rip();