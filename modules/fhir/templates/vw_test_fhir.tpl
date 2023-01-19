{{*
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=fhir       script=test_fhir}}
{{mb_script module=dPpatients script=patient}}
{{mb_script module=dPpatients script="autocomplete"}}

<script>
  Main.add(function () {
    tabs = Control.Tabs.create('tabs-test_fhir', true, {
      afterChange: function (name_div) {
        switch (name_div.id) {
          case 'test_fhir_pdqm':
            TestFHIR.showPDQmRequest();
            break;
          case 'test_fhir_pixm':
            TestFHIR.showPIXmRequest();
            break;
          case 'test_fhir_mhd':
            TestFHIR.showMHDRequest();
            break;
          case 'test_fhir_resources':
            TestFHIR.showFHIRResources();
            break;

          default :
        }
      }
    });
  });
</script>

<ul id="tabs-test_fhir" class="control_tabs">
  <li><a href="#test_fhir_pdqm">{{tr}}CPDQm{{/tr}}</a></li>
  <li><a href="#test_fhir_pixm">{{tr}}CPIXm{{/tr}}</a></li>
  <li><a href="#test_fhir_mhd">{{tr}}CMHD{{/tr}}</a></li>
  <li><a href="#test_fhir_resources">{{tr}}CFHIRResources{{/tr}}</a></li>
</ul>

<div id="test_fhir_pdqm" style="display: none">
</div>

<div id="test_fhir_pixm" style="display: none">
</div>

<div id="test_fhir_mhd" style="display: none">
</div>

<div id="test_fhir_resources" style="display: none">
</div>