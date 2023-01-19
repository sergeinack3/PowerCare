{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="cabinet" script="edit_consultation" ajax=true}}
{{mb_script module=patients script="pat_selector" ajax=true}}
{{mb_script module="oxCabinet" script="TdBTamm" ajax=true}}

<script>
    Main.add(function () {
        PatSelector.init = function () {
            this.sForm = 'addPatientMeeting';
            this.sId = 'patient_id';
            this.sView = 'add_patient_name';
            this.pop();
        };
    });

</script>

{{if $app->user_prefs.UISTYLE == "tamm"}}
    <form name="addPatientMeeting" method="post" onsubmit="return onSubmitFormAjax(this)" style="width: 100%">
        <input type="hidden" name="reunion_id" value="{{$meeting->_id}}"/>
        <input type="hidden" name="callback" value="Consultation.loadListePatientReunion.curry({{$meeting->_id}})"/>

        {{mb_class object=$patient_meeting}}
        {{mb_key   object=$patient_meeting}}

        {{mb_field object=$patient_meeting field=patient_id hidden=true}}
        <input type="text" name="add_patient_name" style="width: 15em;" readonly="readonly"
               onchange="this.form.onsubmit()" onfocus="" onclick="PatSelector.init();"/>

        <button class="search notext" type="button" onclick="PatSelector.init();">{{tr}}Search{{/tr}}</button>
        <button class="cancel notext" type="button"
                onclick="$V(this.form.add_patient_id, ''); $V(this.form.add_patient_name, '')">
            {{tr}}Delete{{/tr}}
        </button>
    </form>
    <button type="button" class="mediuser_black notext" style="float: right" title="{{tr}}patient-next-meeting{{/tr}}"
            onclick="TdBTamm.modalAddPatientNextMeeting('{{$meeting->_id}}')">
    </button>
{{/if}}

{{* Registered patients for the next meeting *}}
<table class="tbl">
    <tr>
        <th>{{tr}}CPatient{{/tr}}</th>
        <th>{{mb_title class=CPatient field=naissance}}</th>
        <th>{{mb_title class=CPatient field=sexe}}</th>
        {{if $app->user_prefs.UISTYLE == "tamm"}}
            <th class="narrow"></th>
        {{/if}}
    </tr>
    {{foreach from=$patients_meeting item=_patient_meeting}}
        <tr>
            <td>
                {{mb_value object=$_patient_meeting->_ref_patient}}
            </td>
            <td>{{$_patient_meeting->_ref_patient->naissance|date_format:$conf.date}}</td>
            <td>{{tr}}CPatient.sexe.{{$_patient_meeting->_ref_patient->sexe}}{{/tr}}</td>

            {{if $app->user_prefs.UISTYLE == "tamm"}}
                <td>
                    <form name="delete-patient-reunion-{{$_patient_meeting->_id}}" method="post">
                        {{mb_class object=$_patient_meeting}}
                        {{mb_key object=$_patient_meeting}}
                        <button type="button" class="trash notext remove-patient-meeting" title="{{tr}}Delete{{/tr}}"
                                onclick="confirmDeletion(this.form, {ajax: true, objName: '{{$_patient_meeting->_ref_patient}} ' + $T('CPatientReunion-of the meeting')}, {onComplete: Consultation.loadListePatientReunion.curry({{$_patient_meeting->reunion_id}})})"></button>
                    </form>
                </td>
            {{/if}}
        </tr>
        {{foreachelse}}
        <tr>
            <td colspan="3" class="empty">{{tr}}CReunion-back-patients_reunions.empty{{/tr}}</td>
        </tr>
    {{/foreach}}
</table>
