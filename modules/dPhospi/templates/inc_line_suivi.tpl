{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=show_target value=true}}
{{mb_default var=from_lock   value=false}}
{{mb_default var=force_new   value=false}}
{{mb_default var=show_link   value=true}}
{{mb_default var=show_type   value=true}}
{{mb_default var=show_compact_trans value=false}}
{{mb_default var=see_type_user value=false}}

{{assign var=trans_compact value="soins Transmissions trans_compact"|gconf}}

{{if $_suivi|@instanceof:'Ox\Mediboard\Hospi\CObservationMedicale'}}
    {{if @$show_patient}}
        <td><strong>{{$_suivi->_ref_sejour->_ref_patient}}</strong></td>
        <td class="text">{{$_suivi->_ref_sejour->_ref_last_affectation->_ref_lit->_view}}</td>
    {{/if}}
    <td style="text-align: center;">
        {{if !$readonly && "soins Observations manual_alerts"|gconf}}
            {{mb_include module=hospi template=inc_vw_alerte_obs obs=$_suivi}}
        {{/if}}
        <strong>
            Obs
            {{if $_suivi->type == "reevaluation"}}
                <br/>
                <span class="compact">
          <label title="{{tr}}CObservationMedicale.type.reevaluation{{/tr}}">
            ({{tr}}CObservationMedicale.type.reevaluation-short{{/tr}})
          </label>
        </span>
            {{elseif $_suivi->type == "synthese"}}
                <br/>
                <span class="compact">({{tr}}CObservationMedicale.type.synthese{{/tr}})</span>
            {{elseif $_suivi->type == "communication"}}
                <br/>
                <span class="compact">({{tr}}CObservationMedicale.type.communication{{/tr}})</span>
            {{/if}}
            {{if $_suivi->etiquette}}
                <br/>
                <span class="texticon" style="font-size: 0.8em;">
          {{tr}}CObservationMedicale.etiquette.{{$_suivi->etiquette}}{{/tr}}
        </span>
            {{/if}}
        </strong>
    </td>
    <td class="narrow text">
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_suivi->_ref_user}}
        {{if $see_type_user}}
            <span class="compact">({{$_suivi->_ref_user->_user_type_view}})</span>
        {{/if}}
        <br/>
        {{mb_value object=$_suivi field=date}}
    </td>
    <td class="narrow text">
        {{if $_suivi->object_id}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_suivi->_ref_object->_guid}}');">
       {{if 'Ox\Mediboard\Prescription\CPrescription::isMPMActive'|static_call:null && $_suivi->_ref_object|instanceof:'Ox\Mediboard\Mpm\CPrescriptionLineMedicament'}}
           {{$_suivi->_ref_object->_ucd_view}}
       {{else}}
           {{$_suivi->_ref_object->_view}}
       {{/if}}
     </span>
        {{/if}}
    </td>
    <td colspan="3" class="text">
        <div>
            <strong>
                {{mb_value object=$_suivi field=text}}
            </strong>
        </div>
    </td>
    {{if !$readonly}}
        <td class="button">
            {{assign var=period_modif value="soins Observations period_modif"|gconf}}
            {{if $_suivi->_canEdit || !$period_modif || 'Ox\Core\CMbDT::timeRelative'|static_call:$_suivi->date:$dtnow:"%02d" < $period_modif}}
                <form name="Del-{{$_suivi->_guid}}" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
                    <input type="hidden" name="dosql" value="do_observation_aed"/>
                    <input type="hidden" name="del" value=""/>
                    <input type="hidden" name="cancellation_date" value=""/>
                    <input type="hidden" name="m" value="dPhospi"/>
                    <input type="hidden" name="observation_medicale_id" value="{{$_suivi->_id}}"/>
                    <input type="hidden" name="sejour_id" value="{{$_suivi->sejour_id}}"/>

                    {{if $_suivi->_canEdit}}
                        <button type="button" class="trash notext"
                                onclick="Soins.deleteObservation(this, submitSuivi);">{{tr}}Delete{{/tr}}</button>
                    {{/if}}

                    {{if $_suivi->user_id == $app->user_id}}
                        {{if $_suivi->cancellation_date}}
                            <button type="button" class="change notext"
                                    onclick="$V(this.form.cancellation_date, ''); submitSuivi(this.form, 1)">
                                {{tr}}Restore{{/tr}}
                            </button>
                        {{else}}
                            <button type="button" class="cancel notext"
                                    onclick="if(confirm('Vous êtes sur le point d\'annuler cette observation, confirmez-vous cette action ?')) {  $V(this.form.cancellation_date, 'now'); submitSuivi(this.form, 1) }">
                                {{tr}}Cancel{{/tr}}
                            </button>
                        {{/if}}
                    {{/if}}
                </form>
                {{if $_suivi->_canEdit}}
                    <button type="button" class="edit notext"
                            onclick="Soins.addObservation(null, null, '{{$_suivi->_id}}');">

                    </button>
                {{/if}}
            {{/if}}
            {{if $_suivi->cancellation_date}}
                <br/>
                <span style="font-weight: bold">
        Annulée le {{mb_value object=$_suivi field="cancellation_date"}}
      </span>
            {{/if}}
        </td>
    {{/if}}
{{/if}}

