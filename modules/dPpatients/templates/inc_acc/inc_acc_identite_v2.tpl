{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=anticipated_directives ajax=$ajax}}
{{mb_script module=dPpatients script=ins ajax=$ajax}}
{{mb_script module=jfse script=Jfse ajax=$ajax}}
{{mb_script module=jfse script=VitalCard ajax=$ajax}}
{{mb_script module=patients   script=patient_handicap}}

{{mb_default var=use_id_interpreter value=0}}
{{mb_default var=validate_identity value=0}}

{{assign var=readonly value=null}}

{{if $conf.ref_pays === '1' && $patient->_id && $patient->status && !in_array($patient->status, array('VIDE', 'PROV'))}}
    {{assign var=readonly value=1}}
{{/if}}

<script>
    Main.add(function () {
        {{if $patient->_id}}
          Patient.refreshInfoTutelle('{{$patient->tutelle}}');
        {{else}}
          Patient.checkDoublon();
        {{/if}}

        Patient.origin_mode_obtention = "{{$patient->_mode_obtention}}";

        InseeFields.initCPVille("editFrm", "cp", "ville", null, 'pays', "tel");
        InseeFields.initCPVille("editFrm", "cp_naissance", "lieu_naissance", "_code_insee", "_pays_naissance_insee", "adresse");
    });
</script>

<div id="alert_tutelle"></div>

{{if $validate_identity}}
    <div class="small-info">
        {{tr}}CPatient-Ways to validate identity{{/tr}}
    </div>
{{/if}}

{{if $readonly && $app->user_prefs.allow_modify_strict_traits}}
    <div class="small-warning">
        {{tr}}CPatient-Unlock strict traits modification{{/tr}}
        <button type="button" class="unlock" onclick="Patient.allowTraitStrictModification(this.up('div'));">{{tr}}Unlock{{/tr}}</button>
    </div>
{{/if}}

