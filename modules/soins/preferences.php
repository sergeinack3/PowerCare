<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Préférences par Module
use Ox\Mediboard\System\CPreferences;

CPreferences::$modules["soins"] = array(
  "vue_sejours",
  "default_services_id",
  "use_current_day",
  "check_show_const_transmission",
  "check_show_macrocible",
  "check_show_diet",
  "show_categorie_pancarte",
  "type_view_demande_particuliere",
  "preselect_me_care_folder",
  "detail_atcd_alle",
  "show_bedroom_empty",
  "show_last_macrocible"
);
