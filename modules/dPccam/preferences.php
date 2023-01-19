<?php
/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Préférences par Module
use Ox\Mediboard\System\CPreferences;

CPreferences::$modules["dPccam"] = array (
  "new_search_ccam",
  "multiple_select_ccam",
  "user_executant",
  'actes_comp_supp_favoris',
  'precode_modificateur_7',
  'precode_modificateur_J',
  'spread_modifiers',
  'default_qualif_depense',
  'preselected_filters_ngap_sejours',
  'use_ccam_acts',
  'enabled_majoration_F'
);
