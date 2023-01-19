{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h1>
  {{tr}}{{$title}}{{/tr}}
  <br />
  par utilisateur
  du {{$min_date|date_format:$conf.date}}
  au {{$max_date|date_format:$conf.date}}
</h1>

<table class="tbl me-stat-factu">
  <tr>
    <th class="title" rowspan="2">{{tr}}CMediusers{{/tr}}</th>
    <th class="title" colspan="{{$dates|@count}}">Totaux par {{tr}}{{$period}}{{/tr}}</th>
  </tr>
  <tr>
    {{if $period == "day"  }}{{assign var=format value="%d %a"}}{{/if}}
    {{if $period == "week" }}{{assign var=format value="%V"   }}{{/if}}
    {{if $period == "month"}}{{assign var=format value="%m"   }}{{/if}}
    {{if $period == "year" }}{{assign var=format value="%y"   }}{{/if}}

    {{foreach from=$dates item=_date}}
    <th class="text me-text-align-center" style="width: {{math equation="60/x" x=$dates|@count}}%;" title="{{$_date}}">
      {{$_date|date_format:$format}}
    </th>
    {{/foreach}}
  </tr>

  {{foreach from=$groups item=_group}}
  <tr>
    <th class="category">{{$_group}}</th>

  {{assign var=guid value=$_group->_guid}}
  {{if isset($sections[$guid]|smarty:nodefaults)}}
    {{assign var=_dates value=$sections[$guid]}}
    {{foreach from=$dates item=_date}}
      <th style="text-align: center;">
      {{if array_key_exists($_date, $_dates)}}
        {{foreach from=$_dates.$_date key=_part item=_value}}
          <strong class="{{$_part}}">{{$_value}}</strong>
        {{/foreach}}
      {{/if}}
      </th>
    {{/foreach}}

  {{else}}
    <th class="section"></th>

  {{/if}}
  </tr>


  {{foreach from=$_group->_ref_functions item=_function}}
  <tr>
    <th class="section" style="text-align: left;">
      {{mb_include module=mediusers template=inc_vw_function function=$_function classe="me-full-width"}}
    </th>

  {{assign var=guid value=$_function->_guid}}
  {{if isset($sections[$guid]|smarty:nodefaults)}}
    {{assign var=_dates value=$sections[$guid]}}
    {{foreach from=$dates item=_date}}
      <th class="section" style="text-align: center;">
        {{if array_key_exists($_date, $_dates)}}
          {{foreach from=$_dates.$_date key=_part item=_value}}
            <strong class="{{$_part}}">{{$_value}}</strong>
          {{/foreach}}
        {{/if}}
      </th>
    {{/foreach}}

    {{else}}
    <th class="section"></th>
  {{/if}}
  </tr>

  {{foreach from=$_function->_ref_users key=user_id item=_user}}
  <tr>
    <td>
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_user classe="me-full-width"}}
    </td>
     {{assign var=_dates value=$totals.$user_id}}
     {{foreach from=$dates item=_date}}

      <td style="text-align: center;"
        {{if isset($cells[$user_id][$_date]|smarty:nodefaults)}}
          class="{{$cells[$user_id][$_date]}}"
        {{/if}}
        >
        {{if array_key_exists($_date, $_dates)}}
          {{foreach from=$_dates.$_date key=_part item=_value}}
            <strong class="{{$_part}}">{{$_value}}</strong>
          {{/foreach}}
        {{/if}}
      </td>
    {{/foreach}}
  </tr>
  {{/foreach}}
  {{/foreach}}
  {{foreachelse}}
  <tr><td class="empty" colspan=31">{{tr}}Stats.none{{/tr}}</td></tr>
  {{/foreach}}
</table>