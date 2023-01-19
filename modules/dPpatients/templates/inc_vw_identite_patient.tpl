{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=tooltip value=0}}
{{mb_default var=rgpd_manager value=null}}

{{if "dmp"|module_active}}
  {{mb_script module="dmp" script="cdmp" ajax="true"}}
{{/if}}

{{if "appFineClient"|module_active}}
  {{mb_script module="appFineClient" script="appFineClient" ajax="true"}}
{{/if}}

{{if "dPpatients sharing patient_data_sharing"|gconf}}
  {{mb_script module=dPpatients script=patient_group ajax=true}}
{{/if}}

<script>
  ObjectTooltip.modes.tooltipMedecin = {
    module: 'patients',
    action: 'tooltipMedecin',
    sClass: 'tooltip'
  };
</script>

<table class="form me-align-auto me-margin-bottom-8">
  <tr {{if $patient->deces}}class="hatching"{{/if}}>
    <th class="title text" colspan="5">
      {{mb_include module=dPpatients template=inc_view_ins_patient patient=$patient}}
      {{mb_include module=patients template=inc_status_icon float=right}}

      {{mb_include module=system template=inc_object_idsante400 object=$patient}}
      {{mb_include module=system template=inc_object_history object=$patient}}
      {{mb_include module=system template=inc_object_notes object=$patient}}
      {{$patient}} {{mb_include module=patients template=inc_vw_ipp ipp=$patient->_IPP}}

    </th>
  </tr>
  {{if !$tooltip}}
    <tr>
      <th class="category" colspan="3" style="width: 50%;">
        Identité
      </th>
      <th class="category" colspan="2" style="width: 50%;">Coordonnées</th>
    </tr>
  {{/if}}

  <tr>
    <td rowspan="4" class="narrow" style="vertical-align: middle; text-align: center;">
      {{mb_include module=patients template=inc_vw_photo_identite mode="read" size="64"}}
    </td>

    <th class="me-color-black-medium-emphasis">{{mb_label object=$patient field="nom_jeune_fille"}}</th>
    <td>{{mb_value object=$patient field="nom_jeune_fille"}}</td>

    <th class="me-color-black-medium-emphasis" rowspan="3">{{mb_label object=$patient field="adresse"}}</th>
    <td rowspan="2" class="text">{{mb_value object=$patient field="adresse"}}</td>
  </tr>

  <tr>
    <th class="me-color-black-medium-emphasis">{{mb_label object=$patient field="prenom"}}</th>
    <td>{{mb_value object=$patient field="prenom"}}</td>
  </tr>

  <tr>
    <th class="me-color-black-medium-emphasis">{{mb_label object=$patient field="prenoms"}}</th>
    <td>{{mb_value object=$patient field="prenoms"}}</td>
  </tr>

  <tr>
    <th class="me-color-black-medium-emphasis">{{mb_label object=$patient field="prenom_usuel"}}</th>
    <td>{{mb_value object=$patient field="prenom_usuel"}}</td>

    <th class="me-color-black-medium-emphasis">{{mb_label object=$patient field="ville"}}</th>
    <td>
        {{mb_value object=$patient field="cp"}}
        {{mb_value object=$patient field="ville"}}
    </td>
  </tr>

  <tr>
    <th class="me-color-black-medium-emphasis" colspan="2">{{mb_label object=$patient field="nom"}}</th>
    <td>{{mb_value object=$patient field="nom"}}</td>

    <th class="me-color-black-medium-emphasis">{{mb_label object=$patient field="tel"}}</th>
    <td>{{mb_value object=$patient field="tel"}}</td>
  </tr>

  <tr>
    <th class="me-color-black-medium-emphasis" colspan="2">{{mb_label object=$patient field="naissance"}}</th>
    <td>{{mb_value object=$patient field="naissance"}}</td>

    <th class="me-color-black-medium-emphasis">{{mb_label object=$patient field="tel2"}}</th>
    <td>{{mb_value object=$patient field="tel2"}}</td>
  </tr>

  <tr>
    <th class="me-color-black-medium-emphasis" colspan="2">{{mb_label object=$patient field="_age"}}</th>
    <td>
      {{mb_value object=$patient field="_age"}}
    </td>

    <th class="me-color-black-medium-emphasis">{{mb_label object=$patient field="tel_autre"}}</th>
    <td>{{mb_value object=$patient field="tel_autre"}}</td>
  </tr>

  <tr>
    <th class="me-color-black-medium-emphasis" colspan="2">{{mb_label object=$patient field="sexe"}}</th>
    <td>{{mb_value object=$patient field="sexe"}}</td>

    <th class="me-color-black-medium-emphasis">{{mb_label object=$patient field="email"}}</th>
    <td>{{mb_value object=$patient field="email"}}</td>
  </tr>

  {{if $patient->deces}}
    <tr>
      <th class="me-color-black-medium-emphasis" colspan="2">{{mb_label object=$patient field="deces"}}</th>
      <td colspan="3">{{mb_value object=$patient field="deces"}}</td>
    </tr
  {{/if}}

  <tr>
    <th class="me-color-black-medium-emphasis" colspan="2">{{mb_label object=$patient field="lieu_naissance"}}</th>
    <td>
      {{mb_include module=patients template=inc_lieu_naissance object=$patient}}
    </td>
  </tr>

  <tr>
    {{if $patient->_ref_patient_ins_nir->_id}}
      <th class="me-color-black-medium-emphasis" colspan="2">{{tr}}CPatient-_matricule-ins{{/tr}}</th>
      <td>
        {{mb_value object=$patient->_ref_patient_ins_nir field="ins_nir"}}
        ({{mb_value object=$patient->_ref_patient_ins_nir field="_ins_type"}})
      </td>
    {{else}}
      <th class="me-color-black-medium-emphasis" colspan="2">{{mb_label object=$patient field="matricule"}}</th>
      <td>{{mb_value object=$patient field="matricule"}}</td>
    {{/if}}
    <th class="me-color-black-medium-emphasis">{{mb_label object=$patient field="rques"}}</th>
    <td class="text">
        {{mb_value object=$patient field="rques"}}
    </td>
  </tr>

  <tr>
    <td class="button me-text-align-right" colspan="10">
      <span class="me-inline-block">
        <button type="button" class="search me-primary me-float-right" onclick="Patient.view('{{$patient->_id}}')">
          {{tr}}dPpatients-CPatient-Dossier_complet{{/tr}}
        </button>

        {{if "dPpatients sharing patient_data_sharing"|gconf}}
          {{me_button label="CPatientGroup-action-Data sharing" icon=share onclick="PatientGroup.viewGroups('`$patient->_id`')"}}
        {{/if}}

        <!-- Dossier résumé -->
        <button class="search me-float-right"
                onclick="new Url('dPcabinet', 'vw_resume').addParam('patient_id', '{{$patient->_id}}').popup(800, 500, '{{tr}}Summary{{/tr}}');">
          {{tr}}Summary{{/tr}}
        </button>

        {{me_button label=Print icon=print onclick="Patient.print('`$patient->_id`')"}}

        {{mb_include module=patients template=inc_button_vue_globale_docs patient_id=$patient->_id object=$patient display_center=0 float_right=0 add_class="me-float-right"}}

        {{if $canPatients->edit}}
          {{mb_default var=useVitale value=0}}
          <button type="button" class="edit me-float-right me-tertiary" onclick="Patient.edit('{{$patient->_id}}', '{{$useVitale}}')">
            {{tr}}Modify{{/tr}}
          </button>
        {{/if}}

        {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
          {{mb_include module="appFineClient" template="inc_create_account_appFine"}}
        {{/if}}

        {{if "dmp"|module_active}}
          {{mb_include module=dmp template=inc_button_dmp}}
        {{/if}}

      {{if $can->admin && $app->_ref_user->isAdmin()}}
          <form name="Purge-{{$patient->_guid}}" action="?m={{$m}}&tab=vw_idx_patients" method="post">
            <input type="hidden" name="dosql" value="do_patients_aed" />
            <input type="hidden" name="tab" value="vw_idx_patients" />
            <input type="hidden" name="del" value="0" />
            <input type="hidden" name="_purge" value="0" />
            <input type="hidden" name="patient_id" value="{{$patient->_id}}" />

            <script>
              confirmPurge = function (form) {
                if (confirm("ATTENTION : Vous êtes sur le point de purger le dossier d'un patient !")) {
                  form._purge.value = "1";
                  confirmDeletion(form, {
                    typeName: 'le patient',
                    objName:  '{{$patient->_view|smarty:nodefaults|JSAttribute}}'
                  });
                }
              }
            </script>
          </form>
          {{me_button label=Purge icon=cancel onclick="confirmPurge(getForm('Purge-`$patient->_guid`'))"}}
        {{/if}}

        {{if $app->user_prefs.vCardExport}}
          {{me_button label=Export icon=vcard onclick="Patient.exportVcard('`$patient->_id`')"}}
        {{/if}}
      </span>
      <span class="me-float-right">
        {{me_dropdown_button button_label=Options button_icon=opt button_class="notext me-tertiary"
                              container_class="me-dropdown-button-right"}}
      </span>
    </td>
  </tr>

  {{if ($patient->medecin_traitant || $patient->_ref_medecins_correspondants|@count)}}
    <tr>
      <th class="category" colspan="5">{{tr}}CFunctions-back-medecins_function{{/tr}}</th>
    </tr>
    <tr>
      <td colspan="5" class="text">
        {{assign var=medecin value=$patient->_ref_medecin_traitant}}
        {{if $medecin->_id}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$medecin->_guid}}'{{if isset($patient->_ref_medecin_traitant_exercice_place->_id|smarty:nodefaults)}}, 'tooltipMedecin', {medecin_id: '{{$medecin->_id}}', medecin_exercice_place_id: '{{$patient->medecin_traitant_exercice_place_id}}'}{{/if}});">
          <strong>{{$medecin}}</strong> ;
        </span>
        {{else}}
          {{if $patient->medecin_traitant_declare === "0"}}
            <strong>{{tr}}CPatient-No doctor{{/tr}}</strong>;
          {{/if}}
        {{/if}}
        {{foreach from=$patient->_ref_medecins_correspondants item=curr_corresp}}
          {{assign var=medecin value=$curr_corresp->_ref_medecin}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$medecin->_guid}}'{{if isset($curr_corresp->_ref_medecin_exercice_place->_id|smarty:nodefaults)}}, 'tooltipMedecin', {medecin_id: '{{$medecin->_id}}', medecin_exercice_place_id: '{{$curr_corresp->medecin_exercice_place_id}}'}{{/if}});">
            {{$medecin}} ;
          </span>
        {{/foreach}}
      </td>
    </tr>
  {{/if}}

  {{if $rgpd_manager && $rgpd_manager->isEnabledFor($patient)}}
    {{mb_script module=admin script=rgpd ajax=true}}

    <tr>
      <th class="category" colspan="5">
        {{tr}}CRGPDConsent{{/tr}}

        {{if !$patient->_rgpd_consent || !$patient->_rgpd_consent->_id}}
          <button type="button" class="notext fa fa-upload" onclick="RGPD.addConsent('{{$patient->_class}}', '{{$patient->_id}}');">
            {{tr}}CRGPDConsent-action-Manage{{/tr}}
          </button>
        {{/if}}
      </th>
    </tr>

    <tr>
      {{if $patient->_rgpd_consent && $patient->_rgpd_consent->_id}}
        <td colspan="5">
          {{mb_include module=admin template=inc_rgpd_consent_view consent=$patient->_rgpd_consent}}
        </td>
      {{else}}
        <td colspan="5" class="empty">
          {{tr}}CRGPDConsent.none{{/tr}}
        </td>
      {{/if}}
    </tr>
  {{/if}}
</table>
