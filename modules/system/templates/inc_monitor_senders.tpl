{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  displayHelp = function (type) {
    var url = new Url('system', 'vw_senders_monitoring_help');
    url.addParam('type', type);
    url.requestModal();
  }
</script>

<table class="tbl">
  <tr>
    <th class="title" colspan="6">Production</th>
    <th class="title" colspan="8">Envoi</th>
  <tr>
    <th>{{mb_title class=CViewSender field=name}}</th>
    <th>{{mb_title class=CViewSender field=last_duration}}</th>
    <th>{{mb_title class=CViewSender field=last_size}}</th>
    <th>{{mb_title class=CViewSender field=last_datetime}}</th>
    <th>
      {{mb_title class=CViewSender field=last_status}}
      <button type="button" class="notext help" onclick="displayHelp('sender')"></button>
    </th>
    <th>
      {{mb_title class=CViewSender field=_last_age}} /
      {{mb_title class=CViewSender field=period}}
    </th>

    <th>{{mb_title class=CSourceToViewSender field=source_id}}</th>
    <th>{{mb_title class=CSourceToViewSender field=last_duration}}</th>
    <th>{{mb_title class=CSourceToViewSender field=last_size}}</th>
    <th>
      {{mb_title class=CSourceToViewSender field=last_status}}
      <button type="button" class="notext help" onclick="displayHelp('source')"></button>
    </th>
    <th>
      {{mb_title class=CSourceToViewSender field=last_count}} / 
      {{mb_title class=CViewSender field=max_archives}}
    </th>
    <th>
      {{mb_title class=CSourceToViewSender field=_last_age}} / 
      {{mb_title class=CViewSender field=period}}
    </th>
  </tr>
  
	{{foreach from=$senders item=_sender}}
  <tbody class="hoverable">
	  <tr>
	    {{assign var=count_sources value=$_sender->_ref_senders_source|@count}}
	
	    <td rowspan="{{$count_sources}}">
        {{mb_value object=$_sender field=name}}
      </td>
      
      <td rowspan="{{$count_sources}}" style="text-align: right;">
        {{$_sender->last_duration|string_format:"%.3f"}}s
      </td>

      <td rowspan="{{$count_sources}}" style="text-align: right;">
        <span title="{{$_sender->last_size}}">
          {{$_sender->last_size|decabinary}}
        </span>
      </td>

      {{assign var=class value=error}}
      {{if $_sender->last_status == 'producted'}}
        {{assign var=class value=ok}}
      {{/if}}


      {{assign var=last value=$_sender->last_datetime}}
      {{if $class == 'error' && $_sender->last_error_datetime}}
        {{assign var=last value=$_sender->last_error_datetime}}
      {{/if}}

      <td rowspan="{{$count_sources}}">
        <span title="{{$last}}">
          {{$last|date_format:$conf.datetime}}
        </span>
      </td>

      <td rowspan="{{$count_sources}}" class="{{$class}}">
        {{if $_sender->last_status}}
          {{tr}}CViewSender-last_status.{{$_sender->last_status}}{{/tr}}

          {{if $_sender->last_http_code}}
            / {{$_sender->last_http_code}}
          {{/if}}
        {{/if}}
      </td>

      {{assign var=class value=ok}}
      {{if $_sender->_last_age > $_sender->_full_period}}
        {{assign var=class value=warning}}
      {{/if}}
      {{if $_sender->_last_age > 2 * $_sender->_full_period}}
        {{assign var=class value=error}}
      {{/if}}
      <td class="{{$class}}" rowspan="{{$count_sources}}">
        <span title="{{mb_value object=$_sender field=last_datetime}}">
          {{mb_value object=$_sender field=_last_age}}
        </span> /
        {{mb_value object=$_sender field=_full_period}}mn
      </td>

	    {{foreach from=$_sender->_ref_senders_source item=_sender_source name=sender_source}}
            {{assign var=sender_source value=$_sender_source->_ref_sender_source}}
            {{assign var=source value=$sender_source->_ref_source}}

            <td {{if !$sender_source->actif}}class="error"{{/if}}>
              {{if $source->role != $conf.instance_role}}
                <i class="fas fa-exclamation-triangle" style="color: goldenrod;"
                   title="{{tr var1=$source->role var2=$conf.instance_role}}CViewSenderSource-msg-View sender source incompatible %s with the instance role %s{{/tr}}"></i>
              {{/if}}

              {{if $source->role == "prod"}}
                <strong style="color: red" title="{{tr}}CViewSenderSource_role.prod{{/tr}}">{{tr}}CViewSenderSource_role.prod-court{{/tr}}</strong>
              {{else}}
                <span style="color: green" title="{{tr}}CViewSenderSource_role.qualif{{/tr}}">{{tr}}CViewSenderSource_role.qualif-court{{/tr}}</span>
              {{/if}}

              {{mb_value object=$_sender_source field=source_id tooltip=true}}
            </td>

            {{if $sender_source->_ref_source->role != $conf.instance_role}}
              <td colspan="5" class="hatching empty">
                {{tr var1=$source->role var2=$conf.instance_role}}CViewSenderSource-msg-View sender source incompatible %s with the instance role %s{{/tr}}
              </td>
            {{else}}
              <td style="text-align: right;">
                {{$_sender_source->last_duration|string_format:"%.3f"}}s
              </td>

              {{assign var=class value=ok}}
              {{if $_sender_source->last_size < 1000 && $_sender_source->last_size != $_sender->last_size}}
                {{assign var=class value=error}}
              {{/if}}

              <td class="{{$class}}"  style="text-align: right;">
                <span title="{{$_sender_source->last_size}}">
                  {{$_sender_source->last_size|decabinary}}
                </span>
              </td>

              {{assign var=class value=ok}}
              {{assign var=colspan value="1"}}
              {{if $_sender_source->last_status != "checked"}}
                {{assign var=class value=error}}
              {{/if}}
              {{if !$_sender_source->last_status}}
                {{assign var=colspan value="3"}}
                {{assign var=class value=warning}}
              {{/if}}
              <td colspan="{{$colspan}}" class="{{$class}}">
                {{mb_value object=$_sender_source field=last_status}}
              </td>

              {{if $_sender_source->last_status}}
                {{assign var=class value=ok}}
                {{if $_sender_source->last_count < $_sender->max_archives}}
                  {{assign var=class value=warning}}
                {{/if}}
                {{if $_sender_source->last_count > $_sender->max_archives}}
                  {{assign var=class value=error}}
                {{/if}}
                <td class="{{$class}}">
                  {{mb_value object=$_sender_source field=last_count}} /
                  {{mb_value object=$_sender field=max_archives}}
                </td>

                {{assign var=class value=ok}}
                {{if $_sender_source->_last_age > $_sender->_full_period}}
                  {{assign var=class value=warning}}
                {{/if}}
                {{if $_sender_source->_last_age > 2 * $_sender->_full_period}}
                  {{assign var=class value=error}}
                {{/if}}
                <td class="{{$class}}">
                  <span title="{{mb_value object=$_sender_source field=last_datetime}}">
                    {{mb_value object=$_sender_source field=_last_age}}
                  </span> /
                  {{mb_value object=$_sender field=_full_period}}mn
                </td>
              {{/if}}
            {{/if}}

            {{if !$smarty.foreach.sender_source.last}}</tr><tr>{{/if}}
	    {{foreachelse}}
          <tr><td class="empty" colspan="6">{{tr}}CViewSender.none{{/tr}}</td></tr>
        {{/foreach}}
	  </tr>
  </tbody>

	{{foreachelse}}
  <tr>
    <td colspan="7" class="empty">
      {{tr}}CViewSender.noneactive{{/tr}}
    </td>
  </tr>
	{{/foreach}}
  
</table>
