{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h1>Plan horaire</h1>

<table class="tbl">
  <tr>
    <th colspan="2">
      {{mb_title class=CViewSender field=name}} /
      {{mb_title class=CViewSender field=description}}
    </th>
    <th colspan="2">
      {{mb_title class=CViewSender field=params}}
    </th>
    <th class="narrow" colspan="2">
      {{tr}}CViewSender-back-sources_link{{/tr}}
    </th>
    <th class="narrow">
      {{mb_title class=CViewSender field=period}}
    </th>
    <th class="narrow">
      {{mb_title class=CViewSender field=offset}}
    </th>
    <th class="narrow">
      {{mb_title class=CViewSender field=day}}
    </th>
    <th colspan="60">
      {{tr}}CViewSender-_hour_plan{{/tr}}:

      <span>
        <input name="plan_mode" id="plan_mode_production" type="radio" value="production"
               onclick="ViewSender.refreshList('production');" {{if $plan_mode == "production"}}checked="checked"{{/if}} />
        <label for="plan_mode_production">Production</label>
        <input name="plan_mode" id="plan_mode_sending" type="radio" value="sending"
               onclick="ViewSender.refreshList('sending');" {{if $plan_mode == "sending"}}checked="checked"{{/if}} />
        <label for="plan_mode_sending">Envoi</label>
      </span>
    </th>
  </tr>

  {{foreach from=$senders item=_sender name=senders}}
    <!-- Bilan horaire -->
    {{if $smarty.foreach.senders.iteration % 20 == 1 || $smarty.foreach.senders.last}}
      <tr style="height: 2px; border-top: 2px solid #888;"></tr>
      <tr>
        <td colspan="9" style="text-align: right;">
          <strong>Bilan horaire: {{$hour_total|percent}}</strong>
        </td>
        {{foreach from=$hour_sum key=min item=sum}}

          {{assign var=status value=""}}
          {{if $sum  > 0}}{{assign var=status value=ok     }}{{/if}}
          {{if $sum >= 1}}{{assign var=status value=warning}}{{/if}}
          {{if $sum >= 2}}{{assign var=status value=error  }}{{/if}}

          {{assign var=active value=""}}
          {{if $sum && $min == $minute}}{{assign var=active value=active}}{{/if}}

          <td class="hour-plan {{$status}} {{$active}}" title="{{$sum|percent}} @ {{$min}}" style="height: 2em;"></td>
        {{/foreach}}
      </tr>
    {{/if}}

    {{assign var=senders_source value=$_sender->_ref_senders_source}}
    <tr {{if !$_sender->active}} class="hatching" {{/if}}>
      <td class="narrow">
        <button class="edit notext" onclick="ViewSender.edit('{{$_sender->_id}}');">
          {{tr}}Edit{{/tr}}
        </button> 
      </td>
      <td class="text">
        <div {{if ($_sender->_active)}} style="font-weight: bold;"{{/if}}>
          {{mb_value object=$_sender field=name}}
        </div>
        <div class="compact">
          {{mb_value object=$_sender field=description}}
        </div>
        <div class="compact">
          {{$_sender->last_duration|string_format:"%.3f"}}s /
          {{$_sender->last_size|decabinary}}
        </div>
      </td>
      <td class="narrow">
        <script>
          ViewSender.senders['{{$_sender->_id}}'] = {{$_sender->_params|@json}};
        </script>
        <button class="search notext" onclick="ViewSender.show('{{$_sender->_id}}');">
          {{tr}}View{{/tr}}
        </button>
      </td>
      <td class="text compact">
        {{foreach from=$_sender->_params key=_param item=_value name=params}}
          {{if $smarty.foreach.params.iteration < 4}}
          <div>
            {{if $_value|@is_array}}
              {{foreach from=$_value item=_sub_value}}
                {{$_param}}[] = {{$_sub_value}}
                <br />
              {{/foreach}}
            {{else}}
              {{$_param}} = {{$_value}}
            {{/if}}
            {{if $smarty.foreach.params.iteration == 3 && count($_sender->_params) > 3 }}
            ...
            {{/if}}
          </div>
          {{/if}}
        {{/foreach}}
      </td>
      <td>          
        <button class="add notext" onclick="SourceToViewSender.edit('{{$_sender->_id}}');">
          {{tr}}Add{{/tr}}
        </button> 
      </td>
      <td>
        {{foreach from=$senders_source item=_sender_source}}

          {{assign var=sender_source value=$_sender_source->_ref_sender_source}}
          <div>
            {{if !$sender_source->actif}}
              <i class="fas fa-exclamation-triangle" style="color: goldenrod;"
                 title="{{tr}}CViewSenderSource-msg-View sender source is not active{{/tr}}"></i>
            {{/if}}
            {{if $sender_source->_ref_source->role != $conf.instance_role}}
              <i class="fas fa-exclamation-triangle" style="color: goldenrod;"
                 title="{{tr var1=$sender_source->_ref_source->role var2=$conf.instance_role}}CViewSenderSource-msg-View sender source incompatible %s with the instance role %s{{/tr}}"></i>
            {{/if}}

            {{if $sender_source->_ref_source->role == "prod"}}
              <strong style="color: red" title="{{tr}}CViewSenderSource_role.prod{{/tr}}">{{tr}}CViewSenderSource_role.prod-court{{/tr}}</strong>
            {{else}}
              <span style="color: green" title="{{tr}}CViewSenderSource_role.qualif{{/tr}}">{{tr}}CViewSenderSource_role.qualif-court{{/tr}}</span>
            {{/if}}

            <span onmouseover="ObjectTooltip.createEx(this, '{{$_sender_source->_guid}}');">
              {{$sender_source}}
            </span>
            <div class="compact">
              {{$_sender_source->last_duration|string_format:"%.3f"}}s /
              {{$_sender_source->last_size|decabinary}}
            </div>
          </div>
        {{foreachelse}}
          <div class="empty">{{tr}}CViewSender-back-sources_link.empty{{/tr}}</div>
        {{/foreach}}
      </td>
      <td class="text" style="text-align: right; padding-right: 0.5em;">
        {{if $_sender->every > 1}}
          {{mb_value object=$_sender field=every}}
        {{else}}
          {{mb_value object=$_sender field=period}}
        {{/if}}
      </td>
      <td style="text-align: right; padding-right: 0.5em;">
        {{mb_value object=$_sender field=offset}}mn
      </td>

      <td style="text-align: center;" class="{{if $_sender->day == $day || !$_sender->day}}ok{{else}}warning{{/if}}">
        {{mb_value object=$_sender field=day}}
      </td>

      {{assign var=status value=$_sender->active|ternary:"ok":"off"}}

      {{foreach from=$_sender->_hour_plan key=min item=plan}}
        {{assign var=status value=off}}
        {{if $plan  > 0.0}}{{assign var=status value=ok     }}{{/if}}
        {{if $plan >= 0.5}}{{assign var=status value=warning}}{{/if}}
        {{if $plan >= 1.0}}{{assign var=status value=error  }}{{/if}}

        {{assign var=active value=""}}
        {{if ($min == $minute && $_sender->_active)}}{{assign var=active value=active}}{{/if}}

        {{assign var=partial value=""}}
        {{if $_sender->every > 1 || ($_sender->day && $day != $_sender->day)}}{{assign var=partial value=partial}}{{/if}}

        <td class="hour-plan min-{{$min}} {{$status}} {{$active}} {{$partial}}" title="{{$plan|percent}} @ {{$min}}"></td>
      {{/foreach}}
    </tr>

  {{foreachelse}}
    <tr>
      <td class="empty" colspan="65">{{tr}}CViewSender.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
