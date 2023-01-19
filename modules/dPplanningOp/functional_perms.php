<?php

/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Permissions fonctionnelles par Module
use Ox\Mediboard\System\CPreferences;

CPreferences::$modules["dPplanningOp"] = [
    "create_dhe_with_read_rights",
    "protocole_mandatory",
];
