{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient         value=$grossesse->_ref_parturiente}}
{{assign var=pere            value=$grossesse->_ref_pere}}
{{assign var=dossier_pere    value=$pere->_ref_dossier_medical}}
{{assign var=dossier         value=$grossesse->_ref_dossier_perinat}}
{{assign var=dossier_medical value=$patient->_ref_dossier_medical}}

<script>
    listForms = [
        getForm("constantesMater-{{$dossier->_guid}}"),
        getForm("Antecedents-medicaux-{{$dossier->_guid}}"),
        getForm("Antecedents-chir-gyneco-{{$dossier->_guid}}"),
        {{if $pere->_id}}
        getForm("Dossier-medical-pere-{{$dossier_pere->_guid}}"),
        getForm("constantesPater-{{$dossier->_guid}}"),
        getForm("Antecedents-pere-{{$dossier->_guid}}"),
        {{/if}}
        getForm("Antecedents-familiaux-{{$dossier->_guid}}"),
        getForm("editGrossessesAnt-{{$grossesse->_guid}}"),
        getForm("Antecedents-obstetricaux-{{$dossier->_guid}}")
    ];

    includeForms = function () {
        DossierMater.listForms = listForms.clone();
    };

    submitAllForms = function (callBack) {
        includeForms();
        DossierMater.submitAllForms(callBack);
    };

    calculIMC = function (form) {
        var taille = parseFloat($V(form.taille));
        var poids = parseFloat($V(form.poids));

        if (poids && !isNaN(poids) && poids > 0 && taille && !isNaN(taille) && taille > 0) {
            var imc = Math.round(100 * 100 * 100 * poids / (taille * taille)) / 100;
            $V(form._imc, imc);
        }
    };

    Main.add(function () {
        {{if !$print}}
        includeForms();
        DossierMater.prepareAllForms();
        {{/if}}

        DossierMater._patient_id = '{{$patient->_id}}';

        Control.Tabs.create('tab-antecedents', true, {foldable: true {{if $print}}, unfolded: true{{/if}}});
    });
</script>

<form name="addAntecedentDossierMedical" action="?" method="post">
    <input type="hidden" name="m" value="patients"/>
    <input type="hidden" name="antecedent_id" value=""/>
    <input type="hidden" name="del" value="0"/>
    <input type="hidden" name="dosql" value="do_antecedent_aed"/>
    <input type="hidden" name="_patient_id" value="{{$patient->_id}}"/>
    <input type="hidden" name="_sejour_id" value=""/>
    <input type="hidden" name="type" value="anesth"/>
    <input type="hidden" name="appareil" value=""/>
    <input type="hidden" name="rques" value=""/>
</form>

{{mb_include module=maternite template=inc_dossier_mater_header}}

<div style="float: right; position: relative; top: 32px;">
    <button type="button" class="search not-printable"
            title="{{tr}}CDossierMedical-ATCD/TP-desc{{/tr}}"
            onclick="DossierMater.openAtcdAndTP('{{$patient->_id}}');">
        {{tr}}CDossierMedical-ATCD/TP{{/tr}}
    </button>
</div>

<ul id="tab-antecedents" class="control_tabs me-margin-top-0">
    <li><a href="#antecedents_maternels">{{tr}}CDossierPerinat-tab-Maternal antecedent|pl{{/tr}}</a></li>
    <li><a href="#antecedents_paternels">{{tr}}CDossierPerinat-tab-Paternal antecedent|pl{{/tr}}</a></li>
    <li><a href="#antecedents_familiaux">{{tr}}CDossierPerinat-risque_atcd_familiaux{{/tr}}</a></li>
    <li><a href="#antecedents_obstétricaux">{{tr}}CDossierPerinat-risque_atcd_obst{{/tr}}</a></li>
</ul>

