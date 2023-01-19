{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $result->result_id}}
  <table class="list" style="width: 100%;">
    <tr>
      <th>{{tr}}CDRCCriticalDiagnosis{{/tr}}</th>
      <th style="width: 50px;">{{tr}}CDRCCriticalDiagnosis-criticality{{/tr}}</th>
    </tr>
    {{foreach from=$result->_critical_diagnoses item=_diagnosis}}
      <tr>
        <td>
          {{$_diagnosis->libelle}}
        </td>
        <td style="width: 50px;">
          {{section name=criticality start=0 loop=$_diagnosis->group}}
            <i class="fa fa-lg fa-star" style="color: goldenrod;"></i>
          {{/section}}
        </td>
      </tr>
    {{foreachelse}}
      <tr>
        <td style="font-style: italic; color: #aaa">
          {{tr}}CDRCCriticalDiagnosis.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
  </table>
{{else}}
  <ul class="list">
    <li style="font-style: italic; color: #aaa">
      {{tr}}CDRCConsultationResult-msg-no_selected_result{{/tr}}
    </li>
  </ul>
{{/if}}