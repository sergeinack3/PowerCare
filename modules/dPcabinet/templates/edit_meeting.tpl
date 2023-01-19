{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=edit_meeting ajax=$ajax}}
{{mb_script module=compteRendu script=modele_selector ajax=$ajax}}

<script>
  Main.add(function () {
    Control.Tabs.create('patients_tabs');
    Meeting.init({{$meeting->_id}});

    var li = $$('#patients_tabs li')[0];
    if (li !== undefined) {
      li.down('a').click();
    }
  });
</script>

<div id="meeting_header">{{mb_include module=cabinet template=inc_edit_meeting_header}}</div>

{{* Opens on modal popup to ask for which patients documents should be created *}}
<div id="send_patients_modal" style="display: none;">
  <form name="list_patient_to_generate">
    <fieldset id="model_global">
      <legend>{{tr}}model-global{{/tr}}</legend>
      <table class="table form">
        <tr>
          <th class="width50" style="text-align: left;">{{tr}}models-documents{{/tr}}</th>
          <td>
            <select name="model_id">
              {{foreach from=$models item=_model}}
                <option value="{{$_model->_id}}">{{$_model->nom}}</option>
              {{/foreach}}
            </select>
          </td>
        </tr>
      </table>
    </fieldset>

    <fieldset id="patient_concerned">
      <legend><input type="checkbox" name="all" data-form-name="list_patient_to_generate"> {{tr}}CPatient.list{{/tr}}</legend>
      <table class="table form">
        {{foreach from=$patient_meeting_list item=_patient_meeting}}
          <tr class="patient-send">
            <td class="narrow" style="padding: 5px;"><input id="patient-send-input-{{$_patient_meeting->_id}}" type="checkbox" name="patients[]" value="{{$_patient_meeting->_id}}"></td>
            <td style="padding: 5px;"><lebel for="patient-send-input-{{$_patient_meeting->_id}}">{{$_patient_meeting->_ref_patient->_view}}</lebel></td>
          </tr>
        {{/foreach}}
      </table>
    </fieldset>

    <div style="text-align: right; margin: 10px;">
      <button type="button"
              class="cancel close-modal"
              data-modal-name="patients_modal">{{tr}}Cancel{{/tr}}</button>
      <button type="button"
              class="send close-modal generate-all-docs">{{tr}}Send{{/tr}}</button>
    </div>
  </form>
</div>

{{* Opens on modal popup to ask for which patients the form should be copied *}}
<div id="copy_patients_modal" style="display: none;">
  <form name="list_patient_to_copy">
    <fieldset id="patient_concerned">
      <legend><input type="checkbox" name="all" data-form-name="list_patient_to_copy"> {{tr}}CPatient.list{{/tr}}</legend>
      <table class="table form">
        {{foreach from=$patient_meeting_list item=_patient_meeting}}
          <tr class="patient-copy" id="patient-copy-{{$_patient_meeting->patient_id}}">
            <td class="narrow" style="padding: 5px;">
                <input id="copy_patient_{{$_patient_meeting->patient_id}}"
                       type="checkbox"
                       name="patients[]"
                       value="{{$_patient_meeting->patient_id}}">
            </td>
            <td style="padding: 5px;">
                <label for="copy_patient_{{$_patient_meeting->patient_id}}">
                    {{mb_value object=$_patient_meeting->_ref_patient}}
                </label>
            </td>
          </tr>
        {{/foreach}}
      </table>
    </fieldset>

    <div style="text-align: right; margin: 10px;">
      <button type="button"
              class="cancel close-modal"
              data-modal-name="patients_modal">{{tr}}Cancel{{/tr}}</button>
      <button class="duplicate expand-form-other-patients"
              type="button">{{tr}}CReunion-Copy form{{/tr}}</button>
    </div>
  </form>
</div>

{{if sizeof($patient_meeting_list) == 0}}
  <h4>{{tr}}CReunion-back-patients_reunions.empty{{/tr}}</h4>
{{/if}}

<ul id="patients_tabs" class="control_tabs">
  {{foreach from=$patient_meeting_list item=_patient_meeting}}
    <li>
        <a href="#patient{{$_patient_meeting->_ref_patient->_id}}" data-patient-id="{{$_patient_meeting->patient_id}}">
            {{$_patient_meeting->_ref_patient}}
        </a>
    </li>
  {{/foreach}}
</ul>

{{foreach from=$patient_meeting_list item=_patient_meeting}}
  <div id="patient{{$_patient_meeting->_ref_patient->_id}}"></div>
{{/foreach}}

