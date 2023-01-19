{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="add-CPoseDispositifVasculaire" method="post" action="?" onsubmit="return onSubmitFormAjax(this, Control.Modal.close)">
  <input type="hidden" name="m" value="dPplanningOp" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="callback" value="PoseDispVasc.checkListCallback" />
  {{mb_class class=CPoseDispositifVasculaire}}
  {{mb_key object=$pose}}
  {{mb_field object=$pose hidden=true field=operation_id}}
  {{mb_field object=$pose hidden=true field=sejour_id}}
  
  <table class="main form">
    <tr>
      <th>{{mb_label object=$pose field=date}}</th>
      <td>{{mb_field object=$pose field=date form="add-CPoseDispositifVasculaire" register=true}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$pose field=urgence}}</th>
      <td>{{mb_field object=$pose field=urgence}}</td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$pose field=lieu}}</th>
      <td>{{mb_field object=$pose field=lieu}}</td>
    </tr>
      
    <tr>
      <th>{{mb_label object=$pose field=operateur_id}}</th>
      <td>
        <select name="operateur_id" class="{{$pose->_props.operateur_id}}" style="width: 12em;">
          <option value="" disabled="disabled" selected="selected">&mdash; {{tr}}CPoseDispositifVasculaire-operateur_id{{/tr}}</option>
          {{foreach from=$operateurs item=_operateur}}
            <option value="{{$_operateur->_id}}" {{if $app->user_id == $_operateur->_id || $pose->operateur_id == $_operateur->_id}} selected="selected" {{/if}}>
              {{$_operateur}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$pose field=encadrant_id}}</th>
      <td>
        <select name="encadrant_id" class="{{$pose->_props.encadrant_id}}" style="width: 12em;">
          <option value="" disabled="disabled" selected="selected">&mdash; {{tr}}CPoseDispositifVasculaire-encadrant_id-desc{{/tr}}</option>
          {{foreach from=$operateurs item=_operateur}}
            <option value="{{$_operateur->_id}}" {{if $pose->encadrant_id == $_operateur->_id}} selected="selected" {{/if}}>
              {{$_operateur}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$pose field=type_materiel}}</th>
      <td>{{mb_field object=$pose field=type_materiel emptyLabel="CPoseDispositifVasculaire-type_materiel"}}</td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$pose field=voie_abord_vasc}}</th>
      <td>{{mb_field object=$pose field=voie_abord_vasc}}</td>
    </tr>
    
    <tr>
      <td></td>
      <td>
        <button type="submit" class="submit">{{tr}}Save{{/tr}}</button>
        {{if $pose->_id}}
          <button class="trash" type="button"
                  onclick="confirmDeletion(this.form, {ajax: true, typeName:'', objName:'{{$pose->_view|smarty:nodefaults|JSAttribute}}'}, Control.Modal.close)">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>