<div id="antecedents_maternels" class="me-padding-2" style="display: none;">
    <table class="main layout">
        <tr>
            <td>
                <table class="main">
                    <tr>
                        <td class="halfPane">
                            <form name="constantesMater-{{$dossier->_guid}}" action="?" method="post"
                                  onsubmit="return onSubmitFormAjax(this);">
                                {{mb_class object=$constantes_mater}}
                                {{mb_key   object=$constantes_mater}}

                                {{mb_field object=$constantes_mater field=patient_id hidden=true}}
                                {{mb_field object=$constantes_mater field=context_class hidden=true}}
                                {{mb_field object=$constantes_mater field=context_id hidden=true}}
                                {{mb_field object=$constantes_mater field=datetime hidden=true}}
                                {{mb_field object=$constantes_mater field=user_id hidden=true}}
                                <input type="hidden" name="_count_changes" value="0"/>
                                <input type="hidden" name="_object_guid" value="{{$dossier->_guid}}">
                                <input type="hidden" name="_object_field" value="ant_mater_constantes_id">

                                <table class="form me-no-align me-no-box-shadow me-small-form">
                                    <tr>
                                        <th class="title me-text-align-center"
                                            colspan="10">{{tr}}CDossierPerinat-Medical antecedent|pl{{/tr}}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="2">
                                            {{mb_label object=$constantes_mater field=taille}}
                                            <small class="opacity-50">(cm)</small>
                                        </th>
                                        <td class="halfPane">
                                            {{mb_field object=$constantes_mater field=taille size=3 onchange='calculIMC(this.form);'}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th colspan="2">
                                            {{mb_label object=$constantes_mater field=poids_avant_grossesse}}
                                            <small class="opacity-50">(kg)</small>
                                        </th>
                                        <td>
                                            {{mb_field object=$constantes_mater field=poids_avant_grossesse size=3}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th colspan="2">
                                            {{mb_label object=$constantes_mater field=poids}}
                                            <small class="opacity-50">(kg)</small>
                                        </th>
                                        <td>
                                            {{mb_field object=$constantes_mater field=poids size=3 onchange='calculIMC(this.form);'}}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th colspan="2">
                                            {{mb_label object=$constantes_mater field=_imc}}
                                        </th>
                                        <td>
                                            {{mb_field object=$constantes_mater field=_imc size=3 disabled=true}}
                                        </td>
                                    </tr>
                                </table>
                            </form>

                            <table class="form me-no-align me-no-box-shadow me-small-form">
                                <tr>
                                    <th colspan="2">
                                        {{tr}}CDossierPerinat-Usual medical treatment{{/tr}}
                                        <button type="button" class="edit notext compact not-printable me-tertiary"
                                                onclick="DossierMater.editTP('{{$grossesse->parturiente_id}}');"></button>
                                    </th>
                                    <td id="traitement_personnel">
                                        {{mb_include module=maternite template=inc_list_tp}}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                        <hr/>
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="2">
                                        {{tr}}CDossierPerinat-Transfusion antecedent|pl{{/tr}}
                                        <button type="button" class="edit notext compact not-printable me-tertiary"
                                                onclick="DossierMater.editAtcd('{{$patient->_id}}', 'trans');"></button>
                                    </th>
                                    <td id="atcd_trans">
                                        {{mb_include module=maternite template=inc_list_antecedents antecedents=$dossier_medical->_ref_antecedents_by_type.trans type=trans}}
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="2">
                                        {{tr}}CAntecedent-Allergie|pl{{/tr}}
                                        <button type="button" class="edit notext compact not-printable me-tertiary"
                                                onclick="DossierMater.editAtcd('{{$patient->_id}}', 'alle');"></button>
                                    </th>
                                    <td id="atcd_alle">
                                        {{mb_include module=maternite template=inc_list_antecedents antecedents=$dossier_medical->_ref_antecedents_by_type.alle type=alle}}
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="2">
                                        {{tr}}CAntecedent.type.med{{/tr}}
                                        <button type="button" class="edit notext compact not-printable me-tertiary"
                                                onclick="DossierMater.editAtcd('{{$patient->_id}}', 'med');"></button>
                                    </th>
                                    <td id="atcd_med">
                                        {{mb_include module=maternite template=inc_list_antecedents antecedents=$dossier_medical->_ref_antecedents_by_type.med type=med}}
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="2">
                                        {{tr}}CAntecedent.type.chir{{/tr}}
                                        <button type="button" class="edit notext compact not-printable me-tertiary"
                                                onclick="DossierMater.editAtcd('{{$patient->_id}}', 'chir');"></button>
                                    </th>
                                    <td id="atcd_chir">
                                        {{mb_include module=maternite template=inc_list_antecedents antecedents=$dossier_medical->_ref_antecedents_by_type.chir type=chir}}
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="2">
                                        {{tr}}CAntecedent-No type{{/tr}}
                                        <button type="button" class="edit notext compact not-printable me-tertiary"
                                                onclick="DossierMater.editAtcd('{{$patient->_id}}', '');"></button>
                                    </th>
                                    <td id="atcd_">
                                        {{foreach from=$dossier_medical->_ref_antecedents_by_type item=_antecedents key=_type}}
                                            {{if $_type == '' && $_antecedents|@count > 0}}
                                                {{mb_include module=maternite template=inc_list_antecedents antecedents=$_antecedents type=$_type}}
                                            {{/if}}
                                        {{/foreach}}
                                    </td>
                                </tr>
                                {{foreach from=$dossier_medical->_ref_antecedents_by_type item=_antecedents key=_type}}
                                    {{if !in_array($_type, array('alle', 'trans', 'med', 'chir', 'gyn', 'fam', '')) && $_antecedents|@count > 0}}
                                        <tr>
                                            <th colspan="2">
                                                {{tr}}CAntecedent|pl{{/tr}}
                                                {{tr}}CAntecedent.type.{{$_type}}{{/tr}}
                                                <button type="button"
                                                        class="edit notext compact not-printable me-tertiary"
                                                        onclick="DossierMater.editAtcd('{{$patient->_id}}', '{{$_type}}');"></button>
                                            </th>
                                            <td id="atcd_{{$_type}}">
                                                {{mb_include module=maternite template=inc_list_antecedents antecedents=$_antecedents type=$_type}}
                                            </td>
                                        </tr>
                                    {{/if}}
                                {{/foreach}}
                                <tr>
                                    <td colspan="4">
                                        <hr/>
                                    </td>
                                </tr>
                            </table>
                            <form name="Antecedents-medicaux-{{$dossier->_guid}}" method="post"
                                  onsubmit="return onSubmitFormAjax(this);">
                                {{mb_class object=$dossier}}
                                {{mb_key   object=$dossier}}
                                <input type="hidden" name="_count_changes" value="0"/>
                                <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}"/>
                                <table class="form me-no-box-shadow me-no-align me-small-form">
                                    <tr>
                                        <th class="halfPane">
                                            {{mb_label object=$dossier field=patho_ant}}<br/>
                                            pouvant nécessiter une surveillance particulière
                                        </th>
                                        <td>{{mb_field object=$dossier field=patho_ant default=""}}</td>
                                    </tr>
                                    <tr>
                                        <th>Si oui,</th>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=patho_ant_hta  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'patho_ant_');"}}</th>
                                        <td class="compact">{{mb_label object=$dossier field=patho_ant_hta}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=patho_ant_diabete  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'patho_ant_');"}}</th>
                                        <td class="compact">{{mb_label object=$dossier field=patho_ant_diabete}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=patho_ant_epilepsie  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'patho_ant_');"}}</th>
                                        <td class="compact">{{mb_label object=$dossier field=patho_ant_epilepsie}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=patho_ant_asthme  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'patho_ant_');"}}</th>
                                        <td class="compact">{{mb_label object=$dossier field=patho_ant_asthme}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=patho_ant_pulm  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'patho_ant_');"}}</th>
                                        <td class="compact">{{mb_label object=$dossier field=patho_ant_pulm}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=patho_ant_thrombo_emb  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'patho_ant_');"}}</th>
                                        <td
                                          class="compact">{{mb_label object=$dossier field=patho_ant_thrombo_emb}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=patho_ant_cardio  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'patho_ant_');"}}</th>
                                        <td class="compact">{{mb_label object=$dossier field=patho_ant_cardio}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=patho_ant_auto_immune  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'patho_ant_');"}}</th>
                                        <td
                                          class="compact">{{mb_label object=$dossier field=patho_ant_auto_immune}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=patho_ant_hepato_dig  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'patho_ant_');"}}</th>
                                        <td class="compact">{{mb_label object=$dossier field=patho_ant_hepato_dig}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=patho_ant_thyroide  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'patho_ant_');"}}</th>
                                        <td class="compact">{{mb_label object=$dossier field=patho_ant_thyroide}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=patho_ant_uro_nephro  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'patho_ant_');"}}</th>
                                        <td class="compact">{{mb_label object=$dossier field=patho_ant_uro_nephro}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=patho_ant_infectieuse  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'patho_ant_');"}}</th>
                                        <td
                                          class="compact">{{mb_label object=$dossier field=patho_ant_infectieuse}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=patho_ant_hemato  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'patho_ant_');"}}</th>
                                        <td class="compact">{{mb_label object=$dossier field=patho_ant_hemato}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=patho_ant_cancer_non_gyn  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'patho_ant_');"}}</th>
                                        <td
                                          class="compact">{{mb_label object=$dossier field=patho_ant_cancer_non_gyn}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=patho_ant_psy  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'patho_ant_');"}}</th>
                                        <td class="compact">{{mb_label object=$dossier field=patho_ant_psy}}</td>
                                    </tr>
                                    <tr>
                                        <th class="compact">{{mb_label object=$dossier field=patho_ant_autre}}</th>
                                        <td class="compact">
                                            {{if !$print}}
                                                {{assign var=atcd_autre value=$dossier->patho_ant_autre|JSAttribute}}
                                                {{mb_field object=$dossier field=patho_ant_autre form=Antecedents-medicaux-`$dossier->_guid` onchange="DossierMater.manageAntecedents(this, 'patho_ant_', '`$atcd_autre`');"}}
                                            {{else}}
                                                {{mb_value object=$dossier field=patho_ant_autre}}
                                            {{/if}}
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        </td>
                        <td>
                            <form name="Antecedents-chir-gyneco-{{$dossier->_guid}}" method="post"
                                  onsubmit="return onSubmitFormAjax(this);">
                                {{mb_class object=$dossier}}
                                {{mb_key   object=$dossier}}
                                <input type="hidden" name="_count_changes" value="0"/>
                                <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}"/>
                                <table class="form me-no-align me-no-box-shadow me-small-form">
                                    <tr>
                                        <th class="title me-text-align-center"
                                            colspan="2">{{tr}}CDossierPerinat-Surgical antecedent|pl{{/tr}}</th>
                                    </tr>
                                    <tr>
                                        <th class="halfPane">
                                            {{mb_label object=$dossier field=chir_ant}}<br/>
                                            pouvant nécessiter une surveillance particulière
                                        </th>
                                        <td>{{mb_field object=$dossier field=chir_ant default=""}}</td>
                                    </tr>
                                    <tr>
                                        <th>{{mb_label object=$dossier field=chir_ant_rques}}</th>
                                        <td>
                                            {{if !$print}}
                                                {{assign var=atcd_autre value=$dossier->chir_ant_rques|JSAttribute}}
                                                {{mb_field object=$dossier field=chir_ant_rques form=Antecedents-chir-gyneco-`$dossier->_guid`  onchange="DossierMater.manageAntecedents(this, 'chir_ant_', '`$atcd_autre`');"}}
                                            {{else}}
                                                {{mb_value object=$dossier field=chir_ant_rques}}
                                            {{/if}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="title me-text-align-center"
                                            colspan="2">{{tr}}CDossierPerinat-Gynecological antecedent|pl{{/tr}}</th>
                                    </tr>
                                    <tr>
                                        <th>{{mb_label object=$dossier field=gyneco_ant_regles}}</th>
                                        <td>{{mb_field object=$dossier field=gyneco_ant_regles}}</td>
                                    </tr>
                                    <tr>
                                        <th>{{mb_label object=$dossier field=gyneco_ant_regul_regles}}</th>
                                        <td>
                                            {{mb_field object=$dossier field=gyneco_ant_regul_regles
                                            style="width: 20em;" emptyLabel="CDossierPerinat.gyneco_ant_regul_regles."}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{mb_label object=$dossier field=gyneco_ant_fcv}}</th>
                                        <td>
                                            {{mb_field object=$dossier field=gyneco_ant_fcv
                                            form=Antecedents-chir-gyneco-`$dossier->_guid` register=true}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            {{mb_label object=$dossier field=gyneco_ant}}<br/>
                                            pouvant nécessiter une surveillance particulière
                                        </th>
                                        <td>{{mb_field object=$dossier field=gyneco_ant default=""}}</td>
                                    </tr>
                                    <tr>
                                        <th>Si oui,</th>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=gyneco_ant_herpes  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'gyneco_ant_');"}}</th>
                                        <td class="compact">{{mb_label object=$dossier field=gyneco_ant_herpes}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=gyneco_ant_lesion_col  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'gyneco_ant_');"}}</th>
                                        <td
                                          class="compact">{{mb_label object=$dossier field=gyneco_ant_lesion_col}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=gyneco_ant_conisation  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'gyneco_ant_');"}}</th>
                                        <td
                                          class="compact">{{mb_label object=$dossier field=gyneco_ant_conisation}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=gyneco_ant_cicatrice_uterus  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'gyneco_ant_');"}}</th>
                                        <td
                                          class="compact">{{mb_label object=$dossier field=gyneco_ant_cicatrice_uterus}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=gyneco_ant_fibrome  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'gyneco_ant_');"}}</th>
                                        <td class="compact">{{mb_label object=$dossier field=gyneco_ant_fibrome}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=gyneco_ant_stat_pelv  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'gyneco_ant_');"}}</th>
                                        <td class="compact">{{mb_label object=$dossier field=gyneco_ant_stat_pelv}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=gyneco_ant_cancer_sein  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'gyneco_ant_');"}}</th>
                                        <td
                                          class="compact">{{mb_label object=$dossier field=gyneco_ant_cancer_sein}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=gyneco_ant_cancer_app_genital  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'gyneco_ant_');"}}</th>
                                        <td
                                          class="compact">{{mb_label object=$dossier field=gyneco_ant_cancer_app_genital}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=gyneco_ant_malf_genitale  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'gyneco_ant_');"}}</th>
                                        <td
                                          class="compact">{{mb_label object=$dossier field=gyneco_ant_malf_genitale}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=gyneco_ant_condylomes  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'gyneco_ant_');"}}</th>
                                        <td
                                          class="compact">{{mb_label object=$dossier field=gyneco_ant_condylomes}}</td>
                                    </tr>
                                    <tr>
                                        <th
                                          class="compact">{{mb_field object=$dossier field=gyneco_ant_distilbene  typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'gyneco_ant_');"}}</th>
                                        <td
                                          class="compact">{{mb_label object=$dossier field=gyneco_ant_distilbene}}</td>
                                    </tr>
                                    <tr>
                                        <th class="compact">{{mb_label object=$dossier field=gyneco_ant_autre}}</th>
                                        <td class="compact">
                                            {{if !$print}}
                                                {{assign var=atcd_autre value=$dossier->gyneco_ant_autre|JSAttribute}}
                                                {{mb_field object=$dossier field=gyneco_ant_autre form=Antecedents-chir-gyneco-`$dossier->_guid` onchange="DossierMater.manageAntecedents(this, 'gyneco_ant_', '`$atcd_autre`');"}}
                                            {{else}}
                                                {{mb_value object=$dossier field=gyneco_ant_autre}}
                                            {{/if}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{mb_label object=$dossier field=gyneco_ant_infert}}</th>
                                        <td>{{mb_field object=$dossier field=gyneco_ant_infert default=""}}</td>
                                    </tr>
                                    <tr>
                                        <th>{{mb_label object=$dossier field=gyneco_ant_infert_origine}}</th>
                                        <td>
                                            {{mb_field object=$dossier field=gyneco_ant_infert_origine
                                            style="width: 20em;" emptyLabel="CDossierPerinat.gyneco_ant_infert_origine."}}
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
<div id="antecedents_paternels" class="me-padding-2" style="display: none;">
    {{if $pere->_id}}
        <table class="main layout">
            <tr>
                <td>
                    <form name="constantesPater-{{$dossier->_guid}}" action="?" method="post"
                          onsubmit="return onSubmitFormAjax(this);">
                        {{mb_class object=$constantes_pater}}
                        {{mb_key   object=$constantes_pater}}

                        {{mb_field object=$constantes_pater field=patient_id hidden=true}}
                        {{mb_field object=$constantes_pater field=context_class hidden=true}}
                        {{mb_field object=$constantes_pater field=context_id hidden=true}}
                        {{mb_field object=$constantes_pater field=datetime hidden=true}}
                        {{mb_field object=$constantes_pater field=user_id hidden=true}}

                        <input type="hidden" name="_count_changes" value="0"/>
                        <input type="hidden" name="_object_guid" value="{{$dossier->_guid}}">
                        <input type="hidden" name="_object_field" value="pere_constantes_id">

                        <table class="form me-no-align me-no-box-shadow me-small-form">
                            <tr>
                                <th colspan="2">
                                    {{mb_label object=$constantes_pater field=taille}}
                                    <small class="opacity-50">(cm)</small>
                                </th>
                                <td class="halfPane">
                                    {{mb_field object=$constantes_pater field=taille size=3 onchange='calculIMC(this.form);'}}
                                </td>
                            </tr>
                            <tr>
                                <th colspan="2">
                                    {{mb_label object=$constantes_pater field=poids}}
                                    <small class="opacity-50">(kg)</small>
                                </th>
                                <td>
                                    {{mb_field object=$constantes_pater field=poids size=3 onchange='calculIMC(this.form);'}}
                                </td>
                            </tr>
                            <tr>
                                <th colspan="2">
                                    {{mb_label object=$constantes_pater field=_imc}}
                                </th>
                                <td>
                                    {{mb_field object=$constantes_pater field=_imc size=3 disabled=true}}
                                </td>
                            </tr>
                        </table>
                    </form>

                    <form name="Dossier-medical-pere-{{$dossier_pere->_guid}}" method="post"
                          onsubmit="return onSubmitFormAjax(this);">
                        {{mb_class object=$dossier_pere}}
                        {{mb_key   object=$dossier_pere}}
                        <input type="hidden" name="_count_changes" value="0"/>
                        <input type="hidden" name="object_id" value="{{$pere->_id}}"/>
                        <input type="hidden" name="object_class" value="{{$pere->_class}}"/>
                        <table class="form me-no-align me-no-box-shadow me-small-form">
                            <tr>
                                <th class="halfPane">{{mb_label object=$dossier_pere field=groupe_sanguin}}</th>
                                <td>{{mb_field object=$dossier_pere field=groupe_sanguin style="width: 4em;"}}</td>
                            </tr>
                            <tr>
                                <th>{{mb_label object=$dossier_pere field=rhesus}}</th>
                                <td>{{mb_field object=$dossier_pere field=rhesus style="width: 4em;"}}</td>
                            </tr>
                            <tr>
                                <th>{{mb_label object=$dossier_pere field=groupe_ok}}</th>
                                <td>{{mb_field object=$dossier_pere field=groupe_ok default=0}}</td>
                            </tr>
                        </table>
                    </form>
                    <form name="Antecedents-pere-{{$dossier->_guid}}" method="post"
                          onsubmit="return onSubmitFormAjax(this);">
                        {{mb_class object=$dossier}}
                        {{mb_key   object=$dossier}}
                        <input type="hidden" name="_count_changes" value="0"/>

                        <table class="form me-no-align me-no-box-shadow me-small-form">
                            <tr>
                                <th class="halfPane">{{mb_label object=$dossier field=pere_serologie_vih}}</th>
                                <td>{{mb_field object=$dossier field=pere_serologie_vih style="width: 12em;"}}</td>
                            </tr>
                            <tr>
                                <th>{{mb_label object=$dossier field=pere_electrophorese_hb}}</th>
                                <td>{{mb_field object=$dossier field=pere_electrophorese_hb}}</td>
                            </tr>
                            <tr>
                                <th>{{mb_label object=$dossier field=pere_patho_ant}}</th>
                                <td>{{mb_field object=$dossier field=pere_patho_ant default=""}}</td>
                            </tr>
                            <tr>
                                <th>Si oui,</th>
                                <td></td>
                            </tr>
                            <tr>
                                <th
                                  class="compact">{{mb_field object=$dossier field=pere_ant_herpes typeEnum=checkbox}}</th>
                                <td class="compact">{{mb_label object=$dossier field=pere_ant_herpes}}</td>
                            </tr>
                            <tr>
                                <th class="compact">{{mb_label object=$dossier field=pere_ant_autre}}</th>
                                <td
                                  class="compact">{{mb_field object=$dossier field=pere_ant_autre form=Antecedents-pere-`$dossier->_guid`}}</td>
                            </tr>
                        </table>
                    </form>
                </td>
            </tr>
        </table>
    {{else}}
        <div class=big-info>{{tr}}CCorrespondantPatient.parente.father not specified{{/tr}}</div>
    {{/if}}
</div>
<div id="antecedents_familiaux" class="me-padding-2" style="display: none;">
    <form name="Antecedents-familiaux-{{$dossier->_guid}}" method="post"
          onsubmit="return onSubmitFormAjax(this);">
        {{mb_class object=$dossier}}
        {{mb_key   object=$dossier}}
        <input type="hidden" name="_count_changes" value="0"/>
        <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}"/>
        <table class="main layout">
            <tr>
                <td colspan="2">
                    <table class="form me-no-box-shadow me-no-align">
                        <tr>
                            <th>
                                {{tr}}CAntecedent.type.fam{{/tr}}
                                <button type="button" class="edit notext compact not-printable me-tertiary"
                                        onclick="DossierMater.editAtcd('{{$patient->_id}}', 'fam');"></button>
                            </th>
                            <td id="atcd_fam">
                                {{mb_include module=maternite template=inc_list_antecedents antecedents=$dossier_medical->_ref_antecedents_by_type.fam type=fam}}
                            </td>
                        </tr>
                        <tr>
                            <th class="quarterPane">{{mb_label object=$dossier field=ant_fam}}</th>
                            <td class="quarterPane">{{mb_field object=$dossier field=ant_fam default=""}}</td>
                            <th class="quarterPane">{{mb_label object=$dossier field=consanguinite}}</th>
                            <td class="quarterPane">{{mb_field object=$dossier field=consanguinite default=""}}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="halfPane">
                    Si oui,
                    <table class="form me-no-align me-no-box-shadow">
                        <tr>
                            <th class="narrow"></th>
                            <th class="narrow category me-text-align-left">{{tr}}CAntecedent.type.fam.mere{{/tr}}</th>
                            <th class="narrow category me-text-align-left">{{tr}}CAntecedent.type.fam.pere{{/tr}}</th>
                        </tr>
                        <tr>
                            <th>{{mb_label object=$dossier field=ant_fam_mere_gemellite}}</th>
                            <td>{{mb_field object=$dossier field=ant_fam_mere_gemellite typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'ant_fam_');"}}</td>
                            <td>{{mb_field object=$dossier field=ant_fam_pere_gemellite typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'ant_fam_');"}}</td>
                        </tr>
                        <tr>
                            <th>{{mb_label object=$dossier field=ant_fam_mere_malformations}}</th>
                            <td>{{mb_field object=$dossier field=ant_fam_mere_malformations typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'ant_fam_');"}}</td>
                            <td>{{mb_field object=$dossier field=ant_fam_pere_malformations typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'ant_fam_');"}}</td>
                        </tr>
                        <tr>
                            <th>{{mb_label object=$dossier field=ant_fam_mere_maladie_genique}}</th>
                            <td>{{mb_field object=$dossier field=ant_fam_mere_maladie_genique typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'ant_fam_');"}}</td>
                            <td>{{mb_field object=$dossier field=ant_fam_pere_maladie_genique typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'ant_fam_');"}}</td>
                        </tr>
                        <tr>
                            <th>{{mb_label object=$dossier field=ant_fam_mere_maladie_chrom}}</th>
                            <td>{{mb_field object=$dossier field=ant_fam_mere_maladie_chrom typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'ant_fam_');"}}</td>
                            <td>{{mb_field object=$dossier field=ant_fam_pere_maladie_chrom typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'ant_fam_');"}}</td>
                        </tr>
                        <tr>
                            <th>{{mb_label object=$dossier field=ant_fam_mere_diabete}}</th>
                            <td>{{mb_field object=$dossier field=ant_fam_mere_diabete typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'ant_fam_');"}}</td>
                            <td>{{mb_field object=$dossier field=ant_fam_pere_diabete typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'ant_fam_');"}}</td>
                        </tr>
                        <tr>
                            <th>{{mb_label object=$dossier field=ant_fam_mere_hta}}</th>
                            <td>{{mb_field object=$dossier field=ant_fam_mere_hta typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'ant_fam_');"}}</td>
                            <td>{{mb_field object=$dossier field=ant_fam_pere_hta typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'ant_fam_');"}}</td>
                        </tr>
                        <tr>
                            <th>{{mb_label object=$dossier field=ant_fam_mere_phlebite}}</th>
                            <td>{{mb_field object=$dossier field=ant_fam_mere_phlebite typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'ant_fam_');"}}</td>
                            <td>{{mb_field object=$dossier field=ant_fam_pere_phlebite typeEnum=checkbox onchange="DossierMater.manageAntecedents(this, 'ant_fam_');"}}</td>
                        </tr>
                        <tr>
                            <th>{{mb_label object=$dossier field=ant_fam_mere_autre}}</th>
                            <td>
                                {{if !$print}}
                                    {{assign var=atcd_autre value=$dossier->ant_fam_mere_autre|JSAttribute}}
                                    {{mb_field object=$dossier field=ant_fam_mere_autre form=Antecedents-familiaux-`$dossier->_guid` onchange="DossierMater.manageAntecedents(this, 'ant_fam_', '`$atcd_autre`');"}}
                                {{else}}
                                    {{mb_value object=$dossier field=ant_fam_mere_autre}}
                                {{/if}}
                            </td>
                            <td>
                                {{if !$print}}
                                    {{mb_field object=$dossier field=ant_fam_pere_autre form=Antecedents-familiaux-`$dossier->_guid`}}
                                {{else}}
                                    {{mb_value object=$dossier field=ant_fam_pere_autre}}
                                {{/if}}
                            </td>
                        </tr>
                    </table>
                </td>
                <td></td>
            </tr>
        </table>
    </form>
</div>
<div id="antecedents_obstétricaux" class="me-padding-2" style="display: none;">
    <table class="main layout">
        <tr>
            <td>
                <form name="editGrossessesAnt-{{$grossesse->_guid}}" method="post"
                      onsubmit="return onSubmitFormAjax(this);">
                    {{mb_class object=$grossesse}}
                    {{mb_key   object=$grossesse}}
                    <input type="hidden" name="_count_changes" value="0"/>
                    <table class="form me-no-align me-no-box-shadow">
                        <tr>
                            <th class="halfPane">{{mb_label object=$grossesse field=nb_grossesses_ant}}</th>
                            <td>{{mb_field object=$grossesse field=nb_grossesses_ant}}</td>
                        </tr>
                    </table>
                </form>
            </td>
        </tr>
        <tr>
            <td>
                <script>
                    Main.add(function () {
                        Control.Tabs.create('tab-grossesse', true, {
                            foldable: true {{if $print}},
                            unfolded: true{{/if}}});
                    });
                </script>

                <ul id="tab-grossesse" class="control_tabs small">
                    <li><a href="#grossesses_anterieures">{{tr}}CGrossesseAnt|pl{{/tr}}</a></li>
                    <li><a href="#grossesses_synthese">{{tr}}CGrossesseAnt-synthesis{{/tr}}</a></li>
                </ul>
                <div id="grossesses_anterieures" class="me-padding-2">
                    {{assign var=grossesses_ant value=$grossesse->_ref_grossesses_ant}}
                    <table class="tbl me-no-align">
                        <tr>
                            <th class="narrow">
                                {{tr}}CGrossesse-number{{/tr}}
                                <button type="button" class="add notext not-printable me-tertiary" style="float: left;"
                                        onclick="DossierMater.addGrossesseAnt(null, '{{$grossesse->_id}}');">
                                    {{tr}}Add{{/tr}} {{tr}}CGrossesseAnt.one{{/tr}}
                                </button>
                            </th>
                            {{foreach from=$grossesses_ant item=grossesse_ant name=backgrossesses}}
                                <th colspan="3" style="width: 21em;">
                                    {{math equation="x + 1" x=$smarty.foreach.backgrossesses.index}}
                                    <button type="button" class="edit notext not-printable me-tertiary"
                                            style="float: left;"
                                            onclick="DossierMater.addGrossesseAnt('{{$grossesse_ant->_id}}', '{{$grossesse->_id}}');">
                                        {{tr}}Add{{/tr}} {{tr}}CGrossesseAnt.one{{/tr}}
                                    </button>
                                </th>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: right;">{{mb_label class=CGrossesseAnt field=issue_grossesse}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text"
                                    colspan="3">{{mb_value object=$grossesse_ant field=issue_grossesse}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: right;">{{mb_label class=CGrossesseAnt field=date}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text" colspan="3">{{mb_value object=$grossesse_ant field=date}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: right;">{{mb_label class=CGrossesseAnt field=lieu}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text" colspan="3">{{mb_value object=$grossesse_ant field=lieu}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: right;">{{mb_label class=CGrossesseAnt field=ag}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text" colspan="3">{{mb_value object=$grossesse_ant field=ag}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td
                              style="text-align: right;">{{mb_label class=CGrossesseAnt field=grossesse_apres_amp}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text"
                                    colspan="3">{{mb_value object=$grossesse_ant field=grossesse_apres_amp}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: right;">{{mb_label class=CGrossesseAnt field=complic_grossesse}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text"
                                    colspan="3">{{mb_value object=$grossesse_ant field=complic_grossesse}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td
                              style="text-align: right;">{{mb_label class=CGrossesseAnt field=transfert_in_utero}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text"
                                    colspan="3">{{mb_value object=$grossesse_ant field=transfert_in_utero}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td
                              style="text-align: right;">{{mb_label class=CGrossesseAnt field=mode_debut_travail}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text"
                                    colspan="3">{{mb_value object=$grossesse_ant field=mode_debut_travail}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: right;">{{mb_label class=CGrossesseAnt field=mode_accouchement}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text"
                                    colspan="3">{{mb_value object=$grossesse_ant field=mode_accouchement}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: right;">{{mb_label class=CGrossesseAnt field=anesthesie}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text" colspan="3">{{mb_value object=$grossesse_ant field=anesthesie}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: right;">{{mb_label class=CGrossesseAnt field=perinee}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text" colspan="3">{{mb_value object=$grossesse_ant field=perinee}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: right;">{{mb_label class=CGrossesseAnt field=delivrance}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text" colspan="3">{{mb_value object=$grossesse_ant field=delivrance}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: right;">{{mb_label class=CGrossesseAnt field=suite_couches}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text" colspan="3">{{mb_value object=$grossesse_ant field=suite_couches}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: right;">{{mb_label class=CGrossesseAnt field=vecu_grossesse}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text"
                                    colspan="3">{{mb_value object=$grossesse_ant field=vecu_grossesse}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: right;">{{mb_label class=CGrossesseAnt field=remarques}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text" colspan="3">{{mb_value object=$grossesse_ant field=remarques}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td
                              style="text-align: right;">{{mb_label class=CGrossesseAnt field=grossesse_multiple}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text"
                                    colspan="3">{{mb_value object=$grossesse_ant field=grossesse_multiple}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: right;">{{mb_label class=CGrossesseAnt field=nombre_enfants}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text"
                                    colspan="3">{{mb_value object=$grossesse_ant field=nombre_enfants}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <th>{{tr}}CGrossesseAnt-if several for each child{{/tr}}</th>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <th style="width: 7em;">{{tr}}CGrossesseAnt-enfant-court{{/tr}} 1</th>
                                <th style="width: 7em;">{{tr}}CGrossesseAnt-enfant-court{{/tr}} 2</th>
                                <th style="width: 7em;">{{tr}}CGrossesseAnt-enfant-court{{/tr}} 3</th>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: right;">{{mb_label class=CGrossesseAnt field=sexe_enfant1}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text">{{mb_value object=$grossesse_ant field=sexe_enfant1}}</td>
                                <td class="text">
                                    {{if $grossesse_ant->sexe_enfant2}}
                                        {{mb_value object=$grossesse_ant field=sexe_enfant2}}
                                    {{/if}}
                                </td>
                                <td class="text">
                                    {{if $grossesse_ant->sexe_enfant3}}
                                        {{mb_value object=$grossesse_ant field=sexe_enfant3}}
                                    {{/if}}
                                </td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td
                              style="text-align: right;">{{mb_label class=CGrossesseAnt field=poids_naissance_enfant1}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text">{{mb_value object=$grossesse_ant field=poids_naissance_enfant1}}</td>
                                <td class="text">{{mb_value object=$grossesse_ant field=poids_naissance_enfant2}}</td>
                                <td class="text">{{mb_value object=$grossesse_ant field=poids_naissance_enfant3}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td
                              style="text-align: right;">{{mb_label class=CGrossesseAnt field=etat_nouveau_ne_enfant1}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text">{{mb_value object=$grossesse_ant field=etat_nouveau_ne_enfant1}}</td>
                                <td class="text">{{mb_value object=$grossesse_ant field=etat_nouveau_ne_enfant2}}</td>
                                <td class="text">{{mb_value object=$grossesse_ant field=etat_nouveau_ne_enfant3}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td
                              style="text-align: right;">{{mb_label class=CGrossesseAnt field=allaitement_enfant1}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text">
                                    {{if $grossesse_ant->allaitement_enfant1}}
                                        {{mb_value object=$grossesse_ant field=allaitement_enfant1}}
                                        <br/>
                                    {{/if}}
                                    {{mb_value object=$grossesse_ant field=allaitement_enfant1_desc}}
                                </td>
                                <td class="text">
                                    {{if $grossesse_ant->allaitement_enfant2}}
                                        {{mb_value object=$grossesse_ant field=allaitement_enfant2}}
                                        <br/>
                                    {{/if}}
                                    {{mb_value object=$grossesse_ant field=allaitement_enfant2_desc}}
                                </td>
                                <td class="text">
                                    {{if $grossesse_ant->allaitement_enfant3}}
                                        {{mb_value object=$grossesse_ant field=allaitement_enfant3}}
                                        <br/>
                                    {{/if}}
                                    {{mb_value object=$grossesse_ant field=allaitement_enfant3_desc}}
                                </td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td
                              style="text-align: right;">{{mb_label class=CGrossesseAnt field=malformation_enfant1}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text">{{mb_value object=$grossesse_ant field=malformation_enfant1}}</td>
                                <td class="text">{{mb_value object=$grossesse_ant field=malformation_enfant2}}</td>
                                <td class="text">{{mb_value object=$grossesse_ant field=malformation_enfant3}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td
                              style="text-align: right;">{{mb_label class=CGrossesseAnt field=maladie_hered_enfant1}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text">{{mb_value object=$grossesse_ant field=maladie_hered_enfant1}}</td>
                                <td class="text">{{mb_value object=$grossesse_ant field=maladie_hered_enfant2}}</td>
                                <td class="text">{{mb_value object=$grossesse_ant field=maladie_hered_enfant3}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td
                              style="text-align: right;">{{mb_label class=CGrossesseAnt field=pathologie_enfant1}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text">{{mb_value object=$grossesse_ant field=pathologie_enfant1}}</td>
                                <td class="text">{{mb_value object=$grossesse_ant field=pathologie_enfant2}}</td>
                                <td class="text">{{mb_value object=$grossesse_ant field=pathologie_enfant3}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td
                              style="text-align: right;">{{mb_label class=CGrossesseAnt field=transf_mut_enfant1}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text">{{mb_value object=$grossesse_ant field=transf_mut_enfant1}}</td>
                                <td class="text">{{mb_value object=$grossesse_ant field=transf_mut_enfant2}}</td>
                                <td class="text">{{mb_value object=$grossesse_ant field=transf_mut_enfant3}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: right;">{{mb_label class=CGrossesseAnt field=deces_enfant1}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text">
                                    {{if $grossesse_ant->deces_enfant1}}
                                        {{mb_value object=$grossesse_ant field=deces_enfant1}}
                                    {{/if}}
                                </td>
                                <td class="text">
                                    {{if $grossesse_ant->deces_enfant2}}
                                        {{mb_value object=$grossesse_ant field=deces_enfant2}}
                                    {{/if}}
                                </td>
                                <td class="text">
                                    {{if $grossesse_ant->deces_enfant3}}
                                        {{mb_value object=$grossesse_ant field=deces_enfant3}}
                                    {{/if}}
                                </td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: right;">{{mb_label class=CGrossesseAnt field=age_deces_enfant1}}</td>
                            {{foreach from=$grossesses_ant item=grossesse_ant}}
                                <td class="text">{{mb_value object=$grossesse_ant field=age_deces_enfant1}}</td>
                                <td class="text">{{mb_value object=$grossesse_ant field=age_deces_enfant2}}</td>
                                <td class="text">{{mb_value object=$grossesse_ant field=age_deces_enfant3}}</td>
                            {{/foreach}}
                            <td></td>
                        </tr>
                    </table>
                </div>
                <div id="grossesses_synthese" class="me-padding-2">
                    <form name="Antecedents-obstetricaux-{{$dossier->_guid}}" method="post"
                          onsubmit="return onSubmitFormAjax(this);">
                        {{mb_class object=$dossier}}
                        {{mb_key   object=$dossier}}
                        <input type="hidden" name="_count_changes" value="0"/>
                        <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}"/>
                        <table class="main">
                            <tr>
                                <th colspan="4" class="title">
                                    {{tr}}CGrossesseAnt-synthesis{{/tr}}
                                </th>
                            </tr>
                            <tr>
                                <td class="halfPane">
                                    <table class="form me-no-align me-no-box-shadow me-small-form">
                                        <tr>
                                            <th colspan="3"
                                                class="category">{{tr}}CGrossesseAnt-synthesis-grossesses count{{/tr}}</th>
                                        </tr>
                                        <tr>
                                            <th class="category me-cell-medium-emphasis"
                                                rowspan="6">{{tr}}CGrossesseAnt-outcome is{{/tr}}</th>
                                            <th>{{mb_label object=$dossier field=ant_obst_nb_gr_acc}}</th>
                                            <td>{{mb_field object=$dossier field=ant_obst_nb_gr_acc}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$dossier field=ant_obst_nb_gr_av_sp}}</th>
                                            <td>{{mb_field object=$dossier field=ant_obst_nb_gr_av_sp}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$dossier field=ant_obst_nb_gr_ivg}}</th>
                                            <td>{{mb_field object=$dossier field=ant_obst_nb_gr_ivg}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$dossier field=ant_obst_nb_gr_geu}}</th>
                                            <td>{{mb_field object=$dossier field=ant_obst_nb_gr_geu}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$dossier field=ant_obst_nb_gr_mole}}</th>
                                            <td>{{mb_field object=$dossier field=ant_obst_nb_gr_mole}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$dossier field=ant_obst_nb_gr_img}}</th>
                                            <td>{{mb_field object=$dossier field=ant_obst_nb_gr_img}}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2"
                                                class="halfPane">{{mb_label object=$dossier field=ant_obst_nb_gr_amp}}</th>
                                            <td>{{mb_field object=$dossier field=ant_obst_nb_gr_amp}}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="2"
                                                class="halfPane">{{mb_label object=$dossier field=ant_obst_nb_gr_mult}}</th>
                                            <td>{{mb_field object=$dossier field=ant_obst_nb_gr_mult}}</td>
                                        </tr>
                                        <tr>
                                            <th class="category me-cell-medium-emphasis"
                                                rowspan="3">{{tr}}CGrossesseAnt-complicated to{{/tr}}</th>
                                            <th>{{mb_label object=$dossier field=ant_obst_nb_gr_hta}}</th>
                                            <td>{{mb_field object=$dossier field=ant_obst_nb_gr_hta}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$dossier field=ant_obst_nb_gr_map}}</th>
                                            <td>{{mb_field object=$dossier field=ant_obst_nb_gr_map}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$dossier field=ant_obst_nb_gr_diab}}</th>
                                            <td>{{mb_field object=$dossier field=ant_obst_nb_gr_diab}}</td>
                                        </tr>
                                        <tr>
                                            <th class="category me-cell-medium-emphasis"
                                                rowspan="2">{{tr}}CGrossesseAnt-ended by{{/tr}}</th>
                                            <th>{{mb_label object=$dossier field=ant_obst_nb_gr_cesar}}</th>
                                            <td>{{mb_field object=$dossier field=ant_obst_nb_gr_cesar}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$dossier field=ant_obst_nb_gr_prema}}</th>
                                            <td>{{mb_field object=$dossier field=ant_obst_nb_gr_prema}}</td>
                                        </tr>
                                    </table>
                                </td>
                                <td class="halfPane">
                                    <table class="form me-no-align me-no-box-shadow me-small-form">
                                        <tr>
                                            <th colspan="2"
                                                class="category">{{tr}}CGrossesseAnt-synthesis-children count{{/tr}}</th>
                                        </tr>
                                        <tr>
                                            <th
                                              class="halfPane">{{mb_label object=$dossier field=ant_obst_nb_enf_moins_25000}}</th>
                                            <td>{{mb_field object=$dossier field=ant_obst_nb_enf_moins_25000}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$dossier field=ant_obst_nb_enf_hypotroph}}</th>
                                            <td>{{mb_field object=$dossier field=ant_obst_nb_enf_hypotroph}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$dossier field=ant_obst_nb_enf_macrosome}}</th>
                                            <td>{{mb_field object=$dossier field=ant_obst_nb_enf_macrosome}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$dossier field=ant_obst_nb_enf_morts_nes}}</th>
                                            <td>{{mb_field object=$dossier field=ant_obst_nb_enf_morts_nes}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$dossier field=ant_obst_nb_enf_mort_neonat}}</th>
                                            <td>{{mb_field object=$dossier field=ant_obst_nb_enf_mort_neonat}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$dossier field=ant_obst_nb_enf_mort_postneonat}}</th>
                                            <td>{{mb_field object=$dossier field=ant_obst_nb_enf_mort_postneonat}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$dossier field=ant_obst_nb_enf_malform}}</th>
                                            <td>{{mb_field object=$dossier field=ant_obst_nb_enf_malform}}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </td>
        </tr>
    </table>
</div>
