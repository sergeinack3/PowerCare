{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>

  whitelistDeleteAll = function () {
    if (confirm("{{tr}}CErrorLog.delete_all_whitelist_ask{{/tr}}") === false) {
      return;
    }
    var url = new Url("developpement", "ajax_delete_error_log_whitelist");
    url.addParam("all", 1);
    url.requestUpdate("error-whitelist");
  };

  whitelistDelete = function (id) {
    var url = new Url("developpement", "ajax_delete_error_log_whitelist");
    url.addParam("id", id);
    url.requestUpdate("error-whitelist");
  };

</script>

<table width="99%">
  <tr>
    <td style="text-align: right; width:50%">
      <button class="trash" onclick="whitelistDeleteAll();">{{tr}}CErrorLog.delete_all_whitelist{{/tr}}</button>
    </td>
  </tr>
</table>

<table class="main tbl" id="error-whitelist">
  <tbody>
  <tr>
    <th class="title">{{tr}}Type{{/tr}}</th>
    <th class="title">{{tr}}Message{{/tr}}</th>
    <th class="title">{{tr}}file{{/tr}}</th>
    <th class="title">{{tr}}Counter{{/tr}}</th>
    <th class="title">{{tr}}User{{/tr}}</th>
    <th class="title">{{tr}}Date{{/tr}}</th>
    <th class="title">{{tr}}Delete{{/tr}}</th>
  </tr>
  {{foreach from=$list key=_id item=_item}}
    <tr title="Signature (hash) : {{$_item->hash}}">
      <td class="text">{{$_item->type}}</td>
      <td class="text">{{$_item->text}}</td>
      <td class="text">{{$_item->file_name}}:{{$_item->line_number}}</td>
      <td class="text">{{$_item->count}}</td>
      <td class="text"> {{mb_value object=$_item field=user_id tooltip=true}}</td>
      <td class="text">{{mb_value object=$_item field=datetime}}</td>
      <td class="button">
        <button class="trash notext" title="{{tr}}Delete{{/tr}}" onclick="whitelistDelete('{{$_id}}');"></button>
      </td>
    </tr>
  {{/foreach}}
</table>
