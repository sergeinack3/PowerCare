{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=patient ajax=true}}

{{mb_default var=not_printable value=0}}
{{mb_default var=offline_sejour value=0}}
{{mb_default var=show_dossier value=true}}

<table class="tbl me-patient-complete print_patient me-no-hover"
       style="{{if !$not_printable}}page-break-after: always;{{/if}}">
    {{if $offline_sejour}}
        <thead class="thead_patient">
        <tr>
            <th class="title" colspan="4">
                {{if $in_modal}}
                    <button style="float: right;" class="cancel not-printable"
                            onclick="resetPrintable('{{$sejour->_id}}'); Control.Modal.close();">{{tr}}Close{{/tr}}</button>
                {{/if}}
                {{$object->_view}}
                {{mb_include module=patients template=inc_vw_ipp ipp=$object->_IPP}}
            </th>
        </tr>
        </thead>
    {{/if}}
    {{if !@$no_header}}
        <tr>
            <th class="title text" colspan="4">

                {{mb_include module=patients template=inc_view_ins_patient patient=$object}}

                {{mb_include module=system template=inc_object_idsante400 object=$object}}
                {{mb_include module=system template=inc_object_history object=$object}}

                <a style="float:right;" href="#print-{{$object->_guid}}" onclick="Patient.print('{{$object->_id}}')">
                    {{me_img_title src="print.png" icon="print" class="me-primary" alt_tr=Print}}
                    {{tr}}CConsultation-Print the card{{/tr}}
                    {{/me_img_title}}
                </a>

                {{if $can->edit}}
                    <a style="float:right;" href="#edit-{{$object->_guid}}" onclick="Patient.edit('{{$object->_id}}')">
                        {{me_img_title src="edit.png" icon="edit" class="me-primary" alt="modifier"}}
                        {{tr}}CPatient-title-modify{{/tr}}
                        {{/me_img_title}}
                    </a>
                {{/if}}

                {{if $app->user_prefs.vCardExport}}
                    <a style="float:right;" href="#export-{{$object->_guid}}"
                       onclick="Patient.exportVcard('{{$object->_id}}')">
                        <img src="images/icons/vcard.png" alt="export" title="Exporter le patient"/>
                    </a>
                {{/if}}

                {{mb_include module=system template=inc_object_notes object=$object}}

                <form name="actionPat" action="?" method="get">
                    <input type="hidden" name="m" value="patients"/>
                    <input type="hidden" name="tab" value="vw_idx_patients"/>
                    <input type="hidden" name="patient_id" value="{{$object->_id}}"/>
                    {{$object->_view}}

                    {{mb_include module=patients template=inc_vw_ipp ipp=$object->_IPP}}

                    {{mb_include module=patients template=inc_icon_bmr_bhre patient=$object}}
                </form>
            </th>
        </tr>
    {{/if}}

    <tr>
        <td class="button" colspan="3">
            {{assign var=patient value=$object}}
            {{mb_include module=patients template=inc_vw_photo_identite mode="read"}}
        </td>
        {{if $patient->_ref_patient_ins_nir && $patient->_ref_patient_ins_nir->datamatrix_ins}}
            <td style="margin-top: 4em;" class="title">
                {{mb_include module=dPpatients template=vw_datamatrix_ins}}
            </td>
        {{/if}}
    </tr>

    <tr>
        <td class="text">
            <strong>{{mb_label object=$object field="nom_jeune_fille"}}</strong>
            {{mb_value object=$object field="nom_jeune_fille"}}
        </td>

        <td class="text" colspan="3">
            <strong>{{mb_label object=$object field="adresse"}}</strong>
            {{mb_value object=$object field="adresse"}}
        </td>
    </tr>

    <tr>
      <td class="text">
            <strong>{{mb_label object=$object field="prenom"}}</strong>
            {{mb_value object=$object field="prenom"}}
      </td>

      <td class="text" colspan="3">
            <strong>{{mb_label object=$object field="ville"}}</strong>
            {{mb_value object=$object field="cp"}}
            {{mb_value object=$object field="ville"}}
      </td>
    </tr>

    <tr>
        <td class="text">
            <strong>{{mb_label object=$object field="prenoms"}}</strong>
            {{mb_value object=$object field="prenoms"}}
        </td>

      <td class="text" colspan="3">
            <strong>{{mb_label object=$object field="tel"}}</strong>
            {{mb_value object=$object field="tel"}}
      </td>
    </tr>

    <tr>
        <td class="text">
            <strong>{{mb_label object=$object field="prenom_usuel"}}</strong>
            {{mb_value object=$object field="prenom_usuel"}}
        </td>

      <td class="text" colspan="3">
            <strong>{{mb_label object=$object field="tel2"}}</strong>
            {{mb_value object=$object field="tel2"}}
      </td>
    </tr>

    <tr>
        <td class="text">
            <strong>{{mb_label object=$object field="nom"}}</strong>
            {{mb_value object=$object field="nom"}}
        </td>

      <td class="text" colspan="3">
            <strong>{{mb_label object=$object field="tel_pro"}}</strong>
            {{mb_value object=$object field="tel_pro"}}
      </td>
    </tr>

    <tr>
        <td class="text">
            <strong>{{mb_label object=$object field="naissance"}}</strong>
            {{mb_value object=$object field="naissance"}}
        </td>

      <td class="text" colspan="3">
            <strong>{{mb_label object=$object field="tel_autre"}}</strong>
            {{mb_value object=$object field="tel_autre"}}
      </td>
    </tr>

    <tr>
        <td class="text">
            <strong>{{mb_label object=$object field=_age}}</strong>
            {{mb_value object=$object field=_age}}
        </td>

      <td class="text" colspan="3">
            <strong>{{mb_label object=$object field="email"}}</strong>
            {{mb_value object=$object field="email"}}
      </td>
    </tr>

    <tr>
        <td class="text">
            <strong>{{mb_label object=$object field="sexe"}}</strong>
            {{mb_value object=$object field="sexe"}}
        </td>

      <td class="text" colspan="3">
            <strong>{{mb_label object=$object field="rques"}}</strong>
            {{mb_value object=$object field="rques"}}
      </td>
    </tr>

    <tr>
        <td class="text">
            <strong>{{mb_label object=$object field=lieu_naissance}}</strong>
            {{mb_include module=patients template=inc_lieu_naissance object=$object full=true}}
        </td>

        <td colspan="3"></td>
    </tr>

    <tr>
        {{if $object->_ref_patient_ins_nir->_id}}
            <td>
                <strong>{{tr}}CPatient-_matricule-ins{{/tr}}</strong>
                {{mb_value object=$object->_ref_patient_ins_nir field="ins_nir"}}
                ({{mb_value object=$object->_ref_patient_ins_nir field="_ins_type"}})
            </td>
        {{else}}
            <td>
                <strong>{{mb_label object=$object field="matricule"}}</strong>
                {{mb_value object=$object field="matricule"}}
            </td>
        {{/if}}
        <td colspan="3"></td>
    </tr>

    <tr>
        <td class="text" colspan="3">
            <strong>{{mb_label object=$object field="profession"}}</strong>
            {{mb_value object=$object field="profession"}}
        </td>
    </tr>
    {{if $object->_ref_dossier_medical}}
            <tr>
                <td class="text">
                    <strong>{{mb_title object=$object->_ref_dossier_medical field=groupe_sanguin}}
                        /{{mb_title object=$object->_ref_dossier_medical field=rhesus}}:</strong>
                    {{$object->_ref_dossier_medical->groupe_sanguin}} {{if $object->_ref_dossier_medical->rhesus == "POS"}}+{{elseif $object->_ref_dossier_medical->rhesus == "NEG"}}-{{/if}}
                </td>
                <td class="text" colspan="3"></td>
            </tr>
        {{/if}}

    {{foreach from=$object->_ref_cp_by_relation.prevenir item=prevenir name=foreach_prevenir}}
        <tr>
            <th class="category" colspan="4">Personne à prévenir</th>
        </tr>
        <tr>
            <td class="text">
                <strong>{{mb_label object=$prevenir field="nom"}}</strong>
                {{mb_value object=$prevenir field="nom"}}
            </td>

            <td class="text" colspan="3">
                <strong>{{mb_label object=$prevenir field="adresse"}}</strong>
                {{mb_value object=$prevenir field="adresse"}}
            </td>
        </tr>
        <tr>
            <td class="text">
                <strong>{{mb_label object=$prevenir field="prenom"}}</strong>
                {{mb_value object=$prevenir field="prenom"}}
            </td>

            <td class="text" colspan="3">
                <strong>{{mb_label object=$prevenir field="cp"}}</strong>
                {{mb_value object=$prevenir field="cp"}}
            </td>
        </tr>
        <tr>
            <td class="text">
                <strong>{{mb_label object=$prevenir field="tel"}}</strong>
                {{if $prevenir->tel}}
                    Fixe : {{mb_value object=$prevenir field="tel"}}
                {{/if}}
                {{if $prevenir->mob}}
                    Portable : {{mb_value object=$prevenir field="mob"}}
                {{/if}}
            </td>

            <td class="text" colspan="3">
                <strong>{{mb_label object=$prevenir field="ville"}}</strong>
                {{mb_value object=$prevenir field="ville"}}
            </td>
        </tr>
        <tr>
            <td class="text">
                <strong>{{mb_label object=$prevenir field="parente"}}</strong>
                {{mb_value object=$prevenir field="parente"}}
            </td>

            <td class="text" colspan="3">
                <strong>Remarques</strong>
                {{$prevenir->remarques}}
            </td>
        </tr>
    {{/foreach}}

    {{foreach from=$object->_ref_cp_by_relation.confiance item=confiance}}
        <tr>
            <th class="category" colspan="3">Personne de confiance</th>
        </tr>
        <tr>
            <td class="text">
                <strong>{{mb_label object=$confiance field="nom"}}</strong>
                {{mb_value object=$confiance field="nom"}}
            </td>

            <td class="text" colspan="2">
                <strong>{{mb_label object=$confiance field="adresse"}}</strong>
                {{mb_value object=$confiance field="adresse"}}
            </td>
        </tr>
        <tr>
            <td class="text">
                <strong>{{mb_label object=$confiance field="prenom"}}</strong>
                {{mb_value object=$confiance field="prenom"}}
            </td>

            <td class="text" colspan="2">
                <strong>{{mb_label object=$confiance field="cp"}}</strong>
                {{mb_value object=$confiance field="cp"}}
            </td>
        </tr>
        <tr>
            <td class="text">
                <strong>{{mb_label object=$confiance field="tel"}}</strong>
                {{if $confiance->tel}}
                    Fixe : {{mb_value object=$confiance field="tel"}}
                {{/if}}
                {{if $confiance->mob}}
                    Portable : {{mb_value object=$confiance field="mob"}}
                {{/if}}
            </td>

            <td class="text" colspan="2">
                <strong>{{mb_label object=$confiance field="ville"}}</strong>
                {{mb_value object=$confiance field="ville"}}
            </td>
        </tr>
        <tr>
            <td class="text">
                <strong>{{mb_label object=$confiance field="parente"}}</strong>
                {{mb_value object=$confiance field="parente"}}
            </td>

            <td class="text" colspan="2">
                <strong>Remarques</strong>
                {{$confiance->remarques}}
            </td>
        </tr>
    {{/foreach}}

    <tr>
        <th class="category" colspan="4">Bénéficiaire de soins</th>
    </tr>

    <tr>
        {{if $patient->_ref_patient_ins_nir && $patient->_ref_patient_ins_nir->datamatrix_ins && $patient->status == "QUAL"}}
            <td>
                <strong>{{tr}}CINSPatient{{/tr}}</strong>
                {{mb_value object=$patient->_ref_patient_ins_nir field=ins_nir}}
                ({{$patient->_ref_patient_ins_nir->_ins_type}})
            </td>
        {{else}}
            <td class="text">
                <strong>{{mb_label object=$object field="matricule"}}</strong>
                {{mb_value object=$object field="matricule"}}
            </td>
        {{/if}}

        <td class="text">
            <strong>{{mb_label object=$object field="c2s"}}</strong>

            {{if $object->c2s}}
                {{if $object->fin_amo}}
                    jusqu'au {{mb_value object=$object field="fin_amo"}}
                {{else}}
                    Oui
                {{/if}}
            {{else}}
                Non
            {{/if}}
        </td>

        <td class="text" colspan="2">
            <strong>{{mb_label object=$object field=ame}}</strong>
            {{mb_value object=$object field=ame}}
        </td>
    </tr>

    <tr>
        <td class="text">
            <strong>{{mb_label object=$object field="regime_sante"}}</strong>
            {{mb_value object=$object field="regime_sante"}}
        </td>

        <td class="text" colspan="3">
            <strong>{{mb_label object=$object field="notes_amo"}}</strong>
            {{mb_value object=$object field="notes_amo"}}
            <strong>{{mb_label object=$object field="notes_amc"}}</strong>
            {{mb_value object=$object field="notes_amc"}}
        </td>
    </tr>

    <tr>
        <td class="text">
            <strong>{{mb_label object=$object field="medecin_traitant"}}</strong>
            {{assign var=medecin value=$object->_ref_medecin_traitant}}
            {{if $object->medecin_traitant_declare === "0" && !$medecin->_id}}
                <br/>
                <span class="empty">
          {{tr}}CPatient-Patient doesnt have a GP{{/tr}}
        </span>
            {{elseif $medecin->_id}}
                <span onmouseover="ObjectTooltip.createEx(this, '{{$medecin->_guid}}');">
          {{$medecin}}
        </span>
            {{else}}
                <br/>
                <span class="empty">
          {{tr}}CMediusers-back-medecin.empty{{/tr}}
        </span>
            {{/if}}
        </td>

        <td class="text" colspan="3">
            <strong>Correspondants médicaux</strong>
            {{foreach from=$object->_ref_medecins_correspondants item=curr_corresp}}
                {{assign var=medecin value=$curr_corresp->_ref_medecin}}
                <span onmouseover="ObjectTooltip.createEx(this, '{{$medecin->_guid}}');">
          {{$medecin}}
        </span>
                <br/>
                {{foreachelse}}
                <div class="empty">{{tr}}CCorrespondant.none{{/tr}}</div>
            {{/foreach}}
        </td>
    </tr>

    <!-- Dossier Médical -->
    {{if $object->_ref_dossier_medical->_canRead && $object->_ref_dossier_medical->_id && !$app->user_prefs.limit_prise_rdv && $show_dossier}}
        <tr>
            <td colspan="4">
                {{mb_include module=patients template=CDossierMedical_complete object=$object->_ref_dossier_medical}}
            </td>
        </tr>
    {{/if}}

    {{if "dPpatients sharing patient_data_sharing"|gconf && !$app->user_prefs.limit_prise_rdv && $object->_sharing_groups}}
        <tr>
            <th class="category" colspan="3">
                {{tr}}CPatientGroup-action-Data sharing{{/tr}}
            </th>
        </tr>
        <tr>
            <th class="section">{{tr}}CPatientGroup-Share allowed{{/tr}}</th>
            <th class="section">{{tr}}CPatientGroup-Share denied{{/tr}}</th>
            <th class="section">{{tr}}CPatientGroup-Share unknown{{/tr}}</th>
        </tr>
        <tr>
            <td style="width: 33%;">
                {{foreach from=$object->_sharing_groups.allowed item=_patient_group}}
                    <div>
                        {{$_patient_group->_ref_group}}
                        <span
                          class="compact">({{$_patient_group->_ref_user->_shortview}} &bull; {{mb_value object=$_patient_group field=last_modification}})</span>
                    </div>
                {{/foreach}}
            </td>

            <td style="width: 33%;">
                {{foreach from=$object->_sharing_groups.denied item=_patient_group}}
                    <div>
                        {{$_patient_group->_ref_group}}
                        <span
                          class="compact">({{$_patient_group->_ref_user->_shortview}} &bull; {{mb_value object=$_patient_group field=last_modification}})</span>
                    </div>
                {{/foreach}}
            </td>

            <td style="width: 33%;">
                {{foreach from=$object->_sharing_groups.unknown item=_group}}
                    <div>
                        {{$_group}}
                        <span class="compact">(<i class="fa fa-ban" style="color: firebrick;"></i>)</span>
                    </div>
                {{/foreach}}
            </td>
        </tr>
    {{/if}}
</table>
