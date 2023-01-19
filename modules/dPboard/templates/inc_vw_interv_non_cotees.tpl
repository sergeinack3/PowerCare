{{*
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="dPplanningOp" script="operation"}}
{{mb_script module="patients"     script="pat_selector"}}

<script>
    onSelectChirFunction = function (field) {
        if (field.name == 'praticien_id') {
            if (field.form.elements['function_id']) {
                $V(field.form.elements['_function_view'], '', false);
                $V(field.form.elements['function_id'], 0, false);
            }
        } else {
            $V(field.form.elements['_chir_view'], '', false);
            $V(field.form.elements['praticien_id'], 0, false);
        }

        field.form.submit();
    };

    updateActes = function () {
        $('doFilterCotation').disable();
        $('doExportCotation').disable();

        var url = new Url("board", "listInterventionNonCotees");
        var chir_id = '{{$chirSel}}';
        if (chir_id == '' && $V(getForm('filterObjects').praticien_id)) {
            chir_id = $V(getForm('filterObjects').praticien_id);
        }

        url.addParam("praticien_id", chir_id);
        url.addParam('function_id', '{{$function->_id}}');
        url.addParam("all_prats", "{{$all_prats}}");
        url.addParam("begin_date", "{{$begin_date}}");
        url.addParam("end_date", "{{$end_date}}");
        url.addParam('objects_whithout_codes', '{{$objects_whithout_codes}}');
        url.addParam('show_unexported_acts', '{{$show_unexported_acts}}');
        url.addParam('ccam_codes', '{{'|'|implode:$ccam_codes}}');
        url.addParam('libelle', '{{$libelle}}');
        url.addParam('protocole_id', '{{$protocole_id}}');
        {{if $m != 'dPsalleOp'}}
        url.addParam('object_classes', $V(getForm('filterObjects').object_classes).join('|'));
        {{else}}
        url.addParam('object_classes', 'COperation');
        {{/if}}
        url.addParam('display_operations', '{{$display_operations}}');
        url.addParam('display_consultations', '{{$display_consultations}}');
        url.addParam('display_sejours', '{{$display_sejours}}');
        url.addParam('display_seances', '{{$display_seances}}');
        url.addParam('nda', '{{$nda}}');
        url.addParam('patient_id', '{{$patient->_id}}');
        url.addParam('codage_lock_status', '{{$codage_lock_status}}');
        url.addParam('excess_fee_chir_status', '{{$excess_fee_chir_status}}');
        url.addParam('excess_fee_anesth_status', '{{$excess_fee_anesth_status}}');
        url.addParam('display_all', '{{$display_all}}');
        url.requestUpdate("list_interv_non_cotees", function () {
            $('doFilterCotation').enable();
            $('doExportCotation').enable();
        });
    };

    popupExport = function () {
        var formFrom = getForm('filterObjects');
        var formTo = getForm('exportCotationSalleOp');
        $V(formTo.praticien_id, $V(formFrom.praticien_id));
        $V(formTo.function_id, $V(formFrom.function_id));
        $V(formTo.begin_date, $V(formFrom.begin_date));
        $V(formTo.end_date, $V(formFrom.end_date));
        $V(formTo.object_classes, $V(formFrom.object_classes));
        $V(formTo.all_prats, $V(formFrom.all_prats));
        $V(formTo.objects_whithout_codes, $V(formFrom.objects_whithout_codes));
        $V(formTo.show_unexported_acts, $V(formFrom.show_unexported_acts));
        $V(formTo.ccam_codes, $V(formFrom.ccam_codes));
        $V(formTo.libelle, $V(formFrom.libelle));
        $V(formTo.display_operations, $V(formFrom.display_operations));
        $V(formTo.display_consultations, $V(formFrom.display_consultations));
        $V(formTo.display_sejours, $V(formFrom.display_sejours));
        $V(formTo.display_all, $V(formFrom.display_all));
        {{if $m != 'dPsalleOp'}}
        $V(formTo.object_classes, $V(formFrom.object_classes).join('|'));
        {{else}}
        $V(formTo.object_classes, 'COperation');
        {{/if}}
        $V(formTo.codage_lock_status, $V(formFrom.codage_lock_status));
        $V(formTo.nda, $V(formFrom.nda));
        $V(formTo.patient_id, $V(formFrom.patient_id));
        formTo.submit();
    };

    deleteCCAMCode = function (code) {
        var elts = $$('span.ccam_' + code);
        if (elts.length > 0) {
            elts[0].remove();
        }

        var form = getForm('filterObjects');
        var codes = $V(form.ccam_codes).split('|');
        codes.splice(codes.indexOf(code), 1);
        $V(form.ccam_codes, codes.join('|'));
    }

    addCCAMCode = function (code) {
        var elts = $('display_ccam_codes');
        var elt_code = '<span class="circled ccam_' + code.readAttribute('data-code') + '">' +
          code.readAttribute('data-code')
          + '<span style="margin-left: 5px; cursor: pointer;" onclick="deleteCCAMCode(\''
          + code.readAttribute('data-code') + '\')" title="{{tr}}Delete{{/tr}}"><i class="fa fa-times"></i></span>' +
          '</span>';
        elts.insert(elt_code);

        var form = getForm('filterObjects');
        var codes = $V(form.ccam_codes).split('|');
        codes.push(code.readAttribute('data-code'));
        $V(form.ccam_codes, codes.join('|'));
    }

    toggleValueCheckbox = function (checkbox, element) {
        $V(element, checkbox.checked ? 1 : 0);
    };

    selectObjectType = function (input) {
        var values = $V(input);

        if (values.length == 0) {
            $('doFilterCotation').disable();
            $('row-display_all').hide();
        } else {
            $('doFilterCotation').enable();
            $('row-display_all').show();
        }

        if (values.indexOf('CConsultation') != -1) {
            $V(input.form.elements['display_consultations'], 1);
        } else {
            $V(input.form.elements['display_consultations'], 0);
        }

        if (values.indexOf('COperation') != -1) {
            $V(input.form.elements['display_operations'], 1);
        } else {
            $V(input.form.elements['display_operations'], 0);
        }

        if (values.indexOf('CSejour') != -1) {
            $V(input.form.elements['display_sejours'], 1);
        } else {
            $V(input.form.elements['display_sejours'], 0);
        }

        if (values.indexOf('CSejour-seance') != -1) {
            $V(input.form.elements['display_seances'], 1);
        } else {
            $V(input.form.elements['display_seances'], 0);
        }

        var text = [];
        values.each(function (value) {
            text.push($T(value + '|pl').toLowerCase());
        });

        $('display_all-objects_type').innerHTML = text.join(', ');
    };

    checkObject = function () {
        var checkboxes = $$('input[type="checkbox"].select_objects:checked');
        if (checkboxes.length > 0) {
            $('mass_coding').enable();
        } else {
            $('mass_coding').disable();
        }
    }

    checkAllObjects = function (main_checkbox) {
        var checked = main_checkbox.checked;

        var checkboxes = $$('input[type="checkbox"].select_objects');
        checkboxes.each(function (checkbox) {
            checkbox.checked = checked;
        });

        if (checked) {
            $('mass_coding').enable();
        } else {
            $('mass_coding').disable();
        }
    };

    massCoding = function () {
        var checkboxes = $$('input[type="checkbox"].select_objects:checked');
        var objects_guid = [];

        checkboxes.each(function (checkbox) {
            objects_guid.push(checkbox.readAttribute('data-guid'));
        });

        var url = new Url('ccam', 'massCoding');
        url.addParam('objects_guid', objects_guid.join('|'));
        url.addParam('libelle', $V(getForm('filterObjects').libelle));
        url.addParam('protocole_id', $V(getForm('filterObjects').protocole_id));
        url.addParam('chir_id', '{{$chirSel}}');
        {{if $object_classes|@count == 1 && $display_operations}}
        url.addParam('object_class', 'COperation');
        {{else}}
        url.addParam('object_class', 'CSejour-seances');
        {{/if}}
        url.requestModal(-10, -50, {
            showClose:     0,
            showReload:    0,
            method:        'post',
            getParameters: {m: 'ccam', a: 'massCoding'}
        });
    };

    Main.add(function () {
        var form = getForm('filterObjects');
        Calendar.regField(form.begin_date);
        Calendar.regField(form.end_date);

        {{if $perm_fonct != 'only_me' || !$user->_is_professionnel_sante}}
        var url = new Url('mediusers', 'ajax_users_autocomplete');
        url.addParam('prof_sante', 1);
        {{if $perm_fonct == 'same_function'}}
        url.addParam('function', '{{$user->function_id}}');
        url.addParam('edit', '0');
        {{elseif $perm_fonct == 'write_right'}}
        url.addParam('edit', '1');
        {{else}}
        url.addParam('edit', '0');
        {{/if}}
        url.addParam('input_field', '_chir_view');
        url.autoComplete(getForm('selectChir').elements['_chir_view'], null, {
            minChars:           0,
            method:             'get',
            select:             'view',
            dropdown:           true,
            afterUpdateElement: function (field, selected) {
                if ($V(field) == '') {
                    $V(field, selected.down('.view').innerHTML);
                }

                $V(field.form.elements['praticien_id'], selected.getAttribute('id').split('-')[2]);
            }
        });

        var url = new Url('mediusers', 'ajax_functions_autocomplete');
        url.addParam('type', 'cabinet');
        {{if $perm_fonct == 'write_right'}}
        url.addParam('edit', '1');
        {{else}}
        url.addParam('edit', '0');
        {{/if}}
        url.addParam('input_field', '_function_view');
        url.autoComplete(getForm('selectChir').elements['_function_view'], null, {
            minChars:           0,
            method:             'get',
            select:             'view',
            dropdown:           true,
            afterUpdateElement: function (field, selected) {
                if ($V(field) == '') {
                    $V(field, selected.down('.view').innerHTML);
                }

                $V(field.form.elements['function_id'], selected.getAttribute('id').split('-')[2]);
            }
        });
        {{/if}}

        /* Autocomplete des actes CCAM */
        var url = new Url('ccam', 'autocompleteCcamCodes');
        url.addParam('date', '{{$begin_date}}');
        url.addParam('_codes_ccam', $V(form._codes_ccam));
        url.autoComplete(form._codes_ccam, '', {
            minChars:      1,
            dropdown:      true,
            width:         '250px',
            updateElement: function (selected) {
                addCCAMCode(selected);
            }
        });

        aProtocoles = {
            sejour: {},
            interv: {}
        };

        /* Autocomplete des protocoles de DHE */
        var url = new Url('planningOp', 'ajax_protocoles_autocomplete');
        url.addParam('input_field', 'libelle');
        //url.addParam('view_field', 'libelle');
        url.addParam('for_sejour', 0);
        url.autoComplete(form.libelle, '', {
            minChars:      3,
            method:        'get',
            select:        'libelle',
            dropdown:      true,
            width:         '250px',
            updateElement: function (selected) {
                $V(form.libelle, selected.down('span.view').down('strong').innerHTML.trim());
                $V(form.protocole_id, selected.readAttribute('data-id'));
            },
            callback:      function (input, queryString) {
                return queryString +
                  (input.form.search_all_chir.checked ? "" : "&chir_id=" + '{{$chirSel}}');
            }
        });

        if (!$V(form.praticien_id) && $('ChoixPraticien_praticien_id')) {
            $V(form.praticien_id, $V($('ChoixPraticien_praticien_id')));
        }

        var text = [];

        {{if $m != 'dPsalleOp'}}
        $V(form.object_classes).each(function (object_class) {
            text.push($T(object_class + '|pl'));
        });
        {{else}}
        text.push($T('COperation|pl'));
        {{/if}}
        $('display_all-objects_type').innerHTML = text.join(', ');

        updateActes();
    });
