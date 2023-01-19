<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Mediboard\System\CPreferences;

/**
 * dPbloc
 */
// Préférences par Module
CPreferences::$modules["dPbloc"] = array (
  "suivisalleAutonome",
  "startAutoRefreshAtStartup",
  "bloc_display_duration_intervention",
  "view_planning_bloc",
  "planning_bloc_period_1",
  "planning_bloc_period_2",
  "planning_bloc_period_3",
  "planning_bloc_period_4",
  "auto_entree_bloc_on_pat_select",
  'planning_bloc_show_cancelled_operations',
);
