{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=facturation script=facture}}
<script>
    function changeLink(form, new_link, type_view, csv) {
        $V(document.printFrm.m, 'facturation');
        $V(document.printFrm.a, new_link);
        checkRapport(form, type_view, csv);
    }

    function exportBilling(form) {
        $V(form.a, 'export_billing');
        $V(form.export, 1);
        $V(form.suppressHeaders, 1);
        $V(form.dialog, 0);
        $V(form._date_min, $V(document.printFrm._date_min));
        $V(form._date_max, $V(document.printFrm._date_max));
        $V(form.chir, $V(document.printFrm.chir));
        form.submit();
    }

    function checkRapport(oFormcompta, type_view, csv) {
        var oForm = document.printFrm;
        // Mode comptabilite
        var compta = 0;

        if (!oForm.chir.value && (oForm.a.value == "print_actes" || oForm.a.value == "vw_consultations_non_facture")) {
            alert($T('Compta.choose_prat'));
            return false;
        }
        if (!(checkForm(oForm))) {
            return false;
        }
        var url = new Url();
        url.setModuleAction("facturation", oForm.a.value);
        if (csv) {
            $V(oForm.export_csv, 1);
            $V(oForm.suppressHeaders, 1);
            oForm.target = "_blank";
        } else {
            $V(oForm.export_csv, 0);
            $V(oForm.suppressHeaders, 0);
            oForm.target = "";
        }
        $V(oForm._etat_reglement_patient, $V(oFormcompta._etat_reglement_patient));
        $V(oForm._etat_reglement_tiers, $V(oFormcompta._etat_reglement_tiers));
        $V(oForm._etat_accident_travail, $V(oFormcompta._etat_accident_travail));
        $V(oForm.typeVue, $V(oFormcompta.typeVue));
        $V(oForm.all_group_money, $V(oFormcompta._all_group_money));
        $V(oForm.all_group_compta, $V(oFormcompta._all_group_compta));
        $V(oForm.function_compta, $V(oFormcompta._function_compta));
        $V(oForm.cs, $V(oFormcompta.cs));

        if (type_view) {
            $V(oForm.type_view, type_view);
            $V(oForm._type_affichage, $V(oFormcompta.typeVue_etab));
            $V(oForm.mode, $V(oFormcompta.mode_etab));
        } else {
            $V(oForm.type_view, 'consult');
            $V(oForm._type_affichage, $V(oFormcompta._type_affichage));
            $V(oForm.mode, $V(oFormcompta.mode));
        }
        url.addFormData(oForm);
        if (csv) {
            oForm.submit();
        } else if (compta == 1) {
            url.popup(950, 600, $T('compta-rapport_compta-title'));
        } else {
            url.popup(950, 600, $T('Report'));
        }
        return false;
    }

    function viewActes(csv) {
        var oForm = document.printFrm;
        var oFormcompta = document.printCompta;
        $V(oForm.m, 'dPplanningOp');
        $V(oForm.a, "vw_actes_realises");
        if (!oForm.chir.value) {
            alert($T('Compta.choose_prat'));
            return false;
        }

        $V(oForm.typeVue, $V(oFormcompta.typeVue));
        $V(oForm.bloc_id, $V(oFormcompta.bloc_id));
        $V(oForm.order, $V(oFormcompta.order));
        if (csv) {
            $V(oForm.export_csv, 1);
            $V(oForm.suppressHeaders, 1);
            oForm.target = "_blank";
            oForm.submit();
        } else {
            $V(oForm.export_csv, 0);
            $V(oForm.suppressHeaders, 0);
            oForm.target = "";
            var url = new Url();
            url.setModuleAction("dPplanningOp", "vw_actes_realises");
            url.addFormData(oForm);
            url.popup(950, 550, $T('compta-vw_actes_realises-title'));
        }
        return false;
    }

    function printRatioOptam(form) {
        if (!$V(document.printFrm.chir)) {
            Modal.alert($T('common-Practitioner.choose_select'));
            return;
        }

        if (!$V(form.elements['_date_souscription_optam'])) {
            Modal.alert('Veuillez renseigner votre date de souscription à l\'Optam');
            return;
        }

        var url = new Url('facturation', 'get_depassement_ratio_optam');
        url.addParam('user_id', $V(document.printFrm.chir));
        url.addParam('date_souscription_optam', $V(form.elements['_date_souscription_optam']));
        url.requestUpdate('ratio_optam');
    };
