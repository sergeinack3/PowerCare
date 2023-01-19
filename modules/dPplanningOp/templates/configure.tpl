{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create('tabs-configure', true, {afterChange: function(container) {
      if (container.id == "CConfigEtab") {
        Configuration.edit('dPplanningOp', ['CGroups'], $('CConfigEtab'));
      }
    }});
  });
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#configure-mode_easy">Aff. DHE Simplifiée</a></li>
  <li><a href="#configure-COperation">{{tr}}COperation{{/tr}}</a></li>
  <li><a href="#configure-CSejour">{{tr}}CSejour{{/tr}}</a></li>
  <li><a href="#configure-CLibelleOp">{{tr}}CLibelleOp{{/tr}}</a></li>
  <li><a href="#configure-blocage">{{tr}}CBlocage{{/tr}}</a></li>
  <li><a href="#configure-CIdSante400">{{tr}}CIdSante400-tag{{/tr}}</a></li>
  <li><a href="#configure-maintenance">{{tr}}Maintenance{{/tr}}</a></li>
  <li><a href="#Offline">{{tr}}Offline{{/tr}}</a></li>
  <li><a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}} </a></li>
  <li><a href="#Purge">{{tr}}Purge{{/tr}}</a></li>
</ul>

<div id="configure-mode_easy" style="display: none">
  {{mb_include template=inc_config_mode_easy}}
</div>

<div id="configure-COperation" style="display: none;">
  {{mb_include template=COperation_config}}
</div>

<div id="configure-CSejour" style="display: none;">
  {{mb_include template=CSejour_config}}
</div>

<div id="configure-CLibelleOp" style="display: none;">
  {{mb_include template=CLibelleOp_config}}
</div>

<div id="configure-blocage" style="display: none;">
  {{mb_include template=inc_config_blocage}}
</div>

<div id="configure-CIdSante400" style="display: none;">
  {{mb_include template=inc_config_etiquette}}
</div>

<div id="configure-maintenance" style="display:none">
  {{mb_include template=inc_config_actions}}
</div>

<div id="Offline" style="display: none;">
  {{mb_include template=inc_config_offline}}
</div>

<div id="CConfigEtab" style="display: none"></div>

<div id="Purge" style="display: none;">
  {{mb_include template=inc_configure_purge_sejours}}
</div>