{{if $_suivi|@instanceof:'Ox\Mediboard\Patients\CConstantesMedicales'}}
    {{assign var=config_host value='Ox\Mediboard\Patients\CConstantesMedicales::guessHost'|static_call:$sejour}}
    {{assign var=constants_by_ranks value='Ox\Mediboard\Patients\CConstantesMedicales::getConstantsByRank'|static_call:'form':false:$config_host}}
    <td style="text-align: center;">
        <label title="Constantes">Cst</label>
    </td>
    <td class="narrow text">
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_suivi->_ref_user}}
        <br/>
        {{mb_value object=$_suivi field=datetime}}
    </td>
    <td colspan="4" class="text">
        {{foreach from=$constants_by_ranks key=_type item=_ranks}}
            {{foreach from=$_ranks key=_rank item=_constants}}
                {{if $_rank != -1}}
                    {{foreach from=$_constants item=_constant}}
                        {{assign var=_param value=$params.$_constant}}
                        {{if $_constant|substr:0:1 != "_" && $_suivi->$_constant != null}}
                            {{mb_title object=$_suivi field=$_constant}} :
                            {{if array_key_exists("formfields", $_param)}}
                                {{mb_value object=$_suivi field=$_param.formfields.0 size="2"}}
                                {{if array_key_exists(1, $_param.formfields)}}
                                    /
                                    {{mb_value object=$_suivi field=$_param.formfields.1 size="2"}}
                                {{/if}}
                            {{else}}
                                {{mb_value object=$_suivi field=$_constant}}
                            {{/if}} {{$_param.unit}} {{if array_key_exists($_constant, $_suivi->_refs_comments)}}({{$_suivi->_refs_comments.$_constant->comment}}){{/if}},
                            <br>
                        {{/if}}
                    {{/foreach}}
                {{/if}}
            {{/foreach}}
        {{/foreach}}
        {{if $_suivi->comment}}
            ({{$_suivi->comment}})
        {{/if}}
    </td>
    <td></td>
{{/if}}

{{if $_suivi|@instanceof:'Ox\Mediboard\Prescription\CPrescriptionLineElement' || $_suivi|@instanceof:'Ox\Mediboard\Prescription\CPrescriptionLineComment'}}
    <td style="text-align: center">
        <label title="Ligne de prescription">
            <strong>Presc</strong>
        </label>
    </td>
    <td class="narrow  text">
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_suivi->_ref_praticien}}
        <br/>
        {{mb_value object=$_suivi field=debut}}
    </td>
    <td colspan="4" {{if $_suivi->_count.transmissions}} class="arretee" {{/if}}>
        {{if !$readonly}}
            <button type="button" class="tick" onclick="addTransmissionAdm('{{$_suivi->_id}}','{{$_suivi->_class}}');"
                    style="float: right;">
                Réaliser ({{$_suivi->_count.transmissions}})
            </button>
        {{/if}}

        {{if $_suivi|instanceof:'Ox\Mediboard\Prescription\CPrescriptionLineElement'}}
            <strong
              onmouseover="ObjectTooltip.createEx(this, '{{$_suivi->_ref_element_prescription->_guid}}');">{{$_suivi->_view}}</strong>
        {{/if}}
        {{mb_value object=$_suivi field="commentaire"}}
    </td>
    <td class="text {{if $_suivi->_count.transmissions}}arretee{{/if}}">
        {{if !$readonly && $_suivi->_canEdit && !$_suivi->_count.transmissions}}
            <form name="Del-{{$_suivi->_guid}}" action="?" method="post">
                <input type="hidden" name="m" value="dPprescription"/>
                {{if $_suivi|instanceof:'Ox\Mediboard\Prescription\CPrescriptionLineElement'}}
                    <input type="hidden" name="dosql" value="do_prescription_line_element_aed"/>
                {{else}}
                    <input type="hidden" name="dosql" value="do_prescription_line_comment_aed"/>
                {{/if}}
                <input type="hidden" name="del" value="1"/>
                {{mb_key object=$_suivi}}
                <input type="hidden" name="sejour_id" value="{{$_suivi->_ref_prescription->object_id}}"/>
                <button type="button" class="trash notext" onclick="submitSuivi(this.form, 1);"></button>
            </form>
            <button type="button" class="edit notext"
                    onclick="addPrescription('{{$_suivi->_ref_prescription->object_id}}', '{{$app->user_id}}', '{{$_suivi->_id}}', '{{$_suivi->_class}}');"></button>
        {{/if}}
    </td>
{{/if}}

