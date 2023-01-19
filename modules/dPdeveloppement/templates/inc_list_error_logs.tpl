{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.setTabCount("error-tab", "{{$total}}");
  });

  confirmFormSubmit = function (message, form, args) {
    Modal.confirm($T(message, args), {
      onOK: function () {
        form.onsubmit();
      }
    })
  }

</script>

<style type="text/css">
  .error-logs pre {
    width: 100%;
    border: none;
    margin: 0;
    max-height: 220px;
  }

  .error-logs tr td {
    vertical-align: top;
    padding: 1px;
  }
</style>

<div style="float: left">
    {{if $list_ids}}
      <form name="delete-logs-db" action="" method="post" onsubmit="return onSubmitFormAjax(this, ErrorLogs.filterError)">
        <input type="hidden" name="m" value="developpement"/>
        <input type="hidden" name="dosql" value="do_error_log_multi_delete"/>
        <input type="hidden" name="log_ids" value="{{','|implode:$list_ids}}"/>
        <input type="hidden" name="is_elastic_log" value="{{$is_elastic_log}}"/>

        <button class="trash" type="button"
                onclick="confirmFormSubmit('ErrorLog-msg-Would you like to delete those %s errors logs?', this.form, {{$list_ids|@count}});">
            {{tr}}Delete{{/tr}}
        </button>
      </form>
    {{/if}}

  <form name="manage-logs-db" action="" method="post" onsubmit="return onSubmitFormAjax(this, ErrorLogs.filterError)">
    <input type="hidden" name="m" value="developpement"/>
    <input type="hidden" name="dosql" value="purgeErrorLog"/>
    <input type="hidden" name="is_elastic_log" value="{{$is_elastic_log}}"/>

    <button class="trash" type="button"
            onclick="confirmFormSubmit('ErrorLog-msg-Would you like to empty the error log?', this.form)">
        {{tr}}common-action-Purge{{/tr}}
    </button>
  </form>
</div>


{{if $count_error_log_whitelist > 0 }}
  <div style="float: left">
    <button type="button" onclick="ErrorLogs.listErrorLogWhitelist(this)">
        {{tr}}CErrorLog.error_log_whitelist_info{{/tr}} ({{$count_error_log_whitelist}})
      </a>
    </button>
  </div>
{{/if}}

{{mb_include module=system template=inc_pagination change_page="changePage" total=$total current=$start step=30}}