</script>
{{mb_script module=dPboard script=board}}

<form name="selectChir" method="get" action="?">
    <input type="hidden" name="m" value="{{$m}}"/>
    <input type="hidden" name="tab" value="{{$tab}}"/>
    <label for="praticien_id" title="{{tr}}CFilterCotation-practician-title{{/tr}}">{{tr}}Praticien{{/tr}}</label>
    <input type="hidden" name="praticien_id" value="{{$chir->_id}}" onchange="onSelectChirFunction(this);"
      {{if $perm_fonct == 'only_me' && $user->_is_professionnel_sante}} disabled{{/if}}>
    <input type="text" name="_chir_view"
           value="{{$chir}}" {{if $perm_fonct == 'only_me' && $user->_is_professionnel_sante}} disabled{{/if}}>

    {{if $perm_fonct != 'only_me'}}
        <label for="function_id">{{tr}}Cabinet{{/tr}}</label>
        <input type="hidden" name="function_id" value="{{$function->_id}}" onchange="onSelectChirFunction(this);">
        <input type="text" name="_function_view" value="{{$function}}">
    {{/if}}
</form>

<form name="exportCotationSalleOp" method="get" target="_blank">
    <input type="hidden" name="m" value="board"/>
    <input type="hidden" name="a" value="listInterventionNonCotees"/>
    <input type="hidden" name="dialog" value="1"/>
    <input type="hidden" name="suppressHeaders" value="1"/>
    <input type="hidden" name="praticien_id"/>
    <input type="hidden" name="function_id"/>
    <input type="hidden" name="begin_date"/>
    <input type="hidden" name="end_date"/>
    <input type="hidden" name="all_prats" value="{{$all_prats}}"/>
    <input type="hidden" name="chirSel" value="{{$chirSel}}"/>
    <input type="hidden" name="objects_whithout_codes" value="{{$objects_whithout_codes}}"/>
    <input type="hidden" name="show_unexported_acts" value="{{$show_unexported_acts}}"/>
    <input type="hidden" name="ccam_codes" value="{{'|'|implode:$ccam_codes}}"/>
    <input type="hidden" name="libelle" value="{{$libelle}}"/>
    <input type="hidden" name="display_operations" value="{{$display_operations}}"/>
    <input type="hidden" name="display_consultations" value="{{$display_consultations}}"/>
    <input type="hidden" name="display_sejours" value="{{$display_sejours}}"/>
    <input type="hidden" name="display_all" value="{{$display_all}}"/>
    <input type="hidden" name="object_classes"/>
    <input type="hidden" name="codage_lock_status"/>
    <input type="hidden" name="excess_fee_chir_status"/>
    <input type="hidden" name="excess_fee_anesth_status"/>
    <input type="hidden" name="nda"/>
    <input type="hidden" name="patient_id"/>
    <input type="hidden" name="export" value="1"/>
