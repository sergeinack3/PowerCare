{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="hl7" script="test_hl7" ajax=true}}

<table class="main layout">
  <tr>
    <td>
      <fieldset class="me-no-box-shadow">
        <legend>
          <select name="value_type_R01_name" onchange="TestHL7.refreshFormulaireORUR01(this);">
            <option>Choisissez le type de valeur du message ORU</option>
            {{foreach from=$values_type_oru item=_value_type key=_type_name}}
              <option value="{{$_type_name}}">{{$_type_name}}</option>
            {{/foreach}}
          </select>
        </legend>
      </fieldset>
    </td>
  </tr>
</table>

{{foreach from=$values_type_oru item=_value_type key=_type_name}}
  <div id="{{$_type_name}}" class="form-test-oru" style="display: none;">
    <form name="form_oru-{{$_type_name}}" method="get" onsubmit="return TestHL7.sendMessageORU(this);">
      <input type="hidden" name="value_type_R01" value="{{$_type_name}}"/>
      {{foreach from=$_value_type item=_type_input key=_name_parameter}}
          <input type="{{$_type_input}}" name="{{$_name_parameter}}" placeholder="{{$_name_parameter}}"/>
      {{/foreach}}
      <button type="submit" class="send">{{tr}}Send{{/tr}}</button>
    </form>
  </div>
{{/foreach}}

<div id="messageHL7ORU"></div>