{{foreach from=$error_logs item=_log}}
  <div id="details-error-log-{{$_log->_id}}" style="display: none; width: 800px;">
    <table class="tbl">
      <tr>
        <th>{{tr}}What{{/tr}}</th>
        <th>{{tr}}When{{/tr}}</th>
        <th>{{tr}}Who{{/tr}}</th>
        <th>{{tr}}Where{{/tr}}</th>
        <th>{{tr}}Request{{/tr}}</th>
      </tr>

      <tr>
        <td class="text">
          <strong>
              {{$_log->text}}
          </strong>
        </td>

        <td>
            {{if $_log->_datetime_min && $_log->_datetime_max && $_log->_datetime_min !== $_log->_datetime_max}}
              <div title="{{$_log->_datetime_min}}">
                  {{mb_value object=$_log field=_datetime_min}}
                ({{mb_value object=$_log field=_datetime_min format=relative}})
              </div>
              <div title="{{$_log->_datetime_max}}">
                  {{mb_value object=$_log field=_datetime_max}}
                ({{mb_value object=$_log field=_datetime_max format=relative}})
              </div>
            {{else}}
              <div title="{{$_log->_datetime_max}}">
                  {{mb_value object=$_log field=datetime}}
                ({{mb_value object=$_log field=datetime format=relative}})
              </div>
            {{/if}}
        </td>
        <td>
            {{foreach from=$_log->_similar_user_ids item=_user_id name=output}}
                {{if $smarty.foreach.output.iteration < 4 || ($smarty.foreach.output.total == 4 && $smarty.foreach.output.last)}}
                  <div>
                      {{if $_user_id && array_key_exists($_user_id,$users)}}
                          {{$users.$_user_id}}
                      {{else}}
                        <em class="empty">{{tr}}None{{/tr}}</em>
                      {{/if}}
                  </div>
                {{elseif $smarty.foreach.output.last}}
                  <em>... {{$smarty.foreach.output.total}} users</em>
                {{/if}}
                {{foreachelse}}
              <div>
                  {{assign var=user_id value=$_log->user_id}}
                  {{if $user_id && array_key_exists($user_id,$users)}}
                      {{$users.$user_id}}
                  {{else}}
                    <em class="empty">{{tr}}None{{/tr}}</em>
                  {{/if}}
              </div>
            {{/foreach}}
        </td>

        <td>
            {{foreach from=$_log->_similar_server_ips item=_server_ip}}
              <div>
                  {{$_server_ip}}
              </div>
                {{foreachelse}}
              <div>
                  {{$_log->server_ip}}
              </div>
            {{/foreach}}
        </td>

        <td>
            <div>
              {{$_log->request_uid}}
            </div>
        </td>
      </tr>
    </table>


    <table class="tbl">
      <tr>
        <th>{{tr}}Call{{/tr}}</th>
        <th>{{tr}}File{{/tr}}</th>
        <th>{{tr}}Line{{/tr}}</th>
      </tr>
      <tr>
        <td style="width: 20%;"></td>
        <td>{{$_log->file_name|ide:$_log->line_number:$_log->file_name}}</td>
        <td class="narrow" style="text-align: right;">{{$_log->line_number}}</td>
      </tr>
        {{foreach from=$_log->_stacktrace_output item=_output name=output}}
          <tr>
            <td class="text">{{if "function"|array_key_exists:$_output}}{{$_output.function}}{{/if}}</td>
            <td
              class="text">{{if "file"|array_key_exists:$_output & "line"|array_key_exists:$_output }}{{$_output.file|ide:$_output.line:$_output.file}}{{/if}}</td>
            <td
              style="text-align: right;">{{if "file"|array_key_exists:$_output & "line"|array_key_exists:$_output }}{{$_output.file|ide:$_output.line:$_output.line}}{{/if}}</td>
          </tr>
        {{/foreach}}
    </table>

    <table class="tbl">
      <tr>
        <th style="width: 33%;">{{mb_title class=CErrorLog field=param_GET_id}}</th>
        <th style="width: 33%;">{{mb_title class=CErrorLog field=param_POST_id}}</th>
        <th style="width: 33%;">{{mb_title class=CErrorLog field=session_data_id}}</th>
      </tr>
      <tr>
        <td>
          <pre style="width: 250px; height: 200px;">{{$_log->_param_GET|@print_r:true}}</pre>
        </td>

        <td>
          <pre style="width: 250px; height: 200px;">{{$_log->_param_POST|@print_r:true}}</pre>
        </td>

        <td>
          <pre style="width: 250px; height: 200px;">{{$_log->_session_data|@print_r:true}}</pre>
        </td>
      </tr>

      <tr>
        <td class="button">
            {{if $applicationVersion.title}}
                {{$applicationVersion.title|nl2br}}
            {{/if}}
        </td>
        <td class="button">
          <button class="cancel" type="button" onclick="Control.Modal.close()">{{tr}}Close{{/tr}}</button>
        </td>
        <td class="button">
            {{if $_log->_url}}
              <a href="{{$_log->_url}}" class="button link" target="_blank">
                Lien
              </a>
            {{/if}}
        </td>
      </tr>
    </table>
  </div>
{{/foreach}}

