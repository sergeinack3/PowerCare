<?php

/**
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * dPboard
 */
// Préférences par Module
use Ox\Mediboard\System\CPreferences;

CPreferences::$modules["dPboard"] = [
    "show_all_docs",
    "alternative_display",
    "select_view",
    "nb_previous_days"
];
