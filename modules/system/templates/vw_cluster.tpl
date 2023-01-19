{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
</script>

<h2>Hearbeat</h2>

<table class="tbl">
  <tr>
    <th>{{mb_title class=CHeartbeat field=server_id}}</th>
    <th colspan="2">{{mb_title class=CHeartbeat field=ts}}</th>
    <th>{{mb_title class=CHeartbeat field=file}}</th>
    <th>{{mb_title class=CHeartbeat field=position}}</th>
    <th>{{mb_title class=CHeartbeat field=relay_master_log_file}}</th>
    <th>{{mb_title class=CHeartbeat field=exec_master_log_pos}}</th>
  </tr>

  {{foreach from=$heartbeats item=_heartbeat}}
  <tr>
    {{assign var=class value=$_heartbeat->_lag|threshold:0:ok:30:warning:300:error}}
    <td>{{mb_value object=$_heartbeat field=server_id}}</td>
    <td class="{{$class}}">{{mb_value object=$_heartbeat field=ts}}</td>
    <td class="{{$class}}">{{mb_value object=$_heartbeat field=_datetime format=relative}}</td>
    <td>{{mb_value object=$_heartbeat field=file}}</td>
    <td>{{mb_value object=$_heartbeat field=position}}</td>
    <td>{{mb_value object=$_heartbeat field=relay_master_log_file}}</td>
    <td>{{mb_value object=$_heartbeat field=exec_master_log_pos}}</td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty" colspan="10">{{tr}}CHeartbeat.none{{/tr}}</td>
  </tr>
  {{/foreach}}

</table>