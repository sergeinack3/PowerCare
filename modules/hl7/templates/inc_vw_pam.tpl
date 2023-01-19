{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create("menu-pam", true, {
      afterChange: function(container) {
        switch (container.id) {
          case "generate":
            TestHL7.showPatientGenerator();
            break;
        }
      }
    });

    Control.Tabs.create("menu-pat", true);
  });
</script>

<table  class="main layout">
  <tr>
    <td class="narrow">
      <ul id="menu-pam" class="control_tabs_vertical">
        <li><a href="#patient">Patient supplier</a></li>
        <li><a href="#generate">Générer des patients</a></li>
      </ul>
    </td>
    <td id="patient" style="display: none;">
      <ul id="menu-pat" class="control_tabs small">
        <li><a href="#search_demographic">Demographic Supplier</a></li>
        <li><a href="#search_encounter">Encounter Supplier</a></li>
      </ul>

      {{mb_include module="hl7" template="inc_form_session_receiver"}}

      <div id="search_demographic" style="display: none;">
        {{mb_include module="hl7" template="inc_search_demographic"}}
      </div>
      <div id="search_encounter" style="display: none;">
        <script>
          Main.add(function() {
            TestHL7.searchPatient();
          });
        </script>
      </div>
    </td>
    <td id="generate" style="display: none;"></td>
  </tr>
</table>

