<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Pr�f�rences par Module
use Ox\Mediboard\System\CPreferences;

CPreferences::$modules["ssr"] = array(
  "ssr_planning_dragndrop",
  "ssr_planification_duree",
  "ssr_planification_show_equipement",
);