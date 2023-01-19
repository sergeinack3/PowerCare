{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=reglement}}
{{mb_script module=facturation script=rapport}}
{{mb_script module=facturation script=facture ajax=true}}

{{if "dPfacturation CRelance use_relances"|gconf && $all_impayes}}
    {{mb_script module=facturation script=relance}}
{{/if}}

{{if !$ajax}}
    <div style="float: right;">
        {{mb_include module=facturation template=inc_totaux_rapport}}
    </div>
    <div>
        <strong>
            <button type="button" class="notext print not-printable"
                    onclick="window.print();">{{tr}}Print{{/tr}}</button>
            {{tr}}Report{{/tr}}
            {{mb_include module=system template=inc_interval_date from=$filter->_date_min to=$filter->_date_max}}
        </strong>
    </div>
    <div>
        {{tr}}CReglement-considered{{/tr}} :
        {{if $filter->_mode_reglement}}{{$filter->_mode_reglement}}{{else}}tous{{/if}}
    </div>
    {{if $filter->_etat_reglement_patient}}
        <div>
            {{tr}}CReglement-patient|pl{{/tr}}:
            {{tr}}CConsultation._etat_reglement_tiers.{{$filter->_etat_reglement_patient}}{{/tr}}
        </div>
    {{/if}}

    {{if $filter->_etat_reglement_tiers}}
        <div>
            {{tr}}CReglement-tier|pl{{/tr}}:
            {{tr}}CConsultation._etat_reglement_tiers.{{$filter->_etat_reglement_tiers}}{{/tr}}
        </div>
    {{/if}}

    <!-- Praticiens concernés -->
    {{foreach from=$listPrat item=_prat}}
        <div>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_prat show_adeli=1}}</div>
    {{/foreach}}

{{/if}}

