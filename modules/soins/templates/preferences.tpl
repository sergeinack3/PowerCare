{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include template=inc_pref spec=enum var=vue_sejours values="standard|global"}}
{{mb_include template=inc_pref spec=str  var=default_services_id readonly=1}}
{{mb_include template=inc_pref spec=bool var=use_current_day}}
{{mb_include template=inc_pref spec=bool var=check_show_const_transmission}}
{{mb_include template=inc_pref spec=bool var=check_show_diet}}
{{mb_include template=inc_pref spec=bool var=check_show_macrocible}}
{{mb_include template=inc_pref spec=bool var=show_categorie_pancarte}}
{{mb_include template=inc_pref spec=enum var=type_view_demande_particuliere values="last_macro|trans_low|trans_hight|macro_low|macro_hight"}}
{{mb_include template=inc_pref spec=enum var=preselect_me_care_folder values="0|1|2|3"}}
{{mb_include template=inc_pref spec=bool var=detail_atcd_alle}}
{{mb_include template=inc_pref spec=bool var=show_bedroom_empty}}
{{mb_include template=inc_pref spec=bool var=show_last_macrocible}}