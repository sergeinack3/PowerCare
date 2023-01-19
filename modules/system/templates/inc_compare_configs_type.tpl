{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=count_diff value=0}}

<table class="main tbl" id="{{$type}}-configs-table-{{$_mod_name}}">
  <tr>
    <th>Feature <input type="search" class="search" onkeyup="filterConfig(this, '{{$type}}-configs-table-{{$_mod_name}}');" value=""/></th>
    <th class="narrow">{{$main_file}}</th>
    {{foreach from=$files item=_file}}
      <th class="narrow">{{$_file}}</th>
    {{/foreach}}
  </tr>

  {{foreach from=$features key=_feature item=_values}}
    {{assign var=equals value=1}}
    <tr id="config-{{$_mod_name}}-{{$_feature}}-{{$type}}" class="config-line">
      <td class="config-feature">
        <strong>{{tr}}config-{{$_feature|replace:' ':'-'}}{{/tr}}</strong>
        <br/>
        <span class="compact">{{$_feature}}</span>
      </td>
      <td>
        {{if $main_file|array_key_exists:$_values}}
          {{$_values.$main_file}}
        {{/if}}
      </td>

      {{foreach from=$files item=_file}}
        {{assign var=value_ok value=1}}
        {{if !$_file|array_key_exists:$_values || !$main_file|array_key_exists:$_values|| $_values.$main_file != $_values.$_file}}
          {{assign var=value_ok value=0}}
          {{assign var=equals value=0}}
        {{/if}}

        <td class="text {{if !$value_ok}}warning{{/if}}">
          {{if $_file|array_key_exists:$_values}}
            {{$_values.$_file}}
          {{/if}}
        </td>
      {{/foreach}}

      {{if $equals}}
        <script>
          $("config-{{$_mod_name}}-{{$_feature}}-{{$type}}").toggleClassName("config-groups-line")
        </script>
      {{else}}
        {{assign var=count_diff value=$count_diff+1}}
      {{/if}}
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="100" class="empty">{{tr}}CConfiguration.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>

<script>
  Main.add(function() {
    if (!CompareObject.{{$module_name}}) {
      CompareObject.{{$module_name}} = {count_errors : 0, count_total:0};
    }
    CompareObject.{{$module_name}}.count_errors += {{$count_diff}};
    CompareObject.{{$module_name}}.count_total += {{$features|@count}};
    Control.Tabs.setTabCount("configs-{{$type}}-{{$_mod_name}}", {{$count_diff}}, {{$features|@count}})
  });
</script>