{{if $filter->_type_affichage}}
<table class="main">
    {{foreach from=$listPlages item=_plage}}
        {{if !$ajax}}
            <tbody id="{{$_plage.plage->_guid}}">
        {{/if}}
        <tr>
            <td colspan="2">
                <br/>
                <br/>
                <strong>
                    {{$_plage.plage->_ref_chir}}
                    {{if $_plage.plage->remplacant_id}}
                        {{tr}}CConsultation.replaced_by{{/tr}} {{$_plage.plage->_ref_remplacant->_view}}
                    {{elseif $_plage.plage->_ref_pour_compte->_id}}
                        {{tr}}CPlageConsult-pour_compte_of{{/tr}} {{$_plage.plage->_ref_pour_compte->_view}}
                    {{/if}}

                    &mdash; {{$_plage.plage->date|date_format:$conf.longdate}}
                    {{tr}}from{{/tr}} {{$_plage.plage->debut|date_format:$conf.time}}
                    {{tr}}to{{/tr}}  {{$_plage.plage->fin|date_format:$conf.time}}

                    {{if $_plage.plage->libelle}}
                        : {{mb_value object=$_plage.plage field=libelle}}
                    {{/if}}
                </strong>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table class="tbl">
                    <tr>
                        <th colspan="2" class="narrow text">{{tr}}CFactureCabinet{{/tr}}</th>
                        <th style="width: 20%;">{{mb_label class=CConsultation field=patient_id}}</th>
                        {{if $filter->_etat_accident_travail != 'no'}}
                            <th style="width: 20%;">{{tr}}CConsultation-AT-desc{{/tr}}</th>
                        {{/if}}
                        <th style="width: 20%;">{{mb_label class=CConsultation field=tarif}}</th>

                        {{if $type_aff}}
                            <th class="narrow">{{mb_title class=CConsultation field=secteur1}}</th>
                            <th class="narrow">{{mb_title class=CConsultation field=secteur2}}</th>
                            <th class="narrow">{{mb_title class=CConsultation field=secteur3}}</th>
                            <th class="narrow">{{mb_title class=CConsultation field=du_tva}}</th>
                            <th class="narrow">{{mb_title class=CConsultation field=_somme}}</th>
                            <th style="width: 20%;">{{mb_title class=CConsultation field=du_patient}}</th>
                            <th style="width: 20%;">{{mb_title class=CConsultation field=du_tiers}}</th>
                        {{else}}
                            <th class="narrow">{{tr}}CFacture-montant{{/tr}}</th>
                            <th class="narrow">{{mb_label class=CFactureCabinet field=remise}}</th>
                            <th class="narrow">{{mb_title class=CConsultation field=_somme}}</th>
                            <th style="width: 20%;">{{mb_title class=CConsultation field=du_patient}}</th>
                        {{/if}}

                        <th>{{mb_title class=CFactureCabinet field=patient_date_reglement}}</th>
                        {{if "dPfacturation CRelance use_relances"|gconf && $all_impayes}}
                            <th>{{tr}}CRelance{{/tr}}</th>
                        {{/if}}
                        <th>{{tr}}Cancel{{/tr}}</th>
                    </tr>

                    {{foreach from=$_plage.factures item=_facture}}
                        <tr id="line_facture_{{$_facture->_guid}}">
                            {{if $_facture->_id}}
                                <td>
                                    <strong onmouseover="ObjectTooltip.createEx(this, '{{$_facture->_guid}}')">
                                        {{$_facture->_view}}
                                        {{if $_facture->_current_fse}}({{tr}}CConsultation-back-fses{{/tr}}: n° {{$_facture->_current_fse_number}}){{/if}}
                                    </strong>
                                    {{if $_facture->group_id != $g}}
                                        <span class="compact"><br/>({{$_facture->_ref_group}})</span>
                                    {{/if}}
                                </td>
                                <td>{{mb_include module=system template=inc_object_notes object=$_facture}}</td>
                            {{else}}
                                <td colspan="2">
                                    <strong>{{$_facture}}</strong>
                                </td>
                            {{/if}}

                            <td class="text">
                                <a name="{{$_facture->_guid}}">
                                    {{assign var=patient value=$_facture->_ref_patient}}
                                    {{mb_include module=system template=inc_vw_mbobject object=$patient}}
                                </a>
                            </td>
                            {{if $filter->_etat_accident_travail != 'no'}}
                                <td>{{mb_value object=$_facture->_ref_last_consult field=date_at}}</td>
                            {{/if}}
                            <td class="text">
                                {{foreach from=$_facture->_ref_consults item=_consult}}
                                    <div {{if !$_consult->tarif}} class="empty" {{/if}}>
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_consult->_guid}}')">
                {{mb_value object=$_consult field=_date}}: {{mb_value object=$_consult field=tarif default=None}}
              </span>
                                    </div>
                                    {{foreachelse}}
                                    <div class="empty">{{tr}}CConsultation.none{{/tr}}</div>
                                {{/foreach}}
                            </td>

                            <td>{{mb_value object=$_facture field=_secteur1 empty=1}}</td>
                            {{if $type_aff}}
                                <td>{{mb_value object=$_facture field=_secteur2 empty=1}}</td>
                                <td>{{mb_value object=$_facture field=_secteur3 empty=1}}</td>
                                <td>{{mb_value object=$_facture field=du_tva empty=1}}</td>
                            {{else}}
                                <td>{{mb_value object=$_facture field=remise empty=1}}</td>
                            {{/if}}
                            <td>
                                {{mb_value object=$_facture field=_montant_avec_remise empty=1}}
                            </td>

                            <td>
                                <table class="layout">
                                    {{foreach from=$_facture->_ref_reglements_patient item=_reglement}}
                                        <tr>
                                            <td class="narrow">
                                                <button class="edit notext" type="button"
                                                        onclick="Rapport.editReglement('{{$_reglement->_id}}', '{{$_reglement->date}}', '{{$_facture->_guid}}', '{{$_plage.plage->_guid}}');">
                                                    {{tr}}Edit{{/tr}}
                                                </button>
                                            </td>
                                            <td class="narrow" style="text-align: right;">
                                                <strong>{{mb_value object=$_reglement field=montant}}</strong></td>
                                            <td>
                                                {{mb_value object=$_reglement field=mode}}
                                                {{if $_reglement->reference}}({{mb_value object=$_reglement field=reference}}){{/if}}
                                            </td>
                                            <td
                                              class="narrow">{{mb_value object=$_reglement field=date date=$_plage.plage->date}}</td>
                                        </tr>
                                    {{/foreach}}

                                    {{if abs($_facture->_du_restant_patient) > 0.01 || ("dPfacturation CReglement use_lock_acquittement"|gconf && $_facture->du_patient)}}
                                        <tr>
                                            <td colspan="3" class="button">
                                                {{assign var=new_reglement value=$_facture->_new_reglement_patient}}
                                                <button class="add" type="button"
                                                        onclick="Rapport.addReglement('{{$_facture->_guid}}', '{{$new_reglement.emetteur}}', '{{$new_reglement.montant}}', '{{$new_reglement.mode}}', null, '{{$_plage.plage->_guid}}');">
                                                    {{if abs($_facture->_du_restant_patient) > 0.01}}
                                                        {{tr}}Add{{/tr}}
                                                        <strong>{{$new_reglement.montant}}</strong>
                                                    {{else}}
                                                        {{tr}}CReglement-title-create{{/tr}}
                                                    {{/if}}
                                                </button>
                                            </td>
                                        </tr>
                                    {{/if}}
                                </table>
                            </td>
                            {{if $type_aff}}
                                <td>
                                    <table class="layout">
                                        {{foreach from=$_facture->_ref_reglements_tiers item=_reglement}}
                                            <tr>
                                                <td class="narrow">
                                                    <button class="edit notext" type="button"
                                                            onclick="Rapport.editReglement('{{$_reglement->_id}}', '{{$_reglement->date}}', '{{$_facture->_guid}}', '{{$_plage.plage->_guid}}');">
                                                        {{tr}}Edit{{/tr}}
                                                    </button>
                                                </td>
                                                <td class="narrow" style="text-align: right;">
                                                    <strong>{{mb_value object=$_reglement field=montant}}</strong></td>
                                                <td>
                                                    {{mb_value object=$_reglement field=mode}}
                                                    {{if $_reglement->emetteur == 'tiers' && $_reglement->tireur}}
                                                        ({{mb_value object=$_reglement field=tireur}})
                                                    {{/if}}
                                                </td>
                                                <td
                                                  class="narrow">{{mb_value object=$_reglement field=date date=$_plage.plage->date}}</td>
                                            </tr>
                                        {{/foreach}}

                                        {{if abs($_facture->_du_restant_tiers) > 0.01 || ("dPfacturation CReglement use_lock_acquittement"|gconf && $_facture->du_tiers)}}
                                            <tr>
                                                <td colspan="4" class="button">
                                                    {{assign var=new_reglement value=$_facture->_new_reglement_tiers}}
                                                    <button class="add" type="button"
                                                            onclick="Rapport.addReglement('{{$_facture->_guid}}', '{{$new_reglement.emetteur}}', '{{$new_reglement.montant}}', '{{$new_reglement.mode}}', null, '{{$_plage.plage->_guid}}');">
                                                        {{if abs($_facture->_du_restant_tiers) > 0.01}}
                                                            {{tr}}Add{{/tr}}
                                                            <strong>{{$new_reglement.montant}}</strong>
                                                        {{else}}
                                                            {{tr}}CReglement-title-create{{/tr}}
                                                        {{/if}}
                                                    </button>
                                                </td>
                                            </tr>
                                        {{/if}}
                                    </table>
                                </td>
                            {{/if}}
                            <td>
                                <form name="edit-date-aquittement-{{$_facture->_guid}}" action="#" method="post">
                                    {{mb_class object=$_facture}}
                                    {{mb_key object=$_facture}}
                                    {{mb_field object=$_facture field=patient_date_reglement form="edit-date-aquittement-`$_facture->_guid`" register=true
                                    onchange="onSubmitFormAjax(this.form);"}}
                                </form>
                            </td>
                            {{if "dPfacturation CRelance use_relances"|gconf && $all_impayes}}
                                <td class="button">
                                    {{if $_facture->_ref_relances|@count}}
                                        <button type="button" class="pdf notext"
                                                onclick="Relance.printRelance('{{$_facture->_class}}', '{{$_facture->_id}}', 'relance', '{{$_facture->_ref_last_relance->_id}}');">
                                            {{tr}}See_pdf.relance{{/tr}}
                                        </button>
                                    {{elseif $_facture->_is_relancable}}
                                        <form name="relance_{{$_facture->_guid}}" method="post" action=""
                                              onsubmit="return onSubmitFormAjax(this, {onComplete : function() {location.reload();}});">
                                            {{mb_class object=$_facture->_ref_last_relance}}
                                            <input type="hidden" name="relance_id" value=""/>
                                            <input type="hidden" name="object_id" value="{{$_facture->_id}}"/>
                                            <input type="hidden" name="object_class" value="{{$_facture->_class}}"/>
                                            <input type="hidden" name="callback" value="Relance.pdf"/>
                                            <button class="add notext"
                                                    type="submit">{{tr}}CFacture-action-create-relance{{/tr}}</button>
                                        </form>
                                    {{/if}}
                                </td>
                            {{/if}}
                            {{if !$_facture->annule && $_facture->_reglements_total == 0 && !$_facture->_ref_echeances|@count}}
                                <td>
                                    <form name="facture_extourne" method="post" action="?">
                                        {{mb_class object=$_facture}}
                                        {{mb_key   object=$_facture}}
                                        <input type="hidden" name="facture_class" value="{{$_facture->_class}}"/>
                                        <input type="hidden" name="_duplicate" value="0"/>
                                        <input type="hidden" name="annule" value="1"/>
                                        <button type="button" class="cancel" onclick="Facture.annule(this.form)">
                                            {{tr}}Cancel{{/tr}}
                                        </button>
                                    </form>
                                </td>
                            {{else}}
                                <td>-</td>
                            {{/if}}
                        </tr>
                    {{/foreach}}
                    <tr id="{{$_plage.plage->_guid}}_total">
                        <td colspan="{{if $filter->_etat_accident_travail != 'no'}}5{{else}}4{{/if}}"
                            style="text-align: right">
                            <strong>{{tr}}Total{{/tr}}</strong>
                        </td>
                        <td><strong>{{$_plage.total.secteur1|currency}}</strong></td>
                        <td><strong>{{$_plage.total.secteur2|currency}}</strong></td>
                        {{if $type_aff}}
                            <td><strong>{{$_plage.total.secteur3|currency}}</strong></td>
                            <td><strong>{{$_plage.total.du_tva|currency}}</strong></td>
                        {{/if}}
                        <td><strong>{{$_plage.total.total|currency}}</strong></td>
                        <td><strong>{{$_plage.total.patient|currency}}</strong></td>
                        {{if $type_aff}}
                            <td><strong>{{$_plage.total.tiers|currency}}</strong></td>
                        {{/if}}
                        <td {{if "dPfacturation CRelance use_relances"|gconf && $all_impayes}}colspan="2"{{/if}}></td>
                    </tr>
                </table>
            </td>
        </tr>
        {{if !$ajax}}
            </tbody>
        {{/if}}
    {{/foreach}}
    {{/if}}

    {{if !$ajax}}
</table>
{{/if}}
