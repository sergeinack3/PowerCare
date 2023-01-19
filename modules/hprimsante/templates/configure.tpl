{{*
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create('tabs-configure', true, {afterChange: function(container) {
      if (container.id == 'CConfigEtab') {
        Configuration.edit('hprimsante', 'CGroups', $('CConfigEtab'));
      }
    }});
  });
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#config-treatment">{{tr}}config-hprimsante-treatment{{/tr}}</a></li>
  <li><a href="#config-purge_echange">{{tr}}config-hprimsante-purge_echange{{/tr}}</a></li>
  <li><a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}}</a></li>
</ul>

<div id="config-treatment" style="display: none;">
  {{mb_include template=inc_config_treatment}}
</div>

<div id="config-purge_echange" style="display: none;">
  {{mb_include template=inc_config_purge_echange}}
</div>

<div id="CConfigEtab" style="display: none"></div>
