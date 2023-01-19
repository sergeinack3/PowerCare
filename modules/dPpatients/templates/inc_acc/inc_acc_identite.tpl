{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{*
 * ATTENTION
 * Ce fichier dispose d'une seconde version pour le thème Mediboard Design
 * Il ne faut pas oublier d'appliquer les modifications sur ce fichier également
 * inc_acc_identite_v2.tpl
 *
 * En cas de problème, ne pas hésiter à demander à Adam ou Yvan
*}}

{{mb_script module=patients script=anticipated_directives ajax=$ajax}}

{{assign var=allowed_modify value=$app->user_prefs.allowed_modify_identity_status}}
{{mb_default var=use_id_interpreter value=0}}

<script>
    Main.add(function () {
        {{if $patient->_id}}
        Patient.refreshInfoTutelle('{{$patient->tutelle}}');
        {{else}}
        Patient.checkDoublon();
        {{/if}}
    });
</script>

<table style="width: 100%">
    <tr>
        <td colspan="2" id="alert_tutelle"></td>
    </tr>
    <tr>
        <td style="width: 50%">
            <table class="form" id="patient_identite">
                <tr>
                    <th class="category" colspan="3">{{tr}}CPatient-identite-patient-title{{/tr}}</th>
                </tr>

                <tr>
                    <td colspan="3" class="text">
                        <div id="doublon-patient"></div>
                    </td>
                </tr>

                <tr>
                    <th style="width:30%">{{mb_label object=$patient field="nom"}}</th>
                    <td>
                        {{if $patient->_id && !$allowed_modify && $patient->status == "VALI"}}
                            {{mb_value object=$patient field="nom"}}
                        {{else}}
                            {{mb_field object=$patient field="nom" onchange="Patient.checkDoublon(); Patient.copyIdentiteAssureValues(this)"}}
                            {{if !$patient->_id}}
                                <button type="button" style="padding: 0;" onclick="Patient.anonymous()" tabIndex="1000">
                                    <img src="modules/dPpatients/images/anonyme.png" alt="Anonyme"/>
                                </button>
                            {{/if}}
                        {{/if}}
                        {{if $use_id_interpreter}}
                            <button type="button" class="fas fa-id-card notext"
                                    onclick="IdInterpreter.open(this.form, '{{$patient->_guid}}')">
                                {{tr}}CIdInterpreter.fill_from_image{{/tr}}
                            </button>
                        {{/if}}
                    </td>

                    {{if $patient->_id}}
                        <td rowspan="14" class="narrow" style="text-align: center;" id="{{$patient->_guid}}-identity">
                            {{mb_include template=inc_vw_photo_identite mode="edit"}}
                        </td>
                    {{/if}}
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="prenom"}}</th>
                    <td>
                        {{if $patient->_id && $patient->status == "VALI" && !$allowed_modify}}
                            {{mb_value object=$patient field="prenom"}}
                        {{else}}
                            {{mb_field object=$patient field="prenom" onchange="Patient.checkDoublon(); Patient.copyIdentiteAssureValues(this)"}}
                        {{/if}}
                    </td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="prenoms"}}</th>
                    <td>{{mb_field object=$patient field="prenoms" onchange="Patient.checkDoublon(); Patient.copyIdentiteAssureValues(this)"}} </td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="prenom_usuel"}}</th>
                    <td>
                        {{if $patient->_id && $patient->status == "VALI" && !$allowed_modify}}
                            {{mb_value object=$patient field="prenom_usuel"}}
                        {{else}}
                            {{mb_field object=$patient field="prenom_usuel"}}
                        {{/if}}
                    </td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="nom_jeune_fille"}}</th>
                    <td>
                        {{if $patient->_id && $patient->status == "VALI" && !$allowed_modify}}
                            {{mb_value object=$patient field="nom_jeune_fille"}}
                        {{else}}
                            {{mb_field object=$patient field="nom_jeune_fille" onchange="Patient.checkDoublon(); Patient.copyIdentiteAssureValues(this)"}}
                            <button type="button" class="carriage_return notext"
                                    title="{{tr}}CPatient.name_recopy{{/tr}}"
                                    onclick="$V(getForm('editFrm').nom_jeune_fille, $V(getForm('editFrm').nom));"
                                    tabIndex="1000"></button>
                        {{/if}}
                    </td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="sexe"}}</th>
                    <td>
                        {{if $patient->_id && $patient->status == "VALI" && !$allowed_modify}}
                            {{mb_value object=$patient field="sexe"}}
                        {{else}}
                            {{mb_field object=$patient field="sexe" canNull=false typeEnum=radio
                            onchange="Patient.copyIdentiteAssureValues(this); Patient.changeCivilite();"}}
                        {{/if}}
                    </td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="naissance"}}</th>
                    <td>
                        {{if $patient->_id && $patient->status == "VALI" && !$allowed_modify}}
                            {{mb_value object=$patient field="naissance"}}
                        {{else}}
                            {{mb_field object=$patient field="naissance"
                            onchange="Patient.checkDoublon(); Patient.copyIdentiteAssureValues(this); Patient.changeCivilite();"}}
                        {{/if}}
                    </td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="civilite"}}</th>
                    <td>
                        {{assign var=civilite_locales value=$patient->_specs.civilite}}
                        <select name="civilite" onchange="Patient.copyIdentiteAssureValues(this);">
                            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                            {{foreach from=$civilite_locales->_locales key=key item=_civilite}}
                                <option value="{{$key}}" {{if $key == $patient->civilite}}selected{{/if}}>
                                    {{tr}}CPatient.civilite.{{$key}}-long{{/tr}} - ({{$_civilite}})
                                </option>
                            {{/foreach}}
                        </select>
                    </td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="situation_famille"}}</th>
                    <td>{{mb_field object=$patient field="situation_famille"}}</td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field=mdv_familiale}}</th>
                    <td>
                        {{mb_field object=$patient field=mdv_familiale
                        style="width: 12em;" emptyLabel="CPatient.mdv_familiale."}}
                    </td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field=condition_hebergement}}</th>
                    <td>
                        {{mb_field object=$patient field=condition_hebergement
                        style="width: 12em;" emptyLabel="CPatient.condition_hebergement."}}
                    </td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="rang_naissance"}}</th>
                    <td>{{mb_field object=$patient field="rang_naissance" emptyLabel=Select}}</td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="cp_naissance"}}</th>
                    <td>{{mb_field object=$patient field="cp_naissance" onchange="Patient.copyIdentiteAssureValues(this)"}}</td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="lieu_naissance"}}</th>
                    <td>
                        {{mb_field object=$patient field="lieu_naissance" onchange="Patient.copyIdentiteAssureValues(this)"}}
                        {{mb_field object=$patient field=commune_naissance_insee hidden=true}}
                    </td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="_pays_naissance_insee"}}</th>
                    <td>
                        {{mb_field object=$patient field="_pays_naissance_insee" onchange="Patient.copyIdentiteAssureValues(this)" class="autocomplete"}}
                        <div style="display:none;" class="autocomplete" id="_pays_naissance_insee_auto_complete"></div>
                    </td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field=niveau_etudes}}</th>
                    <td>
                        {{mb_field object=$patient field=niveau_etudes
                        style="width: 12em;" emptyLabel="CPatient.niveau_etudes."}}
                    </td>
                </tr>

                <tr>
                    <th class="halfPane">{{mb_label object=$patient field=activite_pro}}</th>
                    <td>
                        {{mb_field object=$patient field=activite_pro
                        style="width: 12em;" emptyLabel="CPatient.activite_pro." onchange="Patient.toggleActivitePro(this.value);"}}
                    </td>
                </tr>

                <tr class="activite_pro" {{if !$patient->activite_pro_date}}style="display: none;"{{/if}}>
                    <th class="halfPane">{{mb_label object=$patient field=activite_pro_date}}</th>
                    <td>
                        {{mb_field object=$patient field=activite_pro_date register=true form=editFrm}}
                    </td>
                </tr>

                <tr class="activite_pro" {{if !$patient->activite_pro_rques}}style="display: none;"{{/if}}>
                    <th class="halfPane">{{mb_label object=$patient field=activite_pro_rques}}</th>
                    <td>
                        {{mb_field object=$patient field=activite_pro_rques}}
                    </td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="profession"}}</th>
                    <td>{{mb_field object=$patient field="profession" form=editFrm onchange="Patient.copyIdentiteAssureValues(this)" autocomplete="true,2,30,true,true,2"}}</td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="csp"}}</th>
                    <td>
                        <input type="text" name="_csp_view" size="25" value="{{$patient->_csp_view}}"/>
                        {{mb_field object=$patient field="csp" hidden=true}}
                        <button type="button" class="cancel notext"
                                onclick="$V(this.form.elements['csp'], ''); $V(this.form.elements['_csp_view'], '');">{{tr}}Empty{{/tr}}</button>
                    </td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field=fatigue_travail}}</th>
                    <td>{{mb_field object=$patient field=fatigue_travail default=""}}</td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field=travail_hebdo}}</th>
                    <td>{{mb_field object=$patient field=travail_hebdo}}</td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field=transport_jour}}</th>
                    <td>{{mb_field object=$patient field=transport_jour}}</td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="matricule"}}</th>
                    <td>{{mb_field object=$patient field="matricule" onchange="Patient.copyIdentiteAssureValues(this)"}}</td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="qual_beneficiaire"}}</th>
                    <td>{{mb_field object=$patient field="qual_beneficiaire" style="width:20em;"}}</td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="tutelle"}}</th>
                    <td colspan="2">
                        {{mb_field object=$patient field="tutelle" typeEnum=radio default=$patient->tutelle onchange="Patient.refreshInfoTutelle(this.value);"}}
                    </td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="don_organes"}}</th>
                    <td colspan="2">{{mb_field object=$patient field="don_organes" typeEnum=radio}}</td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="directives_anticipees"}}</th>
                    <td colspan="2" class="td-directives-anticipees">
                        {{assign var=display_warning value=0}}
                        {{if $patient->directives_anticipees == 1}}
                            {{if is_countable($patient->_refs_directives_anticipees) && $patient->_refs_directives_anticipees|@count == 0}}
                                {{assign var=display_warning value=1}}
                            {{/if}}
                        {{/if}}
                        {{mb_field object=$patient field="directives_anticipees" typeEnum=radio onchange="Patient.checkAdvanceDirectives(this, '$display_warning');"}}

                        {{if $patient->directives_anticipees == 1}}
                            <button type="button" class="search notext"
                                    title="{{tr}}CDirectiveAnticipee-action-See advance directive|pl{{/tr}}"
                                    onclick="Patient.showAdvanceDirectives();" tabindex="1000"></button>
                        {{/if}}

                        {{if $display_warning}}
                            <i class="fas fa-exclamation-triangle no-directives" style="color: #ff9502; font-size: 14px"
                               title="{{tr}}CDirectiveAnticipee-No directive{{/tr}}"></i>
                        {{/if}}
                    </td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="vip"}}</th>
                    <td colspan="2">{{mb_field object=$patient field="vip" typeEnum="checkbox"}}</td>
                </tr>

                {{if "terreSante"|module_active}}
                    {{mb_include module=terreSante template=inc_checkbox_consent patient=$patient}}
                {{/if}}

                <tr>
                    <th>{{mb_label object=$patient field="deces"}}</th>
                    <td colspan="2">{{mb_field object=$patient field="deces" register=true form=editFrm}}</td>
                </tr>
            </table>
        </td>

        <td>
            <table class="form">
                <col style="width: 100px;"/>
                <tr>
                    <th class="category" colspan="2">{{tr}}CPatient-coordonnees-patient-title{{/tr}}</th>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="adresse"}}</th>
                    <td>{{mb_field object=$patient field="adresse" onchange="Patient.copyAssureValues(this)"}}</td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="cp"}}</th>
                    <td>{{mb_field object=$patient field="cp" onchange="Patient.copyAssureValues(this)"}}</td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="ville"}}</th>
                    <td>{{mb_field object=$patient field="ville" onchange="Patient.copyAssureValues(this)"}}</td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="pays"}}</th>
                    <td>
                        {{mb_field object=$patient field="pays" size="31" onchange="Patient.copyAssureValues(this)" class="autocomplete"}}
                        <div style="display:none;" class="autocomplete" id="pays_auto_complete"></div>
                    </td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field=phone_area_code}}</th>
                    <td>{{mb_field object=$patient field=phone_area_code}}</td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="tel"}}</th>
                    <td>
                        {{mb_field object=$patient field="tel" onchange="Patient.copyAssureValues(this)" onkeyup="Patient.checkNotMobilePhone(this);"}}
                        <div class="warning" id="phoneFormat"
                             style="display: none;">{{tr}}CPatient-alert-Warning this looks like a mobile phone number{{/tr}}</div>
                    </td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="tel2"}}</th>
                    <td>
                        {{mb_field object=$patient field="tel2" onchange="Patient.copyAssureValues(this);" onkeyup="Patient.checkMobilePhone(this);"}}
                        {{mb_field object=$patient field="allow_sms_notification" typeEnum='checkbox'}}{{mb_label object=$patient field="allow_sms_notification"}}
                        <div class="warning" id="mobilePhoneFormat"
                             style="display: none;">{{tr}}CPatient-alert-Warning this does not look like a mobile phone number{{/tr}}</div>
                    </td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="tel_pro"}}</th>
                    <td>{{mb_field object=$patient field="tel_pro"}}</td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="tel_autre"}}</th>
                    <td>{{mb_field object=$patient field="tel_autre"}}</td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field=tel_autre_mobile}}</th>
                    <td>{{mb_field object=$patient field=tel_autre_mobile}}</td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="email"}}</th>
                    <td>
                        {{mb_field object=$patient field="email"}}
                        {{mb_field object=$patient field="allow_email" typeEnum='checkbox'}}{{mb_label object=$patient field="allow_email"}}
                    </td>
                </tr>

                <tr>
                    <th>{{mb_label object=$patient field="rques"}}</th>
                    <td>{{mb_field object=$patient field="rques"}}</td>
                </tr>
                {{if "provenance"|module_active && 'Ox\Core\CAppUI::isGroup'|static_call:null}}
                    <tr>
                        {{mb_include module=provenance template=inc_edit_provenance_patient}}
                    </tr>
                {{/if}}

                {{if $conf.dPpatients.CPatient.function_distinct}}
                    <tr>
                        <th></th>
                        <td>
                            <button type="button" class="search"
                                    onclick="Patient.accessibilityData();">{{tr}}CPatient-accessibility_data{{/tr}}</button>
                        </td>
                    </tr>
                {{/if}}

                {{if "sisra"|module_active}}
                    <tr>
                        <th>{{mb_label object=$patient field="allow_sisra_send"}}</th>
                        <td>{{mb_field object=$patient field="allow_sisra_send"}}</td>
                    </tr>
                {{/if}}

                {{if $functions|@count > 1}}
                    <tr>
                        <th class="category" colspan="2">{{tr}}CPatient-Cabinet choice{{/tr}}</th>
                    </tr>
                    <tr>
                        <th></th>
                        <td>
                            <select name="function_id" onchange="$V(getForm('editFrm').function_id, this.value);">
                                {{mb_include module=mediusers template=inc_options_function list=$functions selected=$patient->function_id}}
                            </select>
                        </td>
                    </tr>
                {{/if}}
            </table>
        </td>
    </tr>
</table>
