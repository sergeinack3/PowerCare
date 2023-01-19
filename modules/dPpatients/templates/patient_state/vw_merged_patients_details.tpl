{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2 style="text-align: center;">
  {{$date|date_format:$conf.date}} &mdash; {{tr var1=$logs_count}}CPatientSate-msg-%d merged patients{{/tr}}
</h2>

<hr />

<div class="small-info">
  {{tr}}CPatientState-msg-Tags marked as trahs could be old patient IPP.{{/tr}}
</div>

<table class="main tbl">
  {{assign var=_count value=0}}
  {{foreach from=$logs key=_key item=_log}}
    {{math assign=_count equation='x + 1' x=$_count}}
    {{mb_include module=dPpatients template=patient_state/CPatientState_merged_view object=$_log}}
  {{/foreach}}
</table>
