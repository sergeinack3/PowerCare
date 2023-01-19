{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $plages_with_interv|@count}}
  {{mb_script module=dPplanningOp script=operation ajax=true}}

  <div class="small-warning">
    Attention, il existe
    {{if $plages_with_interv|@count > 1}}
      {{$plages_with_interv|@count}} plages qui contiennent des interventions à replanifier.
    {{else}}
      {{$plages_with_interv|@count}} plage qui contient des interventions à replanifier.
    {{/if}}
  </div>
{{/if}}

<table class="tbl">
  <tr>
    <th class="category" colspan="3">
      Plages vides qui seront supprimées
    </th>
  </tr>
  {{foreach from=$plages_to_delete item=_plage}}
    <tr>
      <td colspan="3">{{$_plage}}</td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="3">
        {{tr}}CPlageOp.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
  {{if $plages_to_edit|@count}}
    <tr>
      <th class="category" colspan="2">
        Plages vides qui seront modifiées
      </th>
    </tr>
    {{foreach from=$plages_to_edit item=_plage}}
      <tr>
        <td colspan="3">{{$_plage}}</td>
      </tr>
    {{/foreach}}
  {{/if}}
  {{if $plages_with_interv|@count}}
    <tr>
      <th class="category" colspan="3">
        Plages à replanifier
      </th>
    </tr>
    {{foreach from=$plages_with_interv item=_plage}}
      <tr>
        <td rowspan="{{$_plage->_ref_operations|@count}}">{{$_plage}}</td>
        {{foreach from=$_plage->_ref_operations item=_operation}}
          <td>
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_guid}}');">{{$_operation}}</span>
          </td>
          <td class="narrow">
            <button type="button" class="edit notext" onclick="Operation.editModal('{{$_operation->_id}}', '{{$_operation->plageop_id}}', Blocage.refreshPlageToDelete.curry(getForm('editBlocage')));">{{tr}}Edit{{/tr}}</button>
          </td>
          </tr><tr>
        {{/foreach}}
      </tr>
    {{/foreach}}
  {{/if}}
</table>
