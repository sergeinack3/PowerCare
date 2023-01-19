<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Préférences par Module
use Ox\Mediboard\System\CPreferences;

CPreferences::$modules["dPbloc"] = array (
  "allowed_check_entry_bloc",
  'drag_and_drop_horizontal_planning',
  'bloc_planning_visibility'
);
