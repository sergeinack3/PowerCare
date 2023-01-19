{{*
 * @package Mediboard\Sa
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create('tabs-configure', true, {afterChange: function(container) {
      if (container.id == "CConfigEtab") {
        Configuration.edit('sa', ['CGroups'], $('CConfigEtab'));
      }
    }});
  });

</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#SA">{{tr}}SA{{/tr}}</a></li>
  <li><a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}}</a></li>
</ul>

<div id="SA" style="display: none;">
  {{mb_include template=inc_config_sa}}
</div>

<div id="CConfigEtab" style="display: none;">
</div>