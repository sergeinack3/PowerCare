{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=multi_label value="dPplanningOp COperation multiple_label"|gconf}}
{{assign var=mode_easy   value=$conf.dPplanningOp.COperation.mode_easy}}

{{assign var=colspan value=1}}

{{if $conf.dPplanningOp.COperation.show_remarques}}
    {{if $conf.dPplanningOp.COperation.easy_materiel}}
        {{math equation=x+2 x=$colspan assign=colspan}}
    {{/if}}

    {{if $conf.dPplanningOp.COperation.easy_remarques}}
        {{math equation=x+1 x=$colspan assign=colspan}}
    {{/if}}
{{/if}}

<form name="editOpEasy" action="?m={{$m}}" method="post" onsubmit="return checkFormOperation()">
    {{if $op->_id && $op->_ref_sejour->sortie_reelle && !$modules.dPbloc->_can->edit}}
        <!-- <input type="hidden" name="_locked" value="1" /> -->
    {{/if}}
    {{mb_field object=$op field="protocole_id" hidden=1 class="me-placeholder"}}
    <table class="form me-small-form">
        {{if $op->annulee == 1}}
            <tr>
                <th class="category cancelled" colspan="{{$colspan+1}}">
                    {{tr}}COperation-annulee{{/tr}}
                </th>
            </tr>
        {{/if}}

        {{if $mode_easy === "2col"}}
            <tr>
                <th class="category" colspan="{{$colspan+1}}">{{tr}}COperation-msg-informations{{/tr}}</th>
            </tr>
        {{/if}}

        <!-- Selection du ou des chirurgiens -->
        <tr>
            <th class="narrow">{{mb_label object=$op field="chir_id"}}</th>
            <td colspan="{{$colspan}}">
                <script>
                    Main.add(function () {
                        var formeasy = getForm("editOpEasy");
                        Sejour.selectPraticien(formeasy.chir_id, formeasy.chir_id_view);
                        Sejour.selectPraticien(formeasy.chir_2_id, formeasy.chir_2_id_view);
                        Sejour.selectPraticien(formeasy.chir_3_id, formeasy.chir_3_id_view);
                        Sejour.selectPraticien(formeasy.chir_4_id, formeasy.chir_4_id_view);
                    });
                </script>
                {{mb_field object=$op field="chir_id" hidden=hidden value=$chir->_id onchange="synchroPrat(); Value.synchronize(this); removePlageOp(true);"}}
                <input type="text" name="chir_id_view" class="autocomplete" style="width:15em;"
                       onchange="Value.synchronize(this);"
                       value="{{if $chir->_id}}{{$chir->_view}}{{/if}}" placeholder="&mdash; Choisir un praticien"/>
                <button type="button" onclick="toggleOtherPrats()" title="{{tr}}Add{{/tr}}"
                        class="notext me-tertiary me-dark {{if $op->chir_2_id || $op->chir_3_id || $op->chir_4_id}}up{{else}}down{{/if}}"></button>
                <input name="_limit_search_easy_op" class="changePrefListUsers" type="checkbox"
                       {{if $app->user_prefs.useEditAutocompleteUsers}}checked{{/if}}
                       onchange="changePrefListUsers(this);"
                       title="Limiter la recherche des praticiens"/>
            </td>
        </tr>
        {{if $conf.dPplanningOp.COperation.show_secondary_function && !$op->_id}}
            <tr>
                <th>
                    {{mb_label class=CMediusers field=function_id}}
                </th>
                <td id="secondary_functions_easy" colspan="{{$colspan}}">
                    {{mb_include module=dPcabinet template=inc_refresh_secondary_functions chir=$chir change_active=0}}
                </td>
            </tr>
        {{/if}}
        <tr class="other_prats"
            {{if !$op->chir_2_id && !$op->chir_3_id && !$op->chir_4_id}}style="display: none"{{/if}}>
            <th>
                {{mb_label object=$op field="chir_2_id"}}
            </th>
            <td colspan="{{$colspan}}">
                {{mb_field object=$op field="chir_2_id" hidden=hidden value=$op->chir_2_id onchange="Value.synchronize(this);"}}
                <input type="text" name="chir_2_id_view" class="autocomplete" style="width:15em;"
                       onchange="Value.synchronize(this);"
                       value="{{if $op->chir_2_id}}{{$op->_ref_chir_2->_view}}{{/if}}"
                       placeholder="&mdash; Choisir un chirurgien"/>
                <button type="button" class="cancel notext me-tertiary me-dark"
                        onclick="$V(this.form.chir_2_id, '');$V(this.form.chir_2_id_view, '');"></button>
            </td>
        </tr>
        <tr class="other_prats"
            {{if !$op->chir_2_id && !$op->chir_3_id && !$op->chir_4_id}}style="display: none"{{/if}}>
            <th>
                {{mb_label object=$op field="chir_3_id"}}
            </th>
            <td colspan="{{$colspan}}">
                {{mb_field object=$op field="chir_3_id" hidden=hidden value=$op->chir_3_id onchange="Value.synchronize(this);"}}
                <input type="text" name="chir_3_id_view" class="autocomplete" style="width:15em;"
                       onchange="Value.synchronize(this);"
                       value="{{if $op->chir_3_id}}{{$op->_ref_chir_3->_view}}{{/if}}"
                       placeholder="&mdash; Choisir un chirurgien"/>
                <button type="button" class="cancel notext me-tertiary me-dark"
                        onclick="$V(this.form.chir_3_id, '');$V(this.form.chir_3_id_view, '');"></button>
            </td>
        </tr>
        <tr class="other_prats"
            {{if !$op->chir_2_id && !$op->chir_3_id && !$op->chir_4_id}}style="display: none"{{/if}}>
            <th>
                {{mb_label object=$op field="chir_4_id"}}
            </th>
            <td colspan="{{$colspan}}">
                {{mb_field object=$op field="chir_4_id" hidden=hidden value=$op->chir_4_id onchange="Value.synchronize(this);"}}
                <input type="text" name="chir_4_id_view" class="autocomplete" style="width:15em;"
                       onchange="Value.synchronize(this);"
                       value="{{if $op->chir_4_id}}{{$op->_ref_chir_4->_view}}{{/if}}"
                       placeholder="&mdash; Choisir un chirurgien"/>
                <button type="button" class="cancel notext me-tertiary me-dark"
                        onclick="$V(this.form.chir_4_id, '');$V(this.form.chir_4_id_view, '');"></button>
            </td>
        </tr>

        {{if $mode_easy === "1col"}}
            {{if $conf.dPplanningOp.CSejour.easy_service}}
                <!-- Selection du service -->
                <tr>
                    <th>{{mb_label object=$sejour field="service_id"}}</th>
                    <td colspan="{{$colspan}}">
                        <select name="service_id" class="{{$sejour->_props.service_id}}"
                                onchange="Value.synchronize(this, 'editSejour');" style="width: 15em;">
                            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                            {{foreach from=$listServices item=_service}}
                                <option value="{{$_service->_id}}"
                                        {{if $sejour->service_id == $_service->_id}}selected{{/if}}>
                                    {{$_service->_view}}
                                </option>
                            {{/foreach}}
                        </select>
                    </td>
                </tr>
            {{/if}}

            {{assign var=required_uf_soins value="dPplanningOp CSejour required_uf_soins"|gconf}}
            {{if $required_uf_soins != "no"}}
                <!-- Selection de l'unité de soins -->
                <tr>
                    <th>{{mb_label object=$sejour field="uf_soins_id"}}</th>
                    <td colspan="{{$colspan}}">
                        <select name="uf_soins_id" class="ref {{if $required_uf_soins == "obl"}}notNull{{/if}}"
                                style="width: 15em;"
                                onchange="Value.synchronize(this, 'editSejour');">
                            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                            {{foreach from=$ufs.soins item=_uf}}
                                <option value="{{$_uf->_id}}" {{if $sejour->uf_soins_id == $_uf->_id}}selected{{/if}}>
                                    {{mb_value object=$_uf field=libelle}}
                                </option>
                            {{/foreach}}
                        </select>
                    </td>
                </tr>
            {{/if}}
        {{/if}}

        {{assign var=required_uf_med value="dPplanningOp CSejour required_uf_med"|gconf}}
        {{if $required_uf_med != "no"}}
            <!-- Selection de l'unité médicale -->
            <tr>
                <th>{{mb_label object=$sejour field="uf_medicale_id"}}</th>
                <td colspan="{{$colspan}}">
                    <select name="uf_medicale_id" class="ref {{if $required_uf_med == "obl"}}notNull{{/if}}"
                            style="width: 15em;"
                            onchange="Value.synchronize(this, 'editSejour');">
                        <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                        {{foreach from=$ufs.medicale item=_uf}}
                            <option value="{{$_uf->_id}}" {{if $sejour->uf_medicale_id == $_uf->_id}}selected{{/if}}>
                                {{mb_value object=$_uf field=libelle}}
                            </option>
                        {{/foreach}}
                    </select>
                </td>
            </tr>
        {{/if}}

        <!-- Affichage du libelle -->
        {{assign var=easy_length_input_label value=$conf.dPplanningOp.COperation.easy_length_input_label}}
        <tr>
            <th>{{mb_label object=$op field="libelle"}}</th>
            <td colspan="{{$colspan}}">
                <table class="layout" style="width: {{$easy_length_input_label}}%; box-sizing: border-box;">
                    <tr>
                        <td style="padding: 0;">
                            {{mb_field object=$op field="libelle" style="width: 100%; box-sizing: border-box;"
                            onfocus="ProtocoleSelector.init()" readonly="readonly"}}
                          <span style="padding: 0;" class="narrow protocoleAction">
                              {{if $op->_ref_protocole && $op->_ref_protocole->_id}}
                                <button class="cancel notext me-secondary" type="button"
                                        onclick="ProtocoleSelector.unselect('{{$op->_ref_protocole->_id}}')"
                                        title="{{tr}}CProtocole.unselect{{/tr}}">
                                </button>
                              {{else}}
                                <button class="search notext me-tertiary" type="button"
                                        onclick="ProtocoleSelector.init()"
                                        title="{{tr}}CProtocole.select{{/tr}}">
                                </button>
                              {{/if}}
                          </span>

                            {{if $multi_label}}
                                <button class="edit notext me-tertiary" type="button"
                                        onclick="LiaisonOp.edit('{{$op->_id}}');"></button>
                            {{/if}}
                        </td>
                    </tr>
                </table>

                {{mb_include module=planningOp template="inc_search_protocole" formOp="editOpEasy" formSecondOp="editOp" id_protocole="get_protocole_easy"}}

                <div id="row_keep_protocol_editOpEasy"{{if !$protocole->_id}} style="display: none;"{{/if}}>
                    <label for="_keep_protocol">
                        <input type="checkbox" name="_keep_protocol" {{if $protocole->_id}}checked="checked"{{/if}}
                               onchange="changeKeepProtocole(this, 'easy');">
                        Conserver la sélection du protocole
                    </label>
                </div>
              <div class="libelleProtocole">
              {{if $op->_ref_protocole && $op->_ref_protocole->_id}}
                  <span class="circled me-margin-right-5">
                    {{$op->_ref_protocole->libelle}}
                  </span>
              {{/if}}
              </div>
            </td>
        </tr>
        <tr class="libellesProtocolesOperatoires">
          <th>{{tr}}CProtocoleOperatoire|pl{{/tr}}</th>
          <td>
              {{if $op->_ref_protocole && $op->_ref_protocole->_id && $op->_ref_protocole->_list_libelles_protocoles_op|@count}}
                  {{foreach from=$op->_ref_protocole->_list_libelles_protocoles_op item=_libelle_protocole}}
                    <span class="circled">{{$_libelle_protocole}}</span>
                  {{/foreach}}
              {{/if}}
          </td>
        </tr>

        <!-- Diagnostic principal et secondaire -->
        {{if $mode_easy === "1col" && $conf.dPplanningOp.CSejour.easy_cim10}}
            <tr>
                <th>{{mb_label object=$sejour field="DP"}}</th>
                <td colspan="{{$colspan}}">
                    <script>
                        Main.add(function () {
                            CIM.autocomplete(getForm("editOpEasy").keywords_code, null, {
                                limit_favoris: '{{$app->user_prefs.cim10_search_favoris}}',
                                chir_id:       $V(getForm('editOpEasy').chir_id),
                                field_type:    'dp',
                                /* Permet de prendre en compte le type de séjour de façon dynamique */
                                callback:           function (input, queryString) {
                                    var form = getForm("editSejour");
                                    var sejour_type = 'mco';
                                    if ($V(form.elements['type']) == 'ssr') {
                                        sejour_type = 'ssr';
                                    } else if ($V(form.elements['type']) == 'psy') {
                                        sejour_type = 'psy';
                                    }
                                    return queryString + "&sejour_type=" + sejour_type;
                                },
                                afterUpdateElement: function (input) {
                                    $V(getForm("editOpEasy").DP, input.value);
                                }
                            });
                        });
                    </script>

                    <input type="text" name="keywords_code" class="autocomplete str code cim10" value="{{$sejour->DP}}"
                           onchange="Value.synchronize(this, 'editSejour');" style="width: 12em"/>
                    <button type="button" class="cancel notext me-tertiary me-dark"
                            onclick="$V(this.form.DP, '');">{{tr}}Cancel{{/tr}}</button>
                    <button type="button" class="search notext me-tertiary"
                            onclick="CIM.viewSearch($V.curry(this.form.elements['DP']), $V(this.form.elements['chir_id']){{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, null, null, null, $V.curry(getForm('editSejour').elements['type']), 'dp'{{/if}});">{{tr}}button-CCodeCIM10-choix{{/tr}}</button>
                    <input type="hidden" name="DP" value="{{$sejour->DP}}"
                           onchange="$V(this.form.keywords_code, this.value); Value.synchronize(this, 'editSejour');"/>
                </td>
            </tr>
            <tr>
                <th>{{mb_label object=$sejour field="DR"}}</th>
                <td colspan="{{$colspan}}">
                    <script>
                        Main.add(function () {
                            CIM.autocomplete(getForm("editOpEasy").DR_keywords_code, null, {
                                {{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}
                                field_type: 'dr',
                                /* Permet de prendre en compte le type de séjour de façon dynamique */
                                callback: function (input, queryString) {
                                    var form = getForm("editSejour");
                                    var sejour_type = 'mco';
                                    if ($V(form.elements['type']) == 'ssr') {
                                        sejour_type = 'ssr';
                                    } else if ($V(form.elements['type']) == 'psy') {
                                        sejour_type = 'psy';
                                    }
                                    return queryString + "&sejour_type=" + sejour_type;
                                },
                                {{/if}}
                                afterUpdateElement: function (input) {
                                    $V(getForm("editOpEasy").DR, input.value);
                                }
                            });
                        });
                    </script>

                    <input type="text" name="DR_keywords_code" class="autocomplete str code cim10"
                           value="{{$sejour->DR}}" onchange="Value.synchronize(this, 'editSejour');"
                           style="width: 12em"/>
                    <button type="button" class="cancel notext me-tertiary me-dark"
                            onclick="$V(this.form.DR, '');"></button>
                    <button type="button" class="search notext me-tertiary"
                            onclick="CIM.viewSearch($V.curry(this.form.elements['DR']), $V(this.form.elements['chir_id']){{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, null, null, null, $V.curry(getForm('editSejour').elements['type']), 'dr'{{/if}});">{{tr}}button-CCodeCIM10-choix{{/tr}}</button>
                    <input type="hidden" name="DR" value="{{$sejour->DR}}"
                           onchange="$V(this.form.DR_keywords_code, this.value); Value.synchronize(this, 'editSejour');"/>
                </td>
            </tr>
        {{/if}}


        <!-- Liste des codes ccam -->
        <tr {{if !$conf.dPplanningOp.COperation.use_ccam}}style="display: none;"{{/if}}>
            <th>
                Liste des codes CCAM
                {{mb_field object=$op field="codes_ccam" onchange="refreshListCCAM('easy');" hidden=1}}
            </th>
            <td colspan="{{$colspan}}" class="text" id="listCodesCcamEasy"></td>
        </tr>

        <!-- Selection du coté -->
        <tr>
            <th>{{mb_label object=$op field="cote"}}</th>
            <td colspan="{{$colspan}}">
                {{mb_field object=$op field="cote" style="width: 15em" emptyLabel="Choose" onchange="Value.synchronize(this); modifOp();"}}
            </td>
        </tr>

        <!-- Choix du type d'anesthésie -->
        {{if $conf.dPplanningOp.COperation.easy_type_anesth}}
            <tr>
                <th>{{mb_label object=$op field="type_anesth"}}</th>
                <td colspan="{{$colspan}}">
                    <select name="type_anesth" style="width: 15em;" onchange="Value.synchronize(this)">
                        <option value="">&mdash; Anesthésie</option>
                        {{foreach from=$listAnesthType item=curr_anesth}}
                            {{if $curr_anesth->actif || $op->type_anesth == $curr_anesth->type_anesth_id}}
                                <option value="{{$curr_anesth->type_anesth_id}}"
                                        {{if $op->type_anesth == $curr_anesth->type_anesth_id}}selected{{/if}}>
                                    {{$curr_anesth->name}} {{if !$curr_anesth->actif && $op->type_anesth == $curr_anesth->type_anesth_id}}(Obsolète){{/if}}
                                </option>
                            {{/if}}
                        {{/foreach}}
                    </select>
                </td>
            </tr>
        {{/if}}
        <!-- Selection de la date -->
        {{if $modurgence}}
            <tr>
                <th>
                    {{mb_label object=$op field="date"}}
                </th>
                <td colspan="{{$colspan}}">
                    <input type="hidden" name="plageop_id" value=""/>
                    <input type="hidden" name="_date"
                           value="{{if $op->_datetime}}{{$op->_datetime|iso_date}}{{else}}{{$date_min}}{{/if}}"/>
                    {{assign var="operation_id" value=$op->operation_id}}
                    {{mb_ternary var=update_entree_prevue test=$op->operation_id value="" other="updateEntreePrevue();"}}
                    <input type="text" name="date_da" readonly value="{{$op->date|date_format:$conf.date}}"/>
                    <input type="hidden" name="date" value="{{$op->date}}" class="date notNull"
                           onchange="{{$update_entree_prevue}}
                             Value.synchronize(this.form.date_da);
                             Value.synchronize(this);
                             document.editSejour._curr_op_date.value = this.value;
                             modifSejour();
                             $V(this.form._date, this.value);"/>
                    <script>
                        Main.add(function () {
                            var dates = {
                                limit: {
                                    start: "{{$date_min}}",
                                    stop:  "{{$date_max}}"
                                }
                            };
                            Calendar.regField(getForm("editOpEasy").date{{if !$can->admin && !@$modules.dPbloc->_can->edit}}, dates{{/if}});
                        });
                    </script>
                    à
                    <input type="text" class="time" name="_time_urgence_da" readonly
                           value="{{$op->_time_urgence|date_format:"%H:%M"}}"/>
                    <input name="_time_urgence" class="notNull time" type="hidden" value="{{$op->_time_urgence}}"
                           onchange="Value.synchronize($(this.form._time_urgence_da));Value.synchronize(this);"/>

                    <script>
                        Main.add(function () {
                            Calendar.regField(getForm("editOpEasy")._time_urgence, null, {
                                datePicker: false,
                                timePicker: true
                            });
                        });
                    </script>
                </td>
            </tr>
        {{else}}
            <tr>
                <th>
                    <input type="hidden" name="plageop_id" class="notNull {{$op->_props.plageop_id}}"
                           onchange="Value.synchronize(this);" ondblclick="PlageOpSelector.init()"
                           value="{{$plage->plageop_id}}"/>
                    {{mb_label object=$op field="plageop_id"}}
                    <input type="hidden" name="date" value=""/>
                    <input type="hidden" name="_date" value="{{$plage->date}}"
                           onchange="Value.synchronize(this);
                  Sejour.preselectSejour(this.value);"/>
                </th>
                <td colspan="{{$colspan}}">
                    <input type="text" name="_locale_date" readonly="readonly"
                           style="width: 15em;"
                           onfocus="this.blur(); PlageOpSelector.init()"
                           value="{{$op->_datetime|date_format:$conf.datetime}}"/>
                    <button id="didac_plage_op_select_button" type="button" class="search notext me-tertiary"
                            onclick="PlageOpSelector.init()">Choisir une date
                    </button>

                    {{if !$modurgence}}
                        <button type="button" class="agenda notext me-tertiary" onclick="PlageOpSelector.init(1);">DHE
                            multiple
                        </button>
                    {{/if}}
                </td>
            </tr>
        {{/if}}

        {{if !$modurgence}}
            <tr>
                <th></th>
                <td class="area_dhe_multiple" colspan="{{$colspan}}"></td>
            </tr>
        {{/if}}

        <tr>
            <th>
                {{mb_label object=$op field=urgence}}
            </th>
            <td colspan="{{$colspan}}">
                {{mb_field object=$op field=urgence onchange="Value.synchronize(this, null, false);"}}
            </td>
        </tr>

        <tr>
            <th>Taux d'occupation</th>
            <td colspan="{{$colspan}}" id="occupationeasy">
            </td>
        </tr>

        <!-- Selection du patient -->
        <tr>
            <th>
                <input type="hidden" name="patient_id" class="notNull {{$sejour->_props.patient_id}}"
                       ondblclick="PatSelector.init()" value="{{$patient->patient_id}}"
                       onchange="changePat(); $('button-edit-patient-easy').setVisible(this.value);"/>
                {{mb_label object=$sejour field="patient_id"}}
            </th>
            <td colspan="{{$colspan}}">
                <input type="text" name="_patient_view" style="width: 15em" value="{{$patient->_view}}"
                       readonly="readonly"
                  {{if $conf.dPplanningOp.CSejour.patient_id || !$sejour->_id || $app->user_type == 1}}
                      onfocus="PatSelector.init()"
                  {{/if}}
                />
                {{if $conf.dPplanningOp.CSejour.patient_id || !$sejour->_id || $app->user_type == 1}}
                    <button id="didac_patient_select_button" type="button" class="search notext me-tertiary"
                            onclick="PatSelector.init()">Choisir un patient
                    </button>
                    <button id="button-edit-patient-easy" type="button"
                            onclick="location.href='?m=patients&tab=vw_edit_patients&patient_id='+this.form.patient_id.value"
                            class="edit notext me-tertiary" {{if !$patient->_id}}style="display: none;"{{/if}}>
                        {{tr}}Edit{{/tr}}
                    </button>
                    {{if $conf.dPplanningOp.CPatient.easy_correspondant}}
                        <button id="button-edit-corresp-easy" type="button"
                                onclick="Patient.editModal(this.form.patient_id.value, 0, 'window.parent.afterModifPatient', null, 'correspondance');"
                                class="search me-tertiary me-dark" {{if !$patient->_id}}style="display: none;"{{/if}}>
                            Corresp.
                        </button>
                    {{/if}}
                {{/if}}

                {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
                    <div style="display : inline" id="button_creation_account_appFine_easy">
                        {{mb_include module=appFineClient template=inc_create_account_appFine form=true object_guid=$sejour->_guid}}
                    </div>
                {{/if}}

                {{if 'web100T'|module_active && $sejour->_id}}
                    {{mb_include module=web100T template=inc_button_iframe _sejour=$sejour}}
                    {{mb_include module=web100T template=inc_patient_note sejour=$sejour}}
                {{/if}}
                <br/>
                <input type="text" name="_seek_patient" style="width: 13em;" placeholder="{{tr}}fast-search{{/tr}}"
                "autocomplete" onblur="$V(this, '')" />
                {{assign var=patient_id_config value=$conf.dPplanningOp.CSejour.patient_id}}
                {{if !($patient_id_config == 0 && $sejour->_id) && !($patient_id_config == 2 && $sejour->entree_reelle)}}
                    <script>
                        Main.add(function () {
                            var form = getForm("editOpEasy");
                            var formop = getForm("editSejour");
                            var url = new Url("system", "ajax_seek_autocomplete");
                            url.addParam("object_class", "CPatient");
                            url.addParam("field", "patient_id");
                            url.addParam("view_field", "_patient_view");
                            url.addParam("input_field", "_seek_patient");
                            url.autoComplete(form.elements._seek_patient, null, {
                                minChars:           3,
                                method:             "get",
                                select:             "view",
                                dropdown:           false,
                                width:              "300px",
                                afterUpdateElement: function (field, selected) {
                                    $V(field.form.patient_id, selected.getAttribute("id").split("-")[2]);
                                    $V(formop.patient_id, selected.getAttribute("id").split("-")[2]);
                                    $V(field.form.elements._patient_view, selected.down('.view').innerHTML);
                                    $V(formop.elements._patient_view, selected.down('.view').innerHTML);
                                    $V(field.form.elements._seek_patient, "");
                                    var view = selected.down('.view');
                                    if (form._tutelle) {
                                        $V(form._tutelle, view.get("tutelle"));
                                        form._tutelle.enable();
                                    }
                                }
                            });
                            Event.observe(form.elements._seek_patient, 'keydown', PatSelector.cancelFastSearch);
                        });
                    </script>
                {{/if}}
            </td>
        </tr>

        {{if "dPplanningOp CSejour show_tutelle"|gconf}}
            <tr{{if !$conf.dPplanningOp.CPatient.easy_tutelle}} style="display: none;"{{/if}}>
                <th><label for="_tutelle"
                           title="{{tr}}CPatient-tutelle-desc{{/tr}}">{{tr}}CPatient-tutelle{{/tr}}</label></th>
                <td colspan="{{$colspan}}">
                    {{mb_field object=$patient field=tutelle disabled=disabled onchange="setTutelle(this);"}}
                </td>
            </tr>
        {{/if}}

        {{if $mode_easy === "1col" && $conf.dPplanningOp.CPatient.easy_handicap}}
            <tr>
                {{mb_include module=planningOp template=inc_field_handicap onchange="Value.synchronize(this, 'editSejour');"}}
            </tr>
        {{/if}}

        {{if $conf.dPplanningOp.CPatient.easy_aide_organisee}}
            <tr>
                <th>{{mb_label object=$sejour field=aide_organisee}}</th>
                <td
                  colspan="{{$colspan}}">{{mb_field object=$sejour field=aide_organisee onchange="Value.synchronize(this, 'editSejour');"}}</td>
            </tr>
        {{/if}}

        {{if $conf.dPplanningOp.CSejour.easy_mode_sortie}}
            <tr>
                <th>{{mb_label object=$sejour field=mode_sortie}}</th>
                <td
                  colspan="{{$colspan}}">{{mb_field object=$sejour field=mode_sortie onchange="Value.synchronize(this, 'editSejour');"}}</td>
            </tr>
        {{/if}}

        {{assign var=required_atnc value="dPplanningOp CSejour required_atnc"|gconf}}
        {{if $mode_easy === "1col" && (("dPplanningOp CSejour fields_display show_atnc"|gconf && $conf.dPplanningOp.CSejour.easy_atnc) || $required_atnc)}}
            {{mb_ternary var=notnull_atnc test=$required_atnc value="notNull" other=""}}
            <th>{{mb_label object=$sejour field="ATNC" class=$notnull_atnc}}</th>
            <td colspan="{{$colspan}}">
                {{mb_field object=$sejour field="ATNC" class=$notnull_atnc typeEnum="select" emptyLabel="Non renseigné"
                onchange="checkATNC(this, 'easy')"}}
            </td>
        {{/if}}

        <!-- ALD et C2S -->
        <tbody id="ald_patient_easy" {{if !$conf.dPplanningOp.CSejour.easy_ald_c2s}} style="display: none;"{{/if}}>
        {{mb_include module=planningOp template=inc_check_ald onchange="Value.synchronize(this, 'editSejour');"}}
        </tbody>

        <!-- Selection du type de chambre et du régime alimentaire-->
        {{if $mode_easy === "1col" && $conf.dPplanningOp.CSejour.easy_chambre_simple && "dPhospi prestations systeme_prestations"|gconf == "standard"}}
            <tr>
                <th>{{mb_label object=$sejour field="chambre_seule"}}</th>
                <td
                  colspan="{{$colspan}}">{{mb_field object=$sejour field="chambre_seule" onchange="checkChambreSejourEasy()"}}</td>
            </tr>
        {{/if}}

        {{if $conf.dPplanningOp.CSejour.easy_chambre_simple || $conf.dPplanningOp.COperation.easy_regime || "dPbloc CPlageOp systeme_materiel"|gconf == "expert" || "dPhospi prestations systeme_prestations"|gconf == "expert"}}
            <tr>
                <td></td>
                <td colspan="{{$colspan}}">
                    {{if $conf.dPplanningOp.COperation.easy_materiel && "dPbloc CPlageOp systeme_materiel"|gconf == "expert"}}
                        {{mb_include module=dPbloc template=inc_button_besoins_ressources object_id=$op->_id type=operation_id from_dhe=1}}
                    {{/if}}
                    {{if $conf.dPplanningOp.CSejour.easy_chambre_simple && "dPhospi prestations systeme_prestations"|gconf == "expert" && $sejour->_id}}
                        <button type="button" class="search me-tertiary" onclick="Prestations.edit('{{$sejour->_id}}')">
                            Prestations
                        </button>
                    {{/if}}
                    {{if $conf.dPplanningOp.COperation.easy_regime}}
                        {{mb_include template=regimes_alimentaires prefix=easy}}
                    {{/if}}
            </tr>
        {{/if}}

        <!-- Consultation d'accompagnement -->
        {{if $conf.dPplanningOp.CSejour.consult_accomp}}
            <tr>
                <th>{{mb_label object=$sejour field=consult_accomp}}</th>
                <td
                  colspan="{{$colspan}}">{{mb_field object=$sejour field=consult_accomp typeEnum=radio onchange="checkConsultAccompSejourEasy()"}}</td>
            </tr>
        {{/if}}

        {{if ($conf.dPplanningOp.COperation.easy_materiel || $conf.dPplanningOp.COperation.easy_remarques) && $conf.dPplanningOp.COperation.show_remarques}}
            {{if $conf.dPplanningOp.COperation.easy_materiel}}
                <tr>
                    <td></td>
                    <td class="text" {{if !$conf.dPplanningOp.COperation.easy_remarques}}colspan="2"{{/if}}>
                        {{mb_label object=$op field="materiel"}}
                    </td>
                    <td class="text">{{mb_label object=$op field="exam_per_op"}}</td>
                </tr>
                <tr>
                    <td></td>
                    <td {{if !$conf.dPplanningOp.COperation.easy_remarques}}colspan="2"{{/if}}>
                        {{mb_field object=$op field="materiel" onchange="Value.synchronize(this);" form="editOpEasy"
                        aidesaisie="validateOnBlur: 0"}}
                    </td>
                    <td>
                        {{mb_field object=$op field="exam_per_op" onchange="Value.synchronize(this);" form="editOpEasy"
                        aidesaisie="validateOnBlur: 0"}}
                    </td>
                </tr>
            {{/if}}

            {{if $conf.dPplanningOp.COperation.easy_remarques}}
                <tr>
                    <td></td>
                    <td class="text" colspan="3">{{mb_label object=$op field="rques"}}</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="3">
                        {{mb_field object=$op field="rques" onchange="Value.synchronize(this);" form="editOpEasy"
                        aidesaisie="validateOnBlur: 0"}}
                    </td>
                </tr>
            {{/if}}
        {{/if}}
        {{if "dPplanningOp CSejour fields_display accident"|gconf && $conf.dPplanningOp.COperation.easy_accident}}
            <tr>
                <th>{{mb_label object=$sejour field="date_accident"}}</th>
                <td
                  colspan="{{$colspan}}">{{mb_field object=$sejour form="editOpEasy" field="date_accident" register=true onchange="checkAccidentEasy();"}}</td>
            </tr>
            <tr>
                <th>{{mb_label object=$sejour field="nature_accident"}}</th>
                <td
                  colspan="{{$colspan}}">{{mb_field object=$sejour field="nature_accident" emptyLabel="Choose" style="width: 15em;" onchange="checkAccidentEasy();"}}</td>
            </tr>
        {{/if}}

        {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
            {{mb_include module=appFineClient template=inc_button_pack_dhe object=$sejour patient=$patient prefixe=_easy}}
        {{/if}}
    </table>
</form>
