<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Pr�f�rences par Module
use Ox\Mediboard\System\CPreferences;

CPreferences::$modules["dPurgences"] = array(
  "defaultRPUSort",
  "chooseSortRPU",
  "showMissingRPU",
  'stats_urgences_age_ranges',
);
