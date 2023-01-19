{{*
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
  Main.add(function () {
      Control.Tabs.create('tabs-configure', true, {
        afterChange: function (container) {
          if (container.id === "CConfigurationFHIR") {
            Configuration.edit('fhir', ['CGroups', 'CMessageSupported'], $('CConfigurationFHIR'));
          }
        }
      })
  });
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#FHIR">{{tr}}FHIR{{/tr}}</a></li>
  <li><a href="#CConfigurationFHIR">{{tr}}config-fhir configuration{{/tr}}</a></li>
</ul>

<div id="FHIR" style="display: none;">
  {{mb_include template=inc_config_fhir}}
</div>

<div id="CConfigurationFHIR" style="display: none;"></div>
