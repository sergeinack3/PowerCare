{{*
 * @package Mediboard\Style\Mediboard
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

  {{assign var=mod_name value="/^dP/"|preg_replace:"":$m}}
  {{assign var=selected_group_tab value=false}}
  <script>
    Main.add(
      function() {
        {{foreach from=$tabs item=grouptabs key=_group}}
          {{assign var=skip value=false}}
          {{foreach from=$grouptabs item=_tab key=_tabkey}}
            {{assign var=selected value="0"}}
            {{assign var=separator value="0"}}
            {{if !$skip}}
              {{if $_group === "settings"}}
                {{if $_group == $tab || $tab|array_key_exists:$grouptabs}}
                  {{assign var=selected_group_tab value=$grouptabs}}
                  {{assign var=selected value="1"}}
                {{/if}}
                {{assign var=tabname value="`$_group`"}}
                {{assign var=skip value=true}}
                {{assign var=separator value="1"}}
              {{/if}}
            {{/if}}
          {{/foreach}}
        {{/foreach}}
        {{if $selected_group_tab}}
          Control.Tabs.GroupedTabs.initialize('control_grouped_tabs', $('main-content'), MediboardExt.base_url);
        {{/if}}
      }
    );
  </script>

  {{if $selected_group_tab}}
    <div class="nav-subtabs">
      <ul id="control_grouped_tabs">
        {{foreach from=$selected_group_tab item=_subtab key=_subtabkey}}
          <li>
            <span class="subtab {{if $tab == $_subtabkey}}active{{/if}}" id="{{$_subtabkey}}"
                  data-href="{{$_subtab}}">
              {{tr}}mod-{{$m}}-tab-{{$_subtabkey}}{{/tr}}
            </span>
          </li>
        {{/foreach}}
      </ul>
    </div>
    <div class="nav-subtabs-compenser"></div>
  {{/if}}
  <div class="main-container">
    <div class="main-content" id="main-content">
