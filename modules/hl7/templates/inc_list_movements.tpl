{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination total=$total_movements current=$page change_page='changePage' jumper=25}}

<table class="tbl">
  <tr>
    <th> </th>
    <th class="category">{{mb_title object=$movement field=movement_type}}</th>
    <th class="category">{{mb_title object=$movement field=original_trigger_code}}</th>
    <th class="category">{{mb_title object=$movement field=start_of_movement}}</th>
    <th class="category">{{mb_title object=$movement field=last_update}}</th>
    <th class="category">{{mb_title object=$movement field=sejour_id}}</th>
    <th class="category">{{mb_title object=$movement field=affectation_id}}</th>
  </tr>
  {{foreach from=$movements item=_movement}}

    <tr {{if $_movement->cancel}}class="hatching"{{/if}}>
      <td>
        <code onmouseover="ObjectTooltip.createEx(this,'{{$_movement->_guid}}', 'identifiers')">{{$_movement->_view}}</code>
      </td>
      <td>{{mb_value object=$_movement field=movement_type}}</td>
      <td><code>{{mb_value object=$_movement field=original_trigger_code}}</code></td>
      <td>
        <label title='{{mb_value object=$_movement field="start_of_movement"}}'>
          {{mb_value object=$_movement field="start_of_movement" format=relative}}
        </label>
      </td>
      <td>
        <label title='{{mb_value object=$_movement field="last_update"}}'>
          {{mb_value object=$_movement field="last_update" format=relative}}
        </label>
      </td>
      {{assign var=sejour value=$_movement->_ref_sejour}}
      <td class="text">
        {{mb_include module=system template=inc_vw_mbobject object=$sejour}}
      </td>

      {{assign var=affectation value=$_movement->_ref_affectation}}
      <td class="text">
        {{mb_include module=system template=inc_vw_mbobject object=$affectation}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="6" class="empty">{{tr}}CMovement.none{{/tr}}</th>
    </tr>
  {{/foreach}}
</table>