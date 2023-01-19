{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include template=inc_pref spec=bool var=vCardExport}}
{{mb_include template=inc_pref spec=str  var=medecin_cps_pref}}
{{mb_include template=inc_pref spec=bool var=patient_recherche_avancee_par_defaut}}
{{mb_include template=inc_pref spec=bool var=sort_atc_by_date}}
{{mb_include template=inc_pref spec=bool var=new_date_naissance_selector}}
{{mb_include template=inc_pref spec=bool var=constantes_show_comments_tooltip}}
{{mb_include template=inc_pref spec=bool var=constantes_show_view_tableau}}
{{mb_include template=inc_pref spec=enum var=display_all_docs values="icon|list"}}
{{mb_include template=inc_pref spec=bool var=vue_globale_display_all_forms}}
{{mb_include template=inc_pref spec=enum var=constants_table_orientation values='vertical|horizontal'}}
{{mb_include template=inc_pref spec=bool var=check_establishment_grid_mode}}
{{mb_include module=admin template=inc_pref spec=bool var=dPpatients_show_forms_resume}}
{{mb_include template=inc_pref spec=bool var=see_statut_patient}}
{{mb_include template=inc_pref spec=bool var=alert_bmr_bhre}}
{{mb_include template=inc_pref spec=bool var=hide_diff_func_atcd}}

<tr>
  <th colspan="5" class="category">Carte Vitale</th>
</tr>

{{mb_include template=inc_pref spec=str  var=VitaleVisionDir}}
{{mb_ternary var=enum_vitale test="mbHost"|module_active value="none|vitaleVision|mbHost" other="none|vitaleVision"}}
{{mb_include template=inc_pref spec=enum var=LogicielLectureVitale values=$enum_vitale}}
{{mb_include template=inc_pref spec=enum var=update_patient_from_vitale_behavior values="choice|never|always"}}
