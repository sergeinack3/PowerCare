{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "dPsalleOp COperation password_sortie"|gconf && $app->_ref_user->isAnesth()}}
  {{mb_include template=inc_pref spec=bool var=autosigne_sortie}}
{{/if}}
{{mb_include template=inc_pref spec=str var=default_salles_id readonly=1}}
{{mb_include template=inc_pref spec=bool var=check_all_interventions}}
{{mb_include template=inc_pref spec=bool var=pec_sspi_current_user}}
{{mb_include template=inc_pref spec=bool var=show_all_datas_surveillance_timeline}}
