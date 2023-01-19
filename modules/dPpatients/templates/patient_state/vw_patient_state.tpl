{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=patient_state ajax=$ajax}}
{{mb_script module=patients script=patient ajax=$ajax}}

{{if "ameli"|module_active && "ameli INSi active"|gconf}}
    {{mb_script module=ameli script=INSi ajax=true}}
{{/if}}

<script>
    Main.add(function () {
        Control.Tabs.create('tabs-main_patient_state', true, {
            afterChange: function (container) {
                switch (container.id) {
                    case "patient_manage":
                        PatientState.filterPatientState(getForm('filter_patient_state'));
                        break;
                    case "patient_stats":
                        PatientState.viewStats();
                        break;
                    case 'correct_status':
                        Patient.viewOldVali();
                        break;
                {{if "ameli"|module_active && "ameli INSi active"|gconf}}
                    case "verifierlot":
                        INSi.viewListLotIdentite();
                        break;
                    case "verify_identities":
                        INSi.viewListVerifyIdentities();
                        break;
                {{/if}}
                }
            }
        });
    });
</script>

<table class="main layout">
  <tr>
    <td class="narrow" style="white-space: nowrap;">
      <ul id="tabs-main_patient_state" class="control_tabs_vertical">
          {{if "ameli"|module_active && 'ameli INSi active'|gconf}}
            <li><a href="#verifierlot">{{tr}}CINSiLotIdentites{{/tr}}</a></li>
            <li><a href="#verify_identities">{{tr}}CINSiVerifiedIdentity{{/tr}}</a></li>
          {{/if}}
        <li><a href="#patient_manage">{{tr}}CPatientState.manage{{/tr}}</a></li>
        <li><a href="#correct_status">{{tr}}CPatient-Correct old VALI status{{/tr}}</a></li>
        <li><a href="#patient_stats">{{tr}}Stats{{/tr}}</a></li>
      </ul>
    </td>

    <td id="patient_manage">
        {{mb_include module=patients template=patient_state/inc_filter_patient_state date_min=$date_min
        date_max=$date_max}}
      <div id="patient_manage_container">
      </div>
    </td>
    <td id="correct_status"></td>
    <td id="patient_stats"></td>
      {{if "ameli"|module_active && "ameli INSi active"|gconf}}
        <td id="verifierlot"></td>
        <td id="verify_identities"></td>
      {{/if}}
  </tr>
</table>
