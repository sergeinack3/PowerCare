{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
    <div class="small-info">
        {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
    </div>
    {{mb_return}}
{{/if}}

{{if "dmp"|module_active}}
    {{mb_script module=dmp script=cdmp register=true}}
{{/if}}
{{mb_script module=patients script=evenement_patient register=true}}
{{mb_script module=hospi    script=modele_etiquette  register=true}}
{{if "oxCabinet"|module_active}}
    {{mb_script module=oxCabinet script=TdBTamm ajax=$ajax}}
{{/if}}

<script>
  ObjectTooltip.modes.tooltipMedecin = {
    module: 'patients',
    action: 'tooltipMedecin',
    sClass: 'tooltip'
  };
</script>

{{assign var="patient" value=$object}}
<table class="tbl tooltip">
    <tr>
        <th class="title text" colspan="3">
            {{mb_include module=patients template=inc_view_ins_patient patient=$patient}}

            {{mb_include module=system template=inc_object_idsante400 object=$patient}}
            {{mb_include module=system template=inc_object_history object=$patient}}
            {{mb_include module=system template=inc_object_notes object=$patient}}
            {{$patient}}
          {{mb_include module=patients template=inc_vw_ipp ipp=$patient->_IPP}}
          {{mb_include module=patients template=inc_status_icon float='right'}}
        </th>
    </tr>
    <tr>
        <td rowspan="6" style="width: 1px;" class="me-valign-middle me-text-align-center">
            {{mb_include module=patients template=inc_vw_photo_identite mode=read patient=$patient size=50}}
        </td>
    </tr>
    <tr>
      <td>
          {{mb_label object=$patient field="nom_jeune_fille"}} : {{mb_value object=$patient field="nom_jeune_fille"}} {{mb_include module=patients template=inc_icon_bmr_bhre}}
      </td>
      {{if $patient->nom}}
      <td>
          {{mb_label object=$patient field="nom"}} : {{mb_value object=$patient field="nom"}}
      </td>
      {{/if}}
    </tr>
    <tr>
      <td>
          {{mb_label object=$patient field="prenom"}} : {{mb_value object=$patient field="prenom"}}
      </td>
      <td>
          {{mb_label object=$patient field="prenom_usuel"}} :
          {{mb_value object=$patient field="prenom_usuel"}}
      </td>
    </tr>
    {{if $patient->prenoms}}
    <tr>
      <td>
          {{mb_label object=$patient field="prenoms"}} : {{mb_value object=$patient field="prenoms"}} <br />
      </td>
      <td>
          {{mb_label object=$patient field="sexe"}} :
          {{mb_value object=$patient field="sexe"}}
      </td>
    </tr>
    {{/if}}
    <tr>
      <td>
          {{mb_label object=$patient field=_age}} : {{mb_value object=$patient field=_age}} ({{mb_value object=$patient field=naissance}})
      </td>
      <td>
          {{if $patient->lieu_naissance || $patient->pays_naissance_insee}}
              {{mb_label object=$patient field="lieu_naissance"}} : {{mb_value object=$patient field="lieu_naissance"}}
              {{if $patient->cp_naissance}}({{mb_value object=$patient field="cp_naissance"}}){{/if}}
              {{if $patient->pays_naissance_insee}}
                  {{assign var=pays value='Ox\Mediboard\Patients\CPaysInsee::getPaysByNumerique'|static_call:$patient->pays_naissance_insee}}
                - {{$pays->nom_fr}}
              {{/if}}
          {{/if}}
      </td>
    </tr>
    <tr>
        {{if $patient->_ref_patient_ins_nir->_id}}
            <td>
                {{tr}}CPatient-_matricule-ins{{/tr}} :
                {{mb_value object=$patient->_ref_patient_ins_nir field="ins_nir"}}
                ({{mb_value object=$patient->_ref_patient_ins_nir field="_ins_type"}}) <a title = "{{tr}}dPpatients-msg-click here to copy the number{{/tr}}" class="button copy notext me-tertiary me-btn-small me-bg-white" onclick="navigator.clipboard.writeText('{{$patient->_ref_patient_ins_nir->ins_nir}}')"></a>
            </td>
        {{else}}
            <td>
                {{mb_label object=$patient field="matricule"}} :
                {{mb_value object=$patient field="matricule"}} <a title = "{{tr}}dPpatients-msg-click here to copy the number{{/tr}}" class="button copy notext me-tertiary me-btn-small me-bg-white" onclick="navigator.clipboard.writeText('{{$patient->matricule}}')"></a>
            </td>
        {{/if}}
        <td>
            {{if $patient->pays_naissance_insee == 250}}
                {{mb_label object=$patient field="commune_naissance_insee"}} :
            {{else}}
                {{mb_label object=$patient field="pays_naissance_insee"}} :
            {{/if}}
            {{mb_value object=$patient field="_code_insee"}}
        </td>
    </tr>
    <tr>
      <td class="email-nowrap">
          {{mb_label object=$patient field="email"}} : {{mb_value object=$patient field="email"}}
      </td>
      <td>
          {{mb_label object=$patient field="allow_email"}} :
          {{mb_value object=$patient field="allow_email"}}
      </td>
    </tr>
    <tr>
      <td colspan="2">
          {{mb_label object=$patient field="tel"}} :
          {{mb_value object=$patient field="tel"}}
      </td>
      <td>
          {{mb_label object=$patient field="allow_sms_notification"}} :
          {{mb_value object=$patient field="allow_sms_notification"}}
      </td>
    </tr>
    <tr>
        <td colspan="2" class="me-valign-top">
            {{mb_label object=$patient field="tel2"}} :
            {{mb_value object=$patient field="tel2"}}
        </td>
        <td class="text me-valign-top" rowspan="2">
            {{mb_label object=$patient field="rques"}} :
            {{mb_value object=$patient field="rques"}}
        </td>
    </tr>
    <tr>
      <td colspan="2">
          {{mb_label object=$patient field="adresse"}} : {{$patient->adresse}} {{mb_value object=$patient field="cp"}} {{mb_value object=$patient field="ville"}}
      </td>
    </tr>
    {{if "oxCabinet"|module_active}}
        {{if $patient->_can->edit}}
            {{mb_include module=oxCabinet template=inc_patient_tooltip_actions}}
        {{/if}}
    {{else}}
        <tr>
            <td colspan="3" class="button">
                {{mb_script module=patients script=patient register=true}}

                {{if $object->_can->edit && (!$patient->_ref_last_verrou_dossier->_id || $patient->_ref_last_verrou_dossier->annule)}}
                    <button type="button" class="edit" onclick="Patient.editModal('{{$patient->_id}}')">
                        {{tr}}Modify{{/tr}}
                    </button>
                {{/if}}

                {{if $app->_ref_user->isMedical()}}
                    <button class="edit"
                            onclick="Patient.showDossierMedical('{{$patient->_id}}');">{{tr}}CDossierMedical-ATCD/TP{{/tr}}</button>
                {{/if}}

                <button type="button" class="print" onclick="Patient.print('{{$patient->_id}}')">
                    {{tr}}Print{{/tr}}
                </button>

                {{if "dPhospi"|module_active && $modules.dPhospi->_can->read && $patient->_count_modeles_etiq}}
                    <button type="button" class="print"
                      {{if $patient->_count_modeles_etiq == 1}}
                        onclick="ModeleEtiquette.print('{{$patient->_class}}', '{{$patient->_id}}');"
                      {{else}}
                        onclick="ModeleEtiquette.chooseModele('{{$patient->_class}}', '{{$patient->_id}}')"
                      {{/if}}>
                        {{tr}}CModeleEtiquette-court{{/tr}}
                    </button>
                {{/if}}

                {{if !$patient->_ref_last_verrou_dossier->_id || $patient->_ref_last_verrou_dossier->annule}}
                    <button type="button" class="search"
                            onclick="EvtPatient.showEvenementsPatient('{{$patient->_id}}', null, false);">
                        {{tr}}CEvenementPatient|pl{{/tr}}
                    </button>
                {{/if}}

                <button type="button" class="search" onclick="Patient.view('{{$patient->_id}}')">
                    {{tr}}dPpatients-CPatient-Dossier_complet{{/tr}}
                </button>

                <!-- Dossier résumé -->
                <button class="search" onclick="Patient.showSummary('{{$patient->_id}}')">
                    {{tr}}Summary{{/tr}}
                </button>

                {{if $app->user_prefs.vCardExport}}
                    <button type="button" class="vcard" onclick="Patient.exportVcard('{{$patient->_id}}')">
                        {{tr}}Export{{/tr}}
                    </button>
                {{/if}}

                {{if "dmp"|module_active}}
                    {{mb_include module=dmp template=inc_button_dmp}}
                {{/if}}

                {{if $can->admin && $app->_ref_user->isAdmin()}}
                    <form name="Purge-{{$patient->_guid}}" action="?m={{$m}}&tab={{$tab}}" method="post">
                        <input type="hidden" name="m" value="patients"/>
                        <input type="hidden" name="dosql" value="do_patients_aed"/>
                        <input type="hidden" name="del" value="0"/>
                        <input type="hidden" name="_purge" value="0"/>
                        <input type="hidden" name="patient_id" value="{{$patient->_id}}"/>

                        <button type="button" class="cancel"
                                onclick="Patient.confirmPurge(this.form, '{{$patient->_view|smarty:nodefaults|JSAttribute}}');">
                            {{tr}}Purge{{/tr}}
                        </button>
                    </form>
                {{/if}}

                {{if "instanceContexte"|module_active && $patient->_ref_instance_contextes}}
                    {{mb_include module=instanceContexte template=inc_patient_button}}
                {{/if}}
            </td>
        </tr>
    {{/if}}

    {{if ($patient->medecin_traitant || $patient->_ref_medecins_correspondants|@count) || $patient->_ref_correspondants_patient|@count}}
        <tr>
            {{if ($patient->medecin_traitant || $patient->_ref_medecins_correspondants|@count)}}
                <th class="category" colspan="2">{{tr}}CPatient-part-correspondants-medicaux{{/tr}}</th>
            {{/if}}
            {{if $patient->_ref_correspondants_patient|@count}}
                <th class="category" colspan="2">{{tr}}CPatient-part-correspondants-patients{{/tr}}</th>
            {{/if}}
        </tr>
        <tr>
            {{if ($patient->medecin_traitant || $patient->_ref_medecins_correspondants|@count)}}
                <td colspan="2" class="text">
                    {{assign var=medecin value=$patient->_ref_medecin_traitant}}
                    {{if $medecin->_id}}
                        <strong>
                            {{if $patient->_ref_medecin_traitant_exercice_place->_id}}
                                <span onmouseover="ObjectTooltip.createEx(this, '{{$medecin->_guid}}', 'tooltipMedecin', {medecin_id: '{{$medecin->_id}}', medecin_exercice_place_id: '{{$patient->medecin_traitant_exercice_place_id}}'});">
                            {{$medecin->_view}}
                          </span>
                            {{else}}
                                {{mb_value object=$medecin}}
                            {{/if}}
                        </strong>
                        <br/>
                    {{/if}}
                    {{foreach from=$patient->_ref_medecins_correspondants item=curr_corresp}}
                        {{assign var=medecin value=$curr_corresp->_ref_medecin}}
                        {{if $curr_corresp->_ref_medecin_exercice_place->_id}}
                            <span onmouseover="ObjectTooltip.createEx(this, '{{$medecin->_guid}}', 'tooltipMedecin', {medecin_id: '{{$medecin->_id}}', medecin_exercice_place_id: '{{$curr_corresp->medecin_exercice_place_id}}'});">
                        {{$medecin->_view}}
                      </span>
                        {{else}}
                            {{mb_value object=$curr_corresp->_ref_medecin}}
                        {{/if}}
                        <br/>
                    {{/foreach}}
                </td>
            {{/if}}
            {{if $patient->_ref_correspondants_patient|@count}}
                <td colspan="2" class="text">
                {{foreach from=$patient->_ref_correspondants_patient item=corr_patient}}
                    <span><strong>{{$corr_patient->_view}} : </strong></span>
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$corr_patient->_guid}}')">{{$corr_patient->nom}} {{$corr_patient->prenom}}</span>
                    <br/>
                {{/foreach}}
                </td>
            {{/if}}
        </tr>
    {{/if}}

    {{if "addictologie"|module_active && $modules.addictologie->_can->read && $patient->_ref_last_dossier_addictologie}}
        {{assign var=last_dossier_addictologie value=$patient->_ref_last_dossier_addictologie}}
        {{assign var=referent_user             value=$last_dossier_addictologie->_ref_referent_user}}
        {{assign var=pathologies               value=$last_dossier_addictologie->_ref_pathologies_addictologie}}
        {{assign var=suivis                    value=$last_dossier_addictologie->_ref_suivis_addictologie}}
        <tr>
            <th class="category"
                colspan="3">{{tr}}CDossierAddictologie-msg-Information from the latest addictology file{{/tr}}</th>
        </tr>
        <tr>
            <td colspan="2">
                {{mb_label object=$last_dossier_addictologie field=sejour_id}} :
                {{mb_value object=$last_dossier_addictologie field=sejour_id tooltip=true}}
            </td>
            <td colspan="2">
                {{mb_label object=$last_dossier_addictologie field=referent_user_id}} :
                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$referent_user}}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                {{mb_label object=$last_dossier_addictologie field=convention}} :
                {{mb_value object=$last_dossier_addictologie field=convention}}
            </td>
            <td colspan="2">
                {{mb_label object=$last_dossier_addictologie field=cas_particulier}} :
                {{mb_value object=$last_dossier_addictologie field=cas_particulier}}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                {{mb_label object=$last_dossier_addictologie field=suivi_social}} :
                {{mb_value object=$last_dossier_addictologie field=suivi_social}}
            </td>
        </tr>
        {{if $pathologies|@count}}
            <tr>
                <td colspan="1">
                    <strong>{{tr}}CMediusers-back-pathologies{{/tr}}</strong>
                </td>
                <td colspan="3">
                    {{foreach from=$pathologies item=_pathologie}}
                        {{assign var=type_pathologie  value=$_pathologie->_ref_type_pathologie}}
                        {{assign var=motif_pathologie value=$_pathologie->_ref_motif_fin_pathlogie}}
                        <span onmouseover="ObjectTooltip.createEx(this, '{{$_pathologie->_guid}}')">
              {{$type_pathologie}}
            </span>
                        {{if $_pathologie->debut && !$_pathologie->fin}}
                            ({{tr var1=$_pathologie->debut|date_format:$conf.date}}common-Beginning on %s{{/tr}})
                            <br/>
                        {{elseif $_pathologie->debut && $_pathologie->fin}}
                            ({{tr var1=$_pathologie->debut|date_format:$conf.date var2=$_pathologie->fin|date_format:$conf.date}}common-From %s to %s{{/tr}})

                            {{if $motif_pathologie && $motif_pathologie->_id}}
                                - {{tr}}CPathologieDossierAddictologie-motif_fin_pathlogie_id-court{{/tr}} : {{$motif_pathologie}}
                            {{/if}}
                            <br/>
                        {{/if}}
                    {{/foreach}}
                </td>
            </tr>
        {{/if}}
        {{if $suivis|@count}}
            <tr>
                <td colspan="1">
                    <strong>{{tr}}CMediusers-back-suivis{{/tr}}</strong>
                </td>
                <td colspan="3">
                    {{foreach from=$suivis item=_suivi}}
                        {{assign var=type_suivi  value=$_suivi->_ref_type_suivi_addiction}}
                        <span onmouseover="ObjectTooltip.createEx(this, '{{$_suivi->_guid}}')">
              {{$type_suivi}}
            </span>
                        {{if $_suivi->date_debut && !$_suivi->date_fin}}
                            ({{tr var1=$_suivi->date_debut|date_format:$conf.date}}common-Beginning on %s{{/tr}})
                            <br/>
                        {{elseif $_suivi->date_debut && $_suivi->date_fin}}
                            ({{tr var1=$_suivi->date_debut|date_format:$conf.date var2=$_suivi->date_fin|date_format:$conf.date}}common-From %s to %s{{/tr}})
                            <br/>
                        {{/if}}
                    {{/foreach}}
                </td>
            </tr>
        {{/if}}
    {{/if}}

    {{if $patient && $patient->_rgpd_manager && $patient->_rgpd_manager->isEnabledFor($patient)}}
        {{mb_script module=admin script=rgpd register=true}}
        <tr>
            <th class="category" colspan="3">
                {{tr}}CRGPDConsent{{/tr}}

                {{if !$patient->_rgpd_consent || !$patient->_rgpd_consent->_id}}
                    <button type="button" class="notext fa fa-upload"
                            onclick="RGPD.addConsent('{{$patient->_class}}', '{{$patient->_id}}');">
                        {{tr}}CRGPDConsent-action-Upload{{/tr}}
                    </button>
                {{/if}}
            </th>
        </tr>
        <tr>
            {{if $patient->_rgpd_consent && $patient->_rgpd_consent->_id}}
                <td colspan="3">
                    {{mb_include module=admin template=inc_rgpd_consent_view consent=$patient->_rgpd_consent}}
                </td>
            {{else}}
                <td colspan="3" class="empty">
                    {{tr}}CRGPDConsent.none{{/tr}}
                </td>
            {{/if}}
        </tr>
    {{/if}}
</table>
