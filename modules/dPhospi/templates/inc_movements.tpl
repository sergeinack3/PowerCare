{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th colspan="6" class="title">{{$sejour->_view}} </th>
  </tr>
  <tr>
    <th></th>
    <th class="category">{{mb_title object=$movement field=movement_type}}</th>
    <th class="category">{{mb_title object=$movement field=original_trigger_code}}</th>
    <th class="category">{{mb_title object=$movement field=start_of_movement}}</th>
    <th class="category">{{mb_title object=$movement field=affectation_id}}</th>
    <th class="category">{{mb_title object=$movement field=last_update}}</th>
  </tr>
  {{foreach from=$movements item=_movement}}
    {{assign var=affectation value=$_movement->_ref_affectation}}
    <tr {{if $_movement->cancel}}class="hatching"{{/if}}>
      <td>
        <code onmouseover="ObjectTooltip.createEx(this,'{{$_movement->_guid}}', 'identifiers')">{{$_movement->_view}}</code>
      </td>
      <td>{{mb_value object=$_movement field=movement_type}}</td>
      <td><code>{{mb_value object=$_movement field=original_trigger_code}}</code></td>
      <td>
        <label title='{{mb_value object=$_movement field="start_of_movement" format=relative}}'>
          {{mb_include module=system template=inc_interval_datetime from=$_movement->start_of_movement to=$_movement->start_of_movement}}
        </label>
      </td>
      <td>
        {{mb_include module=system template=inc_vw_mbobject object=$affectation}}
      </td>
      <td>
        <label title='{{mb_value object=$_movement field="last_update"}}'>
          {{mb_value object=$_movement field="last_update" format=relative}}
        </label>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="6" class="empty">{{tr}}CMovement.none
      {{/tr}}</th>
    </tr>
  {{/foreach}}
</table>