{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=type_op value="planifiee"}}

{{if $operation->_id && !$operation->plageop_id}}
  {{assign var=type_op value="hors_plage"}}
{{/if}}

{{assign var=salle value=$operation->_ref_salle}}

<input type="hidden" name="plageop_id" value="{{$operation->plageop_id}}" />
<input type="hidden" name="date" value="{{$operation->date}}" onchange="DHE.operation.syncDate(this);"/>
<input type="hidden" name="_place_after_interv_id" value="" />
<input type="hidden" name="_horaire_voulu" value="{{$operation->_horaire_voulu}}" />
<input type="hidden" name="rank" value="{{$operation->rank}}" />

<table class="form">
  <tr>
    <th class="halfPane">{{mb_label object=$operation field=_time_op}}</th>
    <td>
      {{if !"dPplanningOp COperation only_admin_can_change_time_op"|gconf ||
            @$modules.dPplanningOp->_can->admin || $app->_ref_user->isAdmin()}}
        <input type="hidden" name="_time_op" value="{{$operation->_time_op}}" class="time notNull" onchange="DHE.operation.syncView(this);" />
      {{else}}
        {{mb_value object=$operation field=_time_op}}
      {{/if}}
    </td>
  </tr>
  <tr>
    <th>{{mb_label object=$operation field=cote}}</th>
    <td>{{mb_field object=$operation field=cote onchange="DHE.operation.syncView(this);"}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$operation field=urgence}}</th>
    <td>{{mb_field object=$operation field=urgence onchange="DHE.operation.syncViewFlag(this);"}}</td>
  </tr>
  <tr>
    <th>Type</th>
    <td>
      <select name="type_op" onchange="DHE.operation.togglePlanification(this.value);">
        <option value="planifiee"  {{if $type_op == "planifiee" }}selected{{/if}}>Planifiée</option>
        <option value="hors_plage" {{if $type_op == "hors_plage"}}selected{{/if}}>Hors plage</option>
      </select>
    </td>
  </tr>

  <tbody id="operation_planifiee" {{if $type_op != "planifiee"}}style="display: none;"{{/if}}>
    <tr>
      <th>
        {{mb_label object=$operation field=plageop_id}}
      </th>
      <td>
        <input type="text" name="_date_planifiee" readonly
               value="{{$operation->_datetime|date_format:$conf.datetime}}" style="width: 15em" />
        <button type="button" class="search notext" onclick="PlageOpSelector.init();">Choisir une date</button>

        {{if $salle && $salle->_id}}
          <br />
          en {{$salle}}
        {{/if}}
      </td>
    </tr>
  </tbody>

  <tbody id="operation_hors_plage" {{if $type_op != "hors_plage"}}style="display: none;"{{/if}}>
    <tr>
      <th>
        {{mb_label object=$operation field=date}}
      </th>
      <td>
        <input type="hidden" name="_date_hors_plage" {{if $type_op == "hors_plage"}}class="notNull"{{/if}} readonly value="{{$operation->date}}"
               onchange="$V(this.form.date, this.value);" />
        à
        <input type="text" class="time" name="_time_urgence_da" readonly value="{{$operation->_time_urgence|date_format:"%H:%M"}}" />
        <input name="_time_urgence" class="{{if $type_op == "hors_plage"}}notNull{{/if}} time" type="hidden" value="{{$operation->_time_urgence}}"
               onchange="DHE.operation.syncView(this);" />
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$operation field=salle_id}}</th>
      <td>
        <select style="width: 15em;" name="salle_id" onchange="DHE.operation.syncView(this);">
          <option value="">&mdash; {{tr}}CSalle.select{{/tr}}</option>
          {{foreach from=$blocs item=_bloc}}
            <optgroup label="{{$_bloc}}">
              {{foreach from=$_bloc->_ref_salles item=_salle}}
                <option value="{{$_salle->_id}}" {{if $_salle->_id == $operation->salle_id}}selected{{/if}}>
                  {{$_salle}}
                </option>
                {{foreachelse}}
                <option value="" disabled>{{tr}}CSalle.none{{/tr}}</option>
              {{/foreach}}
            </optgroup>
          {{/foreach}}
        </select>
      </td>
    </tr>
  </tbody>
</table>