{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPcompteRendu script=document}}
{{mb_default var=show_dossier value=true}}
{{mb_default var=show_sejour value=true}}
{{mb_default var=print value=false}}


<style>
  .patient-forms.forms-hide {
    display: none;
  }
</style>

<script>
    popFile = function(objectClass, objectId, elementClass, elementId) {
        var url = new Url().ViewFilePopup(objectClass, objectId, elementClass, elementId, 0);
        return false;
    }

    newExam = function(sAction, consultation_id) {
        if (sAction) {
            new Url("cabinet", sAction)
                .addParam("consultation_id", consultation_id)
                .popup(900, 600, "Examen");
        }
    }
</script>

<table
  style="width: 100%; border-spacing: 0; border-collapse: collapse; padding: 2px; vertical-align: middle; white-space: nowrap;">
    <thead>
    <tr>
        <td>
            <table class="tbl me-margin-bottom--3 me-no-border-bottom me-no-border-radius-bottom ">
                <tr>
                    <th colspan="10" class="title">
                        {{$patient->_view}} <br/>
                        {{tr var1=$patient->naissance|date_format:$conf.date }}CPatient-Born on %s{{/tr}}
                        ({{mb_value object=$patient field=_age}}) <br/>
                        {{if $patient->_ref_patient_ins_nir && $patient->_ref_patient_ins_nir->datamatrix_ins && $patient->status == "QUAL"}}
                            {{tr}}CINSPatient{{/tr}} : {{mb_value object=$patient->_ref_patient_ins_nir field=ins_nir}} ({{$patient->_ref_patient_ins_nir->_ins_type}})
                        {{else}}
                          {{mb_label object=$patient field=matricule}}: {{mb_value object=$patient field=matricule}}
                        {{/if}}
                    </th>
                    {{if $patient->_ref_patient_ins_nir && $patient->_ref_patient_ins_nir->datamatrix_ins}}
                      <th style="margin-top: 4em" class="title">
                        <div class="float: right">
                            {{mb_include module=dPpatients template=vw_datamatrix_ins}}
                        </div>
                      </th>
                    {{/if}}
                </tr>
            </table>
        </td>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>
            <table class="tbl me-margin-top-0 me-no-border-radius-top">
                <tr>
                    <th style="width: 33%;">{{tr}}CPatient-Patient file|pl{{/tr}}</th>
                    <th style="width: 33%;">{{tr}}CPatient-Patient document|pl{{/tr}}</th>
                    <th>{{tr}}CPatient-Important File / Document|pl{{/tr}}</th>
                </tr>
                <tr>
                    <td class="top">
                        {{mb_include module=files template=inc_list_docitems list=$patient->_ref_files_by_cat}}
                    </td>
                    <td class="top">
                        {{mb_include module=files template=inc_list_docitems list=$patient->_ref_documents_by_cat}}
                    </td>
                    <td class="top">
                        {{mb_include module=files template=inc_list_docitems list=$patient->_important_files_docs show_context=1}}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    {{if $show_dossier}}
        <tr>
            <td>
                <!-- Dossier Médical -->
                {{mb_include module=patients template=CDossierMedical_complete object=$patient->_ref_dossier_medical}}
            </td>
        </tr>
    {{/if}}
    <tr>
        <td>
            <table class="tbl">
                <tr>
                    <th colspan="4" class="title">
                        <div style="float:right">
                            <input type="checkbox" id="toggle_compta" onchange="$$('.compta').each(Element[($V('toggle_compta') ? 'show' : 'hide')]);"
                                   {{if $app->user_prefs.resumeCompta == 1}}checked="checked"{{/if}}
                            />
                            <label for="toggle_compta"
                                   title="{{tr}}CPatient-action-Show / hide accounting data|pl-desc{{/tr}}">{{tr}}CPatient-Accounting-court{{/tr}}</label>
                            {{if !$print}}
                                {{mb_include module=admin template=inc_inline_pref spec=bool key='dPpatients_show_forms_resume' type='checkbox'
                                name='toggle_forms' label='CExClass|pl' title='CExClass-action-Show/hide object|pl'
                                onclick="\$\$('.patient-forms').invoke('toggleClassName', 'forms-hide');"}}
                            {{/if}}
                        </div>

                        {{tr}}CConsultation|pl{{/tr}}
                    </th>
                </tr>

                <tr>
                    <th>{{tr}}common-Summary{{/tr}}</th>
                    <th>{{tr}}CPatient-part-documents{{/tr}}</th>
                    <th class="compta">
                        <label
                          title="{{tr}}CReglement-Patient and third party regulations, in red if not fully adjusted-desc{{/tr}}">
                            {{tr}}CReglement-Regulations Patient - Third party{{/tr}}
                        </label>
                    </th>

                    <th class="patient-forms {{if !$app->user_prefs.dPpatients_show_forms_resume}} forms-hide{{/if}}">
                        {{tr}}CExClass|pl{{/tr}}
                    </th>
                </tr>

                <!-- Consultations -->
                {{foreach from=$patient->_ref_consultations item=_consult}}
                    {{if !$_consult->annule}}
                        <tr>
                            <th class="section" colspan="4">
                                <div>
                                    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_consult->_ref_plageconsult->_ref_chir}}
                                    &mdash;
                                    {{$_consult->_datetime|date_format:"%A"}}
                                    {{$_consult->_datetime|date_format:$conf.datetime}}
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <td class="text top">
                                {{if $_consult->motif}}
                                    <strong>{{mb_label object=$_consult field=motif}}</strong>
                                    <div>{{mb_value object=$_consult field=motif}}</div>
                                {{/if}}

                                {{if "dPcabinet CConsultation show_projet_soins"|gconf && $_consult->projet_soins}}
                                    <strong>{{mb_label object=$_consult field=projet_soins}}</strong>
                                    <div>{{mb_value object=$_consult field=projet_soins}}</div>
                                {{/if}}

                                {{if $_consult->rques}}
                                    <strong>{{mb_label object=$_consult field=rques}}</strong>
                                    <div>{{mb_value object=$_consult field=rques}}</div>
                                {{/if}}

                                {{if $_consult->histoire_maladie && "dPcabinet CConsultation show_histoire_maladie"|gconf}}
                                  <strong>{{mb_label object=$_consult field=histoire_maladie}}</strong>
                                  <div>{{mb_value object=$_consult field=histoire_maladie}}</div>
                                {{/if}}

                                {{if $_consult->examen}}
                                    <strong>{{mb_label object=$_consult field=examen}}</strong>
                                    <div>{{mb_value object=$_consult field=examen}}</div>
                                {{/if}}

                                {{if $_consult->traitement}}
                                    <strong>{{mb_label object=$_consult field=traitement}}</strong>
                                    <div>{{mb_value object=$_consult field=traitement}}</div>
                                {{/if}}

                                {{if $_consult->conclusion && "dPcabinet CConsultation show_conclusion"|gconf}}
                                    <strong>{{mb_label object=$_consult field=conclusion}}</strong>
                                    <div>{{mb_value object=$_consult field=conclusion}}</div>
                                {{/if}}

                                {{if isset($_consult->_latest_constantes|smarty:nodefaults)}}
                                    {{assign var=_latest_constantes value=$_consult->_latest_constantes}}
                                    <strong>{{tr}}CConstantesMedicales-poids{{/tr}} :</strong>
                                    {{if $_latest_constantes->poids}} {{$_latest_constantes->poids}}  {{tr}}CPatient-unit Kg-court{{/tr}}{{else}}-{{/if}}
                                    <strong>{{tr}}CConstantesMedicales-taille{{/tr}} :</strong>
                                    {{if $_latest_constantes->taille}}{{$_latest_constantes->taille}} {{tr}}CPatient-unit cm-court{{/tr}}{{else}}-{{/if}}
                                    <strong>{{tr}}CConstantesMedicales-_imc{{/tr}} :</strong>
                                    {{if $_latest_constantes->_imc}}  {{$_latest_constantes->_imc}}     {{else}}-{{/if}}
                                {{/if}}

                                {{if $_consult->_ref_examaudio->_id}}
                                    <br/>
                                    <a href="#" onclick="newExam('exam_audio', {{$_consult->_id}})">
                                        <strong>{{tr}}CExamAudio-long{{/tr}}</strong>
                                    </a>
                                {{/if}}
                            </td>

                            <td class="top">
                                {{mb_include module=files template=inc_list_docitems list=$_consult->_refs_docitems_by_cat}}

                                {{if isset($_consult->_ref_prescriptions.externe|smarty:nodefaults)}}
                                    {{assign var=_prescription value=$_consult->_ref_prescriptions.externe}}
                                    {{foreach from=$_prescription->_ref_files item=_file}}
                                        <div>
                                            {{thumblink document=$_file class="button print notext"}}{{/thumblink}}
                                            <a href="#"
                                               onclick="return popFile('{{$_file->object_class}}','{{$_file->object_id}}','{{$_file->_class}}','{{$_file->_id}}')"
                                               style="display: inline-block;">
                                                {{$_file->file_name}}
                                            </a>
                                        </div>
                                    {{/foreach}}
                                {{/if}}
                            </td>

                            <td class="compta" style="text-align: center">
                                {{if $_consult->tarif}}
                                    {{if $_consult->du_patient}}
                                        <div>
                                            P:
                                            {{if !$_consult->_ref_facture->patient_date_reglement}}
                                                <span style="color: #f00;">
                          {{mb_value object=$_consult->_ref_facture field=_reglements_total_patient}}
                        </span>
                                            {{/if}}
                                            /
                                            {{mb_value object=$_consult field=du_patient}}
                                        </div>
                                    {{/if}}

                                    {{if $_consult->du_tiers}}
                                        <div>
                                            T:
                                            {{if !$_consult->_ref_facture->tiers_date_reglement}}
                                                <span style="color: #f00;">
                          {{mb_value object=$_consult->_ref_facture field=_reglements_total_tiers}}
                        </span>
                                            {{/if}}
                                            /
                                            {{mb_value object=$_consult field=du_tiers}}
                                        </div>
                                    {{/if}}
                                {{/if}}
                            </td>

                            <td
                              class="top text patient-forms {{if !$app->user_prefs.dPpatients_show_forms_resume}} forms-hide{{/if}}">
                                {{if $_consult->_ref_forms}}
                                    <ul>
                                        {{foreach from=$_consult->_ref_forms item=_ex_link}}
                                            <li>
                                                <a href="#1"
                                                   onclick="ExObject.display('{{$_ex_link->ex_object_id}}', '{{$_ex_link->ex_class_id}}', '{{$_consult->_guid}}')">
                                                    [{{mb_value object=$_ex_link field=datetime_create}}
                                                    ] {{$_ex_link->loadRefExClass()}}
                                                </a>
                                            </li>
                                        {{/foreach}}
                                    </ul>
                                {{else}}
                                    <br/>
                                    <span class="empty">{{tr}}CExClass.none{{/tr}}</span>
                                {{/if}}
                            </td>
                        </tr>
                    {{/if}}
                {{/foreach}}

                {{if $show_sejour}}
                    <!-- Interventions -->
                    <tr>
                        <th colspan="4" class="title">{{tr}}CSejour|pl{{/tr}}</th>
                    </tr>
                    <tr>
                        <th>{{tr}}common-Summary{{/tr}}</th>
                        <th colspan="2">{{tr}}CPatient-part-documents{{/tr}}</th>

                        <th
                          class="patient-forms {{if !$app->user_prefs.dPpatients_show_forms_resume}} forms-hide{{/if}}">
                            {{tr}}CExClass|pl{{/tr}}
                        </th>
                    </tr>
                    {{foreach from=$patient->_ref_sejours item=_sejour}}
                        {{if !$_sejour->annule}}
                            <tr>
                                <td class="text top">
                                    {{tr}}CSejour-msg-hospi{{/tr}}
                                    {{mb_include module=system template=inc_interval_date from=$_sejour->entree to=$_sejour->sortie}}
                                    <br/>
                                    <strong>{{tr}}CSejour-_type_admission{{/tr}}
                                        : </strong> {{tr}}CSejour.type.{{$_sejour->type}}{{/tr}}
                                    <br/>
                                    <strong>{{tr}}CSejour-praticien_id-desc{{/tr}} : </strong>
                                    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}
                                    <br/>
                                    <strong>{{tr}}CSejour-libelle{{/tr}} : </strong> {{$_sejour->libelle}}
                                    <ul>
                                        {{foreach from=$_sejour->_ref_operations item=_op}}
                                            {{if !$_op->annulee}}
                                                <li>
                                                    {{tr}}CMedecin.titre.dr{{/tr}} {{$_op->_ref_chir->_view}}
                                                    &mdash; {{$_op->_ref_plageop->date|date_format:$conf.date}}
                                                    {{if $_op->libelle}}
                                                        <br/>
                                                        <strong>{{mb_label object=$_op field="libelle"}}</strong>
                                                        :
                                                        {{mb_value object=$_op field="libelle"}}
                                                    {{/if}}
                                                    {{foreach from=$_op->_ext_codes_ccam item=_code}}
                                                        <br/>
                                                        <strong>{{$_code->code}}</strong>
                                                        : {{$_code->libelleLong}}
                                                    {{/foreach}}
                                                </li>
                                            {{/if}}
                                        {{/foreach}}
                                    </ul>
                                </td>
                                <td colspan="2" class="top text">
                                    <strong>{{tr}}CSejour{{/tr}} {{mb_include module=system template=inc_interval_date from=$_sejour->entree to=$_sejour->sortie}}</strong>
                                    {{mb_include module=files template=inc_list_docitems list=$_sejour->_refs_docitems_by_cat}}

                                    {{foreach from=$_sejour->_ref_operations item=_op}}
                                        {{if !$_op->annulee}}
                                            <strong>{{tr var1=$_op->_datetime_best|date_format:$conf.date}}COperation-Intervention of %s{{/tr}}</strong>
                                            {{mb_include module=files template=inc_list_docitems list=$_op->_refs_docitems_by_cat}}
                                        {{/if}}
                                    {{/foreach}}
                                </td>

                                <td
                                  class="top text patient-forms {{if !$app->user_prefs.dPpatients_show_forms_resume}} forms-hide{{/if}}">
                                    <strong>{{tr}}CSejour{{/tr}} {{mb_include module=system template=inc_interval_date from=$_sejour->entree to=$_sejour->sortie}}</strong>

                                    {{if $_sejour->_ref_forms}}
                                        <ul>
                                            {{foreach from=$_sejour->_ref_forms item=_ex_link}}
                                                <li>
                                                    <a href="#1"
                                                       onclick="ExObject.display('{{$_ex_link->ex_object_id}}', '{{$_ex_link->ex_class_id}}', '{{$_sejour->_guid}}')">
                                                        [{{mb_value object=$_ex_link field=datetime_create}}
                                                        ] {{$_ex_link->loadRefExClass()}}
                                                    </a>
                                                </li>
                                            {{/foreach}}
                                        </ul>
                                    {{else}}
                                        <br/>
                                        <span class="empty">{{tr}}CExClass.none{{/tr}}</span>
                                    {{/if}}

                                    {{foreach from=$_sejour->_ref_operations item=_op}}
                                        {{if !$_op->annulee}}
                                            <strong>{{tr var1=$_op->_datetime_best|date_format:$conf.date}}COperation-Intervention of %s{{/tr}}</strong>
                                            {{if $_op->_ref_forms}}
                                                <ul>
                                                    {{foreach from=$_op->_ref_forms item=_ex_link}}
                                                        <li>
                                                            <a href="#1"
                                                               onclick="ExObject.display('{{$_ex_link->ex_object_id}}', '{{$_ex_link->ex_class_id}}', '{{$_op->_guid}}')">
                                                                [{{mb_value object=$_ex_link field=datetime_create}}
                                                                ] {{$_ex_link->loadRefExClass()}}
                                                            </a>
                                                        </li>
                                                    {{/foreach}}
                                                </ul>
                                            {{else}}
                                                <br/>
                                                <span class="empty">{{tr}}CExClass.none{{/tr}}</span>
                                            {{/if}}
                                        {{/if}}
                                    {{/foreach}}
                                </td>
                            </tr>
                        {{/if}}
                    {{/foreach}}
                {{/if}}
            </table>
        </td>
    </tr>
    </tbody>
</table>
