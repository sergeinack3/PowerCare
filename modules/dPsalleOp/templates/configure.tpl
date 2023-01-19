{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    var tabs = Control.Tabs.create('tabs-configure', true, {afterChange: function(container) {
      if (container.id == "CConfigEtab") {
        Configuration.edit('dPsalleOp', ['CGroups', 'CService CGroups.group_id'], $('CConfigEtab'));
      }
    }});
  });
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}}</a></li>
  <li><a href="#changeProtocoleItem">{{tr}}Tools{{/tr}}</a></li>
</ul>

<div id="CConfigEtab" style="display: none;"></div>

<div id="changeProtocoleItem" class="me-no-border me-no-align" style="display: none">
  {{mb_include template=inc_vw_tools}}
</div>