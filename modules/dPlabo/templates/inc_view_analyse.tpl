{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr id="PrescriptionItem-{{$_item->_id}}">
    <td>
      <a href="#{{$_item->_class}}-{{$_item->_id}}" onclick="Prescription.Examen.edit({{$_item->_id}})">
        {{$curr_examen->_view}}
      </a>
      <form name="delPrescriptionExamen-{{$_item->_id}}" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
        <input type="hidden" name="m" value="dPlabo" />
        <input type="hidden" name="dosql" value="do_prescription_examen_aed" />
        <input type="hidden" name="prescription_labo_id" value="{{$prescription->_id}}" />
        <input type="hidden" name="prescription_labo_examen_id" value="{{$_item->_id}}" />
        <input type="hidden" name="del" value="1" />
        {{if $prescription->_status < $prescription|const:"VEROUILLEE"}}
        <button type="button" class="trash notext" title="{{tr}}Delete{{/tr}}" onclick="Prescription.Examen.del(this.form)" >
          {{tr}}Delete{{/tr}}
        </button>
        {{/if}}
        <button type="button" class="search notext" title="{{tr}}View{{/tr}}" onclick="ObjectTooltip.createEx(this, '{{$curr_examen->_guid}}', 'objectCompleteView')">
          {{tr}}View{{/tr}}
        </button>
      </form>
    </td>
    {{if $curr_examen->type == "num" || $curr_examen->type == "float"}}
    <td>{{$curr_examen->unite}}</td>
    <td>{{$curr_examen->min}} &ndash; {{$curr_examen->max}}</td>
    {{else}}
    <td colspan="2">{{mb_value object=$curr_examen field="type"}}</td>
    {{/if}}
    <td>
      {{if !$curr_examen->_external}}
      {{if $_item->date}}
        {{assign var=msgClass value=""}}
        {{if ($curr_examen->type == "num"  || $curr_examen->type == "float") && $_item->resultat}}
          {{mb_ternary var=msgClass test=$_item->_hors_limite value=warning other=message}}
        {{/if}}
        
        <div class="{{$msgClass}}">
          {{mb_value object=$_item field=resultat}}
        </div>
      {{else}}
        <em>En attente</em>
      {{/if}}
      {{else}}
        <em>Analyse externe</em>
      {{/if}}
    </td>
    {{if $curr_examen->_external}}
    <td>
      <input type="radio" name="radio-{{$_item->_id}}" disabled="disabled" />
    </td>
    <td>
      <input type="radio" name="radio-{{$_item->_id}}" disabled="disabled" checked="checked" />
    </td>
    {{else}}
    <td>
      <input type="radio" name="radio-{{$_item->_id}}" disabled="disabled" checked="checked" />
    </td>
    <td>
      <input type="radio" name="radio-{{$_item->_id}}" name="" disabled="disabled" />
    </td>
    {{/if}}
  </tr>
