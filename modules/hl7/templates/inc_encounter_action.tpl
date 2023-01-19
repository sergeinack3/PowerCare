{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  changeEvent = function(element) {
    var constraint = {
      "admit"                         : ["", "A01", "A11"],
      "event_change_class_inpatient"  : ["", "INSERT"],
      "event_change_class_outpatient" : ["", "INSERT"],
      "discharge_patient"             : ["", "A03", "A13"],
      "pre_admit"                     : ["", "A05", "A38"],
      "register_outpatient"           : ["", "A04", "A11"],
      "transfert_patient"             : ["", "A02", "A12"],
      ""                              : [""]
    };

    var event_type = $V(element);

    //Application des contraintes
    $A(element.form.event).each(function (option) {
      if (constraint[event_type].include(option.value)) {
        option.show();
      }
      else {
        option.hide();
      }
    });
  }
</script>

<button type="button" class="carriage_return" onclick="TestHL7.searchPatient()">Recherche patient</button>

{{mb_include module=hl7 template=inc_banner_patient_hl7}}

<form name="hl7_action" method="post" onsubmit="return TestHL7.sendTest(this)">
  <input type="hidden" name="patient_id" value="{{$patient->_id}}">
  <table class="form">
    <tr>
      <th><label for="event_select">Type d'évenement</label></th>
      <td>
        <select id="event_select" name="event_type" onchange="changeEvent(this)">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          <option value="admit">Admit inpatient</option>
          <option value="event_change_class_inpatient">Change patient class to inpatient</option>
          <option value="event_change_class_outpatient">Change patient class to outpatient</option>
          <option value="discharge_patient">Discharge patient</option>
          <option value="pre_admit">Pre-admit Patient</option>
          <option value="register_outpatient">Register outpatient</option>
          <option value="transfert_patient">Transfer patient</option>
        </select>
      </td>
    </tr>

    <tr>
      <th>Evenement</th>
      <td>
        <select name="event" onchange="if($V(this)) {this.form.onsubmit()} $V(this, '');">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          <option value="A01" style="display: none;">INSERT (A01)</option>
          <option value="A02" style="display: none;">INSERT (A02)</option>
          <option value="A03" style="display: none;">INSERT (A03)</option>
          <option value="A04" style="display: none;">INSERT (A04)</option>
          <option value="A05" style="display: none;">INSERT (A05)</option>
          <option value="A11" style="display: none;">CANCEL (A11)</option>
          <option value="A12" style="display: none;">CANCEL (A12)</option>
          <option value="A13" style="display: none;">CANCEL (A13)</option>
          <option value="A38" style="display: none;">CANCEL (A38)</option>
          <option value="INSERT" style="display: none;">INSERT</option>
        </select>
      </td>
    </tr>
  </table>
</form>