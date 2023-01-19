{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th>{{mb_title class=CProductMovement field=datetime}}</th>
    <th>{{mb_title class=CProductMovement field=account}}</th>
    <th>{{mb_title class=CProductMovement field=origin_class}}</th>
    <th>{{mb_title class=CProductMovement field=origin_id}}</th>
    <th>{{mb_title class=CProductMovement field=amount}}</th>
    <th>{{mb_title class=CProductMovement field=object_id}}</th>
  </tr>

  {{foreach from=$movements item=_movement}}
    <tr>
      <td>{{mb_value object=$_movement field=datetime}}</td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, null, 'accountingCode', {code:'{{$_movement->account}}'})">
          {{mb_value object=$_movement field=_account}}
        </span>
      </td>
      <td>{{mb_value object=$_movement field=origin_class}}</td>
      <td>{{mb_value object=$_movement field=origin_id tooltip=1}}</td>
      <td>{{mb_value object=$_movement field=amount}}</td>
      <td>{{mb_value object=$_movement field=object_id tooltip=1}}</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="6">{{tr}}CProductMovement.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>