{{if $_suivi|@instanceof:'Ox\Mediboard\Cabinet\CConsultation'}}
    <td class="narrow" style="text-align: center;">
        <strong onmouseover="ObjectTooltip.createEx(this, '{{$_suivi->_guid}}')">
            {{if $_suivi->type == "entree"}}
                Obs. entrée
            {{elseif $_suivi->_refs_dossiers_anesth|@count >= 1}}
                Cs anesth.
            {{else}}
                Cs
            {{/if}}
        </strong>
    </td>
    <td class="narrow text">
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_suivi->_ref_praticien}}
        <br/>
        {{mb_value object=$_suivi field=_datetime}}
    </td>
    <td></td>
    <td class="text" colspan="3">
        {{if $_suivi->_refs_dossiers_anesth|@count}}
            {{foreach from=$_suivi->_refs_dossiers_anesth item=_dossier_anesth}}
                <strong>
                    Dossier d'anesthésie
                    {{if $_dossier_anesth->operation_id}}
                        pour l'intervention du {{mb_value object=$_dossier_anesth->_ref_operation field=_datetime_best}}
                    {{else}}
                        {{$_dossier_anesth->_id}}
                    {{/if}}
                </strong>
                <br/>
                {{if $_dossier_anesth->operation_id}}
                    {{if $_dossier_anesth->_ASA}}
                        <u>{{tr}}CConsultAnesth-ASA{{/tr}} :</u>
                        {{tr}}COperation.ASA.{{$_dossier_anesth->_ASA}}{{/tr}}
                        <br/>
                    {{/if}}
                    {{if $_dossier_anesth->_position_id}}
                        <u>{{tr}}CConsultAnesth-position_id{{/tr}} :</u>
                        {{$_dossier_anesth->_ref_position}}
                        <br/>
                    {{/if}}
                {{/if}}
                {{if $_dossier_anesth->prepa_preop}}
                    <u>{{mb_label class=CConsultAnesth field=prepa_preop}} :</u>
                    {{mb_value object=$_dossier_anesth field=prepa_preop}}
                    <br/>
                {{/if}}
                {{if $_dossier_anesth->_ref_techniques|@count}}
                    <u>{{tr}}CConsultAnesth-back-techniques-court{{/tr}} :</u>
                    {{foreach from=$_dossier_anesth->_ref_techniques item=_technique name=foreach_techniques}}
                        {{mb_value object=$_technique field=technique}} {{if !$smarty.foreach.foreach_techniques.last}}-{{/if}}
                    {{/foreach}}
                {{/if}}
            {{/foreach}}
            {{if $_suivi->rques}}
                <u>{{tr}}CConsultation-rques{{/tr}} :</u>
                {{mb_value object=$_suivi field=rques}}
                <br/>
            {{/if}}
            {{if $_dossier_anesth->au_total}}
                <u>{{tr}}CConsultAnesth-au_total{{/tr}} :</u>
                {{mb_value object=$_dossier_anesth field=au_total}}
                <br/>
            {{/if}}
        {{else}}
            {{if $_suivi->motif}}<u>{{tr}}CConsultation-motif{{/tr}}</u> : {{mb_value object=$_suivi field=motif}}
                <br/>
            {{/if}}
            {{if $_suivi->histoire_maladie}}
                <u>{{tr}}CConsultation-histoire_maladie{{/tr}}</u>
                : {{mb_value object=$_suivi field=histoire_maladie}}
                <br/>
            {{/if}}
            {{if $_suivi->examen}}<u>{{tr}}CConsultation-examen{{/tr}}</u> : {{mb_value object=$_suivi field=examen}}
                <br/>
            {{/if}}
            {{if $_suivi->traitement}}
                <u>{{tr}}CConsultation-traitement{{/tr}}</u> : {{mb_value object=$_suivi field=traitement}}
                <br/>
            {{/if}}
            {{if $_suivi->rques}}<u>{{tr}}CConsultation-rques{{/tr}}</u> : {{mb_value object=$_suivi field=rques}}
                <br/>
            {{/if}}
            {{if $_suivi->conclusion}}
                <u>{{tr}}CConsultation-conclusion{{/tr}}</u> : {{mb_value object=$_suivi field=conclusion}}
                <br/>
            {{/if}}
            {{if $_suivi->_ref_suivi_grossesse && $_suivi->_ref_suivi_grossesse->conclusion}}
              <u>{{tr}}CSuiviGrossesse-conclusion conclusion of the pregnancy monitoring{{/tr}}</u> : {{mb_value object=$_suivi->_ref_suivi_grossesse field=conclusion}}
              <br/>
            {{/if}}
        {{/if}}
        {{if "forms"|module_active && $_suivi->_list_forms|@count}}
            <u>{{tr}}CExClass|pl{{/tr}} :</u>
            <ul>
                {{foreach from=$_suivi->_list_forms item=_forms key=ex_class_id}}
                    {{foreach from=$_forms item=ex_object key=ex_object_id}}
                        <li>
                            <a href="#1"
                               onclick="ExObject.display('{{$ex_object_id}}', '{{$ex_class_id}}', '{{$ex_object->object_class}}-{{$ex_object->object_id}}')">
                                {{$ex_object->_ref_ex_class->name}}
                            </a>
                        </li>
                    {{/foreach}}
                {{/foreach}}
            </ul>
        {{/if}}
    </td>
    {{if !$readonly}}
        <td class="button">
            {{mb_default var=_callback value=null}}
            {{if $_suivi->_canEdit}}
                <form name="Del-{{$_suivi->_guid}}" action="?m={{$m}}" method="post"
                      onsubmit="return onSubmitFormAjax(this, function() { Soins.loadSuivi('{{$sejour->_id}}'); {{if $_callback}}{{"`$_callback`();"}}{{/if}} })">
                    <input type="hidden" name="dosql" value="do_consultation_aed"/>
                    <input type="hidden" name="m" value="cabinet"/>
                    <input type="hidden" name="consultation_id" value="{{$_suivi->_id}}"/>
                    <input type="hidden" name="annule" value="1"/>
                    <button type="button" class="trash notext"
                            onclick="if (confirm('Voulez-vous vraiment annuler cette consultation ?')) { this.form.onsubmit() } ">
                        {{tr}}Delete{{/tr}}</button>
                </form>
            {{/if}}

            <button type="button" class="{{if $_suivi->_canEdit}}edit{{else}}search{{/if}} notext"
                    onclick="Soins.modalConsult('{{$sejour->_id}}', '{{$_suivi->_id}}' {{if $_callback}}, null, null, {{$_callback}}{{/if}})"></button>
        </td>
    {{/if}}
{{/if}}

