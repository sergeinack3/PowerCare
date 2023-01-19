{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=closing_rights    value=false}}
{{assign var=config_closing    value='dPcabinet CConsultation close_cotation_rights'|gconf}}
{{assign var=auto_cloture_conf value="dPfacturation `$consult->_ref_facture->_class` use_auto_cloture"|gconf}}

{{if $config_closing == 'everyone' || ($config_closing == 'owner_only' && ($user->_id == $consult->_ref_praticien->_id || $user->isAdmin()))}}
    {{assign var=closing_rights value=true}}
{{/if}}

{{mb_default var=cotation_full value=0}}

<script>
    reloadFacture = function () {
        Facture.reload('{{$consult->patient_id}}', '{{$consult->_id}}', 1, '{{$consult->_ref_facture->_id}}', '{{$consult->_ref_facture->_class}}');
    };

    Main.add(function () {
        prepareForm(document.accidentTravail);

        {{if $consult->type_assurance}}
        var url = new Url("cabinet", "ajax_type_assurance");
        url.addParam("consult_id", '{{$consult->_id}}');
        url.requestUpdate("area_type_assurance");
        {{/if}}

        {{if $consult->_ref_patient->ald}}
        if ($('accidentTravail_concerne_ALD_1')) {
            $('accidentTravail_concerne_ALD_1').checked = "checked";
            onSubmitFormAjax(document.accidentTravail);
        }
        {{/if}}

        // Mise a jour de du_patient
        var form = document.forms['tarifFrm'];
        if (form && form.du_patient && form.du_patient.value == "0") {
            Cotation.modifTotal('{{$consult->valide}}');
        }
    });
</script>

{{assign var=ald_mandatory value=false}}
{{assign var=ald_class value=''}}
{{if $patient->ald && (($displayFSE && ($app->user_prefs.LogicielFSE == 'pv' || $app->user_prefs.LogicielFSE == 'oxPyxvital'))
|| ($consult->sejour_id && 'dPplanningOp CSejour ald_mandatory'|gconf))}}
    {{assign var=ald_mandatory value=true}}
    {{if $consult->concerne_ALD != ''}}
        {{assign var=ald_class value=' notNullOK'}}
    {{else}}
        {{assign var=ald_class value=' notNull'}}
    {{/if}}
{{/if}}

