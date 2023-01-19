<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Préférences par Module
use Ox\Mediboard\System\CPreferences;

CPreferences::$modules["dPsalleOp"] = array (
  "chir_modif_timing",
  "allow_edit_timing_sortie_salle",
  "show_dh_salle_op",
  "show_all_gestes_perop"
);
