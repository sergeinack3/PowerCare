{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include template=inc_pref spec=bool var=suivisalleAutonome}}
{{mb_include template=inc_pref spec=bool var=startAutoRefreshAtStartup}}
{{mb_include template=inc_pref spec=bool var=bloc_display_duration_intervention}}
{{mb_include template=inc_pref spec=enum var=view_planning_bloc values='vertical|timeline|horizontal'}}
{{mb_include template=inc_pref spec=bool var=planning_bloc_show_cancelled_operations}}
{{mb_include template=inc_pref spec=enum var=planning_bloc_period_1 values='0|1|2|3|4|5|6|7|8|9|10|11|12|13|14|15|16|17|18|19|20|21|22|23' use_locale=false}}
{{mb_include template=inc_pref spec=enum var=planning_bloc_period_2 values='0|1|2|3|4|5|6|7|8|9|10|11|12|13|14|15|16|17|18|19|20|21|22|23' use_locale=false}}
{{mb_include template=inc_pref spec=enum var=planning_bloc_period_3 values='0|1|2|3|4|5|6|7|8|9|10|11|12|13|14|15|16|17|18|19|20|21|22|23' use_locale=false}}
{{mb_include template=inc_pref spec=enum var=planning_bloc_period_4 values='0|1|2|3|4|5|6|7|8|9|10|11|12|13|14|15|16|17|18|19|20|21|22|23' use_locale=false}}
{{mb_include template=inc_pref spec=bool var=auto_entree_bloc_on_pat_select}}