{{if $_suivi|@instanceof:'Ox\Mediboard\Hospi\CTransmissionMedicale'}}
    {{if @$show_patient}}
        <td class="narrow">{{$_suivi->_ref_sejour->_ref_patient}}</td>
        <td class="narrow">{{$_suivi->_ref_sejour->_ref_last_affectation->_ref_lit->_view}}</td>
    {{/if}}
    <td class="narrow" style="text-align: center;">
        <label title="Transmission">TC</label>
    </td>
    {{if $show_compact_trans}}
        <td class="text">
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_suivi->_ref_user}}
            <br/>
            {{mb_value object=$_suivi field=date}}
        </td>
    {{else}}
        <td
          class="narrow text">{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_suivi->_ref_user initials=border}}</td>
        <td style="text-align: center;" class="narrow">
            {{assign var=sejour_id_ditto value=$_suivi->sejour_id}}
            {{mb_ditto name="date-$sejour_id_ditto" value=$_suivi->date|date_format:$conf.date}}
        </td>
        <td class="narrow">{{$_suivi->date|date_format:$conf.time}}</td>
    {{/if}}
    {{if $show_target}}
        <td class="text" style="height: 22px;">
            {{if $_suivi->_ref_cible->object_id && $_suivi->_ref_cible->object_class}}
                {{assign var=classes value=' '|explode:"CPrescriptionLineMedicament CPrescriptionLineElement CAdministration CPrescriptionLineMix"}}
                {{if in_array($_suivi->object_class, $classes)}}
                    {{assign var=view_object value=$_suivi->_ref_object->_view}}
                    {{if $_suivi->object_class == "CPrescriptionLineMix"}}
                        {{assign var=view_object value=""}}
                        {{foreach from=$_suivi->_ref_object->_ref_lines item=_mix_item}}
                            {{assign var=view_object value="`$view_object` `$_mix_item->_view`, "}}
                        {{/foreach}}
                    {{/if}}
                    <span
                      title="{{$view_object}} {{if $_suivi->_ref_object|instanceof:'Ox\Mediboard\Prescription\CPrescriptionLineElement' && $_suivi->_ref_object->commentaire}}({{$_suivi->_ref_object->commentaire}}){{/if}}"
                      style="float: left; border: 2px solid #800; width: 5px; height: 11px; margin-right: 3px;">
          </span>
                {{/if}}
                {{if (!$readonly && $_suivi->_canEdit) || $force_new}}
                    <a href="#1" onclick="
                {{if $force_new}}
                  Control.Modal.close();
                {{/if}}
                  Soins.addTransmission('{{$_suivi->sejour_id}}', '{{$app->user_id}}', null, null, null, null, '{{$_suivi->cible_id}}');"
                    >
                {{/if}}

                {{if !in_array($_suivi->object_class, $classes)}}
                    {{$_suivi->_ref_object->_view}}
                {{/if}}
                {{if $_suivi->object_class == "CPrescriptionLineMedicament"}}
                    [{{$_suivi->_ref_object->_ref_produit->_ref_ATC_2_libelle}}]
                {{/if}}

                {{if $_suivi->object_class == "CPrescriptionLineElement"}}
                    [{{$_suivi->_ref_object->_ref_element_prescription->_ref_category_prescription->_view}}]
                {{/if}}

                {{if $_suivi->object_class == "CAdministration"}}
                    {{if $_suivi->_ref_object->object_class == "CPrescriptionLineMedicament"}}
                        [{{$_suivi->_ref_object->_ref_object->_ref_produit->_ref_ATC_2_libelle}}]
                    {{/if}}

                    {{if $_suivi->_ref_object->object_class == "CPrescriptionLineElement"}}
                        [{{$_suivi->_ref_object->_ref_object->_ref_element_prescription->_ref_category_prescription->_view}}]
                    {{/if}}
                {{/if}}

                {{if (!$readonly && $_suivi->_canEdit) || $force_new}}
                    </a>
                {{/if}}
            {{/if}}
            {{if $_suivi->_ref_cible->libelle_ATC}}
                <a href="#1" onclick="
                {{if $force_new}}
                  Control.Modal.close();
                {{/if}}
                  Soins.addTransmission('{{$_suivi->sejour_id}}', '{{$_suivi->user_id}}', null, null, null, null, '{{$_suivi->cible_id}}');"
                >{{$_suivi->libelle_ATC}}</a>
            {{/if}}
        </td>
    {{/if}}
    {{if $show_compact_trans}}
        <td class="text libelle_trans">
            {{if $_suivi->type == 'data'}}{{mb_value object=$_suivi field=text}}{{/if}}
        </td>
        <td class="text libelle_trans">
            {{if $_suivi->type == 'action'}}{{mb_value object=$_suivi field=text}}{{/if}}
        </td>
        <td class="text libelle_trans">
            {{if $_suivi->type == 'result'}}{{mb_value object=$_suivi field=text}}{{/if}}
        </td>
    {{else}}
        <td class="text {{if $_suivi->type}}trans-{{$_suivi->type}}{{/if}} libelle_trans" colspan="3">
            {{mb_value object=$_suivi field=text}}
        </td>
    {{/if}}
    {{if !$readonly}}
        <td class="text">
            <form name="Del-{{$_suivi->_guid}}" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
                <input type="hidden" name="dosql" value="do_transmission_aed"/>
                <input type="hidden" name="del" value=""/>
                <input type="hidden" name="cancellation_date" value=""/>

                <input type="hidden" name="m" value="dPhospi"/>
                <input type="hidden" name="transmission_medicale_id" value="{{$_suivi->_id}}"/>
                <input type="hidden" name="sejour_id" value="{{$_suivi->sejour_id}}"/>

                {{if $_suivi->_canEdit}}
                    <button type="button" class="trash notext"
                            onclick="$V(this.form.del, '1'): submitSuivi(this.form, 1)">{{tr}}Delete{{/tr}}</button>
                {{/if}}

                {{if $app->user_id == $_suivi->user_id}}
                    {{if $_suivi->cancellation_date}}
                        <button type="button" class="change notext"
                                onclick="$V(this.form.cancellation_date, ''); submitSuivi(this.form, 1);">{{tr}}Restore{{/tr}}</button>
                    {{else}}
                        <button type="button" class="cancel notext"
                                onclick="$V(this.form.cancellation_date, 'now'); submitSuivi(this.form, 1);">{{tr}}Cancel{{/tr}}</button>
                    {{/if}}
                {{/if}}
            </form>
            {{if $_suivi->_canEdit}}
                <button type="button" class="edit notext"
                        onclick="Soins.addTransmission(null, null, '{{$_suivi->_id}}', null, null, null, null, 1)"></button>
            {{/if}}
        </td>
    {{/if}}
{{/if}}

