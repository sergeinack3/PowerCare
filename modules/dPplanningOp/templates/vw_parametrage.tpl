{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPplanningOp script=parametrage_mode ajax=1}}
{{if !$refresh_mode}}
<div id="mode_parametrage_container">
{{/if}}
  <script>
    Main.add(function() {
      Control.Tabs.create("custom-cpi-mode-entre-sortie", true);
      ParametrageMode.reloadListModeDestPec("destination");
      ParametrageMode.reloadListModeDestPec("pec");
    });
  </script>

  <ul class="control_tabs" id="custom-cpi-mode-entre-sortie">
    <li><a href="#tab-CChargePriceIndicator">{{tr}}CChargePriceIndicator{{/tr}}</a></li>
    <li><a href="#tab-CModeEntreeSejour">{{tr}}CModeEntreeSejour{{/tr}}</a></li>
    <li><a href="#tab-CModeSortieSejour">{{tr}}CModeSortieSejour{{/tr}}</a></li>
    <li><a href="#tab-CModeDestinationSejour">{{tr}}CModeDestinationSejour{{/tr}}</a></li>
    <li><a href="#tab-CModePECSejour">{{tr}}CModePECSejour{{/tr}}</a></li>
  </ul>

  <div id="tab-CChargePriceIndicator" style="display: none;">
    {{mb_include template=CChargePriceIndicator_config}}
  </div>

  <div id="tab-CModeEntreeSejour" style="display: none;">
    {{mb_include template=CModeEntreeSortieSejour_config list_modes=$list_modes_entree mode_class=CModeEntreeSejour}}
  </div>

  <div id="tab-CModeSortieSejour" style="display: none;">
    {{mb_include template=CModeEntreeSortieSejour_config list_modes=$list_modes_sortie mode_class=CModeSortieSejour}}
  </div>

  <div id="tab-CModeDestinationSejour" style="display: none;"></div>

  <div id="tab-CModePECSejour" style="display: none;"></div>
{{if !$refresh_mode}}
</div>
{{/if}}