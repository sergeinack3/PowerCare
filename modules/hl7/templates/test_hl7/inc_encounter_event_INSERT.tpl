{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=hl7 template=inc_banner_event_hl7}}

{{assign var="formName" value="test_hl7_event$event"}}

<form method="post" name="{{$formName}}" onsubmit="return onSubmitFormAjax(this)">
  <input type="hidden" name="m" value="hl7">
  <input type="hidden" name="dosql" value="do_encounter_event">
  <input type="hidden" name="event" value="{{$event}}">
  <input type="hidden" name="event_type" value="{{$event_type}}">
  <input type="hidden" name="patient_id" value="{{$patient->_id}}">
  <input type="hidden" name="callback" value="Control.Modal.close">
  <table class="form">
    {{foreach from=$patient->_ref_sejours item=_sejour name=loop_sejour}}
      <tr>
        <td>
          <label>
            <input type="radio" name="sejour_id" value="{{$_sejour->_id}}">
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
              {{$_sejour->_view}} [{{if $_sejour->_NDA}}{{$_sejour->_NDA}}{{else}}-{{/if}}]
            </span>
          </label>
        </td>
        <td>
          {{mb_value object=$_sejour field="type"}}
        </td>
        {{if $smarty.foreach.loop_sejour.first}}
          <td rowspan="{{$patient->_ref_sejours|@count}}" style="vertical-align: middle">
            {{mb_field class="CSejour" field="type"}}
          </td>
        {{/if}}
      </tr>
    {{foreachelse}}
      <tr><td><span class="empty">{{tr}}CSejour.none{{/tr}}</span></td></tr>
    {{/foreach}}
    <tr>
      <td class="button" colspan="3"><button type="submit" class="save">{{tr}}Save{{/tr}}</button></td>
    </tr>
  </table>
</form>