</form>

<form name="filterObjects" method="get" action="?">
    <input type="hidden" name="m" value="{{$m}}"/>
    <input type="hidden" name="tab" value="viewInterventionsNonCotees"/>
    <input type="hidden" name="praticien_id" value="{{$chirSel}}"/>
    <table class="form">
        <tr>
            <th colspan="6" class="title">
                <button id="doExportCotation" type="button" class="hslip" onclick="popupExport();"
                        style="float: right;">{{tr}}Export-CSV{{/tr}}</button>
                {{tr}}filter-criteria{{/tr}}
            </th>
        </tr>
        <tr>
            <th>
                <label>{{tr}}Period{{/tr}}</label>
            </th>
            <td>
                <div class="me-margin-10">
                    <label>
                        {{tr}}date.From{{/tr}}
                        <input type="hidden" name="begin_date" value="{{$begin_date}}"
                               class="date notNull" onchange="Board.customPeriod(true)"/>
                    </label>
                    <label>
                        {{tr}}date.To{{/tr}}
                        <input type="hidden" name="end_date" value="{{$end_date}}" class="date notNull"
                               onchange="Board.customPeriod(false)"/>
                    </label>
                </div>
                <div class="me-margin-10">
                    <label>
                        <input type="radio" name="select_days" onclick="Board.setPeriod('{{$today}}','{{$today}}');"
                               value="day"/>
                        {{tr}}CConsultation-current-day{{/tr}}
                    </label>
                    <label>
                        <input type="radio" name="select_days"
                               onclick="Board.setPeriod('{{$curr_week_start}}','{{$curr_week_end}}');" value="week"/>
                        {{tr}}CConsultation-current-week{{/tr}}
                    </label>
                    <label>
                        <input type="radio" name="select_days"
                               onclick="Board.setPeriod('{{$curr_month_start}}','{{$curr_month_end}}');" value="month"/>
                        {{tr}}CConsultation-current-month{{/tr}}
                    </label>
                    <label>
                        <input type="radio" name="select_days"
                               onclick="Board.setPeriod('{{$last_month_start}}','{{$last_month_end}}');"
                               value="last_month"/>
                        {{tr}}Last-month{{/tr}}
                    </label>
                </div>
            </td>
            <th>
                <label for="objects_whithout_codes">{{tr}}CFilterCotation-objects_without_codes{{/tr}}</label>
            </th>
            <td>
                <input type="checkbox" name="_cb_objects_whithout_codes" {{if $objects_whithout_codes}}checked{{/if}}
                       onchange="toggleValueCheckbox(this, this.form.objects_whithout_codes);"/>
                <input type="hidden" name="objects_whithout_codes" value="{{$objects_whithout_codes}}"/>
            </td>
            <th>
                <label for="unexported_acts">{{tr}}CFilterCotation-unexported_acts{{/tr}}</label>
            </th>
            <td>
                <input type="checkbox" name="_cb_show_unexported_acts" {{if $show_unexported_acts}}checked{{/if}}
                       onchange="toggleValueCheckbox(this, this.form.show_unexported_acts);"/>
                <input type="hidden" name="show_unexported_acts" value="{{$show_unexported_acts}}"/>
            </td>
        </tr>
        <tr>
            <th>
                <label for="ccam_codes">{{tr}}CFilterCotation-ccam_codes{{/tr}}</label>
            </th>
            <td>
                <input type="hidden" name="ccam_codes" value="{{'|'|implode:$ccam_codes}}"/>
                <input type="text" name="_codes_ccam" class="autocomplete" size="10"/>
                <span id="display_ccam_codes">
          {{foreach from=$ccam_codes item=_code}}
              {{if $_code != ''}}
                  <span class="circled ccam_{{$_code}}">
                {{$_code}}
                <span style="margin-left: 5px; cursor: pointer;" onclick="deleteCCAMCode('{{$_code}}')"
                      title="{{tr}}Delete{{/tr}}"><i class="fa fa-times"></i></span>
              </span>
              {{/if}}
          {{/foreach}}
        </span>
            </td>
            <th>
                <label for="libelle">{{tr}}CFilterCotation-libelle{{/tr}}</label>
            </th>
            <td>
                <input type="text" name="libelle" value="{{$libelle}}" style="width: 15em;"
                       placeholder="{{tr}}fast-search{{/tr}} motif"/>
                <input type="hidden" name="protocole_id" value="{{$protocole_id}}"/>
                <input type="checkbox" name="search_all_chir"
                       title="{{tr}}CFilterCotation-search-all-practicians-title{{/tr}}"/>
                <button type="button" class="cancel notext"
                        onclick="$V(this.form.libelle, ''); $V(this.form.protocole_id, '');">{{tr}}Empty{{/tr}}</button>
            </td>
            {{if $m != 'dPsalleOp'}}
                <th>
                    <label for="object_classes">{{tr}}CFilterCotation-object_classes{{/tr}}</label>
                </th>
                <td>
                    <select name="object_classes" onchange="selectObjectType(this);" multiple>
                        <option value="COperation"
                                {{if $display_operations}}selected{{/if}}>{{tr}}common-Operation|pl{{/tr}}</option>
                        <option value="CConsultation"
                                {{if $display_consultations}}selected{{/if}}>{{tr}}common-Consultation|pl{{/tr}}</option>
                        <option value="CSejour"
                                {{if $display_sejours}}selected{{/if}}>{{tr}}common-Stay|pl{{/tr}}</option>
                        <option value="CSejour-seance"
                                {{if $display_seances}}selected{{/if}}>{{tr}}common-Session|pl{{/tr}}</option>
                    </select>
                    <input type="hidden" name="display_operations" value="{{$display_operations}}"/>
                    <input type="hidden" name="display_consultations" value="{{$display_consultations}}"/>
                    <input type="hidden" name="display_sejours" value="{{$display_sejours}}"/>
                    <input type="hidden" name="display_seances" value="{{$display_seances}}"/>
                </td>
            {{else}}
                <td colspan="2"></td>
            {{/if}}
        </tr>
        <tr>
            <th>
                <label for="nda">{{tr}}CFilterCotation-nda{{/tr}}</label>
            </th>
            <td>
                <input type="text" name="nda" value="{{$nda}}"/>
            </td>
            <th>
                <label for="patient_id">{{tr}}CFilterCotation-patient{{/tr}}</label>
            </th>
            <td>
                <input type="text" name="_seek_patient" style="width: 13em;"
                       placeholder="{{tr}}fast-search{{/tr}} patient" value="{{$patient->_view}}"/>
                <button type="button" onclick="$V(this.form._seek_patient, ''); $V(this.form.patient_id, '');"
                        class="cancel notext" title="Vider le champ"></button>
                <input type="hidden" name="patient_id" value="{{$patient->_id}}"/>
                <script>
                    Main.add(function () {
                        var form = getForm('filterObjects');
                        var url = new Url('system', 'ajax_seek_autocomplete');
                        url.addParam('object_class', 'CPatient');
                        url.addParam('field', 'patient_id');
                        url.addParam('view_field', '_seek_patient');
                        url.addParam('input_field', '_seek_patient');
                        url.autoComplete(form.elements._seek_patient, null, {
                            minChars:           3,
                            method:             'get',
                            select:             'view',
                            dropdown:           false,
                            width:              '300px',
                            afterUpdateElement: function (field, selected) {
                                $V(field.form.patient_id, selected.getAttribute('id').split('-')[2]);
                                $V(field.form._seek_patient, selected.down('.view').innerHTML);
                            }
                        });
                        Event.observe(form.elements._seek_patient, 'keydown', PatSelector.cancelFastSearch);
                    });
                </script>
            </td>
            <th>
                <label for="codage_lock_status">{{tr}}CFilterCotation-codage_lock_status{{/tr}}</label>
            </th>
            <td>
                <select name="codage_lock_status">
                    <option value="">&mdash; {{tr}}CFilterCotation.codage_lock_status.none{{/tr}}</option>

                    {{foreach from=$list_codage_lock_status item=_status}}
                        <option value="{{$_status}}"
                                {{if $codage_lock_status == $_status}}selected{{/if}}>
                            {{tr}}CFilterCotation.codage_lock_status.{{$_status}}{{/tr}}</option>
                    {{/foreach}}
                </select>
            </td>
        </tr>
        <tr id="row-display_all">
            {{if $user->isChirurgien()}}
                <th>
                    <label for="excess_fee_payment_status">
                        {{tr}}CFilterCotation-filter_excess_fee_chir_status{{/tr}}
                    </label>
                </th>
                <td>
                    <select name="excess_fee_chir_status">
                        <option value="">
                            &mdash; {{tr}}CFilterCotation-filter_select_excess_fee_chir_status{{/tr}}</option>
                        {{foreach from=$list_excess_fee_payment_status item=_status}}
                            <option value="{{$_status}}"
                                    {{if $excess_fee_chir_status == $_status}}selected{{/if}}>
                                {{tr}}COperation.reglement_dh_anesth.{{$_status}}{{/tr}}</option>
                        {{/foreach}}
                    </select>
                </td>
            {{elseif $user->isAnesth()}}
                <th>
                    <label for="excess_fee_payment_status">
                        {{tr}}CFilterCotation-filter_excess_fee_anesth_status{{/tr}}
                    </label>
                </th>
                <td>
                    <select name="excess_fee_anesth_status">
                        <option value="">
                            &mdash; {{tr}}CFilterCotation-filter_select_excess_fee_anesth_status{{/tr}}</option>
                        {{foreach from=$list_excess_fee_payment_status item=_status}}
                            <option value="{{$_status}}"
                                    {{if $excess_fee_anesth_status == $_status}}selected{{/if}}>
                                {{tr}}COperation.reglement_dh_anesth.{{$_status}}{{/tr}}</option>
                        {{/foreach}}
                    </select>
                </td>
            {{/if}}
            <th colspan="{{if !$user->isChirurgien() && !$user->isAnesth()}}5{{else}}3{{/if}}">
                <label for="display_all">{{tr}}CFilterCotation-display_all{{/tr}}<span
                      id="display_all-objects_type"></span></label>
            </th>
            <td>
                <input type="checkbox" name="_cb_display_all" {{if $display_all}}checked{{/if}}
                       onchange="toggleValueCheckbox(this, this.form.display_all);"/>
                <input type="hidden" name="display_all" value="{{$display_all}}"/>
            </td>
        </tr>
        <tr>
            <td class="button" colspan="6">
                <button id="doFilterCotation" type="submit" class="search">{{tr}}Search{{/tr}}</button>
                {{if $chirSel && $object_classes|@count == 1
                && ($display_consultations
                || ($display_operations && ($ccam_codes|@count || $libelle)) || $display_seances)}}
                    <button id="mass_coding" type="button" class="edit" onclick="massCoding();"
                            disabled>{{tr}}common-Masscoding{{/tr}}</button>
                {{/if}}
            </td>
        </tr>
    </table>
</form>

<div id="list_interv_non_cotees">
</div>
