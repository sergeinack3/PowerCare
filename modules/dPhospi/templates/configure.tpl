{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(Control.Tabs.create.curry('tabs-configure', true, {
    afterChange: function (container) {
      if (container.id == "CConfigEtab") {
        Configuration.edit('dPhospi', ['CGroups', 'CService CGroups.group_id'], $('CConfigEtab'));
      }
    }
  }));
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#Icones">{{tr}}Icones{{/tr}}</a></li>
  <li><a href="#CMovement">{{tr}}CMovement{{/tr}}</a></li>
  <li><a href="#config-synchro_sejour_affectation">{{tr}}config-synchro_sejour_affectation{{/tr}}</a></li>
  <li><a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}}</a>
  </li>
</ul>

<div id="Icones" style="display: none;">
  {{mb_include template=CLit_config}}
</div>

<div id="CMovement" style="display: none;">
  {{mb_include template=CMovement_config}}
</div>

<div id="config-synchro_sejour_affectation" style="display: none;">
  {{mb_include template=inc_config_synchro_sejour_affectation}}
</div>

<div id="CConfigEtab" style="display: none"></div>