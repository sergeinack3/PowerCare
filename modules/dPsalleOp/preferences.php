<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Préférences par Module
use Ox\Mediboard\System\CPreferences;

CPreferences::$modules["dPsalleOp"] = array(
  "autosigne_sortie",
  "default_salles_id",
  "check_all_interventions",
  "pec_sspi_current_user",
  "show_all_datas_surveillance_timeline",
);