{{if $_suivi|@instanceof:'Ox\Mediboard\Soins\CRDVExterne'}}
    {{assign var=patient value=$_suivi->_ref_sejour->_ref_patient}}
    <td style="text-align: center;">
        <label title="{{tr}}CRDVExterne{{/tr}}">{{tr}}CRDVExterne-RDV-court{{/tr}}</label>
    </td>
    <td class="narrow text">
    <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
      {{$patient->_view}}
    </span>
        <br/>
        {{mb_value object=$_suivi field=date_debut}}
    </td>
    <td colspan="4" class="text">
        <span title="{{$_suivi->description}}">{{$_suivi->libelle}}</span> ({{mb_value object=$_suivi field=duree}} min
        - {{mb_value object=$_suivi field=statut}})<br/>
        {{if $_suivi->commentaire}}
            <strong>{{mb_label object=$_suivi field=commentaire}}</strong>
            : {{mb_value object=$_suivi field=commentaire}}
            <br/>
        {{/if}}
    </td>
    <td></td>
{{/if}}

{{if $_suivi|@instanceof:'Ox\Mediboard\PlanningOp\CAppelSejour'}}
    {{assign var=patient value=$_suivi->_ref_sejour->_ref_patient}}
    <td style="text-align: center;">
        <label title="{{tr}}CAppelSejour{{/tr}}">
            {{tr}}CAppelSejour-event-appel{{/tr}}
            <br/>
            ({{$_suivi->type}})
        </label>
    </td>
    <td class="narrow text">
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_suivi->_ref_user}}
        <br/>
        {{mb_value object=$_suivi field=datetime}}
    </td>
    <td colspan="4" class="text">
        <strong>{{mb_label object=$_suivi field=etat}}</strong>: {{mb_value object=$_suivi field=etat}} <br/>
        {{if $_suivi->commentaire}}
            <strong>{{mb_label object=$_suivi field=commentaire}}</strong>
            : {{mb_value object=$_suivi field=commentaire}}
            <br/>
        {{/if}}
    </td>
    <td></td>
{{/if}}

