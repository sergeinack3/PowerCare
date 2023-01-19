{{*
 * @package Mediboard\Ihe
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ihe script=ihe}}

<script>
  Main.add(function() {
    Control.Tabs.create('tabs-configure', true, {afterChange: function(container) {
      if (container.id == "CConfigEtab") {
        Configuration.edit('ihe', ['CGroups'], $('CConfigEtab'));
      }
    }});
  });
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#ihe">{{tr}}IHE{{/tr}}</a></li>
  <li><a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}} </a></li>
</ul>

<div id="ihe" style="display: none;">
  {{mb_include module=ihe template=inc_config_ihe}}
</div>

<div id="CConfigEtab" style="display: none"></div>