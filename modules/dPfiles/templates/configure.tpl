{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create('tabs-configure', true);
    Configuration.edit(
      'dPfiles',
      ['CGroups'],
      $('CConfigEtab')
    );
  });
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#CFile"          >{{tr}}CFile{{/tr}}</a></li>
  <li><a href="#ooo">OpenOffice.org</a></li>
  <li><a href="#test">{{tr}}CFile-test_operations{{/tr}}</a></li>
  <li><a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}}</a></li>
</ul>

<div id="CFile" style="display: none;">
  {{mb_include template=CFile_configure}}
</div>

<div id="ooo" style="display: none;">
  {{mb_include template=inc_configure_ooo}}
</div>

<div id="test" style="display: none;">
  {{mb_include template=inc_test_files}}
</div>

<div id="CConfigEtab" style="display: none;"></div>