</script>

<form name="printCompta" action="?" method="get" target="_blank" onSubmit="return checkRapport(this);">
    <input type="hidden" name="m" value="facturation">
    <input type="hidden" name="a" value=""/>
    <input type="hidden" name="dialog" value="1"/>
    <input type="hidden" name="export" value="0">
    <input type="hidden" name="suppressHeaders" value="0"/>
    <input type="hidden" name="_date_min" value="">
    <input type="hidden" name="_date_max" value="">
    <input type="hidden" name="chir" value="">
    <input type="hidden" name="export_csv" value="">
    <table class="main form me-no-align me-no-box-shadow">
        {{if "dPfacturation CFactureCabinet view_bill"|gconf}}
            <tr>
                <th class="category" colspan="4">{{tr}}Gestion.compta_cabinet{{/tr}}</th>
            </tr>
            <tr>
                <td colspan="2" class="button" style="width:50%;">
                    <button class="print" type="button" onclick="changeLink(document.printFrm, 'print_noncote');">
                        {{tr}}Gestion.print_noncote{{/tr}}
                    </button>
                    <button class="download notext me-tertiary" type="button"
                            onclick="changeLink(document.printFrm, 'print_noncote', null, true);">
                        {{tr}}Gestion.print_noncote{{/tr}}
                    </button>
                    <div class="small-info" style="text-align:center;">
                        {{tr}}Gestion.print_noncote.desc{{/tr}}
                    </div>
                </td>
                <td colspan="2" class="button">
                    <button class="print" type="button" onclick="changeLink(document.printFrm, 'print_retrocession');">
                        {{tr}}Gestion.print_retrocession{{/tr}}
                    </button>
                    <button class="download notext me-tertiary" type="button"
                            onclick="changeLink(document.printFrm, 'print_retrocession', null, true);">
                        {{tr}}Gestion.print_retrocession{{/tr}}
                    </button>
                    <div class="small-info" style="text-align:center;">
                        {{tr}}Gestion.print_retrocession.desc{{/tr}}
                    </div>
                </td>
            </tr>
            <tr>
                <td class="button" colspan="4" style="padding-top:5px;padding-bottom:5px;">
                    <hr/>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="button" style="width:50%;">
                    <button class="print" type="button"
                            onclick="changeLink(document.printFrm, 'vw_consultations_non_facture');">
                        {{tr}}Gestion.vw_consultations_non_facture{{/tr}}
                    </button>
                    <div class="small-info" style="text-align:center;">
                        {{tr}}Gestion.consultation_non_facture.desc{{/tr}}
                    </div>
                </td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td class="button" colspan="4" style="padding-top:5px;padding-bottom:5px;">
                    <hr/>
                </td>
            </tr>
            <tr>
                <th>{{tr}}Compta.cs_free{{/tr}}</th>
                <td>
                    <label for="cs_1">{{tr}}common-Yes{{/tr}}</label>
                    <input type="radio" name="cs" value="1" checked="checked"/>
                    <label for="cs_0">{{tr}}common-No{{/tr}}</label>
                    <input type="radio" name="cs" value="0"/>
                </td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <th>{{mb_label object=$filter field="_etat_reglement_patient"}}</th>
                <td>{{mb_field object=$filter field="_etat_reglement_patient" emptyLabel="All" canNull="true"}}</td>
                <th>{{mb_label object=$filter_reglement field="mode"}}</th>
                <td>{{mb_field object=$filter_reglement field="mode" emptyLabel="All" canNull="true"}}</td>
            </tr>
            <tr>
                <th>{{mb_label object=$filter field="_etat_reglement_tiers"}}</th>
                <td>{{mb_field object=$filter field="_etat_reglement_tiers" emptyLabel="All" canNull="true"}}</td>
                <th>{{mb_label object=$filter field="_type_affichage"}}</th>
                <td>{{mb_field object=$filter field="_type_affichage" canNull="true"}}</td>
            </tr>
            <tr>
                <th>{{mb_label object=$filter field="_etat_accident_travail"}}</th>
                <td>{{mb_field object=$filter field="_etat_accident_travail" emptyLabel="All" canNull="true"}}</td>
                <th>{{mb_label object=$filter field="_all_group_compta"}}</th>
                <td>{{mb_field object=$filter field="_all_group_compta" typeEnum=checkbox}}</td>
            </tr>
            <tr>
                <th>{{mb_label object=$filter field="_all_group_money"}}</th>
                <td>{{mb_field object=$filter field="_all_group_money" typeEnum=checkbox}}</td>
                <th>{{mb_label object=$filter field=_function_compta}}</th>
                <td>{{mb_field object=$filter field=_function_compta typeEnum=checkbox}}</td>
            </tr>
            <tr>
                <td class="button" colspan="2">
                    <button class="print" type="button" onclick="changeLink(this.form, 'print_rapport');">
                        {{tr}}Compta.valid_money{{/tr}}
                    </button>
                    <button class="download notext me-tertiary" type="button"
                            onclick="changeLink(this.form, 'print_rapport', null, true);">
                        {{tr}}Compta.valid_money{{/tr}}
                    </button>
                    {{if "dPccam codage use_cotation_ccam"|gconf}}
                        <button class="download me-tertiary" type="button"
                                onclick="exportBilling(this.form);">{{tr}}Export{{/tr}}</button>
                    {{/if}}
                </td>
                <td>
                    <button class="download notext me-tertiary" type="button"
                            onclick="changeLink(this.form, 'print_compta', null, true);" style="float:right;">
                        {{tr}}Compta.print{{/tr}}
                    </button>
                    <button class="print" type="button" onclick="changeLink(this.form, 'print_compta');"
                            style="float:right;">
                        {{tr}}Compta.print{{/tr}}
                    </button>
                </td>
                <td>
                    <button class="print" type="button" onclick="changeLink(this.form, 'print_bordereau');"
                            style="float:left;">
                        {{tr}}Compta.print_cheque{{/tr}}
                    </button>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="small-info" style="text-align:center;">{{tr}}Compta.info{{/tr}}</div>
                </td>
                <td colspan="2">
                    <div class="small-info" style="text-align:center;">
                        {{tr}}Compta.encaissements_info{{/tr}}
                    </div>
                </td>
            </tr>
            {{if "dPccam codage use_cotation_ccam"|gconf}}
                <tr>
                    <td class="button" colspan="4">
                        <hr/>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <th>
                        {{mb_label object=$filter field=_date_souscription_optam}}
                    </th>
                    <td>
                        {{mb_field object=$filter field=_date_souscription_optam form='printCompta' register=true}}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="button">
                        <button class="print" type="button"
                                onclick="changeLink(this.form, 'print_tva');">{{tr}}Gestion.print_tva{{/tr}}</button>
                    </td>
                    <td colspan="2" class="button">
                        <button type="button" class="print"
                                onclick="printRatioOptam(this.form);">{{tr}}Gestion.print_ratios_optam{{/tr}}</button>
                        <div id="ratio_optam"></div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="small-info" style="text-align:center;">
                            {{tr}}Gestion.print_tva.desc{{/tr}}
                        </div>
                    </td>
                    <td colspan="2">
                        <div class="small-info" style="text-align:center;">
                            {{tr}}Gestion.print_ratios_optam-desc{{/tr}}
                        </div>
                    </td>
                </tr>
            {{/if}}
        {{/if}}
        {{if "dPfacturation CFactureEtablissement view_bill"|gconf}}
            <tr>
                <th class="category" colspan="4">{{tr}}Gestion.compta_etab{{/tr}}</th>
            </tr>
            <tr>
                <th>
                    <label for="typeVue">{{tr}}CFactureEtablissement-type-view{{/tr}}</label>
                </th>
                <td>
                    <select name="typeVue">
                        <option value="1">{{tr}}CFactureEtablissement-complete-list{{/tr}}</option>
                        <option value="2">{{tr}}CFactureEtablissement-total|pl{{/tr}}</option>
                    </select>
                </td>
                <th>
                    <label for="typeVue_etab">{{tr}}CFactureEtablissement-type-view{{/tr}}</label>
                </th>
                <td>
                    <select name="typeVue_etab">
                        <option value="complete">{{tr}}CFactureEtablissement-complete-list{{/tr}}</option>
                        <option value="totaux">{{tr}}CFactureEtablissement-total|pl{{/tr}}</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="bloc_id">{{tr}}CBlocOperatoire{{/tr}}</label>
                </th>
                <td>
                    <select name="bloc_id">
                        <option value="">&mdash; {{tr}}common-all|pl{{/tr}}</option>
                        {{foreach from=$blocs item=_bloc}}
                            <option value="{{$_bloc->_id}}">{{$_bloc}}</option>
                        {{/foreach}}
                    </select>
                </td>
                <th>{{mb_label object=$filter_reglement field="mode"}}</th>
                <td>
                    <select name="mode_etab" class="{{$filter_reglement->_props.mode}}">
                        <option value="">— {{tr}}All{{/tr}}</option>
                        {{foreach from=$filter_reglement->_specs.mode->_list item=_mode}}
                            <option value="{{$_mode}}">{{tr}}CReglement.mode.{{$_mode}}{{/tr}}</option>
                        {{/foreach}}
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="order">{{tr}}CFactureEtablissement-show-date{{/tr}}</label></th>
                <td>
                    <select name="order">
                        <option value="sortie_reelle">{{tr}}CFactureEtablissement-real-output{{/tr}}</option>
                        <option value="acte_execution">{{tr}}CFactureEtablissement-date-acte{{/tr}}</option>
                    </select>
                </td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td class="button" colspan="2">
                    {{if $conf.ref_pays == 1}}
                        <button type="button" class="print"
                                onclick="viewActes();">{{tr}}Compta.valid_money{{/tr}}</button>
                        <button type="button" class="download notext"
                                onclick="viewActes(true);">{{tr}}Compta.valid_money{{/tr}}</button>
                    {{else}}
                        <button class="print me-secondary" type="submit"
                                onclick="changeLink(this.form, 'print_actes');">
                            {{tr}}Compta.valid_money{{/tr}}
                        </button>
                        <button class="download notext me-tertiary" type="submit"
                                onclick="changeLink(this.form, 'print_actes', null, true);">
                            {{tr}}Compta.valid_money{{/tr}}
                        </button>
                    {{/if}}
                </td>
                <td>
                    <button class="download notext me-tertiary" type="button"
                            onclick="changeLink(this.form, 'print_compta', 'etab', true);" style="float:right;">
                        {{tr}}Compta.print{{/tr}}
                    </button>
                    <button class="print" type="button" onclick="changeLink(this.form, 'print_compta', 'etab');"
                            style="float:right;">
                        {{tr}}Compta.print{{/tr}}
                    </button>
                </td>
                <td>
                    <button class="print" type="button" onclick="changeLink(this.form, 'print_bordereau', 'etab');"
                            style="float:left;">
                        {{tr}}Compta.print_cheque{{/tr}}
                    </button>
                </td>
            </tr>
            <tr>
                <td class="button" colspan="2">
                    <div class="small-info" style="text-align:center;">
                        {{tr}}Gestion.print_actes.desc{{/tr}}
                    </div>
                </td>
                <td colspan="2">
                    <div class="small-info" style="text-align:center;">
                        {{tr}}Compta.print_bordereau.desc{{/tr}}
                    </div>
                </td>
            </tr>
        {{/if}}
        <tr>
            <th class="category" colspan="4">{{tr}}Gestion.totaux{{/tr}}</th>
        </tr>
        <tr>
            <td colspan="2" class="button">
                <button type="button" class="search me-primary"
                        onclick="Facture.viewTotaux();">{{tr}}mod-dPfacturation-tab-ajax_total_cotation{{/tr}}</button>
            </td>
            <td colspan="2"></td>
        </tr>
    </table>
</form>
