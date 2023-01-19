{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    var tabs = Control.Tabs.create('tabs-configure', true);
    if (tabs.activeLink.key == "CConfigEtab") {
      Configuration.edit('dPfacturation', ['CGroups', 'CFunctions CGroups.group_id'], $('CConfigEtab'));
    }
  });
</script>

<ul id="tabs-configure" class="control_tabs">
  <li onmousedown="Configuration.edit('dPfacturation', ['CGroups', 'CFunctions CGroups.group_id'], $('CConfigEtab'))">
    <a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}}</a>
  </li>
  <li><a href="#tools">{{tr}}Tools{{/tr}}</a></li>
</ul>

<div id="CConfigEtab" style="display: none"></div>

<div id="tools" style="display: none">
  {{mb_include module=facturation template=tools/tools_factu}}
</div>