<div class="me-poc-container">
    <div class="me-list-categories">
        <div class="me-categorie-form identite {{if !$patient->_id}}identite_new{{/if}}" id="patient_identite">
            <div class="categorie-form_titre text">
                {{tr}}common-Identity{{/tr}}

                <span class"me-float-right">
          {{if !$patient->_ref_sources_identite|@count || in_array($patient->status, array('PROV', 'VIDE'))}}
              {{if $app->user_prefs.LogicielLectureVitale == 'mbHost'}}
                  {{assign var=autoRead value=false}}
                  {{if !$patient->_id}}
                      {{assign var=autoRead value=true}}
                  {{/if}}

                  {{mb_include module=mbHost template=inc_vitale operation='create' autoRead=$autoRead formName='editFrm'}}
              {{/if}}
          {{/if}}

                    {{if $patient->_id}}
                        <button type="button" onclick="SourceIdentite.openList();"
                                class="search me-tertiary">{{tr}}CSourceIdentite|pl{{/tr}}</button>
                        {{mb_include module=patients template=inc_status_icon}}
                    {{/if}}

          <button type="button" class="add me-secondary" onclick="Patient.addJustificatif('{{$patient->_id}}')"
                  class="add">{{tr}}CPatient-Justificatif{{/tr}}</button>

          {{if !$patient->_id && $conf.ref_pays == 1}}
              <button class="me-tertiary fas fa-qrcode" type="button" onclick="INS.openModalReadDatamatrixINS(0)">
              INS
            </button>
          {{/if}}
                    {{if "ameli"|module_active && $patient->_id && $app->user_prefs.allow_use_insi_tlsi}}
                        {{mb_include module=ameli template=services/inc_insiicir_button}}
                    {{/if}}

                    {{if $patient->_ref_patient_ins_nir->_id}}
                        <button type="button" class="search"
                                onclick="Patient.showDatamatrixIns('{{$patient->_id}}')">{{tr}}CPatientINSNIR_datamatrix_ins{{/tr}}</button>
                    {{/if}}
        </span>

                {{if "jfse"|module_active && $app->user_prefs.LogicielFSE == 'jfse'}}
                    <span class"me-float-right">
                {{if $patient->_id}}
                    {{mb_include module=jfse template=vital_card/update_button}}
                {{/if}}
            </span>
                {{/if}}

            </div>
            <div class="categorie-form_photo" id="{{$patient->_guid}}-identity">
                {{if $patient->_id}}
                    {{mb_include template=inc_vw_photo_identite size="60" mode="edit"}}
                {{/if}}
            </div>
            <div class="categorie-form_fields">
                <div class="categorie-form_fields-group">
                    <div id="doublon-patient"></div>
                    {{me_form_field mb_object=$patient mb_field="nom_jeune_fille"}}
                        {{mb_field object=$patient field="nom_jeune_fille" onchange="Patient.checkDoublon(); Patient.copyIdentiteAssureValues(this)"
                             readonly=$readonly class="trait-strict"}}
                        {{if !$patient->_id}}
                            <button type="button" class="me-tertiary notext anonyme" style="padding: 0;"
                                    onclick="Patient.anonymous()"
                                    tabIndex="1000"></button>
                        {{/if}}
                    {{/me_form_field}}

                    <div id="NamesMatchWarning" style="display: none" class="small-warning">{{tr}}CPatient.first_birth_name_warning{{/tr}}</div>

                    {{if $readonly}}
                        {{assign var=onchange value="Patient.checkBirthNameMatchesNames();"}}
                    {{else}}
                        {{assign var=onchange value="Patient.checkDoublon(); Patient.copyIdentiteAssureValues(this); Patient.checkBirthNameMatchesNames();"}}
                    {{/if}}

                    {{me_form_field mb_object=$patient mb_field="prenom"}}
                        {{mb_field object=$patient field="prenom" onchange=$onchange readonly=$readonly class="trait-strict"}}
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="prenoms"}}
                        {{mb_field object=$patient field=prenoms readonly=$readonly class="trait-strict"
                        onchange="Patient.checkDoublon(); Patient.copyIdentiteAssureValues(this); Patient.toggleCopyPrenom(this); Patient.checkBirthNameMatchesNames();"}}

                        {{if !$readonly}}
                            <button type="button" id="copy_prenom" class="carriage_return notext me-tertiary"
                                    title="{{tr}}CPatient.firstname_recopy{{/tr}}"
                                    onclick="Patient.copyPrenom(this.form.prenom);"
                                    {{if $patient->prenoms}}style="display: none;"{{/if}}
                                    tabIndex="1001"></button>
                        {{/if}}
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="prenom_usuel"}}
                        {{mb_field object=$patient field="prenom_usuel"}}
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="nom"}}
                        {{mb_field object=$patient field="nom" onchange="Patient.checkDoublon(); Patient.copyIdentiteAssureValues(this)"}}
                        <button type="button" class="carriage_return notext me-tertiary"
                                title="{{tr}}CPatient.name_recopy{{/tr}}"
                                onclick="$V(getForm('editFrm').nom, $V(getForm('editFrm').nom_jeune_fille));"
                                tabIndex="1000"></button>
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="naissance"}}
                        {{mb_field object=$patient field="naissance"
                             onchange="Patient.checkDoublon(); Patient.copyIdentiteAssureValues(this); Patient.changeCivilite();"
                             readonly=$readonly
                             class="trait-strict"}}
                    {{/me_form_field}}

                    {{me_form_field layout=true mb_object=$patient mb_field="sexe" readonly=$readonly }}
                        {{mb_field object=$patient field="sexe" typeEnum=radio canNull=false
                             onchange="Patient.copyIdentiteAssureValues(this); Patient.changeCivilite();"
                             readonly=$readonly class="trait-strict"}}
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="civilite"}}
                    {{assign var=civilite_locales value=$patient->_specs.civilite}}
                        <select name="civilite" onchange="Patient.copyIdentiteAssureValues(this);">
                            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                            {{foreach from=$civilite_locales->_locales key=key item=_civilite}}
                                <option value="{{$key}}" {{if $key == $patient->civilite}}selected{{/if}}>
                                    {{tr}}CPatient.civilite.{{$key}}-long{{/tr}} - ({{$_civilite}})
                                </option>
                            {{/foreach}}
                        </select>
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="lieu_naissance"}}
                        {{mb_field object=$patient field="lieu_naissance" onchange="Patient.copyIdentiteAssureValues(this)" readonly=$readonly class="trait-strict"}}
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="cp_naissance"}}
                        {{mb_field object=$patient field="cp_naissance" onchange="Patient.copyIdentiteAssureValues(this)" readonly=$readonly class="trait-strict"}}
                    {{/me_form_field}}

                    {{mb_field object=$patient field="commune_naissance_insee" readonly=$readonly hidden="hidden" class="trait-strict"}}

                    {{me_form_field mb_object=$patient mb_field="_pays_naissance_insee"}}
                        {{mb_field object=$patient field="_pays_naissance_insee" onchange="Patient.copyIdentiteAssureValues(this)" class="trait-strict autocomplete" readonly=$readonly}}
                        <div style="display:none;" class="autocomplete" id="_pays_naissance_insee_auto_complete"></div>
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="_code_insee"}}
                        {{mb_field object=$patient field="_code_insee" class="trait-strict autocomplete" readonly=$readonly}}
                        <div style="display:none;" class="autocomplete" id="_code_insee_complete"></div>
                    {{/me_form_field}}

                    <div class="small-info">
                        {{tr}}CSourceIdentite-Information about birth location{{/tr}}
                    </div>
                </div>
                <div class="categorie-form_fields-group">
                    {{me_form_field mb_object=$patient mb_field="rang_naissance"}}
                    {{mb_field object=$patient field="rang_naissance" emptyLabel=Select}}
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="deces"}}
                    {{mb_field object=$patient field="deces" register=true form=editFrm}}
                    {{/me_form_field}}

                    {{me_form_bool mb_object=$patient mb_field="vip"}}
                    {{mb_field object=$patient field="vip" typeEnum="checkbox"}}
                    {{/me_form_bool}}

                    {{me_form_bool mb_object=$patient mb_field="_douteux"}}
                    {{mb_field object=$patient field="_douteux" typeEnum="checkbox" onchange="Patient.warningIdentity(this, 'douteux');"}}
                    {{/me_form_bool}}

                    {{me_form_bool mb_object=$patient mb_field="_fictif"}}
                    {{mb_field object=$patient field="_fictif" typeEnum="checkbox" onchange="Patient.warningIdentity(this, 'fictif');"}}
                    {{/me_form_bool}}
                </div>

                <div class="categorie-form_fields-group">
                    {{me_form_field mb_object=$ins label='CPatientINSNIR-desc' mb_field="ins_nir"}}
                    {{mb_field object=$ins field="ins_nir" readonly=true}}
                    {{/me_form_field}}

                    {{if $ins->_id}}
                        {{me_form_field mb_object=$ins mb_field="_ins_type"}}
                          {{mb_field object=$ins field=_ins_type readonly=true}}
                        {{/me_form_field}}
                    {{/if}}

                    {{me_form_field mb_object=$ins mb_field="oid"}}
                    {{mb_field object=$ins field="oid" readonly=true}}
                    {{/me_form_field}}
                </div>
            </div>
        </div>

        <div class="me-categorie-form adresse">
            <div class="categorie-form_titre">
                Coordonnées et contact
            </div>
            <div class="categorie-form_fields">
                <div class="categorie-form_fields-group">
                    {{me_form_field mb_object=$patient mb_field="adresse"}}
                    {{mb_field object=$patient field="adresse" onchange="Patient.copyAssureValues(this)"}}
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="cp"}}
                    {{mb_field object=$patient field="cp" onchange="Patient.copyAssureValues(this)"}}
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="ville"}}
                    {{mb_field object=$patient field="ville" onchange="Patient.copyAssureValues(this)"}}
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="pays"}}
                    {{mb_field object=$patient field="pays" size="31" onchange="Patient.copyAssureValues(this)" class="autocomplete"}}
                        <div style="display:none;" class="autocomplete" id="pays_auto_complete"></div>
                    {{/me_form_field}}
                </div>

                <div class="categorie-form_fields-group">
                    {{me_form_field mb_object=$patient mb_field="phone_area_code"}}
                    {{mb_field object=$patient field=phone_area_code}}
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="tel"}}
                    {{mb_field object=$patient field="tel" onchange="Patient.copyAssureValues(this)" onkeyup="Patient.checkNotMobilePhone(this);"}}
                        <div class="warning" id="phoneFormat"
                             style="display: none;">{{tr}}CPatient-alert-Warning this looks like a mobile phone number{{/tr}}</div>
                    {{/me_form_field}}

                    {{me_form_field layout=true mb_object=$patient mb_field="tel2" field_class="me-no-border me-padding-0"}}
                    {{mb_field object=$patient field="tel2" onchange="Patient.copyAssureValues(this);" onkeyup="Patient.checkMobilePhone(this);"}}
                    {{mb_field object=$patient field="allow_sms_notification" typeEnum='checkbox'}}{{mb_label object=$patient field="allow_sms_notification"}}
                        <div class="warning" id="mobilePhoneFormat"
                             style="display: none;">{{tr}}CPatient-alert-Warning this does not look like a mobile phone number{{/tr}}</div>
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="tel_pro"}}
                    {{mb_field object=$patient field="tel_pro"}}
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="tel_autre"}}
                    {{mb_field object=$patient field="tel_autre"}}
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="tel_autre_mobile"}}
                    {{mb_field object=$patient field=tel_autre_mobile}}
                    {{/me_form_field}}

                    {{me_form_field layout=true mb_object=$patient mb_field="email" field_class="me-no-border me-padding-0"}}
                    {{mb_field object=$patient field="email"}}
                    {{/me_form_field}}

                    {{me_form_field layout=true mb_object=$patient mb_field="allow_email"}}
                        {{if $patient->allow_email != 2 && !"dPpatients CPatient allow_email_not_defined"|gconf}}
                            {{assign var=prop value="enum list|0|1 fieldset|contact"}}
                        {{else}}
                            {{assign var=prop value="enum list|0|1|2 fieldset|contact"}}
                        {{/if}}
                        {{mb_field object=$patient field="allow_email" typeEnum='radio' prop=$prop}}
                    {{/me_form_field}}
                </div>
            </div>
        </div>
    </div>

    <div class="me-list-categories">

        <div class="me-categorie-form situation">
            <div class="categorie-form_titre">
                Situation
            </div>
            <div class="categorie-form_fields">
                <div class="categorie-form_fields-group">
                    {{me_form_field mb_object=$patient mb_field="situation_famille"}}
                    {{mb_field object=$patient field="situation_famille"}}
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="mdv_familiale"}}
                    {{mb_field object=$patient field=mdv_familiale
                    style="width: 12em;" emptyLabel="CPatient.mdv_familiale."}}
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="condition_hebergement"}}
                    {{mb_field object=$patient field=condition_hebergement
                    style="width: 12em;" emptyLabel="CPatient.condition_hebergement."}}
                    {{/me_form_field}}
                </div>

                <div class="categorie-form_fields-group">
                    {{me_form_field mb_object=$patient mb_field="niveau_etudes"}}
                    {{mb_field object=$patient field=niveau_etudes
                    style="width: 12em;" emptyLabel="CPatient.niveau_etudes."}}
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="activite_pro"}}
                    {{mb_field object=$patient field=activite_pro
                    style="width: 12em;" emptyLabel="CPatient.activite_pro." onchange="Patient.toggleActivitePro(this.value);"}}
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="activite_pro_date"}}
                    {{mb_field object=$patient field=activite_pro_date register=true form=editFrm}}
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="activite_pro_rques"}}
                    {{mb_field object=$patient field=activite_pro_rques}}
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="profession"}}
                    {{mb_field object=$patient field="profession" form=editFrm onchange="Patient.copyIdentiteAssureValues(this)" autocomplete="true,2,30,true,true,2"}}
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="csp"}}
                        <input type="text" name="_csp_view" size="25" value="{{$patient->_csp_view}}"/>
                    {{mb_field object=$patient field="csp" hidden=true}}
                        <button type="button" class="cancel notext me-tertiary me-dark"
                                onclick="$V(this.form.elements['csp'], ''); $V(this.form.elements['_csp_view'], '');">{{tr}}Empty{{/tr}}</button>
                    {{/me_form_field}}

                    {{me_form_bool layout=true mb_object=$patient mb_field="fatigue_travail"}}
                    {{mb_field object=$patient field=fatigue_travail default=""}}
                    {{/me_form_bool}}

                    {{me_form_field  mb_object=$patient mb_field="travail_hebdo"}}
                    {{mb_field object=$patient field=travail_hebdo}}
                    {{/me_form_field}}

                    {{me_form_field  mb_object=$patient mb_field="transport_jour"}}
                    {{mb_field object=$patient field=transport_jour}}
                    {{/me_form_field}}

                    <table>
                        {{mb_include module=patients template=inc_field_handicap}}
                    </table>
                </div>
            </div>
        </div>
        <div class="me-categorie-form assurance">
            <div class="categorie-form_titre">
                Assurance
            </div>
            <div class="categorie-form_fields">
                <div class="categorie-form_fields-group">
                    {{me_form_field mb_object=$patient mb_field="matricule"}}
                        {{if in_array($patient->status, array('VIDE', 'PROV', 'VALI'))}}
                            {{mb_field object=$patient field="matricule" onchange="Patient.copyIdentiteAssureValues(this)" class="trait-strict"}}
                        {{else}}
                            {{mb_field object=$patient field="matricule" onchange="Patient.copyIdentiteAssureValues(this)" readonly=$readonly disabled=$readonly class="trait-strict"}}
                        {{/if}}
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="qual_beneficiaire"}}
                    {{mb_field object=$patient field="qual_beneficiaire" style="width:20em;"}}
                    {{/me_form_field}}

                    {{me_form_field layout=true mb_object=$patient mb_field="tutelle"}}
                    {{mb_field object=$patient field="tutelle" typeEnum=radio default=$patient->tutelle onchange="Patient.refreshInfoTutelle(this.value);"}}
                    {{/me_form_field}}
                </div>
            </div>
        </div>
        <div class="me-categorie-form info-sup">
            <div class="categorie-form_titre">
                Informations supplémentaires
            </div>
            <div class="categorie-form_fields">
                <div class="categorie-form_fields-group">
                    {{me_form_field layout=true mb_object=$patient mb_field="don_organes"}}
                    {{mb_field object=$patient field="don_organes" typeEnum=radio}}
                    {{/me_form_field}}

                    {{me_form_field layout=true mb_object=$patient mb_field="directives_anticipees" field_class="td-directives-anticipees"}}
                    {{assign var=display_warning value=0}}
                    {{if $patient->directives_anticipees == 1}}
                        {{if is_countable($patient->_refs_directives_anticipees) && $patient->_refs_directives_anticipees|@count == 0}}
                            {{assign var=display_warning value=1}}
                        {{/if}}
                    {{/if}}
                    {{mb_field object=$patient field="directives_anticipees" typeEnum=radio onchange="Patient.checkAdvanceDirectives(this, '$display_warning');"}}

                    {{if $patient->directives_anticipees == 1}}
                        <button type="button" class="search notext me-tertiary"
                                title="{{tr}}CDirectiveAnticipee-action-See advance directive|pl{{/tr}}"
                                onclick="Patient.showAdvanceDirectives();" tabindex="1000"></button>
                    {{/if}}

                    {{if $display_warning}}
                        <i class="fas fa-exclamation-triangle no-directives" style="color: #ff9502; font-size: 14px"
                           title="{{tr}}CDirectiveAnticipee-No directive{{/tr}}"></i>
                    {{/if}}
                    {{/me_form_field}}

                    {{me_form_field mb_object=$patient mb_field="rques"}}
                    {{mb_field object=$patient field="rques"}}
                    {{/me_form_field}}
                </div>

                {{if "sisra"|module_active}}
                    <div>
                        {{me_form_bool mb_object=$patient mb_field="allow_sisra_send"}}
                          {{mb_field object=$patient field="allow_sisra_send"}}
                        {{/me_form_bool}}
                    </div>
                {{/if}}

                {{if "terreSante"|module_active}}
                    {{mb_include module=terreSante template=inc_checkbox_consent patient=$patient}}
                {{/if}}

                {{if "dmp"|module_active}}
                    {{mb_include module=dmp template=inc_checkbox_consent patient=$patient}}
                {{/if}}

                {{if "mssante"|module_active}}
                    {{mb_include module=mssante template=inc_checkboxes_consent patient=$patient}}
                {{/if}}

                {{if $functions|@count > 1}}
                    <div class="categorie-form_fields-group">
                        {{me_form_field label="CPatient-Cabinet choice"}}
                            <select name="function_id" onchange="$V(getForm('editFrm').function_id, this.value);">
                                {{mb_include module=mediusers template=inc_options_function list=$functions selected=$patient->function_id}}
                            </select>
                        {{/me_form_field}}
                    </div>
                {{/if}}

                {{if "provenance"|module_active && 'Ox\Core\CAppUI::isGroup'|static_call:null}}
                    {{mb_include module=provenance template=inc_edit_provenance_patient_v2}}
                {{/if}}

                {{if $conf.dPpatients.CPatient.function_distinct}}
                  <div class="categorie-form_fields-group">
                    <button type="button" class="search me-tertiary"
                            onclick="Patient.accessibilityData();">{{tr}}CPatient-accessibility_data{{/tr}}</button>
                  </div>
                {{/if}}
            </div>
        </div>
    </div>
</div>