<fieldset>
    <legend>{{tr}}CConsultation-cotation{{/tr}}</legend>

    <div style="text-align: center; font-weight: bold;">
        {{if $patient->c2s}}
            {{tr}}CPatient-c2s{{/tr}}
            <br/>
        {{/if}}

        {{if $patient->ame}}
            {{tr}}CPatient-AME{{/tr}}
            <br/>
        {{/if}}

        {{if $conf.ref_pays == 1}}
            <span id="patient_ald"{{if $patient->ald != '1'}} style="display: none;"{{/if}}>
        {{tr}}CPatient-ald{{/tr}}<br/>
      </span>
        {{/if}}

        <span id="patient_acs"{{if $patient->acs != '1'}} style="display: none;"{{/if}}>
      {{tr}}CPatient-acs{{/tr}}
      <span id="patient_acs_contrat"{{if $patient->acs_type == 'none'}} style="display: none;"{{/if}}>
          &mdash; {{tr}}CPatient-contrat{{/tr}} <span
            id="patient_acs_contrat_value">{{$patient->acs_type|strtoupper}}</span>
      </span>
    <span>
    </div>

    <!-- Formulaire de selection de tarif -->
    <form name="selectionTarif" action="?m={{$m}}" method="post"
          onsubmit="return Cotation.selectTarif(this, {{$ald_mandatory}});">
        <input type="hidden" name="m" value="cabinet"/>
        <input type="hidden" name="del" value="0"/>
        <input type="hidden" name="dosql" value="do_consultation_aed"/>
        {{mb_key object=$consult}}
        <input type="hidden" name="_bind_tarif" value="1"/>
        <input type="hidden" name="_delete_actes" value="0"/>

        {{if $consult->tarif == "pursue"}}
            {{mb_field object=$consult field=tarif hidden=1}}
        {{/if}}

        <table class="form me-no-box-shadow">
            {{if !$consult->valide}}
                <tr id="consult_concern_ald"{{if $consult->_ref_patient->ald != '1'}} style="display: none;"{{/if}}>
                    <th style="width: 15%;">{{mb_label object=$consult field=concerne_ALD class=$ald_class}}</th>
                    <td style="width: 85%;">
                        <input id="selectionTarif_concerne_ALD_1" type="radio" class="bool{{$ald_class}}"
                               name="concerne_ALD" value="1"
                                {{if $consult->concerne_ALD == '1'}} checked="checked"{{/if}}
                                {{if $consult->_ref_patient->ald != '1'}} disabled{{/if}}
                               onclick="Cotation.syncALD(this);"/>
                        <label for="selectionTarif_concerne_ALD_1">{{tr}}Yes{{/tr}}</label>
                        <input id="selectionTarif_concerne_ALD_0" type="radio" class="bool{{$ald_class}}"
                               name="concerne_ALD" value="0"
                                {{if $consult->concerne_ALD == '0' || ($consult->concerne_ALD == '' && !$ald_mandatory)}} checked="checked"{{/if}}
                                {{if $consult->_ref_patient->ald != '1'}} disabled{{/if}}
                               onclick="Cotation.syncALD(this);"/>
                        <label for="selectionTarif_concerne_ALD_0">{{tr}}No{{/tr}}</label>
                    </td>
                </tr>
                {{if $displayFSE && ($app->user_prefs.LogicielFSE == 'pv' || $app->user_prefs.LogicielFSE == 'oxPyxvital') && $consult->_ref_patient->ald == '0'}}
                    <tr id="consult_force_ald">
                        <th style="width: 15%;"><label for="selectionTarif_concerne_ALD"
                                                       title="{{tr}}CConsultation-forcer-ald-desc{{/tr}}">{{tr}}CConsultation-forcer-ald{{/tr}}</label>
                        </th>
                        <td style="width: 85%;">{{mb_field object=$consult field=concerne_ALD}}</td>
                    </tr>
                {{/if}}
            {{elseif $consult->_ref_patient->ald || $consult->concerne_ALD}}
                <tr>
                    <th style="width: 15%;">{{mb_label object=$consult field=concerne_ALD}}</th>
                    <td
                      style="width: 85%;">{{if $consult->concerne_ALD != ''}}{{mb_value object=$consult field=concerne_ALD}}{{/if}}</td>
                </tr>
            {{/if}}
            {{if !$consult->valide}}
                <tr>
                    <th style="width: 15%;"><label for="choix"
                                                   title="{{tr}}CConsultation-cotation-desc{{/tr}}">{{tr}}CConsultation-cotation{{/tr}}</label>
                    </th>
                    <td style="width: 85%;">
                        <select name="_codable_guid" class="str" style="width: 130px;" onchange="this.form.onsubmit();">
                            <option value="" selected="selected">&mdash; {{tr}}Choose{{/tr}}</option>
                            {{if $tarifs.user|@count}}
                                <optgroup label="{{tr}}CConsultation-Practitioner price{{/tr}}">
                                    {{foreach from=$tarifs.user item=_tarif}}
                                        <option value="{{$_tarif->_guid}}"
                                                {{if $_tarif->_precode_ready}}class="checked"{{/if}}>{{$_tarif}}</option>
                                    {{/foreach}}
                                </optgroup>
                            {{/if}}
                            {{if $tarifs.func|@count}}
                                <optgroup label="{{tr}}CConsultation-Office price{{/tr}}">
                                    {{foreach from=$tarifs.func item=_tarif}}
                                        <option value="{{$_tarif->_guid}}"
                                                {{if $_tarif->_precode_ready}}class="checked"{{/if}}>{{$_tarif}}</option>
                                    {{/foreach}}
                                </optgroup>
                            {{/if}}
                            {{if $list_devis|@count}}
                                <optgroup label="{{tr}}CDevisCodage{{/tr}}">
                                    {{foreach from=$list_devis item=_devis}}
                                        <option value="{{$_devis->_guid}}">{{$_devis->libelle}}</option>
                                    {{/foreach}}
                                </optgroup>
                            {{/if}}
                            {{if "dPcabinet Tarifs show_tarifs_etab"|gconf && $tarifs.group|@count}}
                                <optgroup label="{{tr}}CConsultation-Etablishment price{{/tr}}">
                                    {{foreach from=$tarifs.group item=_tarif}}
                                        <option value="{{$_tarif->_guid}}"
                                                {{if $_tarif->_precode_ready}}class="checked"{{/if}}>{{$_tarif}}</option>
                                    {{/foreach}}
                                </optgroup>
                            {{/if}}
                        </select>
                    </td>
                </tr>
            {{else}}
                <tr>
                    <th style="width: 15%;" class="me-valign-top">{{tr}}CConsultation-cotation{{/tr}}</th>
                    <td style="width: 85%;">
                        {{if $consult->valide}}
                            {{mb_script module=cabinet script=tarif ajax=true}}
                            <!-- Creation d'un nouveau tarif avec les actes de la consultation courante -->
                            <button id="inc_vw_reglement_button_create_tarif" class="submit" type="button"
                                    style="float: right;"
                                    onclick="Tarif.newCodable('{{$consult->_id}}', 'CConsultation', '{{$praticien->_id}}');">
                                {{tr}}CConsultation-action-new-tarif{{/tr}}
                            </button>
                        {{else}}
                            <button type="button" style="float: right;" class="add" onclick="Cotation.pursueTarif();">
                                {{tr}}Add{{/tr}}
                            </button>
                        {{/if}}
                        {{mb_value object=$consult field=tarif}}
                    </td>
                </tr>
            {{/if}}
        </table>
    </form>
    <!-- Fin formulaire de selection du tarif -->

    <!-- Formulaire date d'éxécution de tarif -->
    <form name="editExecTarif" action="?m={{$m}}" method="post"
          onsubmit="return onSubmitFormAjax(this, Reglement.reload.curry());">
        {{mb_key object=$consult}}
        {{mb_class object=$consult}}

        <table class="form me-no-box-shadow">
            <tr>
                <th style="width: 15%;">{{mb_label object=$consult field="exec_tarif"}}</th>
                <td style="width: 85%;">
                    {{if $consult->valide}}
                        {{mb_value object=$consult field="exec_tarif"}}
                    {{else}}
                        {{mb_field object=$consult field="exec_tarif" form="editExecTarif" register=true onchange="this.form.onsubmit();"}}
                    {{/if}}
                </td>
            </tr>
        </table>
    </form>

    <hr class="me-no-display"/>

    <table class="form me-no-box-shadow">
        <!-- Les actes codés -->
        {{if "dPccam codage use_cotation_ccam"|gconf == "1"}}
            {{assign var=rowspan value=2}}
            {{if 'lpp'|module_active && "lpp General cotation_lpp"|gconf}}
                {{math assign=rowspan equation="x+1" x=$rowspan}}
            {{/if}}
            {{if "dPccam frais_divers use_frais_divers_CConsultation"|gconf}}
                {{math assign=rowspan equation="x+1" x=$rowspan}}
            {{/if}}

            {{assign var=can_delete_acte value=0}}

            {{if $closing_rights && (!$consult->tarif || ($consult->_ref_facture->patient_date_reglement !== "" && $consult->du_patient) || !$consult->valide)}}
                {{assign var=can_delete_acte value=1}}
            {{/if}}

            {{if $consult->_ref_praticien->isExecutantCCAM()}}
                <tr>
                    <th style="width: 15%;">{{tr}}CConsultation-codes_ccam{{/tr}}</th>
                    <td colspan="2">{{mb_field object=$consult field="_tokens_ccam" readonly="readonly" hidden=1}}
                        {{foreach from=$consult->_ref_actes_ccam item=acte_ccam}}
                            {{if $can_delete_acte}}
                                <form name="delActeCCAP-{{$acte_ccam->_guid}}" method="post"
                                      onsubmit="return onSubmitFormAjax(this, Reglement.reload)">
                                    {{mb_class object=$acte_ccam}}
                                    {{mb_key   object=$acte_ccam}}
                                    <input type="hidden" name="del" value="1"/>
                                    <button type="button" class="remove" onclick="this.form.onsubmit();">
                                        {{$acte_ccam->_shortview}}
                                    </button>
                                </form>
                            {{else}}
                                <span
                                  onmouseover="ObjectTooltip.createEx(this, '{{$acte_ccam->_guid}}');">{{$acte_ccam->_shortview}}</span>
                            {{/if}}
                            {{foreachelse}}
                            <span class="empty">{{tr}}CActeCCAM.none{{/tr}}</span>
                        {{/foreach}}
                    </td>
                    <td rowspan="{{$rowspan}}" style="vertical-align: middle;">
                        <button type="button" class="edit" id="edit_actes" style="float: right;"
                                onclick="Cotation.viewActes('{{$consult->_id}}', {{$ald_mandatory}});">{{tr}}CConsultation-action-gerer-actes{{/tr}}</button>
                    </td>
                </tr>
            {{/if}}
            <tr>
                <th style="width: 15%;">{{tr}}CConsultation-codes_ngap{{/tr}}</th>
                <td colspan="2">{{mb_field object=$consult field="_tokens_ngap" readonly="readonly" hidden=1}}
                    {{foreach from=$consult->_ref_actes_ngap item=acte_ngap}}
                        {{if $can_delete_acte}}
                            <form name="delActeNGAP-{{$acte_ngap->_guid}}" method="post"
                                  onsubmit="return onSubmitFormAjax(this, Reglement.reload)">
                                {{mb_class object=$acte_ngap}}
                                {{mb_key   object=$acte_ngap}}
                                <input type="hidden" name="del" value="1"/>
                                <button type="button" class="remove" onclick="this.form.onsubmit();">
                                    {{$acte_ngap->_shortview}} {{if $acte_ngap->complement}}({{$acte_ngap->complement}}){{/if}}
                                </button>
                            </form>
                        {{else}}
                            <span
                              onmouseover="ObjectTooltip.createEx(this, '{{$acte_ngap->_guid}}');">{{$acte_ngap->_shortview}}</span>
                        {{/if}}
                        {{foreachelse}}
                        <span class="empty">{{tr}}CActeNGAP.none{{/tr}}</span>
                    {{/foreach}}
                </td>
                {{if !$consult->_ref_praticien->isExecutantCCAM()}}
                    <td rowspan="{{$rowspan - 1}}" style="vertical-align: middle;">
                        <button type="button" class="edit" id="edit_actes" style="float: right;"
                                onclick="Cotation.viewActes('{{$consult->_id}}', {{$ald_mandatory}});">{{tr}}CConsultation-action-gerer-actes{{/tr}}</button>
                    </td>
                {{/if}}
            </tr>
            {{if 'lpp'|module_active &&  "lpp General cotation_lpp"|gconf}}
                <tr>
                    <th style="width: 15%;">{{tr}}CConsultation-codes_lpp{{/tr}}</th>
                    <td colspan="2">
                        {{foreach from=$consult->_ref_actes_lpp item=_acte_lpp}}
                            <span
                              onmouseover="ObjectTooltip.createEx(this, '{{$_acte_lpp->_guid}}');">{{$_acte_lpp->code}}</span>
                            {{foreachelse}}
                            <span class="empty">{{tr}}CActeLPP.none{{/tr}}</span>
                        {{/foreach}}
                    </td>
                </tr>
            {{/if}}

            {{if "dPccam frais_divers use_frais_divers_CConsultation"|gconf}}
                <tr>
                    <th style="width: 15%;">{{tr}}CConsultation-frais-divers{{/tr}}</th>
                    <td colspan="2">
                        {{foreach from=$consult->_ref_frais_divers item=frais}}
                            <span
                              onmouseover="ObjectTooltip.createEx(this, '{{$frais->_guid}}');">{{$frais->_shortview}}</span>
                            {{foreachelse}}
                            <span class="empty">{{tr}}CFraisDivers.none{{/tr}}</span>
                        {{/foreach}}
                    </td>
                </tr>
                </div>
            {{/if}}
        {{/if}}
    </table>

    <!-- Formulaire de tarification -->
    <form name="tarifFrm" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
        <input type="hidden" name="m" value="cabinet"/>
        <input type="hidden" name="del" value="0"/>
        <input type="hidden" name="dosql" value="do_consultation_aed"/>
        <input type="hidden" name="type_facture"
               value="{{if $consult->pec_at == 'arret'}}accident{{else}}maladie{{/if}}"/>
        {{mb_key object=$consult}}
        {{mb_field object=$consult field="sejour_id" hidden=1}}

        {{if $consult->valide != '1'}}
            <input type="hidden" name="concerne_ALD"
                    {{if $consult->concerne_ALD == '' && !$ald_mandatory}} value="0"
                    {{else}} value="{{$consult->concerne_ALD}}"{{/if}}>
        {{/if}}

        <table class="form me-no-box-shadow">
            {{assign var=use_category_bill value="dPfacturation CFactureCategory use_category_bill"|conf:"CFunctions-`$consult->_ref_praticien->function_id`"}}
            {{if $use_category_bill != "hide"}}
                <tr>
                    <td>
                        <label for="tarifFrm__category_facturation"
                               {{if $use_category_bill == "obligatory"}}class="notNull"{{/if}}
                               title="{{tr}}CFactureCategory{{/tr}}">
                            {{tr}}CFactureCategory{{/tr}}
                        </label>
                    </td>
                    <td>
                        {{assign var=categories_disabled value=false}}

                        {{if $consult->valide == "1"}}
                            {{assign var=categories_disabled value=true}}
                        {{/if}}

                        {{assign var=categorys_factu value='Ox\Mediboard\Facturation\CFactureCategory::getListForFunction'|static_call:$consult->_ref_praticien->function_id}}
                        <select name="_category_facturation" {{if $categories_disabled}}disabled{{/if}}
                                {{if $use_category_bill == "obligatory"}}class="notNull"{{/if}}>
                            <option value="">{{tr}}Choose{{/tr}}</option>
                            {{foreach from=$categorys_factu item=_category}}
                                <option value="{{$_category->_id}}"
                                        {{if $consult->_ref_facture->category_id == $_category->_id}}selected="selected"{{/if}}>
                                    {{$_category->_view}}
                                </option>
                            {{/foreach}}
                        </select>
                    </td>
                    <td colspan="3"></td>
                </tr>
            {{/if}}
            {{if $consult->valide}}
                <tr>
                    <th style="width: 15%;">{{mb_label object=$consult field="secteur1"}}</th>
                    <td style="width: 35%;">
                        {{mb_value object=$consult field="secteur1"}}
                    </td>
                    <th style="width: 15%;">{{mb_label object=$consult field="_somme"}}</th>
                    <td style="width: 35%;">
                        {{if !$cotation_full}}
                            <button type="button" class="search notext" style="float: right;"
                                    title="{{tr}}CReglement.full_cotation{{/tr}}"
                                    onclick="Reglement.cotationModal();"></button>
                        {{/if}}
                        {{mb_value object=$consult field="_somme" value=$consult->secteur1+$consult->secteur2+$consult->secteur3+$consult->du_tva}}
                        {{if $consult->secteur1+$consult->secteur2+$consult->secteur3+$consult->du_tva == $consult->du_tiers}}
                            <i class="fas fa-check texticon" style="color:green">
                                {{mb_label object=$consult field=du_tiers}}
                            </i>
                        {{/if}}
                    </td>
                </tr>
                <tr {{if !$cotation_full}}style="display: none;"{{/if}}>
                    <th>{{mb_label object=$consult field="secteur2"}}</th>
                    <td>
                        {{mb_value object=$consult field="secteur2"}}
                    </td>
                    <th>{{mb_label object=$consult field="du_patient"}}</th>
                    <td>
                        {{mb_value object=$consult field="du_patient"}}
                    </td>
                </tr>
                <tr {{if !$cotation_full || !"dPccam codage use_cotation_ccam"|gconf}}style="display: none;"{{/if}}>
                    <th>{{mb_label object=$consult field="secteur3"}}</th>
                    <td>
                        {{mb_value object=$consult field="secteur3"}} &nbsp;&nbsp;
                        {{mb_label object=$consult field="du_tva"}}
                        {{mb_value object=$consult field="du_tva"}} ({{$consult->taux_tva}}%)
                    </td>
                    <th>{{mb_label object=$consult field="du_tiers"}}</th>
                    <td>{{mb_value object=$consult field="du_tiers"}}</td>
                </tr>
            {{else}}
                {{math equation="x+y+z+a" x=$consult->secteur1 y=$consult->secteur2 z=$consult->secteur3 a=$consult->du_tva assign=somme}}
                <tr>
                    <th style="width: 15%;">{{mb_label object=$consult field="secteur1"}}</th>
                    <td style="width: 35%;">
                        {{if !$consult->_ref_actes|@count}}
                            {{mb_field object=$consult field="secteur1" onchange="Cotation.modifTotal('"|cat:$consult->valide|cat:"')"}}
                        {{else}}
                            {{mb_field object=$consult field="secteur1" readonly=readonly}}
                        {{/if}}
                    </td>
                    <th style="width: 15%;">{{mb_label object=$consult field="_somme"}}</th>
                    <td style="width: 35%;">
                        {{if !$cotation_full}}
                            <button type="button" class="search notext" style="float: right;"
                                    title="{{tr}}CReglement.full_cotation{{/tr}}"
                                    onclick="Reglement.cotationModal();"></button>
                        {{/if}}

                        {{mb_field size=6 object=$consult field="_somme" value="$somme" onchange="Cotation.modifSecteur2()"}}
                    </td>
                </tr>
                <tr {{if !$cotation_full}}style="display: none;"{{/if}}>
                    <th>{{mb_label object=$consult field="secteur2"}}</th>
                    <td>
                        {{mb_field object=$consult field="secteur2" onchange="Cotation.modifTotal('"|cat:$consult->valide|cat:"')" readonly=true}}
                    </td>
                    {{if !$consult->_ref_facture->patient_date_reglement && !$consult->sejour_id}}
                        <th>{{mb_label object=$consult field="du_patient"}}</th>
                        <td>
                            {{mb_field object=$consult field="du_patient"}}
                        </td>
                    {{else}}
                        <td colspan="2"><input type="hidden" name="du_patient" value="0"/></td>
                    {{/if}}
                </tr>
                <tr {{if !$cotation_full || !"dPccam codage use_cotation_ccam"|gconf}}style="display: none;"{{/if}}>
                    {{if $cotation_full}}
                        <th>{{mb_label object=$consult field="secteur3"}}</th>
                        <td>
                            {{mb_field object=$consult field="secteur3" onchange="Cotation.modifTVA()"}}
                        </td>
                    {{/if}}
                    <th>{{mb_label object=$consult field="du_tiers"}}</th>
                    <td>
                        {{mb_field object=$consult field="tarif" hidden=1}}
                        {{mb_field object=$consult field="du_tiers" readonly=readonly}}
                        {{if (!'fse'|module_active || ($app->user_prefs.LogicielFSE != 'pv' && $app->user_prefs.LogicielFSE != 'oxPyxvital') && $displayFSE)}}
                            <button id="reglement_button_tiers_payant" type="button" class="tick"
                                    onclick="Cotation.tiersPayant();">
                                {{tr}}CConsultation-Total third-party payment{{/tr}}
                            </button>
                        {{/if}}
                    </td>
                </tr>
                <tr {{if !"dPccam codage use_cotation_ccam"|gconf}}style="display: none;"{{/if}}>
                    <th>
                        {{if !$cotation_full}}
                            {{mb_label object=$consult field="secteur3"}}
                        {{else}}
                            {{mb_label object=$consult field="taux_tva"}}
                        {{/if}}
                    </th>
                    <td>
                        {{if !$cotation_full}}
                            {{mb_field object=$consult field="secteur3" onchange="Cotation.modifTVA()"}}
                        {{/if}}
                        {{assign var=default_taux_tva value="dPcabinet CConsultation default_taux_tva"|gconf}}
                        {{assign var=taux_tva value="|"|explode:$default_taux_tva}}
                        <select name="taux_tva" onchange="Cotation.modifTVA()">
                            {{foreach from=$taux_tva item=taux}}
                                <option value="{{$taux}}"
                                        {{if $consult->taux_tva == $taux}}selected="selected"{{/if}}>{{tr}}CConsultation.taux_tva.{{$taux}}{{/tr}}</option>
                            {{/foreach}}
                        </select>
                    </td>
                    <th>{{tr}}CConsultation-TTC{{/tr}}</th>
                    <td>
                        <input type="text" name="_ttc" value="{{$somme}}" class="currency styled-element" size="6"
                               onchange="Cotation.modifSecteur3()"/>{{$conf.currency_symbol|html_entity_decode}}
                    </td>
                </tr>
                <tr {{if !"dPccam codage use_cotation_ccam"|gconf}}style="display: none;"{{/if}}>
                    <th>{{mb_label object=$consult field="du_tva"}}</th>
                    <td>{{mb_field object=$consult field="du_tva" readonly="readonly"}}</td>
                    <th></th>
                    <td></td>
                </tr>
            {{/if}}

            {{if $consult->_ref_facture->patient_date_reglement}}
                <tr style="display: none;">
                    <td colspan="4">
                        {{mb_field object=$consult field="du_patient" hidden=1}}
                    </td>
                </tr>
            {{/if}}
            {{if $consult->tarif && ($consult->_ref_facture->patient_date_reglement == "" || $consult->du_patient == 0) && $consult->valide == "1"}}
                <tr>
                    <td colspan="4" class="button">
                        <input type="hidden" name="valide" value="1"/>
                        <input type="hidden" name="secteur1" value="{{$consult->secteur1}}"/>
                        <input type="hidden" name="secteur2" value="{{$consult->secteur2}}"/>
                        <input type="hidden" name="_somme" value="{{$consult->_somme}}"/>
                        <input type="hidden" name="du_patient" value="{{$consult->du_patient}}"/>
                        <input type="hidden" name="du_tiers" value="{{$consult->du_tiers}}"/>

                        {{if $app->user_prefs.autoCloseConsult}}
                            <input type="hidden" name="chrono" value="{{$consult->chrono}}"/>
                        {{/if}}

                        {{if !$consult->_current_fse && !count($consult->_ref_facture->_ref_reglements)
                        && $closing_rights && $consult->_ref_facture->statut_envoi !== "envoye" &&
                        ($auto_cloture_conf || !$auto_cloture_conf && !$consult->_ref_facture->cloture)}}
                            <button class="cancel" type="button" id="buttonCheckActe"
                                    onclick="Cotation.checkActe(this);">
                                {{tr}}CConsultation-action-Reopen the quotation{{/tr}}
                            </button>
                        {{/if}}
                        <button class="print" type="button"
                                onclick="Cotation.printActes('{{$consult->_id}}')">{{tr}}CConsultation-action-print-actes{{/tr}}</button>
                        {{if $frais_divers|@count}}
                            <button class="add" type="button"
                                    onclick="Cotation.createSecondFacture();">{{tr}}CConsultation-action-add-invoice{{/tr}}</button>
                        {{/if}}
                    </td>
                </tr>
            {{elseif !$consult->_ref_facture->patient_date_reglement}}
                <tr>
                    <td colspan="4" class="button">
                        <input type="hidden" name="_delete_actes" value="0"/>
                        <input type="hidden" name="valide" value="1"/>

                        {{if $app->user_prefs.autoCloseConsult}}
                            <input type="hidden" name="chrono" value="64"/>
                        {{/if}}
                        {{if $closing_rights}}
                            <button id="reglements_button_cloturer_cotation" class="submit" type="button"
                                    onclick="Cotation.validTarif();">{{tr}}CConsultation-action-close-cotation{{/tr}}</button>
                        {{/if}}
                        <button class="cancel" type="button"
                                onclick="Cotation.cancelTarif('delActes', null, '{{$app->user_prefs.autoCloseConsult}}')">{{tr}}CConsultation-action-empty-cotation{{/tr}}</button>
                    </td>
                </tr>
            {{/if}}
        </table>
    </form>
    <!-- Fin du formulaire de tarification -->

    {{if $frais_divers|@count}}
        <form name="addFactureDivers" action="" method="post"
              onsubmit="return onSubmitFormAjax(this, Reglement.reload.curry());">
            {{math equation="x+1" x=$consult->_ref_factures|@count assign=numero_fact}}
            {{mb_class  object=$consult->_ref_facture}}
            <input type="hidden" name="facture_id" value=""/>
            <input type="hidden" name="group_id" value="{{$g}}"/>
            <input type="hidden" name="patient_id" value="{{$consult->_ref_facture->patient_id}}"/>
            <input type="hidden" name="praticien_id" value="{{$consult->_ref_facture->praticien_id}}"/>
            <input type="hidden" name="_consult_id" value="{{$consult->_id}}"/>
            <input type="hidden" name="ouverture" value="{{$consult->_ref_facture->ouverture}}"/>
            <input type="hidden" name="numero" value="{{$numero_fact}}"/>
        </form>
    {{/if}}
