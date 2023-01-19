{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="diagnostic_position_form" method="post" onsubmit="return false;">
  <ul class="list">
    {{if $result->result_id}}
      {{if $result->symptom || $result->syndrome || $result->disease || $result->certified_diagnosis || $result->unpathological}}
        {{assign var=positions value='Ox\Mediboard\Cim10\Drc\CDRCConsultationResult'|static:'diagnosis_positions'}}
        {{foreach from=$positions item=_position}}
          {{if $result->$_position}}
            <li>
              <label><input type="radio" name="position" value="{{$_position}}">{{tr}}CDRCConsultationResult-{{$_position}}{{/tr}}</label>
            </li>
          {{/if}}
        {{/foreach}}
      {{else}}
        <li style="font-style: italic; color: #aaa">
          {{tr}}CDRCConsultationResult-positions.none{{/tr}}
        </li>
      {{/if}}
    {{else}}
      <li style="font-style: italic; color: #aaa">
        {{tr}}CDRCConsultationResult-msg-no_selected_result{{/tr}}
      </li>
    {{/if}}
  </ul>
</form>