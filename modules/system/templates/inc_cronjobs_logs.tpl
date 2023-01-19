{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination total=$nb_log current=$page_log change_page="CronJob.changePageLog" step=30}}

<script>
  displayErrors = function(request_uid) {
    let url = new Url('dPdeveloppement', 'view_logs');
    url.addParam('request_uid', request_uid);
    url.addParam('hide_filters', 1);
    url.requestModal('90%', '90%');
  }
</script>

<table class="tbl">
  <tr>
    <th>{{mb_title class=CCronJobLog field="status"}}</th>
    <th>{{mb_title class=CCronJobLog field="severity"}}</th>
    <th>{{mb_title class=CCronJobLog field="log"}}</th>
    <th>{{mb_title class=CCronJobLog field="cronjob_id"}}</th>
    <th>{{mb_title class=CCronJobLog field="server_address"}}</th>
    <th>{{mb_title class=CCronJobLog field="start_datetime"}}</th>
    <th>{{mb_title class=CCronJobLog field="end_datetime"}}</th>
    <th>{{mb_title class=CCronJobLog field="duration"}}</th>
    <th>{{mb_title class=CCronJobLog field="request_uid"}}</th>
  </tr>
  {{foreach from=$logs item=_log}}
    {{assign var=status value=$_log->status}}

    {{if $status != 'started' && $status != 'finished' && $status != 'error'}}
      {{assign var=status value='started'}}
      {{if $_log->status >= 200}}
        {{assign var=status value='finished'}}
      {{/if}}

      {{if $_log->status >= 400 || !$_log->status}}
        {{assign var=status value='error'}}
      {{/if}}
    {{/if}}

    <tr>
      <td class="narrow cron-status-{{$status}}">
        {{mb_value object=$_log field="status"}}
      </td>
      <td class="narrow cron-severity-{{$_log->severity}}">
        {{mb_value object=$_log field="severity"}}
      </td>
      <td>
        {{if $_log->log}}
          <pre>{{mb_value object=$_log field="log"}}</pre>
        {{/if}}
      </td>
      <td>
        {{if $_log->_ref_cronjob}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_log->_ref_cronjob->_guid}}');">{{$_log->_ref_cronjob->_view}}</span>
        {{/if}}
      </td>
      <td>{{mb_value object=$_log field="server_address"}}</td>
      <td>{{mb_value object=$_log field="start_datetime"}}</td>
      <td>{{mb_value object=$_log field="end_datetime"}}</td>
      <td>
        {{if $_log->duration}}
          {{$_log->duration|number_format:0:',':' '}} ms
        {{else}}
          {{mb_value object=$_log field="_duration"}}
        {{/if}}
      </td>
      <td>
          {{if $_log->request_uid}}
              <button class="warning notext" onclick="displayErrors('{{$_log->request_uid}}')"></button>
          {{/if}}
      </td>
    </tr>
  {{foreachelse}}
    <tr><td class="empty" colspan="8">{{tr}}CCronJobLog.none{{/tr}}</td></tr>
  {{/foreach}}
</table>
