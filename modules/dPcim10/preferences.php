<?php
/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Pr�f�rences par Module
use Ox\Mediboard\System\CPreferences;

CPreferences::$modules["dPcim10"] = array (
  "new_search_cim10",
  'cim10_search_favoris'
);
