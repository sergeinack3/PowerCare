{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  {{if $can->admin}}
    <tr>
      <th>{{mb_label object=$operation field="anesth_id"}}</th>
      <td colspan="2">
        {{mb_field object=$operation field="anesth_id" style="width: 15em" options=$anesthesistes onchange="DHE.operation.syncView(this);"}}
      </td>
    </tr>
  {{/if}}

  <tr>
    <th class="halfPane">{{mb_label object=$operation field="type_anesth"}}</th>
    <td>
      <select name="type_anesth" style="width: 15em;" onchange="DHE.operation.syncView(this);">
        <option value="">&mdash; Anesthésie</option>
        {{foreach from=$types_anesth item=type_anesth}}
          {{if $type_anesth->actif || $operation->type_anesth == $type_anesth->_id}}
            <option value="{{$type_anesth->_id}}" {{if $operation->type_anesth == $type_anesth->_id}}selected{{/if}}>
              {{$type_anesth->name}} {{if !$type_anesth->actif && $operation->type_anesth == $type_anesth->_id}}(Obsolète){{/if}}
            </option>
          {{/if}}
        {{/foreach}}
      </select>
    </td>
  </tr>

  <tr>
    <th>{{mb_label object=$operation field="ASA"}}</th>
    <td>{{mb_field object=$operation field="ASA" emptyLabel="Choose" onchange="DHE.operation.syncView(this, 'ASA' + \$V(this));"}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$operation field="position_id"}}</th>
    <td>{{mb_field object=$operation field="position_id" emptyLabel="Choose" onchange="DHE.operation.syncView(this);"}}</td>
  </tr>
</table>