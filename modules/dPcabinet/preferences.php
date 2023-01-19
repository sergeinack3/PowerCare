<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Préférences par Module
use Ox\Mediboard\System\CPreferences;

CPreferences::$modules["dPcabinet"] = array (
  "AFFCONSULT",
  "MODCONSULT",
  "AUTOADDSIGN",
  "DefaultPeriod",
  "viewWeeklyConsultCalendar",
  "DossierCabinet",
  "simpleCabinet",
  "ccam_consultation",
  "view_traitement",
  "autoCloseConsult",
  "resumeCompta",
  "showDatesAntecedents",
  "dPcabinet_show_program",
  "pratOnlyForConsult",
  "displayDocsConsult",
  "displayPremedConsult",
  "displayResultsConsult",
  "choosePatientAfterDate",
  "viewFunctionPrats",
  "viewAutreResult",
  "empty_form_atcd",
  "new_semainier",
  "height_calendar",
  "order_mode_grille",
  "create_dossier_anesth",
  "showIntervPlanning",
  "NbConsultMultiple",
  "use_acte_date_now",
  "multi_popups_resume",
  "allow_plage_holiday",
  "show_plage_holiday",
  "today_ref_consult_multiple",
  "dPcabinet_displayFirstTab",
  "show_replication_duplicate",
  "dPcabinet_offline_mode_frequency",
  "context_print_futurs_rdv",
  "show_text_complet",
  "search_free_slot",
  "see_plages_consult_libelle",
  "ant_trai_grid_list_mode",

  // take consultation for :
  "take_consult_for_chirurgien",    // 1
  "take_consult_for_anesthesiste",  // 1
  "take_consult_for_medecin",       // 1
  "take_consult_for_infirmiere",    // le reste non
  "take_consult_for_reeducateur",
  "take_consult_for_sage_femme",
  "take_consult_for_dentiste",
  "take_consult_for_dieteticien",
  "take_consult_for_assistante_sociale",

  "event_remember_date_filter",
);
