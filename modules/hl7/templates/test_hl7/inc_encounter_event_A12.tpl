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
  <input type="hidden" name="patient_id" value="{{$patient->_id}}">
  <input type="hidden" name="callback" value="Control.Modal.close">
  <table class="form">
    {{foreach from=$patient->_ref_sejours item=_sejour}}
      <tr>
        <td>
          {{$_sejour->_view}} [{{if $_sejour->_NDA}}{{$_sejour->_NDA}}{{else}}-{{/if}}]
          <ul>
            {{foreach from=$_sejour->_ref_affectations item=_affectation}}
              <li style="list-style-type: none;">
                <label><input type="radio" name="affectation_id" value="{{$_affectation->_id}}">{{$_affectation}}</label>
              </li>
            {{/foreach}}
          </ul>
        </td>
      </tr>
    {{foreachelse}}
      <tr><td><span class="empty">{{tr}}CSejour.none{{/tr}}</span></td></tr>
    {{/foreach}}
    <tr>
      <td class="button" colspan="3"><button type="submit" class="trash">{{tr}}Delete{{/tr}}</button></td>
    </tr>
  </table>
</form>