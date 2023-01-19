{{*
 * @package Mediboard\dPcabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=see_absence value=false}}

{{if !$see_absence}}
  {{assign var=antecedents_by_date value=$dossier_medical->_all_antecedents}}
{{else}}
  {{assign var=antecedents_by_date value=$atcd_absence}}
{{/if}}

{{foreach from=$antecedents_by_date item=_antecedent}}
  {{mb_include module=dPcabinet template=antecedents/inc_ant antecedent=$_antecedent}}
{{/foreach}}