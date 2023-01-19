<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Pr�f�rences par Module
use Ox\Mediboard\System\CPreferences;

CPreferences::$modules["dPhospi"] = array(
  "ccam_sejour",
  "services_ids_hospi",
  "prestation_id_hospi"
);
