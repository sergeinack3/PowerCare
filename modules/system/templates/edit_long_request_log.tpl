{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    getForm('Edit-Log').report.focus();
  })
</script>

<form name="Edit-Log" method="post">

  {{mb_class object=$log}}
  {{mb_key   object=$log}}

  <table class="main form">
    <tr>
      <th>{{mb_label object=$log field=datetime_start}}</th>
      <td>{{$log->datetime_start}}</td>

      <th>{{mb_label object=$log field=_module}}</th>
      <td>{{mb_value object=$log field=_module}}</td>

      <th>{{mb_label object=$log field=user_id}}</th>
      <td>
        {{if $log->isPublic()}}
          PUBLIC
        {{else}}
          {{mb_value object=$log field=user_id}}
        {{/if}}
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$log field=datetime_end}}</th>
      <td>{{$log->datetime_end}}</td>

      <th>{{mb_label object=$log field=_action}}</th>
      <td>{{mb_value object=$log field=_action}}</td>

      <th>{{mb_label object=$log field=server_addr}}</th>
      <td>{{mb_value object=$log field=server_addr}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$log field=duration}}</th>
      <td>{{mb_value object=$log field=duration}}</td>

      <th></th>
      <td></td>

      <th>{{mb_label object=$log field=session_id}}</th>
      <td>
        {{if $log->_ref_session && $log->_ref_session->_id}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$log->_ref_session->_guid}}');">
          {{$log->session_id}}
        </span>
        {{/if}}
      </td>
    </tr>

    <tr>
      <th class="title" colspan="2" style="width: 33%;">{{mb_label object=$log field=query_params_get}}</th>
      <th class="title" colspan="2" style="width: 33%;">{{mb_label object=$log field=query_params_post}}</th>
      <th class="title" colspan="2" style="width: 33%;">{{mb_label object=$log field=session_data}}</th>
    </tr>
    <tr>
      <td colspan="2">
        <div style="height: 500px; overflow-y: auto">
          {{mb_value object=$log field=_query_params_get export=true}}
        </div>
      </td>

      <td colspan="2">
        <div style="height: 500px; overflow-y: auto">
          {{mb_value object=$log field=_query_params_post export=true}}
        </div>
      </td>

      <td colspan="2">
        <div style="height: 500px; overflow-y: auto">
          {{mb_value object=$log field=_session_data export=true}}
        </div>
      </td>

    </tr>

    <tr>
      <th class="title" colspan="6">{{mb_label object=$log field=query_performance}}</th>
    </tr>

    <tr>
      <td colspan="6" style="text-align: center;">
        {{mb_include style=mediboard_ext template=performance performance=$log->_query_performance long_request=1}}
      </td>
    </tr>

    <tr>
      <td class="button" colspan="6">
        <button name="report" type="button" class="search" onclick="LongRequestLog.showReport();">
          {{tr}}Report{{/tr}}
        </button>

        <button name="delete" type="button" class="trash" onclick="LongRequestLog.confirmDeletion(this.form);">
          {{tr}}Delete{{/tr}}
        </button>

        <a class="button search" href="{{$log->_link}}" target="_blank">{{tr}}Hyperlink{{/tr}}</a>
      </td>
    </tr>
  </table>
</form>
