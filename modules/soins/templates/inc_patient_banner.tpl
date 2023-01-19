{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=mode_pharma value=0}}
{{mb_default var=mode_protocole value=0}}
{{mb_default var=operation_id value=0}}
{{mb_default var=rpu value=null}}
{{mb_default var=see_modif_patient value=1}}
{{mb_default var=switch_view value=0}}
{{mb_default var=with_buttons value=1}}

{{mb_default var=nda_view value=false}}
{{mb_default var=check_mandatory_forms value=false}}

{{unique_id var=unique_id_widget_forms}}

{{assign var=dossier_medical value=$patient->_ref_dossier_medical}}
{{assign var=is_sejour value=false}}
{{if $object|instanceof:'Ox\Mediboard\Cabinet\CConsultation'}}
    {{assign var=antecedents value=$dossier_medical->_ref_antecedents_by_type}}
    {{assign var=dossier_medical_sejour value=0}}
{{else}}
    {{assign var=is_sejour value=true}}
    {{assign var=sejour_id value=$object->_id}}
    {{assign var=prescription value=$object->_ref_prescription_sejour}}
    {{assign var=dossier_medical_sejour value=$object->_ref_dossier_medical}}
    {{assign var=sejour value=$object}}
{{/if}}

{{if $dossier_medical_sejour}}
    {{assign var=antecedents_sejour value=$dossier_medical_sejour->_ref_antecedents_by_type}}
{{else}}
    {{assign var=antecedents_sejour value=0}}
{{/if}}
{{assign var=antecedents value=$dossier_medical->_ref_antecedents_by_type}}
{{assign var=conf_preselect_prat value="dPprescription general preselection_praticien_auto"|gconf}}
{{assign var=is_executant_prescription value=CAppUI::$user->isExecutantPrescription()}}

{{assign var=background_color value=""}}
{{if $patient->_annees < 2}}
    {{assign var=background_color value="background-color: #ABE;"}}
{{/if}}

