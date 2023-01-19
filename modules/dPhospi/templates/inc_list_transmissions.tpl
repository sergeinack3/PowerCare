{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=offline value=0}}
{{mb_default var=readonly value=0}}
{{mb_default var=count_macrocibles value=0}}
{{mb_default var=users value=0}}
{{mb_default var=functions value=0}}
{{mb_default var=with_thead value=0}}
{{mb_default var=checkbox_selected value=0}}

{{if !$checkbox_selected || $checkbox_selected|@count == 0 || in_array("print_transmission", $checkbox_selected)}}
<table class="tbl print_sejour me-no-align me-small">
    {{mb_include module=soins template=inc_thead_dossier_soins colspan=9 with_thead=$with_thead}}

    <tbody {{if !$readonly}}id="transmissions"{{/if}}>
    {{foreach from=$list_transmissions item=_suivi}}
        <tr class="{{if is_array($_suivi)}}
                 print_transmission {{if $_suivi.0->cancellation_date}}hatching{{/if}}
               {{if $_suivi.0->degre == "high"}}
                 transmission_haute
               {{/if}}
               {{if $_suivi.0->object_class}}
                 {{$_suivi.0->_ref_object->_guid}}
               {{/if}}
             {{else}}
               {{$_suivi->_guid}}
               {{if $_suivi|@instanceof:'Ox\Mediboard\Hospi\CTransmissionMedicale'}}
                 {{if $_suivi->cancellation_date}}hatching{{/if}}
                 {{if $_suivi->degre == "high"}}
                   transmission_haute
                 {{/if}}
               {{elseif $_suivi|@instanceof:'Ox\Mediboard\Cabinet\CConsultation' && $_suivi->type == "entree"}}
                 print_observation
                 consultation_entree
               {{elseif $_suivi|@instanceof:'Ox\Mediboard\Hospi\CObservationMedicale'}}
                 print_observation {{if $_suivi->cancellation_date}}hatching{{/if}}
                 {{if $_suivi->degre == "info"}}
                   observation_info
                 {{elseif $_suivi->degre == "high"}}
                   observation_urgente
                 {{/if}}
               {{/if}}
             {{/if}}"
                {{if ($_suivi|@instanceof:'Ox\Mediboard\Prescription\CPrescriptionLineElement' || $_suivi|@instanceof:'Ox\Mediboard\Prescription\CPrescriptionLineComment') && !$readonly}}
            onmouseover="highlightTransmissions('{{$_suivi->_guid}}');" onmouseout="removeHighlightTransmissions();"
                {{/if}}>
            {{mb_include module=hospi template=inc_line_suivi show_patient=false nodebug=true}}
        </tr>
        {{foreachelse}}
        <tr>
            <td colspan="9" class="empty">{{tr}}CTransmissionMedicale.none{{/tr}}</td>
        </tr>
    {{/foreach}}
    </tbody>
    <thead>
    <tr>
        <th colspan="9" class="title">
            {{if !$readonly}}
                <button type="button" class="search me-tertiary me-dark me-small" onclick="Modal.open('legend_suivi')"
                        style="float: right;">Légende
                </button>
            {{/if}}

            {{tr}}soins-Suivi{{/tr}}

            {{if !$readonly}}
                <div class="me-inline-block me-margin-left-12">
                    <select name="other_sejour_id" class="me-small"
                            onchange="Soins.loadSuivi('{{$sejour->_id}}', null, null, null, null, null, this.value);">
                        <option value="all" {{if $other_sejour_id === "all"}}selected{{/if}}>
                            {{if $other_sejour_id === "all"}}&rArr;{{/if}} {{tr}}soins-All contexts{{/tr}}
                        </option>

                        {{foreach from=$sejours_context item=_sejour}}
                            <option value="{{$_sejour->_id}}"
                                    {{if ($other_sejour_id && $_sejour->_id === $other_sejour_id)
                                    || (!$other_sejour_id && $_sejour->_id === $sejour->_id)}}selected{{/if}}>
                                {{if ($other_sejour_id && $_sejour->_id === $other_sejour_id)
                                || (!$other_sejour_id && $_sejour->_id === $sejour->_id)}}&rArr;{{/if}} {{$_sejour}}
                            </option>
                        {{/foreach}}
                    </select>
                </div>
            {{/if}}
        </th>
    </tr>
    {{if !$readonly}}
        <tr>
            <td colspan="9" class="me-bg-transparent" style="background-color: #fff; vertical-align: top;">
                <fieldset class="me-small" style="width: 25%; display: inline-block; float: left;">
                    <legend>
                        {{tr}}Actions{{/tr}}
                    </legend>

                    <div class="text">
                        {{mb_include module=hospi template=inc_add_trans_obs}}

                        {{if $isPraticien}}
                            {{if (in_array($sejour->type, 'Ox\Mediboard\PlanningOp\CSejour::getTypesSejoursUrgence'|static_call:$sejour->praticien_id) && "dPprescription CPrescription prescription_suivi_soins"|gconf && "dPprescription"|module_active)}}
                                <button class="add me-tertiary"
                                        onclick="addPrescription('{{$sejour->_id}}', '{{$user->_id}}')"
                                        style="display: inline !important;">Ajouter une prescription
                                </button>
                            {{/if}}
                            {{if ("dPcabinet"|module_active && $dtnow >= $sejour->entree && $dtnow <= $sejour->sortie)}}
                                {{if "planSoins"|module_active}}
                                    <button type="button" class="new me-tertiary" id="newConsult"
                                            style="display: inline !important;"
                                            onclick="validateAdministration('{{$sejour->_id}}');">Nouvelle consultation
                                    </button>
                                {{/if}}
                                <button type="button" class="new oneclick me-secondary" id="newConsultEntree"
                                        {{if $has_obs_entree}}disabled{{/if}}
                                        onclick="Soins.createConsultEntree();"
                                        style="display: inline !important;">{{tr}}CConsultation-new_obs_entree{{/tr}}</button>
                            {{/if}}
                        {{/if}}

                        {{if $cancelled_nb}}
                            {{if $show_cancelled}}
                                <button type="button" class="search me-tertiary me-dark"
                                        onclick="Soins.loadSuivi('{{$sejour->_id}}', null, null, null, null, '0');">
                                    Masquer les {{$cancelled_nb}}
                                    annulée(s)
                                </button>
                            {{else}}
                                <button type="button" class="search me-tertiary me-dark"
                                        onclick="Soins.loadSuivi('{{$sejour->_id}}', null, null, null, null, '1');">
                                    Afficher les {{$cancelled_nb}}
                                    annulée(s)
                                </button>
                            {{/if}}
                        {{/if}}
                    </div>
                </fieldset>
                <fieldset style="width: 68%; display: inline-block; float: right;"
                          class="me-ws-nowrap me-small me-small-fields">
                    <legend>
                        {{tr}}filters{{/tr}}
                    </legend>
                    <input name="_show_obs_view" id="_show_obs_view" type="checkbox"
                           {{if $_show_obs}}checked{{elseif $cible != ""}}disabled{{/if}}
                           onclick="Soins.loadSuivi('{{$sejour->_id}}', null, null, null, null, null, '{{$other_sejour_id}}');"/>
                    <label for="_show_obs_view"
                           title="{{tr}}CObservationMedicale{{/tr}}">{{tr}}CObservationMedicale._show_obs{{/tr}}</label>

                    {{me_form_field animated=false field_class="me-form-group-inline"}}
                        <select name="_degre_obs" id="_degre_obs" {{if !$_show_obs}}disabled{{/if}}
                                onchange="Soins.loadSuivi('{{$sejour->_id}}', null, null, null, null, null, '{{$other_sejour_id}}');">
                            <option value="all">&mdash; {{tr}}CObservationMedicale-degre{{/tr}}</option>
                            <option value="high"
                                    {{if $_degre_obs === "high"}}selected{{/if}}>{{tr}}CObservationMedicale.degre.high{{/tr}}</option>
                            <option value="info"
                                    {{if $_degre_obs === "info"}}selected{{/if}}>{{tr}}CObservationMedicale.degre.info{{/tr}}</option>
                            <option value="low"
                                    {{if $_degre_obs === "low"}}selected{{/if}}>{{tr}}CObservationMedicale.degre.low{{/tr}}</option>
                        </select>
                    {{/me_form_field}}

                    {{me_form_field animated=false field_class="me-form-group-inline"}}
                        <select name="_type_obs" id="_type_obs" {{if !$_show_obs}}disabled{{/if}}
                                onchange="Soins.loadSuivi('{{$sejour->_id}}', null, null, null, null, null, '{{$other_sejour_id}}');">
                            <option value="all">&mdash; {{tr}}Type{{/tr}}</option>
                            <option value=""
                                    {{if $_type_obs === ""}}selected{{/if}}>{{tr}}CObservationMedicale.type.{{/tr}}</option>
                            <option value="synthese"
                                    {{if $_type_obs === "synthese"}}selected{{/if}}>{{tr}}CObservationMedicale.type.synthese{{/tr}}</option>
                            <option value="communication"
                                    {{if $_type_obs === "communication"}}selected{{/if}}>{{tr}}CObservationMedicale.type.communication{{/tr}}</option>
                        </select>
                    {{/me_form_field}}

                    {{me_form_field animated=false field_class="me-form-group-inline"}}
                        <select name="_etiquette_obs" id="_etiquette_obs" {{if !$_show_obs}}disabled{{/if}}
                                onchange="Soins.loadSuivi('{{$sejour->_id}}', null, null, null, null, null, '{{$other_sejour_id}}');">
                            <option value="all">&mdash; {{tr}}CObservationMedicale-etiquette{{/tr}}</option>
                            {{foreach from=$observation_med->_specs.etiquette->_locales key=key_etiquette item=name_etiquette}}
                                <option value="{{$key_etiquette}}"
                                        {{if $key_etiquette == $_etiquette_obs}}selected="selected"{{/if}}>{{$name_etiquette}}</option>
                            {{/foreach}}
                        </select>
                    {{/me_form_field}}

                    <input class="me-margin-left-8" name="_show_trans_view" id="_show_trans_view" type="checkbox"
                           {{if $_show_trans}}checked{{/if}}
                           onclick="Soins.loadSuivi('{{$sejour->_id}}', null, null, null, null, null, '{{$other_sejour_id}}');"/>
                    <label for="_show_trans_view"
                           title="{{tr}}CTransmissionMedicale{{/tr}}">{{tr}}CTransmissionMedicale._show_trans{{/tr}}</label>

                    {{me_form_field animated=false field_class="me-form-group-inline"}}
                        <select name="_lvl_trans" id="_lvl_trans" {{if !$_show_trans}}disabled{{/if}}
                                onchange="Soins.loadSuivi('{{$sejour->_id}}', null, null, null, null, null, '{{$other_sejour_id}}');">
                            <option value="all">Toutes</option>
                            <option value="high" {{if $_lvl_trans == "high"}}selected{{/if}}>Hautes</option>
                        </select>
                    {{/me_form_field}}

                    {{if $count_macrocibles}}
                        <input class="me-margin-left-8" name="_only_macrocible" id="_only_macrocible_view"
                               type="checkbox" {{if $only_macrocible}}checked{{/if}}
                               onclick="Soins.loadSuivi('{{$sejour->_id}}', null, null, null, null, null, '{{$other_sejour_id}}');"/>
                        <label for="_only_macrocible_view"
                               title="{{tr}}CTransmissionMedicale-Only macrocible{{/tr}}">{{tr}}CTransmissionMedicale-Only macrocible-court{{/tr}}</label>
                    {{/if}}

                    <input class="me-margin-left-8" name="_show_const_view" id="_show_const_view" type="checkbox"
                           {{if $_show_const}}checked{{elseif $cible != ""}}disabled{{/if}}
                           onclick="Soins.loadSuivi('{{$sejour->_id}}', null, null, null, null, null, '{{$other_sejour_id}}');"/>
                    <label for="_show_const_view"
                           title="{{tr}}CConstantesMedicales{{/tr}}">{{tr}}CConstantesMedicales._show_const{{/tr}}</label>

                    <input class="me-margin-left-8" name="_show_adm_cancelled_view" id="_show_adm_cancelled_view"
                           type="checkbox" {{if $show_adm_cancelled}}checked{{/if}}
                           onclick="Soins.loadSuivi('{{$sejour->_id}}', null, null, null, null, null, '{{$other_sejour_id}}');"/>
                    <label for="_show_adm_cancelled_view"
                           title="{{tr}}CTransmissionMedicale-Show adm cancelled{{/tr}}">{{tr}}CTransmissionMedicale-Show adm cancelled-court{{/tr}}</label>

                    <input class="me-margin-left-8" name="_show_rdv_externe_view" id="_show_rdv_externe_view"
                           type="checkbox" {{if $show_rdv_externe}}checked{{/if}}
                           onclick="Soins.loadSuivi('{{$sejour->_id}}', null, null, null, null, null, '{{$other_sejour_id}}');"/>
                    <label for="_show_rdv_externe_view"
                           title="{{tr}}CRDVExterne-action-Show external events{{/tr}}">{{tr}}CRDVExterne-court{{/tr}}</label>

                    <input class="me-margin-left-8" name="_show_call_view" id="_show_call_view" type="checkbox"
                           {{if $show_call}}checked{{/if}}
                           onclick="Soins.loadSuivi('{{$sejour->_id}}', null, null, null, null, null, '{{$other_sejour_id}}');"/>
                    <label for="_show_call_view"
                           title="{{tr}}CAppelSejour-action-Show calls{{/tr}}">{{tr}}CAppelSejour-event-appel{{/tr}}</label>

                    {{if "soins Other see_volet_diet"|gconf}}
                        <input class="me-margin-left-8" name="_show_diet_view" id="_show_diet_view" type="checkbox"
                               {{if $_show_diet}}checked{{/if}}
                               onclick="Soins.loadSuivi('{{$sejour->_id}}', null, null, null, null, null, '{{$other_sejour_id}}');"/>
                        <label for="_show_diet_view"
                               title="Afficher les transmissions et observations de type diététique">Diet.</label>
                    {{/if}}

                    <br/>

                    {{me_form_field animated=false field_class="me-form-group-inline"}}
                        <select name="selCible" style="width: 150px"
                                onchange="Soins.loadSuivi('{{$sejour->_id}}', '', '', this.value, null, null, '{{$other_sejour_id}}')">
                            <option value="">&mdash; Toutes les cibles</option>
                            {{foreach from=$cibles item=cibles_by_state key=state}}
                                {{if $cibles_by_state|@count}}
                                    <optgroup label="{{tr}}CTransmission.state.{{$state}}{{/tr}}"></optgroup>
                                    {{foreach from=$cibles_by_state item=_cible}}
                                        <option {{if $_cible == $cible}}selected{{/if}}
                                                value="{{$_cible}}">{{$_cible}}</option>
                                    {{/foreach}}
                                {{/if}}
                            {{/foreach}}
                        </select>
                    {{/me_form_field}}

                    {{if $users}}
                        {{me_form_field animated=false field_class="me-form-group-inline"}}
                            <select name="user_id" style="width: 150px"
                                    onchange="Soins.loadSuivi('{{$sejour->_id}}',this.value, null, null, null, null, '{{$other_sejour_id}}')">
                                <option value="">&mdash; {{tr}}CUser.all{{/tr}}</option>
                                {{foreach from=$users item=_user}}
                                    <option value="{{$_user->_id}}"
                                            {{if $user_id == $_user->_id}}selected{{/if}}>{{$_user->_view}}</option>
                                {{/foreach}}
                            </select>
                        {{/me_form_field}}
                    {{/if}}

                    {{if $functions}}
                        {{me_form_field animated=false field_class="me-form-group-inline"}}
                            <select name="function_id" style="width: 150px"
                                    onchange="Soins.loadSuivi('{{$sejour->_id}}', null, this.value, null, null, null, '{{$other_sejour_id}}')">
                                <option value="">&mdash; {{tr}}CFunctions.all{{/tr}}</option>
                                {{foreach from=$functions item=_function}}
                                    <option value="{{$_function->_id}}"
                                            {{if $function_id == $_function->_id}}selected{{/if}}>{{$_function->_view}}</option>
                                {{/foreach}}
                            </select>
                        {{/me_form_field}}
                    {{/if}}
                </fieldset>
            </td>
        </tr>
    {{/if}}
    <tr>
        <th rowspan="2" class="narrow">{{tr}}Type{{/tr}}</th>
        <th rowspan="2">{{tr}}User{{/tr}} / {{tr}}Date{{/tr}}</th>
        <th rowspan="2">{{mb_title class=CTransmissionMedicale field=object_class}}</th>
        <th colspan="3" style="width: 50%">{{mb_title class=CTransmissionMedicale field=text}}</th>
        {{if !$readonly}}
            <th rowspan="2" class="narrow"></th>
        {{/if}}
    </tr>
    <tr>
        <th class="category" style="width: 17%">{{tr}}CTransmissionMedicale.type.data{{/tr}}</th>
        <th class="category" style="width: 17%">{{tr}}CTransmissionMedicale.type.action{{/tr}}</th>
        <th class="category" style="width: 17%">{{tr}}CTransmissionMedicale.type.result{{/tr}}</th>
    </tr>
    </thead>
</table>
{{/if}}
