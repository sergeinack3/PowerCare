{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create('tabs-configure', true, {
      afterChange: function (container) {
        if (container.id === 'config-etab') {
          Configuration.edit('mediusers', ['CGroups'], $('config-etab'));
        }
      }
    });
  });
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#config-etab">{{tr}}CConfigEtab{{/tr}}</a></li>
  <li><a href="#config-maintenance">{{tr}}Maintenance{{/tr}}</a></li>
</ul>


<div id="config-etab" style="display: none;"></div>

<div id="config-maintenance" style="display: none;">
  {{mb_include module=mediusers template=inc_config_maintenance}}
</div>