<table class="tbl me-no-align me-no-border-radius-bottom me-patient-banner me-variante">
    <tr>
        <th class="title text" style="text-align: left; border: none; width: 80px; {{$background_color}}">
            {{mb_include module=system template=inc_object_notes object=$patient}}
            {{if $is_sejour}}
                {{assign var=sejour_conf value=$sejour->presence_confidentielle}}
            {{else}}
                {{assign var=sejour_conf value=false}}
            {{/if}}
            <a href="?m=dPpatients&tab=vw_full_patients&patient_id={{$patient->_id}}">
                {{mb_include module="patients" template=inc_vw_photo_identite mode="read" size=52 sejour_conf=$sejour_conf}}
            </a>
        </th>
        <th class="title text" style="border: none; {{$background_color}}">
            <form name="actionPat" action="?" method="get">
                <input type="hidden" name="m" value="patients"/>
                <input type="hidden" name="tab" value="vw_idx_patients"/>
                <input type="hidden" name="patient_id" value="{{$patient->_id}}"/>
                <h2 class="title">
          <span style="font-size: 0.8em;" onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
            {{$patient}}
          </span>

                    {{mb_include module=patients template=inc_icon_bmr_bhre}}

                    {{if $is_sejour && $sejour->_covid_diag}}
                        <span class="texticon texticon-stup" title="{{$sejour->_covid_diag->libelle}}"
                              style="font-size: 10pt;">
              {{$sejour->_covid_diag->libelle_court}}
            </span>
                    {{/if}}

                    <span style="display:inline-block;max-height: 15px;">
            {{mb_include module=patients template=vw_antecedents_allergies}}
          </span>
                    {{if $is_sejour}}
                        {{if "maternite"|module_active}}
                            {{if $object->grossesse_id}}
                                {{if $object->_ref_patient->_ref_last_grossesse && $object->_ref_patient->_ref_last_grossesse->_id && !$object->_ref_patient->_ref_last_grossesse->datetime_cloture && $object->_ref_patient->_ref_last_grossesse->active}}
                                    <span class="texticon texticon-grossesse"
                                          onmouseover="ObjectTooltip.createEx(this, '{{$object->_ref_patient->_ref_last_grossesse->_guid}}')">
                    {{tr}}CGrossesse-in_progress{{/tr}}
                  </span>
                                {{/if}}
                                {{if $object->_ref_grossesse && $object->_ref_grossesse->_ref_dossier_perinat}}
                                    {{mb_include module=cabinet template=inc_conduite_a_tenir dossier=$object->_ref_grossesse->_ref_dossier_perinat}}
                                {{/if}}
                            {{/if}}

                            {{if $object->_ref_patient->civilite == "enf" && $object->_ref_naissance && $object->_ref_naissance->_id}}
                                {{assign var=grossesse_maman value=$object->_ref_naissance->_ref_grossesse}}

                                {{if $grossesse_maman && $grossesse_maman->_id}}
                                    <span style="font-size: 0.7em;">
                   - {{mb_value object=$grossesse_maman field=_semaine_grossesse}} <span
                                          title="{{tr}}CConsultation-Term of mom s pregnancy{{/tr}}">{{tr}}CGrossesse-_semaine_grossesse-court{{/tr}}</span>
                   + {{mb_value object=$grossesse_maman field=_reste_semaine_grossesse}} j
                   -
                    {{tr var1=$grossesse_maman->terme_prevu|date_format:$conf.date}}CGrossesse-Expected term the %s{{/tr}}
                  </span>
                                {{/if}}
                            {{/if}}

                            {{mb_include module=maternite template=inc_input_grossesse patient=$object->_ref_patient modify_grossesse=0}}
                            {{if $object->_class == "CPatient"}}
                                {{assign var=pregnancy value=$object->_ref_last_grossesse}}
                            {{else}}
                                {{assign var=pregnancy value=$object->_ref_grossesse}}
                            {{/if}}
                            {{if $pregnancy && $pregnancy->_id}}
                                <span style="font-size: 0.8em;">
                                  {{if $pregnancy->active}}
                                    - {{mb_value object=$pregnancy field=_semaine_grossesse}} <span
                                      title="{{tr}}CGrossesse-_semaine_grossesse-desc{{/tr}}">{{tr}}CGrossesse-_semaine_grossesse-court{{/tr}}
                                    + {{mb_value object=$pregnancy field=_reste_semaine_grossesse}} j
                                     </span>
                                  {{/if}}
                  -
                  {{tr var1=$pregnancy->terme_prevu|date_format:$conf.date}}CGrossesse-Expected term the %s{{/tr}}
                </span>
                            {{/if}}
                        {{/if}}
                        {{if $object->_class == "CSejour"}}
                            <span id="sejour-ATNC" class="texticon-atnc dhe_flag_important"
                                  title="{{tr}}CSejour-ATNC-desc{{/tr}}" style={{if $object->ATNC != '1'}}"display: none;"{{else}}"font-size: 0.5em;"{{/if}}>
                            {{tr}}CSejour-ATNC-court{{/tr}}</span>

                            <span id="sejour-ATNC" class="texticon-atnc dhe_flag_RRAC"
                                  title="{{tr}}CSejour-RRAC-desc{{/tr}}" style={{if $object->RRAC != '1'}}"display: none;"{{else}}"font-size: 0.5em;"{{/if}}>
                            {{tr}}CSejour-RRAC-court{{/tr}}</span>

                            {{if isset($late_objectifs|smarty:nodefaults) && $late_objectifs|@count}}
                                <span id="sejour-late_objectif" class="texticon texticon-lateObjectifSoin text"
                                      onmouseover="ObjectTooltip.createDOM(this, 'tooltip-ObjectifSoin');">{{tr}}CObjectifSoin{{/tr}}</span>
                                {{*Permet d'afficher la tooltip contenant la liste des late_objectifs*}}
                                <div id="tooltip-ObjectifSoin"
                                     style="display:none">{{mb_include template=inc_vw_list_objectifs_soins listObjectifsSoins=$late_objectifs}}</div>
                            {{/if}}

                        {{/if}}
                        {{if $object->isolement}}
                            <span class="texticon texticon-isolement" title="Isolement">Isol</span>
                        {{/if}}
                        {{if "nouveal"|module_active && "nouveal general active_prm"|gconf}}
                            {{mb_include module=nouveal template=inc_vw_etat_patient sejour=$object container_name="banner"}}
                        {{/if}}
                        {{if $patient->_homonyme}}
                            {{mb_include module=dPpatients template=patient_state/inc_flag_homonyme}}
                        {{/if}}
                        <br/>
                        {{if $object->rques }}
                            <i class="fa fa-exclamation-triangle inc_banner_patient_rques"
                               title="{{$object->rques}}"></i>
                        {{/if}}
                        <span style="font-size: 0.7em;" onmouseover="ObjectTooltip.createEx(this, '{{$object->_guid}}')"
                        >
            {{if $rpu}}
                Admission du {{$rpu->_entree|date_format:$conf.date}}
            {{else}}
                {{$object->_shortview|replace:"Du":"Séjour du"}}
            {{/if}}
            </span>
                        {{if $is_sejour && $sejour->presence_confidentielle}}
                            {{mb_include module=planningOp template=inc_badge_sejour_conf}}
                        {{/if}}

                        -
                        <span style="font-size: 0.6em;">

              <span id="motif_complet_{{$object->_guid}}">
                {{mb_include module=planningOp template=inc_motif_sejour sejour=$object}}
              </span>

                            {{* N'afficher le libellé d'intervention que lorsqu'il y en a qu'une *}}
                            {{if $object->libelle && $object->_ref_operations|@count == 1}}
                                &mdash;
                                {{foreach from=$object->_ref_operations item=_op name=op}}
                                    {{$_op->libelle|smarty:nodefaults|spancate:30:"...":false}} {{if !$smarty.foreach.op.last}};{{/if}}
                                {{/foreach}}
                            {{/if}}
            </span>
                        {{if $object->_jour_op}}
                            {{assign var=nb_days_hide_op value="soins dossier_soins nb_days_hide_op"|gconf}}
                            {{foreach from=$object->_jour_op item=_info_jour_op}}
                                {{if $nb_days_hide_op == 0 || $nb_days_hide_op > $_info_jour_op.jour_op}}
                                    {{if $_info_jour_op.rques }}
                                        <i class="fa fa-exclamation-triangle inc_banner_patient_rques"
                                           title="{{$_info_jour_op.rques}}"></i>
                                    {{/if}}
                                    <span style="font-size: 0.8em;"
                                          onmouseover="ObjectTooltip.createEx(this, '{{$_info_jour_op.operation_guid}}');">(J{{$_info_jour_op.jour_op}})</span>
                                {{/if}}
                            {{/foreach}}
                        {{/if}}
                        {{if $object->_ref_curr_affectation->_id}}
                            <span style="font-size: 0.6em;">
              {{$object->_ref_curr_affectation->_ref_lit}}
            </span>
                        {{/if}}

                        {{if isset($rpu|smarty:nodefaults)}}
                            {{assign var=color value=""}}
                            {{if $rpu->ccmu}}
                                {{assign var=color value="dPurgences Display color_ccmu_`$rpu->ccmu`"|gconf}}
                            {{/if}}
                            <span style="font-size: 0.7em;">
                - Arrivée : {{mb_value object=$object field=entree date=$dnow}}
                <span style="color: #{{$color}}">({{mb_value object=$rpu field=ccmu}})</span>
              </span>
                            {{if $sejour->UHCD}}
                                <span class="texticon" style="color: #800; font-weight: bold;">UHCD</span>
                            {{/if}}
                        {{/if}}
                    {{/if}}

                    {{if $prescription && $prescription->_ref_lines_important|@count}}
                        <br/>
                        {{mb_include module=prescription template=vw_line_important lines=$prescription->_ref_lines_important}}
                    {{/if}}

                    <span id="atcd_majeur">
            {{mb_include module=patients template=inc_atcd_majeur}}
          </span>
                    {{if $nda_view == true}}
                        <span class="me-color-white-medium-emphasis me-no-convert-dark"
                              style="font-size: 0.7em; margin-left: 10px;">[{{$sejour->_NDA_view}}]</span>
                    {{/if}}

                    {{if $is_sejour && $object->circuit_ambu}}
                        <span id="sejour_circuit_ambu" class="texticon-atnc dhe_flag_circuit_ambu"
                              title="{{tr}}CSejour-circuit_ambu-desc{{/tr}}" style="font-size: 0.6em;">
              {{tr}}CSejour-circuit_ambu-court{{/tr}}: {{$object->circuit_ambu}}
            </span>
                    {{/if}}

                    {{if 'forms'|module_active && $object|instanceof:'Ox\Mediboard\PlanningOp\CSejour'}}
                        {{if $check_mandatory_forms}}
                            <script>
                                Main.add(ExObject.checkMandatoryConstraints.curry('{{$object->_class}}', '{{$object->_id}}'));
                            </script>
                            <div id="mandatory-forms"></div>
                        {{/if}}
                    {{/if}}
                </h2>
            </form>
        </th>
        {{if "context"|module_active}}
        <th>
          <div class="me-float-right">
                {{mb_include module=context template=inc_widget_integration object=$object location="patient_header"}}
          </div>
        </th>
        {{/if}}
        <th class="title text me-patient-banner-buttons"
            style="{{if !$see_modif_patient}}display:none;{{/if}}text-align: right; border: none; width: 5%; {{$background_color}}">
            {{if $with_buttons}}
              <div style="height: 20px;">
                  {{mb_include module=system template=inc_object_idsante400 object=$patient}}
                  {{mb_include module=system template=inc_object_history object=$patient}}

                    <a href="#print-{{$patient->_guid}}" onclick="Patient.print('{{$patient->_id}}')" class="not-printable"
                       style="float: right;">
                        {{me_img_title src="print.png" icon="print" alt_tr=Print}}
                        {{tr}}CConsultation-Print the card{{/tr}}
                        {{/me_img_title}}
                    </a>
              </div>

              <div style="height: 20px;">
                  {{if "forms"|module_active}}
                      {{mb_include_buttons location='patient_banner' var1=$sejour->_id}}
                  {{/if}}

                  {{if $can->edit}}
                      <a href="#edit-{{$patient->_guid}}" onclick="Patient.edit('{{$patient->_id}}')"
                         class="not-printable" style="float: right;">
                          {{me_img_title src="edit.png" icon="edit" alt="modifier"}}
                          {{tr}}CPatient-title-modify{{/tr}}
                          {{/me_img_title}}
                      </a>
                  {{/if}}

                  {{if $switch_view}}
                      <a class="button hslip me-color-white" style="float: right;"
                         href="{{if $switch_view === "synthese"}}
                       {{if $consult->_id}}
                         ?m=urgences&dialog=edit_consultation&selConsult={{$consult->_id}}&synthese_rpu=1
                       {{else}}
                         ?m=urgences&dialog=vw_synthese_rpu&rpu_id={{$rpu->_id}}
                       {{/if}}
                     {{elseif $app->_ref_user->isInfirmiere() || !$consult->_id}}
                       ?m=urgences&dialog=vw_aed_rpu&rpu_id={{$rpu->_id}}&sejour_id={{$sejour->_id}}
                     {{else}}
                       ?m=urgences&dialog=edit_consultation&selConsult={{$consult->_id}}
                     {{/if}}"
                      >
                          {{tr}}CRPU.{{$switch_view}}{{/tr}}
                      </a>
                  {{/if}}
              </div>
            {{/if}}

            {{if $app->user_prefs.vCardExport}}
                <a href="#export-{{$patient->_guid}}" onclick="Patient.exportVcard('{{$patient->_id}}')"
                   class="not-printable">
                    <img src="images/icons/vcard.png" alt="export" title="Exporter le patient"/>
                </a>
            {{/if}}

            {{mb_include module=dPpatients template=inc_view_ins_patient patient=$patient}}

            {{if 'oncomip'|module_active}}
                {{mb_include module=oncomip template=inc_oncomip patient_id=$patient->_id}}
            {{/if}}

            {{if $object|instanceof:'Ox\Mediboard\PlanningOp\CSejour' && "trajectoire"|module_active}}
                <script>
                    Main.add(function () {
                        var url = new Url('trajectoire', 'ajax_trajectoire_redirect');
                        url.addParam('patient_id', '{{$patient->_id}}');
                        url.addParam('sejour_id', '{{$object->_id}}');
                        url.requestUpdate('trajectoire_button', function () {
                            $('trajectoire_button').show();
                        });
                    });
                </script>
                <span id="trajectoire_button" style="display: none; width: 20px; height: 20px;"></span>
            {{/if}}

            {{if 'therefore'|module_active}}
                {{mb_include module=therefore template=inc_button_therefore}}
            {{/if}}
        </th>
    </tr>
    {{assign var=show_directives value="soins synthese show_directives"|gconf}}
    {{assign var=show_techniques value="soins synthese show_technique_rea"|gconf}}
    {{if $is_sejour && ($show_directives && $object->directives_anticipees_status == 1) || ($app->_ref_user->isProfessionnelDeSante() && $show_techniques && $object->technique_reanimation_status == 1)}}
        <tr>
            <td colspan="3" style="{{$background_color}}">
                <div class="small-warning">
                    {{if $show_directives && $object->directives_anticipees_status == 1}}
                        <div>
                            {{tr}}CSejour-directives_anticipees{{/tr}} : {{$object->directives_anticipees|nl2br}}
                        </div>
                    {{/if}}
                    {{if $app->_ref_user->isProfessionnelDeSante() && $show_techniques && $object->technique_reanimation_status == 1}}
                        <div>
                            {{tr}}CSejour-technique_reanimation{{/tr}} : {{$object->technique_reanimation|nl2br}}
                        </div>
                    {{/if}}
                </div>
            </td>
        </tr>
    {{/if}}
</table>

<table class="tbl me-no-align me-no-border-radius-top me-info-patient-table me-variante">
    {{mb_include module=soins template=inc_infos_patients_soins add_class=1}}
</table>