{{* Tableau de transmissions *}}
{{* Affichage aggrégé dans le volet transmissions, de 1 à 3 objets (D-A-R) *}}

{{if $_suivi|is_array}}
    {{assign var=nb_trans value=0}}
    {{assign var=last_type value=""}}
    {{assign var=last_index value=0}}
    {{foreach from=$_suivi item=_trans_by_type key=type_trans}}
        {{if $type_trans != "0"}}
            {{if $_trans_by_type|@count}}
                {{assign var=last_type value=$type_trans}}
                {{math equation=x-1 x=$_trans_by_type|@count assign=last_index}}
            {{/if}}
            {{math equation=x+y x=$nb_trans y=$_trans_by_type|@count assign=nb_trans}}
        {{/if}}
    {{/foreach}}
    {{assign var=libelle_ATC value=$_suivi[0]->libelle_ATC}}
    {{assign var=cible_id value=$_suivi[0]->cible_id}}

    {{assign var=locked value=""}}

    {{if isset($last_trans_cible|smarty:nodefaults)}}
        {{assign var=key_last_trans value=$cible_id}}

        {{if !$_suivi[0]->_ref_cible->report && (($_suivi[0]->object_id && $_suivi[0]->object_class) || $_suivi[0]->libelle_ATC)}}
            {{assign var=key_last_trans value="`$_suivi[0]->object_class`-`$_suivi[0]->object_id`-`$_suivi[0]->libelle_ATC`"}}
        {{/if}}

        {{if $_suivi[0]->locked && $key_last_trans && isset($last_trans_cible.$key_last_trans|smarty:nodefaults) && in_array($last_trans_cible.$key_last_trans, $_suivi)}}
            {{assign var=locked value="hatching"}}
        {{/if}}
    {{/if}}
    {{if @$show_patient}}
        <td>{{$_suivi[0]->_ref_sejour->_ref_patient}}</td>
        <td class="text">{{$_suivi[0]->_ref_sejour->_ref_last_affectation->_ref_lit->_view}}</td>
    {{/if}}
    {{if $show_type}}
        <td class="narrow {{$locked}}" style="text-align: center;">
            <label title="Transmission">TC</label>
        </td>
    {{/if}}
    <td class="{{$locked}} text" style="width: 10%">
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_suivi[0]->_ref_user}}
        {{if $see_type_user}}
            <span class="compact">({{$_suivi[0]->_ref_user->_user_type_view}})</span>
        {{/if}}
        <br/>
        {{mb_value object=$_suivi[0] field=date}}
    </td>
    {{if $show_target}}
        <td class="text libelle_trans {{$locked}}" style="height: 22px;">
            {{if $_suivi[0]->object_id && $_suivi[0]->object_class}}
                {{assign var=classes value=' '|explode:"CPrescriptionLineMedicament CPrescriptionLineElement CAdministration CPrescriptionLineMix"}}
                {{if in_array($_suivi[0]->object_class, $classes)}}
                    {{assign var=view_object value=$_suivi[0]->_ref_object->_view}}
                    {{if $_suivi[0]->object_class == "CPrescriptionLineMix"}}
                        {{assign var=view_object value=""}}
                        {{foreach from=$_suivi[0]->_ref_object->_ref_lines item=_mix_item}}
                            {{assign var=view_object value="`$view_object` `$_mix_item->_view`, "}}
                        {{/foreach}}
                    {{elseif $_suivi[0]->object_class == "CAdministration"}}
                        {{assign var=view_object value=$_suivi[0]->_ref_object->_ref_object}}
                    {{/if}}
                    <span
                      title="{{$view_object}} {{if $_suivi[0]->_ref_object|instanceof:'Ox\Mediboard\Prescription\CPrescriptionLineElement' && $_suivi[0]->_ref_object->commentaire}}({{$_suivi[0]->_ref_object->commentaire}}){{/if}}"
                      style="float: left; border: 2px solid #800; width: 5px; height: 11px; margin-right: 3px;">
          </span>
                {{/if}}
                {{if $locked || $trans_compact}}
                    <strong>
                {{/if}}
            {{if $show_link}}
                <a href="#1"
                        {{if $locked || $trans_compact}}
                    onclick="Soins.showTrans('{{$_suivi[0]->_id}}' {{if !$locked}}, 1{{/if}})"
                        {{else}}
                    onclick="
                    {{if $_suivi[0]->_ref_object|instanceof:'Ox\Mediboard\Prescription\CCategoryPrescription' && $_suivi[0]->_ref_object->cible_importante}}
                      Soins.addMacrocible('{{$_suivi[0]->sejour_id}}', '{{$_suivi[0]->object_id}}', '{{$_suivi[0]->cible_id}}');
                    {{else}}
                      Soins.addTransmission('{{$_suivi[0]->sejour_id}}', '{{$app->user_id}}', null, null, null, null, '{{$_suivi[0]->cible_id}}');
                    {{/if}}"
                        {{/if}}>
            {{/if}}
                {{if !in_array($_suivi[0]->object_class, $classes)}}
                    {{$_suivi[0]->_ref_object->_view}}
                {{/if}}
                {{if $_suivi[0]->object_class == "CPrescriptionLineMedicament"}}
                    [{{$_suivi[0]->_ref_object->_ref_produit->_ref_ATC_2_libelle}}]
                {{/if}}

                {{if $_suivi[0]->object_class == "CPrescriptionLineMix"}}
                    [{{mb_value object=$_suivi[0]->_ref_object field=type_line}}]
                {{/if}}

                {{if $_suivi[0]->object_class == "CPrescriptionLineElement"}}
                    [{{$_suivi[0]->_ref_object->_ref_element_prescription->_ref_category_prescription->_view}}]
                {{/if}}

                {{if $_suivi[0]->object_class == "CAdministration"}}
                    {{if in_array($_suivi[0]->_ref_object->object_class, array("CPrescriptionLineMedicament", "CPrescriptionLineMixItem"))}}
                        [{{$_suivi[0]->_ref_object->_ref_object->_ref_produit->_ref_ATC_2_libelle}}]
                    {{/if}}

                    {{if $_suivi[0]->_ref_object->object_class == "CPrescriptionLineElement"}}
                        [{{$_suivi[0]->_ref_object->_ref_object->_ref_element_prescription->_ref_category_prescription->_view}}]
                    {{/if}}
                {{/if}}
                {{if $locked || $trans_compact}}
                    </strong>
                {{/if}}
                {{if $show_link}}
                    </a>
                {{/if}}
            {{/if}}
            {{if $libelle_ATC}}
                {{if $locked || $trans_compact}}
                    <strong>
                {{/if}}
            {{if $show_link}}
                <a href="#1"
                        {{if $locked || $trans_compact}}
                            onclick="Soins.showTrans('{{$_suivi[0]->_id}}' {{if !$locked}}, 1{{/if}})"
                        {{else}}
                            onclick="Soins.addTransmission('{{$_suivi[0]->sejour_id}}', '{{$_suivi[0]->user_id}}', null, null, null, null, '{{$_suivi[0]->cible_id}}');"
                        {{/if}}
                >
            {{/if}}
                {{$_suivi[0]->libelle_ATC}}
                {{if $locked || $trans_compact}}
                    </strong>
                {{/if}}
                {{if $show_link}}
                    </a>
                {{/if}}
            {{/if}}
        </td>
    {{/if}}
    {{if $locked}}
        <td class="hatching" colspan="3" style="text-align: center"></td>
        <td class="hatching">
            <button type="button" class="unlock notext" title="Réouvrir la cible"
                    onclick="Soins.toggleLockCible('{{$_suivi[0]->_id}}', 0, '{{$_suivi[0]->sejour_id}}')"></button>
        </td>
    {{else}}
        {{foreach from=$_suivi item=_trans_by_type key=type_trans}}
            {{if $type_trans != "0"}}
                <td style="width: 18%; page-break-inside: avoid;">
                    {{if is_array($_trans_by_type)}}
                        {{* Fusion de transmissions médicales *}}
                        {{if $_trans_by_type|@count > 1}}
                            {{assign var=transmissions_ids value=""}}
                            {{foreach from=$_trans_by_type item=_trans name=_trans}}
                                {{if $smarty.foreach._trans.first}}
                                    {{assign var=transmissions_ids value=$_trans->_id}}
                                {{else}}
                                    {{assign var=transmissions_ids value="$transmissions_ids-`$_trans->_id`"}}
                                {{/if}}
                            {{/foreach}}
                            <button type="button" class="merge notext" style="float: right;"
                                    onclick="Soins.mergeTrans('{{$transmissions_ids}}')"></button>
                        {{/if}}
                        {{foreach from=$_trans_by_type item=_trans}}
                            <span {{if $_trans->_old}}class="compact"{{/if}}>
                {{mb_value object=$_trans field=text}}
              </span>
                            <br/>
                        {{/foreach}}
                    {{/if}}

                    {{if !$readonly && !$_trans_by_type|@count && (!$_suivi[0]->_ref_object|instanceof:'Ox\Mediboard\Prescription\CCategoryPrescription' || !$_suivi[0]->_ref_object->cible_importante)}}
                        <button type="button" class="add notext transmission_add" style="float: right;"
                                title="Ajouter une transmission"
                                onclick="Soins.addTransmission('{{$_suivi[0]->sejour_id}}', '{{$_suivi[0]->user_id}}', null, null, null, null, '{{$_suivi[0]->cible_id}}', 1, null, '{{$type_trans}}');"></button>
                    {{/if}}
                </td>
            {{/if}}
        {{/foreach}}
        {{if !$readonly}}
            <td class="nowrap button">
                {{if $_suivi.data|@count <= 1 && $_suivi.action|@count <= 1 && $_suivi.result|@count <= 1}}
                    <form name="Del-{{$_suivi[0]->_guid}}" action="?m={{$m}}" method="post"
                          onsubmit="return checkForm(this);">
                        <input type="hidden" name="m" value="hospi"/>
                        <input type="hidden" name="del" value=""/>
                        <input type="hidden" name="cancellation_date" value=""/>

                        {{if $_suivi|@count == 1}}
                            <input type="hidden" name="dosql" value="do_transmission_aed"/>
                        {{else}}
                            <input type="hidden" name="dosql" value="do_multi_transmission_aed"/>
                        {{/if}}

                        {{if $nb_trans == 1}}
                            <input type="hidden" name="transmission_medicale_id" value="{{$_suivi[0]->_id}}"/>
                        {{/if}}
                        {{foreach from=$_suivi item=_trans_by_type key=type_trans}}
                            {{if $type_trans != "0" && $_trans_by_type|@count && !$_trans_by_type[0]->_old}}
                                <input type="hidden" name="{{$_trans_by_type[0]->type}}_id"
                                       value="{{$_trans_by_type[0]->_id}}"/>
                            {{/if}}
                        {{/foreach}}
                        <input type="hidden" name="sejour_id" value="{{$_suivi[0]->sejour_id}}"/>

                        {{if $_suivi[0]->_canEdit}}
                            <button type="button" class="trash notext me-tertiary"
                                    onclick="confirmDeletion(this.form,
                          {typeName:'la/les transmission(s)',
                          ajax: true,
                          callback: (function() { submitSuivi(this.form, 1); }).bind(this) })"></button>
                        {{/if}}

                        {{if $app->user_id == $_suivi[0]->user_id}}
                            {{if $_suivi[0]->cancellation_date}}
                                <button type="button" class="change notext me-tertiary"
                                        onclick="$V(this.form.cancellation_date, ''); submitSuivi(this.form, 1);">{{tr}}Restore{{/tr}}</button>
                            {{else}}
                                <button type="button" class="cancel notext me-tertiary"
                                        onclick="if(confirm('Vous êtes sur le point d\'annuler cette transmission, confirmez-vous cette action ?')) { $V(this.form.cancellation_date, 'now'); submitSuivi(this.form, 1); } ">{{tr}}Cancel{{/tr}}</button>
                            {{/if}}
                        {{/if}}
                    </form>
                    {{if $_suivi[0]->_canEdit}}
                        {{if $nb_trans == 1}}
                            <button type="button" class="edit notext"
                                    onclick="Soins.addTransmission('{{$_suivi[0]->sejour_id}}', null, '{{$_suivi[0]->_id}}', null, null, null, null, 1)"></button>
                        {{else}}
                            <button type="button" class="edit notext"
                                    onclick="Soins.addTransmission('{{$_suivi[0]->sejour_id}}', null, {
                                    {{assign var=is_first_trans value=1}}
                                    {{foreach from=$_suivi item=_trans_by_type key=type_trans name=_trans}}
                                      {{if $type_trans != "0" && isset($_trans_by_type.0|smarty:nodefaults) && !$_trans_by_type.0->_old}}
                                        {{assign var=first_trans value=$_trans_by_type.0}}
                                        {{if !$is_first_trans}},{{/if}}
                                        {{$first_trans->type}}_id: '{{$first_trans->_id}}'
                                        {{assign var=is_first_trans value=0}}
                                      {{/if}}
                                    {{/foreach}}
                                      })"></button>
                        {{/if}}
                    {{/if}}
                    {{if isset($last_trans_cible|smarty:nodefaults)}}
                        {{assign var=key_last_trans value=$cible_id}}

                        {{if !$_suivi[0]->_ref_cible->report && (($_suivi[0]->object_id && $_suivi[0]->object_class) || $_suivi[0]->libelle_ATC)}}
                            {{assign var=key_last_trans value="`$_suivi[0]->object_class`-`$_suivi[0]->object_id`-`$_suivi[0]->libelle_ATC`"}}
                        {{/if}}

                        {{if $key_last_trans && isset($last_trans_cible.$key_last_trans|smarty:nodefaults) && in_array($last_trans_cible.$key_last_trans, $_suivi)}}
                            {{assign var=last_trans value=$_suivi.$last_type.$last_index}}
                            <button type="button" class="lock notext" title="Fermer la cible"
                                    onclick="Soins.toggleLockCible('{{$last_trans->_id}}', 1, '{{$_suivi[0]->sejour_id}}')"></button>
                        {{/if}}
                    {{/if}}
                {{/if}}

                {{if $_suivi[0]->cancellation_date}}
                    <br/>
                    <span style="font-weight: bold">
              Annulée le {{mb_value object=$_suivi[0] field="cancellation_date"}}
            </span>
                {{/if}}
            </td>
        {{/if}}
    {{/if}}
{{/if}}