</fieldset>

{{if $ald_mandatory}}
    <div id="modal-concerne_ALD-mandatory" style="display: none;">
        <form name="editALDMandatory" method="" action="" onsubmit="return Cotation.submitALDMandatory(this);">
            {{mb_class object=$consult}}
            {{mb_key object=$consult}}

            <input type="hidden" name="action" value="">
            <table class="form">
                <tr>
                    <td style="text-align: center;">
                        {{tr}}CConsultation-msg-ald_mandatory{{/tr}}
                    </td>
                </tr>
                <tr>
                    <td class="button">
                        <input id="editConsultALD-{{$consult->_id}}_concerne_ALD_1" type="radio" class="bool"
                               name="concerne_ALD" value="1" onclick="this.form.onsubmit();"/>
                        <label for="editConsultALD-{{$consult->_id}}_concerne_ALD_1">{{tr}}Yes{{/tr}}</label>
                        <input id="editConsultALD-{{$consult->_id}}_concerne_ALD_0" type="radio" class="bool"
                               name="concerne_ALD" value="0" onclick="this.form.onsubmit();"/>
                        <label for="editConsultALD-{{$consult->_id}}_concerne_ALD_0">{{tr}}No{{/tr}}</label>
                    </td>
                </tr>
            </table>
        </form>
    </div>
{{/if}}
