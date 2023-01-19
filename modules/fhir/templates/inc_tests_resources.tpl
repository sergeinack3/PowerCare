{{*
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create('tabs-tests_resources', true);
  });
</script>


{{mb_include module="eai" template="inc_form_session_receiver"}}

<table class="form">
  <tr>
    <td style="vertical-align: top; width: 100px" >
      <ul id="tabs-tests_resources" class="control_tabs_vertical">
        <li><a href="#CFHIRResourcePatient">{{tr}}CFHIRResourcePatient{{/tr}}</a></li>
        <li><a href="#CFHIRResourceEncounter">{{tr}}CFHIRResourceEncounter{{/tr}}</a></li>
      </ul>
    </td>
    <td style="vertical-align: top;">
      <div id="CFHIRResourcePatient" style="display: none">
        {{mb_include template="inc_curd_operations" resource_type="Patient"}}
      </div>

      <div id="CFHIRResourceEncounter" style="display: none">
        {{mb_include template="inc_curd_operations" resource_type="Encounter"}}
      </div>
    </td>
  </tr>
</table>