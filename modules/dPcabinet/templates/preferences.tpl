{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include template=inc_pref spec=bool var=AUTOADDSIGN}}
{{mb_include template=inc_pref spec=enum var=MODCONSULT values="0|1"}}
{{mb_include template=inc_pref spec=bool var=dPcabinet_show_program}}
{{mb_include template=inc_pref spec=enum var=DossierCabinet values="dPcabinet|dPpatients"}}
{{mb_include template=inc_pref spec=bool var=viewWeeklyConsultCalendar}}
{{mb_include template=inc_pref spec=enum var=simpleCabinet values="0|1"}}
{{mb_include template=inc_pref spec=enum var=ccam_consultation values="0|1"}}
{{mb_include template=inc_pref spec=enum var=view_traitement values="0|1"}}
{{mb_include template=inc_pref spec=bool var=autoCloseConsult}}
{{mb_include template=inc_pref spec=bool var=resumeCompta}}
{{mb_include template=inc_pref spec=bool var=showDatesAntecedents}}
{{mb_include template=inc_pref spec=bool var=displayDocsConsult}}
{{mb_include template=inc_pref spec=bool var=choosePatientAfterDate}}
{{mb_include template=inc_pref spec=bool var=empty_form_atcd}}
{{mb_include template=inc_pref spec=str var=order_mode_grille readonly=true}}
{{mb_include template=inc_pref spec=bool var=create_dossier_anesth}}
{{mb_include template=inc_pref spec=bool var=displayPremedConsult}}
{{mb_include template=inc_pref spec=bool var=displayResultsConsult}}
{{mb_include template=inc_pref spec=bool var=viewFunctionPrats}}
{{mb_include template=inc_pref spec=bool var=viewAutreResult}}
{{mb_include template=inc_pref spec=bool var=use_acte_date_now}}
{{mb_include template=inc_pref spec=bool var=multi_popups_resume}}
{{mb_include template=inc_pref spec=enum var=dPcabinet_displayFirstTab values="AntTrait|Examens"}}
{{mb_include template=inc_pref spec=bool var=show_replication_duplicate}}
{{mb_include template=inc_pref spec=enum var=dPcabinet_offline_mode_frequency values="0|24|48|168"}}
{{mb_include template=inc_pref spec=enum var=context_print_futurs_rdv values="prat|cabinet"}}
{{mb_include template=inc_pref spec=bool var=show_text_complet}}
{{mb_include template=inc_pref spec=bool var=ant_trai_grid_list_mode}}

<tr><th class="category" colspan="6">{{tr}}CPlageConsult-planning{{/tr}}</th></tr>
{{mb_include template=inc_pref spec=bool var=allow_plage_holiday}}
{{mb_include template=inc_pref spec=bool var=show_plage_holiday}}
{{mb_include template=inc_pref spec=bool var=new_semainier}}
{{mb_include template=inc_pref spec=enum var=height_calendar values="2000|4000|6000" value_locale_prefix="height_calendar."}}
{{mb_include template=inc_pref spec=bool var=showIntervPlanning}}
{{mb_include template=inc_pref spec=enum var=AFFCONSULT values="0|1"}}
{{mb_include template=inc_pref spec=enum var=DefaultPeriod values="day|week|month|weekly" value_locale_prefix="Period."}}
{{*{{mb_include template=inc_pref spec=enum var=search_free_slot values="0|1|2|3|4|5|6|7|8|9|10"}}*}}
{{mb_include template=inc_pref spec=num var=search_free_slot}}
{{mb_include template=inc_pref spec=str var=see_plages_consult_libelle readonly=true}}

<tr><th class="category" colspan="6">{{tr}}CConsultation-Multiple consultation{{/tr}}</th></tr>
{{mb_include template=inc_pref spec=enum var=NbConsultMultiple values="2|3|4|5|6|7|8|9|10|11|12|20|30"}}
{{mb_include template=inc_pref spec=enum var=today_ref_consult_multiple values="0|1"}}

<tr><th class="category" colspan="6">{{tr}}CConsultation-Making personalized appointments{{/tr}}</th></tr>
{{mb_include template=inc_pref spec=bool var=take_consult_for_chirurgien}}
{{mb_include template=inc_pref spec=bool var=take_consult_for_anesthesiste}}
{{mb_include template=inc_pref spec=bool var=take_consult_for_medecin}}
{{mb_include template=inc_pref spec=bool var=take_consult_for_infirmiere}}
{{mb_include template=inc_pref spec=bool var=take_consult_for_reeducateur}}
{{mb_include template=inc_pref spec=bool var=take_consult_for_sage_femme}}
{{mb_include template=inc_pref spec=bool var=take_consult_for_dentiste}}
{{mb_include template=inc_pref spec=bool var=take_consult_for_dieteticien}}
{{mb_include template=inc_pref spec=bool var=take_consult_for_assistante_sociale}}

<tr><th class="category" colspan="6">{{tr}}mod-dPcabinet-tab-vw_evenements_rappel{{/tr}}</th></tr>
{{mb_include template=inc_pref spec=num var=event_remember_date_filter values="1|2|3|4|5|6|7|8|9|10|11|12"}}
