{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=edit_meeting ajax=$ajax}}
{{mb_script module=compteRendu script=document ajax=$ajax}}

<script>
  Main.add(function () {
    Meeting.meeting_id = {{$patient_meeting->reunion_id}};
    Meeting.patient_meeting_id = {{$patient_meeting->_id}};
    Meeting.active_patient_id = {{$patient_meeting->patient_id}};
    Meeting.generateDocument();
    Meeting.changeModelPatientMeeting();
    Meeting.expandFormOtherPatients();
    Meeting.savePatientMeeting();
    Document.register('{{$patient_meeting->_id}}', '{{$patient_meeting->_class}}', '{{$current_user->_id}}', 'documents{{$patient_meeting->_id}}');
  });
</script>


<table class="main">
  <tr>
    <td class="width50">
      <form id="form{{$patient_meeting->_id}}" name="form{{$patient_meeting->_id}}" method="post" onsubmit="return onSubmitFormAjax(this)">
        {{mb_class object=$patient_meeting}}
        {{mb_key object=$patient_meeting}}
        {{assign var=id value=$patient_meeting->_id}}
        {{assign var=formName value=form$id}}
        <table class="table form">
          <tr>
            <th style="text-align: left">{{mb_label object=$patient_meeting field=motif}}</th>
            <th style="text-align: left">{{mb_label object=$patient_meeting field=remarques}}</th>
          </tr>

          <tr>
            <td class="width50">{{mb_field object=$patient_meeting field=motif register=true form=$formName}}</td>
            <td>{{mb_field object=$patient_meeting field=remarques register=true form=$formName}}</td>
          </tr>

          <tr>
            <th style="text-align: left">{{mb_label object=$patient_meeting field=action}}</th>
            <th style="text-align: left">{{mb_label object=$patient_meeting field=au_total}}</th>
          </tr>

          <tr>
            <td>{{mb_field object=$patient_meeting field=action register=true form=$formName}}</td>
            <td>{{mb_field object=$patient_meeting field=au_total register=true form=$formName}}</td>
          </tr>

          <tr>
            <td></td>
            <td style="text-align: right;">
              <button class="duplicate expand-form-other-patients"
                      type="button"
                      data-patient-meeting="{{$patient_meeting->patient_reunion_id}}">{{tr}}CReunion-Copy form{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>
    </td>

    <td>
      {{* Document widget *}}
      <fieldset>
        <legend>{{tr}}CCompteRendu|pl{{/tr}}</legend>
        <div id="documents{{$patient_meeting->_id}}"></div>
      </fieldset>
    </td>
  </tr>
</table>

