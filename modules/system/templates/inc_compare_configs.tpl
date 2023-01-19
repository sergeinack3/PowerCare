{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  CompareObject = {};

  Main.add(function() {
    Control.Tabs.create("tab-modules-compare-configs");
    {{foreach from=$result key=_mod item=_value}}
      Control.Tabs.create('tab-configs-compare-{{$_mod}}');
    {{/foreach}}
  });
</script>

<fieldset>
  <legend>{{tr}}Option|pl{{/tr}}</legend>
  <form name="groups-config-compare" method="get">
    <input type="checkbox" name="show-or-hide" onclick="changeLinesVisibility(this, 'config-groups-line');"/>
    <label for="show-or-hide">{{tr}}CConfigurationCompare-action-hide same values{{/tr}}</label>
  </form>
</fieldset>

<table class="main layout">
  <tr>
    <td width="10%">
      <ul id="tab-modules-compare-configs" class="control_tabs_vertical">
        {{foreach from=$result key=_mod_name item=_types}}
          <li>
            <a href="#config-{{$_mod_name}}">
              {{if $_mod_name == 'none'}}
                {{tr}}None{{/tr}}
              {{else}}
                {{tr}}module-{{$_mod_name}}-court{{/tr}}
              {{/if}}
            </a>
          </li>
        {{/foreach}}
      </ul>
    </td>

    <td>
      {{foreach from=$result key=_mod_name item=_types}}
        {{assign var=module_name value="mod_$_mod_name"}}

        <div id="config-{{$_mod_name}}" style="display: none;">
          <ul id="tab-configs-compare-{{$_mod_name}}" class="control_tabs">
            <li><a href="#configs-instance-{{$_mod_name}}">{{tr}}CConfiguration-type-instance{{/tr}}</a></li>
            <li><a href="#configs-groups-{{$_mod_name}}">{{tr}}CConfiguration-type-groups{{/tr}}</a></li>
          </ul>

          <div id="configs-instance-{{$_mod_name}}" style="display: none;">

            {{mb_include module=system template=inc_compare_configs_type features=$result.$_mod_name.instance type='instance'}}
          </div>
          <div id="configs-groups-{{$_mod_name}}" style="display: none;">
            {{mb_include module=system template=inc_compare_configs_type features=$result.$_mod_name.groups type='groups'}}
          </div>
        </div>

        <script>
        Main.add(function() {
          Control.Tabs.setTabCount('config-{{$_mod_name}}', CompareObject.{{$module_name}}.count_errors, CompareObject.{{$module_name}}.count_total);
        });
        </script>
      {{/foreach}}
    </td>
  </tr>
</table>