<table class="main tbl error-logs">
  <tr>
    <th></th>
    <th>{{mb_title class=CErrorLog field=stacktrace_id}}</th>
  </tr>
    {{foreach from=$error_logs item=_log}}
      <tbody>
      <tr style="border-top: 2px solid #666;">
        <td class="narrow error-{{$_log->_category}}" rowspan="2" style=" line-height: 1.5;"
            title="{{mb_value object=$_log field=error_type}}">

          <div
            class="rank">{{if $_log->_similar_count == 0}}{{$_log->count|integer}}{{else}}{{$_log->_similar_count|integer}}{{/if}}</div>
          <form name="delete-error-log-{{$_log->_id}}" method="post"
                onsubmit="return onSubmitFormAjax(this, ErrorLogs.filterError)">
            <input type="hidden" name="m" value="developpement"/>
            <input type="hidden" name="dosql" value="do_error_log_multi_delete"/>
            <input type="hidden" name="is_elastic_log" value="{{$is_elastic_log}}"/>

              {{assign var=_log_ids value=","|implode:$_log->_similar_ids}}
              {{if $_log_ids === ""}}
            <input type="hidden" name="log_ids" value="{{$_log->_id}}"/>
            <button
              type="button"
              class="trash notext"
              onclick="confirmFormSubmit('ErrorLog-msg-Would you like to delete this error log?', this.form);"
            >
                {{else}}
              <input type="hidden" name="log_ids" value="{{$_log_ids}}"/>
              <button
                type="button"
                class="trash notext"
                onclick="confirmFormSubmit('ErrorLog-msg-Would you like to delete those %s errors logs?', this.form, {{$_log->_similar_ids|@count}});"
              >
                  {{/if}}

                  {{tr}}Delete{{/tr}}
              </button>
          </form>

            {{if in_array($_log->signature_hash, $whitelist_hash)}}
                {{assign var=class_wl value="remove"}}
                {{assign var=text_wl value="CErrorLog-whitelist-remove"}}
            {{else}}
                {{assign var=class_wl value="add"}}
                {{assign var=text_wl value="CErrorLog-whitelist-add"}}
            {{/if}}
          <button class="{{$class_wl}} notext" type="button"
                  onclick="ErrorLogs.toogleErrorLogWhitelist('{{$_log->_id}}'); this.disabled = true;">{{tr}}{{$text_wl}}{{/tr}}</button>

          <div>
              {{if $_log->_similar_server_ips|@count > 1}}
                  {{$_log->_similar_server_ips|@count}} servers
              {{else}}
                  {{mb_value object=$_log field=server_ip}}
              {{/if}}
          </div>

          <div>
              {{if $_log->_datetime_min && $_log->_datetime_max && $_log->_datetime_min !== $_log->_datetime_max}}
                  {{mb_value object=$_log field=_datetime_min}}
                <br/>
                  {{mb_value object=$_log field=_datetime_max}}
              {{else}}
                  {{mb_value object=$_log field=datetime}}
              {{/if}}
          </div>

          <div>
              {{if $_log->_similar_user_ids|@count > 1}}
                  {{$_log->_similar_user_ids|@count}} users
              {{else}}
                  {{mb_value object=$_log field=user_id tooltip=true}}
              {{/if}}
          </div>

        </td>

        <td class="text" style="{{if $class_wl === 'remove'}}opacity:0.5;{{/if}}">
          <button class="search" style="float:right" type="button"
                  onclick="Modal.open('details-error-log-{{$_log->_id}}')">
              {{tr}}Details{{/tr}}
          </button>

          <strong>{{$_log->text|truncate:200}}</strong>
          <table class="main tbl">
            <tr>
              <td style="width: 20%;"></td>
              <td>{{$_log->file_name|ide:$_log->line_number:$_log->file_name}}</td>
              <td class="narrow"
                  style="text-align: right;">{{$_log->file_name|ide:$_log->line_number:$_log->line_number}}</td>
            </tr>
              {{foreach from=$_log->_stacktrace_output item=_output name=output}}
                  {{if $smarty.foreach.output.iteration < 4 || ($smarty.foreach.output.total == 4 && $smarty.foreach.output.last)}}
                    <tr>
                      <td class="text">{{if "function"|array_key_exists:$_output}}{{$_output.function}}{{/if}}</td>
                      <td
                        class="text">{{if "file"|array_key_exists:$_output & "line"|array_key_exists:$_output }}{{$_output.file|ide:$_output.line:$_output.file}}{{/if}}</td>
                      <td
                        style="text-align: right;">{{if "file"|array_key_exists:$_output & "line"|array_key_exists:$_output }}{{$_output.file|ide:$_output.line:$_output.line}}{{/if}}</td>
                    </tr>
                  {{elseif $smarty.foreach.output.last}}
                    <tr>
                      <td colspan="3">...</td>
                    </tr>
                  {{/if}}
              {{/foreach}}
          </table>
        </td>
      </tr>

      </tbody>
        {{foreachelse}}
      <tr>
        <td class="empty" colspan="5">{{tr}}CErrorLog.none{{/tr}}</td>
      </tr>
    {{/foreach}}
</table>

{{mb_include module=system template=inc_pagination change_page="changePage" total=$total current=$start step=30}}
