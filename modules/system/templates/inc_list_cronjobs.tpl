{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=cronjob ajax=true}}
{{mb_include module=system template=inc_pagination total=$total_exchanges current=$page_cronjob change_page='CronJob.changePageList' jumper='15' step=15}}

<table class="tbl">
  <tr>
    <th class="title" colspan="7">{{tr}}CCronJob{{/tr}}</th>
    <th class="title" colspan="7" style="width: 50%">Execution</th>
  </tr>
  <tr>
    <th>{{mb_title class="CCronJob" field="active"}}</th>
    <th>{{mb_title class="CCronJob" field="name"}}</th>
    <th>{{mb_title class="CCronJob" field="description"}}</th>
    <th>{{mb_title class="CCronJob" field="params"}}</th>
    <th>{{mb_title class="CCronJob" field="token_id"}}</th>
    <th>{{mb_title class="CCronJob" field="execution"}}</th>
    <th>{{mb_title class="CCronJob" field="servers_address"}}</th>
    <th class="narrow" style="text-align: center;">
      <i class="fas fa-circle-notch fa-lg" title="{{tr}}CCronJob-title-_lasts_executions{{/tr}}"></i>
    </th>
    <th>n</th>
    <th>n+1</th>
    <th>n+2</th>
    <th>n+3</th>
    <th>n+4</th>
  </tr>

    {{foreach from=$cronjobs item=_cronjob}}
      <tr>
        <td class="narrow {{if !$_cronjob->active}}opacity-100{{/if}}">
          <form name="editactive_{{$_cronjob->_id}}" method="post" action="?"
                onsubmit="return onSubmitFormAjax(this, CronJob.ChangeActive(this))">
              {{mb_class object=$_cronjob}}
              {{mb_key object=$_cronjob}}
              {{mb_field object=$_cronjob field="active" canNull=true onchange="this.form.onsubmit()"}}
          </form>
        </td>
        <td class="narrow {{if !$_cronjob->active}}opacity-30{{/if}}">
          <button class="edit notext compact" type="button"
                  onclick="CronJob.edit('{{$_cronjob->_id}}')">{{tr}}Modify{{/tr}}</button>
            {{mb_value object=$_cronjob field="name"}}
        </td>

        <td class="text compact {{if !$_cronjob->active}}opacity-30{{/if}}"
            style="text-overflow: ellipsis; max-width: 200px; overflow: hidden;"
            title="{{mb_value object=$_cronjob field="description" no_paragraph="true"}}">{{mb_value object=$_cronjob field="description"}}</td>
        <td
          class="text compact {{if !$_cronjob->active}}opacity-30{{/if}}">{{mb_value object=$_cronjob field="params"}}</td>
        <td class="text compact {{if !$_cronjob->active}}opacity-30{{/if}}">
            {{if $_cronjob->_token}}
              <span
                onmouseover="ObjectTooltip.createEx(this, '{{$_cronjob->_token->_guid}}');">{{$_cronjob->_token->label}}</span>
            {{/if}}
        </td>
        <td class="{{if !$_cronjob->active}}opacity-30{{/if}}"
            style="font-family: monospace">{{mb_value object=$_cronjob field="execution"}}</td>
        <td class="{{if !$_cronjob->active}}opacity-30{{/if}}">
            {{if $_cronjob->servers_address}}
                {{mb_value object=$_cronjob field="servers_address"}}
            {{else}}
                {{tr}}All{{/tr}}
            {{/if}}
        </td>

        <td class="{{if !$_cronjob->active}}opacity-30{{/if}}">
            {{math assign=div equation="x+y" x=$_cronjob->_lasts_status.ok y=$_cronjob->_lasts_status.ko}}
            {{if $div != 0}}
                {{math assign=ratio equation="(x*100)/y" x=$_cronjob->_lasts_status.ok y=$div}}
              <script>
                Main.add(function () {
                  ProgressMeter.init('cronjob-execution-{{$_cronjob->_id}}', '{{$ratio}}');
                });
              </script>
              <div id="cronjob-execution-{{$_cronjob->_id}}" style="width: 20px; height: 20px;"
                   title="{{$ratio}} % ({{$_cronjob->_lasts_status.ok}} / {{$div}})">
              </div>
            {{/if}}
        </td>

          {{foreach from=$_cronjob->_next_datetime item=_next_datetime}}
            <td class="{{if !$_cronjob->active}}opacity-30{{/if}}" style="text-align: right">
                {{if $_next_datetime|iso_date == $dnow}}
                    {{$_next_datetime|date_format:$conf.time}}
                {{else}}
                    {{$_next_datetime|date_format:$conf.datetime}}
                {{/if}}
            </td>
              {{foreachelse}}
            <td class="narrow"></td>
            <td class="narrow"></td>
            <td class="narrow"></td>
            <td class="narrow"></td>
            <td class="narrow"></td>
          {{/foreach}}
      </tr>
        {{foreachelse}}
      <tr>
        <td class="empty" colspan="11">{{tr}}CCronJob.none{{/tr}}</td>
      </tr>
    {{/foreach}}

</table>


