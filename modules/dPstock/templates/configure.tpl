{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create('tabs_stocks', true, {
      afterChange: function (container) {
        if (container.id == "CConfigEtab") {
          Configuration.edit('dPstock', ['CGroups'], $('CConfigEtab'));
        }
      }
    });
  });
</script>

<ul id="tabs_stocks" class="control_tabs">
  <li><a href="#config">{{tr}}Config{{/tr}}</a></li>
  <li><a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}}</a></li>
  <li><a href="#tools">{{tr}}Tools{{/tr}}</a></li>
</ul>

<div id="config" style="display: none;">
  {{mb_include module=stock template=inc_configure}}
</div>

<div id="tools" style="display: none;">
  {{mb_include module=stock template=inc_configure_tools}}
</div>

<div id="CConfigEtab" style="display: none;"></div>