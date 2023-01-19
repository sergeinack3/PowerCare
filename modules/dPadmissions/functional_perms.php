<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Permissions fonctionnels par Module
use Ox\Mediboard\System\CPreferences;

CPreferences::$modules['dPadmissions'] = array (
  'show_dh_admissions'
);