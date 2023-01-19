{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=facturation script=facture ajax=true}}
{{mb_script module=dPpatients  script=pat_selector}}
{{if "dPfacturation CRelance use_relances"|gconf}}
    {{mb_script module=facturation script=relance ajax=true}}
{{/if}}
{{mb_default var=use_bill_ch value=0}}
{{mb_default var=function_limitation value=""}}

<script>
    Main.add(function () {
        Calendar.regField(getForm("choice-facture")._date_min);
        Calendar.regField(getForm("choice-facture")._date_max);
        Facture.addKeyUpListener();
        Facture.refreshList();
    });
</script>

<div id="factures">
    <form name="choice-facture" action="" method="get">
        <input type="hidden" name="facture_class" value="{{$facture->_class}}"/>
        <input type="hidden" name="page" value="{{$page}}" onchange="Facture.refreshList()"/>
        <table class="form" name="choix_type_facture">
            {{assign var="classe" value=$facture->_class}}
            <tr>
                {{me_form_field nb_cells=2 label=CPatient class="me-w33"}}
                {{mb_field object=$patient field="patient_id" hidden=1}}
                    <input type="text" name="_pat_name" style="width: 15em;" value="{{$patient->_view}}"
                           readonly="readonly"
                           ondblclick="PatSelector.init()"/>
                    <button class="cancel notext me-tertiary me-dark" type="button"
                            onclick="$V(this.form._pat_name,''); $V(this.form.patient_id,'')">
                        {{tr}}Empty{{/tr}}
                    </button>
                    <button class="search notext me-tertiary" type="button" onclick="PatSelector.init()">
                        {{tr}}Search{{/tr}}
                    </button>
                    <button class="edit notext me-tertiary" type="button" onclick="Facture.viewPatient();">
                        {{tr}}View{{/tr}}
                    </button>
                    <script>
                        PatSelector.init = function () {
                            this.sForm = "choice-facture";
                            this.sId = "patient_id";
                            this.sView = "_pat_name";
                            this.pop();
                        }
                    </script>
                {{/me_form_field}}
                {{me_form_field nb_cells=2 mb_object=$facture mb_field=statut_envoi class="me-w33"}}
                    <select name="xml_etat">
                        <option value="" {{if !$xml_etat}} selected="selected" {{/if}}>
                            -- {{tr}}common-all|f|pl{{/tr}}</option>
                        <option value="echec" {{if $xml_etat == "echec"}} selected="selected" {{/if}}>
                            {{tr}}CFactureEtablissement.facture.-1{{/tr}}</option>
                        <option value="non_envoye" {{if $xml_etat == "non_envoye"}} selected="selected" {{/if}}>
                            {{tr}}CFactureEtablissement.facture.0{{/tr}}</option>
                        <option value="envoye" {{if $xml_etat == "envoye"}} selected="selected" {{/if}}>
                            {{tr}}CFactureEtablissement.facture.1{{/tr}}</option>
                        </option>
                    </select>
                {{/me_form_field}}
                {{me_form_field nb_cells=2 label=CFactureCabinet-statut-invoice class="me-w33"}}
                    <select name="search_easy" multiple>
                        <option value="0" {{if $search_easy == "0"}} selected="selected" {{/if}}>
                            -- {{tr}}common-all|f|pl{{/tr}}</option>
                        {{if !"dPfacturation $classe use_auto_cloture"|gconf}}
                            <option value="2" {{if $search_easy == "2"}} selected="selected" {{/if}}>
                                {{tr}}CFactureCabinet-facture.cloture|f{{/tr}}
                            </option>
                            <option value="3" {{if $search_easy == "3"}} selected="selected" {{/if}}
                              {{if $type_date_search == "cloture"}}disabled="disabled" {{/if}}>
                                {{tr}}CFactureCabinet-facture.no-cloture|f{{/tr}}
                            </option>
                        {{/if}}
                        <option value="4" {{if $search_easy == "4"}} selected="selected" {{/if}}>
                            {{tr}}CFactureCabinet-facture.no-cote|f{{/tr}}
                        </option>
                        <option value="5" {{if $search_easy == "5"}} selected="selected" {{/if}}>
                            {{tr}}CFactureCabinet-facture.extourne|f{{/tr}}
                        </option>
                        <option value="6" {{if $search_easy == "6"}} selected="selected" {{/if}}>
                            {{tr}}CFactureCabinet-facture.regle|f{{/tr}}
                        </option>
                        {{if "dPfacturation CRelance use_relances"|gconf}}
                            <option value="7" {{if $search_easy == "7"}} selected="selected" {{/if}}>
                                {{tr}}CFactureCabinet-facture.relance|f{{/tr}}
                            </option>
                        {{/if}}
                        <option value="8" {{if $search_easy == "8"}} selected="selected" {{/if}}>
                            {{tr}}CFactureCabinet-facture.rejete|f{{/tr}}
                        </option>
                        <option value="9" {{if $search_easy == "9"}} selected="selected" {{/if}}>
                            {{tr}}CFactureCabinet-facture.no-regle|f{{/tr}}
                        </option>
                    </select>
                {{/me_form_field}}
            </tr>
            <tr>
                {{me_form_field nb_cells=2 label="common-Quick search..."}}
                    <input type="text" name="_seek_patient" style="width: 13em;"/>
                    <script>
                        Main.add(function () {
                            var form = getForm("choice-facture");
                            new Url("facturation", "patient_autocomplete")
                              .autoComplete(
                                form.elements._seek_patient,
                                null,
                                {
                                    minChars:           3,
                                    method:             "get",
                                    select:             "view",
                                    dropdown:           false,
                                    width:              "300px",
                                    afterUpdateElement: function (field, selected) {
                                        $V(field.form.patient_id, selected.getAttribute("id").split("-")[2]);
                                        $V(field.form.elements._pat_name, selected.down('.view').innerHTML);
                                        $V(field.form.elements._seek_patient, "");
                                    }
                                }
                              );
                        });
                    </script>
                {{/me_form_field}}
                {{me_form_field nb_cells=2 mb_object=$facture mb_field=numero}}
                    <select name="numero">
                        <option value="0" {{if $numero == "0"}} selected="selected" {{/if}}>
                            -- {{tr}}common-all|f|pl{{/tr}}</option>
                        <option value="1" {{if $numero == "1"}} selected="selected" {{/if}}>1</option>
                        <option value="2" {{if $numero == "2"}} selected="selected" {{/if}}>2</option>
                        <option value="3" {{if $numero == "3"}} selected="selected" {{/if}}>3</option>
                        </option>
                    </select>
                {{/me_form_field}}
                {{me_form_field nb_cells=2 mb_object=$filter mb_field=_date_min}}
                {{mb_field object=$filter field="_date_min" form="choice-facture" canNull="false" register=true}}
                {{/me_form_field}}
            </tr>
            <tr>
                {{me_form_field nb_cells=2 label=CMediusers-praticien layout=true field_class="me-padding-0 me-no-border" animated=false}}
                    <input type="hidden" name="chirSel" value="{{$chirSel}}"/>
                    <select name="activeChirSel" style="width: 15em;" onchange="$V(this.form.chirSel, $V(this));">
                        <option value="0" {{if !$chirSel}} selected="selected" {{/if}}>
                            &mdash; {{tr}}CMediusers-select-professionnel{{/tr}}</option>
                        {{if $facture->_class == "CFactureEtablissement"}}
                            <option value="-1" {{if $chirSel == "-1"}} selected="selected" {{/if}}>
                                <b>&mdash; {{tr}}All{{/tr}}</b></option>
                        {{/if}}
                        {{mb_include module=mediusers template=inc_options_mediuser selected=$chirSel list=$listChirs}}
                    </select>
                    <select name="allChirSel" style="width: 15em; display: none;"
                            onchange="$V(this.form.chirSel, $V(this));">
                        <option value="0" {{if !$chirSel}} selected="selected" {{/if}}>
                            &mdash; {{tr}}CMediusers-select-professionnel{{/tr}}</option>
                        {{if $facture->_class == "CFactureEtablissement"}}
                            <option value="-1" {{if $chirSel == "-1"}} selected="selected" {{/if}}>
                                <b>&mdash; {{tr}}All{{/tr}}</b></option>
                        {{/if}}
                        {{mb_include module=mediusers template=inc_options_mediuser selected=$chirSel list=$listAllChirs}}
                    </select>
                    <label>
                        <input type="checkbox" onclick="Facture.togglePratSelector(this.form);"/>
                        {{tr}}common-User disabled{{/tr}}
                    </label>
                {{/me_form_field}}
                <td colspan="2"></td>
                {{me_form_field nb_cells=2 mb_object=$filter mb_field=_date_max}}
                {{mb_field object=$filter field="_date_max" form="choice-facture" canNull="false" register=true}}
                {{/me_form_field}}
            </tr>

            <tr>
                {{me_form_field nb_cells=2 label=CFactureCabinet-invoice-number}}
                    <input name="num_facture" value="{{$num_facture}}" type="text"/>
                {{/me_form_field}}
                {{me_form_field nb_cells=2 mb_object=$facture mb_field=montant_total}}
                {{mb_field object=$facture field=montant_total}}
                {{/me_form_field}}
                {{if !"dPfacturation $classe use_auto_cloture"|gconf}}
                    {{me_form_field nb_cells=2 label=CFactureEtablissement-date-of}}
                        <select name="type_date_search" onchange="Facture.updateEtatSearch();">
                            <option value="cloture" {{if $type_date_search == "cloture"}} selected="selected" {{/if}}>
                                {{tr}}CFactureEtablissement-etat.cloture{{/tr}}
                            </option>
                            <option
                              value="ouverture" {{if $type_date_search == "ouverture"}} selected="selected" {{/if}}>
                                {{tr}}CFactureEtablissement-etat.ouverture{{/tr}}
                            </option>
                        </select>
                    {{/me_form_field}}
                {{else}}
                    <th></th>
                    <td><input type="hidden" name="type_date_search" value="ouverture"/></td>
                {{/if}}
            </tr>
            <tr>
                <td class="button" colspan="6">
                    <button type="button" onclick="$V(this.form.page, 0);Facture.refreshList();"
                            class="search me-primary">
                        {{tr}}Filter{{/tr}}
                    </button>
                    <button type="button" onclick="Facture.showLegend('{{$facture->_class}}');"
                            class="search me-tertiary" style="float:right;">
                        {{tr}}CFactureCabinet-action-legend{{/tr}}
                    </button>
                </td>
            </tr>
        </table>
    </form>
    {{mb_include module=facturation template=vw_list_factures}}
</div>
