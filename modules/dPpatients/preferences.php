<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Préférences par Module
use Ox\Mediboard\System\CPreferences;

CPreferences::$modules["dPpatients"] = array(
  "VitaleVisionDir",
  "LogicielLectureVitale",
  "vCardExport",
  "medecin_cps_pref",
  "patient_recherche_avancee_par_defaut",
  "sort_atc_by_date",
  'update_patient_from_vitale_behavior',
  'new_date_naissance_selector',
  'constantes_show_comments_tooltip',
  'constantes_show_view_tableau',
  "display_all_docs",
  'constants_table_orientation',
  "check_establishment_grid_mode",
  'dPpatients_show_forms_resume',
  "vue_globale_importance",
  "vue_globale_cats",
  "vue_globale_docs_prat",
  "vue_globale_docs_func",
  "vue_globale_display_all_forms",
  "see_statut_patient",
  "alert_bmr_bhre",
  "hide_diff_func_atcd",
);
