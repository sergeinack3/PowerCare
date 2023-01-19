{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=edit_mode value=0}}

<tr {{if $is_alerte}}id="{{$_alerte->_guid}}"{{/if}}>
  <td>{{$_operation->_datetime|date_format:$conf.date}}</td>
  <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_chir}}</td>
  <td>{{mb_value object=$_operation->_ref_patient}}</td>
  <td>
    {{if $edit_mode}}
      <form name="removeFrm" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this)">
        <input type="hidden" name="m" value="dPplanningOp" />
        <input type="hidden" name="_id" value="{{$_operation->_id}}" />
        <input type="hidden" name="dosql" value="do_operation_aed" />
        <input type="hidden" name="del" value="0" />
        <select name="salle_id" onchange="this.form.onsubmit();">
          <option value="">Non défini</option>
          {{foreach from=$blocs item=_bloc}}
            <optgroup label="{{$_bloc->_view}}">
              {{foreach from=$_bloc->_ref_salles item=_salle}}
                <option value="{{$_salle->_id}}" {{if $_operation->salle_id == $_salle->_id}}selected{{/if}}>{{$_salle}}</option>
              {{/foreach}}
              </optgroup>
          {{/foreach}}
        </select>
      </form>
    {{else}}
      {{mb_value object=$_operation->_ref_salle}}
    {{/if}}
  </td>
  <td class="text">
    {{mb_include module=planningOp template=inc_vw_operation}}
    <br />
    {{if $is_alerte}}
    {{$_alerte->comments|nl2br}}
    <br />
    <form name="removeFrm" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this);">
      <input type="hidden" name="m" value="system" />
      <input type="hidden" name="dosql" value="do_alert_aed" />
      <input type="hidden" name="del" value="0" />
      <input type="hidden" name="alert_id" value="{{$_alerte->_id}}" />
      <input type="hidden" name="tag" value="{{$_alerte->tag}}" />
      <input type="hidden" name="level" value="{{$_alerte->level}}" />
      <input type="hidden" name="comments" value="{{$_alerte->comments}}" />
      <input type="hidden" name="handled" value="1" />
      <button type="button" class="tick" onclick="this.form.onsubmit(); $('{{$_alerte->_guid}}').hide();">Traité</button>
    </form>
    {{/if}}
  </td>
</tr>