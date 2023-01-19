{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=source_to_view_sender ajax=true}}

<form name="Edit-{{$sender->_guid}}" action="?m={{$m}}" method="post" onsubmit="return ViewSender.onSubmit(this);">
    {{mb_class object=$sender}}
    {{mb_key   object=$sender}}
  <input type="hidden" name="del" value="0"/>

  <table class="form">

      {{mb_include template=inc_form_table_header object=$sender}}
    
    <tr>
      <th class="narrow">{{mb_label object=$sender field=name}}</th>
      <td>{{mb_field object=$sender field=name}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$sender field=active}}</th>
      <td>{{mb_field object=$sender field=active}}</td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$sender field=description}}</th>
      <td>{{mb_field object=$sender field=description}}</td>
    </tr>
    
    <tr>
      <th>
          {{mb_label object=$sender field=params}}
        <br/>
        <button class="add" type="button" onclick="ViewSender.urlToParams(this);">URL</button>
      </th>
      <td>{{mb_field object=$sender field=params}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$sender field=multipart}}</th>
      <td>{{mb_field object=$sender field=multipart}}</td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$sender field=period}}</th>
      <td>{{mb_field object=$sender field=period}}</td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$sender field=offset}}</th>
      <td>{{mb_field object=$sender field=offset}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$sender field=every}}</th>
      <td>{{mb_field object=$sender field=every}}</td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$sender field=max_archives}}</th>
      <td>{{mb_field object=$sender field=max_archives}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$sender field=day}}</th>
      <td>{{mb_field object=$sender field=day emptyLabel="CViewSender.day.every"}}</td>
    </tr>

      {{if $sender->_id}}
        <tr>
          <th>{{mb_label object=$sender field=last_size}}</th>
          <td>{{$sender->last_size|decabinary}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$sender field=last_duration}}</th>
          <td>{{$sender->last_duration|string_format:"%.3f"}}s</td>
        </tr>
        <tr>
          <th>{{mb_label object=$sender field=last_datetime}}</th>
          <td>
          <span title="{{$sender->last_datetime}}">
            {{$sender->last_datetime|date_format:$conf.datetime}}
          </span>
          </td>
        </tr>
        <tr>
          <th>{{mb_label object=$sender field=last_status}}</th>
          <td>
              {{if $sender->last_status}}
                  {{tr}}CViewSender-last_status.{{$sender->last_status}}{{/tr}}
              {{/if}}
          </td>
        </tr>
      {{/if}}
    
    <tr>
      <td class="button" colspan="2">
          {{if $sender->_id}}
            <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
            <button class="duplicate" type="submit" onclick="ViewSender.duplicate(this.form);">
                {{tr}}Duplicate{{/tr}}
            </button>
            <button class="trash" type="button" onclick="ViewSender.confirmDeletion(this.form);">
                {{tr}}Delete{{/tr}}
            </button>
            <br/>
            <button class="search" type="button" onclick="ViewSender.show('{{$sender->_id}}');">
                {{tr}}Show{{/tr}}
            </button>
              {{if $sender->active}}
                  {{if $sender->_ref_senders_source}}
                    <button class="send" type="button" onclick="ViewSender.productAndSend('{{$sender->_id}}');">
                        {{tr}}CViewSender-action-Product and send{{/tr}}
                    </button>
                  {{else}}
                    <button class="add me-secondary" type="button" onclick="ViewSender.openSenderSourceLink('{{$sender->_id}}');">
                        {{tr}}CViewSender-action-Link to source{{/tr}}
                    </button>
                  {{/if}}
              {{/if}}
          {{else}}
            <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
          {{/if}}
      </td>
    </tr>
  
  </table>

</form>
