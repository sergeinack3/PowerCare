{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create("config-tabs", true);
    Configuration.edit('system', ['CGroups'], $('CConfigEtab'));
  });
</script>

<ul class="control_tabs" id="config-tabs">
  <li><a href="#ui">{{tr}}config-ui{{/tr}}</a></li>
  <li><a href="#formats">{{tr}}config-formats{{/tr}}</a></li>
  <li><a href="#system">{{tr}}config-system{{/tr}}</a></li>
  <li><a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}}</a></li>
  <li><a href="#shortcuts">{{tr}}appbar_shortcuts{{/tr}}</a></li>
  <li><a href="#CMessage">{{tr}}CMessage{{/tr}}</a></li>
  <li><a href="#exploitation">{{tr}}common-Exploitation{{/tr}}</a></li>
  <li><a href="#maintenance">{{tr}}common-Maintenance{{/tr}}</a></li>
</ul>

{{assign var=m value=""}}

<div id="ui">
  {{mb_include template=inc_config_ui}}
</div>

<div id="formats">
  {{mb_include template=inc_config_formats}}
</div>

<div id="system">
  {{mb_include template=inc_config_system}}
</div>

<div id="CConfigEtab" style="display: none;"></div>

<div id="shortcuts">
  {{mb_include template=inc_config_shortcuts}}
</div>

<div id="test" style="display: none;"></div>

<div id="CMessage">
  {{mb_include template=CMessage_configure}}
</div>

<div id="exploitation">
  {{mb_include template=inc_config_exploitation}}
</div>

<div id="maintenance">
  {{mb_include template=inc_config_maintenance}}
</div>
