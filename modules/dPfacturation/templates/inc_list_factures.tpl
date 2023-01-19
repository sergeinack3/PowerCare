{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=total_factures value=24}}
{{mb_default var=type_relance value=0}}
{{mb_default var=print value=0}}
{{mb_default var=change_page value='Facture.changePage'}}
{{mb_default var=type_relance_number_word value=""}}
{{mb_default var=step value=25}}

<script>
    {{if $print}}
    Main.add(function () {
        window.print();
    });
    {{/if}}
</script>

{{assign var=use_bill_ch value=0}}
<table class="tbl">
    {{if $total_factures > $step && !$print}}
        {{mb_include module=system template=inc_pagination total=$total_factures current=$page step=$step change_page=$change_page}}
    {{/if}}
    <tr>
        <th colspan="15" class="title">
            {{if $type_relance_number_word}}
                {{tr}}{{$facture->_class}}.to_relance{{/tr}} : {{tr}}CRelance.statut.{{$type_relance_number_word}}{{/tr}}
            {{else}}
                {{tr}}CFacture|pl{{/tr}} {{if $print}}({{$factures|@count}}){{/if}}
                {{if !$type_relance}}
                    {{if !$print}}
                        <button type="button" class="print me-tertiary" style="float: right;"
                                onclick="Facture.refreshList(1);">{{tr}}Print{{/tr}}</button>
                    {{/if}}
                    <a class="button download me-tertiary" href="?m=facturation&raw=ajax_list_factures&export_csv=1"
                       target="_blank" style="float: right;">
                        {{tr}}Export-CSV{{/tr}}
                    </a>
                {{/if}}
            {{/if}}
        </th>
    </tr>
    <tr>
        {{if !$print}}
            <th class="narrow"></th>
        {{/if}}
        <th class="narrow">{{tr}}CFacture-date{{/tr}}</th>
        <th class="narrow">{{mb_title object=$facture field=numero}}</th>
        <th>{{mb_title class=CPatient field=nom}}</th>
        <th>{{mb_title class=CPatient field=prenom}}</th>
        {{if $use_bill_ch}}
            <th>{{mb_title object=$facture field=_type_rbt}}</th>
            <th>
                <input type="text" size="3" onkeyup="Facture.filterFullName(this);" id="filter-patient-name"
                       style="float: right;"/>
                {{tr}}CFacture-_debiteur{{/tr}}
            </th>
            <th>{{mb_title object=$facture field=type_facture}}</th>
        {{/if}}

        {{if $facture->_class == "CFactureEtablissement"}}
            <th>{{tr}}CSejour-date{{/tr}}</th>
        {{else}}
            <th>{{tr}}CConsultation-date{{/tr}}</th>
        {{/if}}

        <th>{{tr}}CFactureCabinet-amount-invoice{{/tr}}</th>
        <th>{{tr}}CFactureCabinet-amount-paid{{/tr}}</th>
        <th>{{tr}}CFactureCabinet-amount-unpaid{{/tr}}</th>
        {{if $use_bill_ch}}
            <th>{{tr}}CFactureCabinet-send-xml-or-paper{{/tr}}</th>
        {{/if}}

        <th class="narrow" {{if $use_bill_ch}}colspan="2"{{/if}}>{{mb_title object=$facture field=_statut}}</th>
    </tr>
    {{foreach from=$factures item=_facture}}

        {{assign var=line_class value=$_facture->_main_statut}}
        {{if $_facture->_main_statut === "hatching" || $_facture->_main_statut === "extournee"}}
            {{assign var=line_class value="hatching"}}
        {{/if}}
        {{assign var=rowspan value=1}}
        {{if $use_bill_ch && $_facture->statut_envoi !== 'envoye' && $_facture->cloture}}
            {{assign var=rowspan value=2}}
        {{/if}}
        <tr>
        {{if !$print}}
            <td class="narrow {{$line_class}}" rowspan="{{$rowspan}}">
                <button type="button" class="edit notext"
                        onclick="Facture.edit('{{$_facture->facture_id}}', '{{$_facture->_class}}');">
                    {{tr}}CFacture.see{{/tr}}
                </button>
            </td>
        {{/if}}
        <td class="narrow {{$line_class}}" rowspan="{{$rowspan}}">
            {{if $_facture->cloture}}
                {{mb_value object=$_facture field=cloture}}
            {{else}}
                {{mb_value object=$_facture field=ouverture}}
            {{/if}}
        </td>
        <td class="narrow {{$line_class}}" rowspan="{{$rowspan}}">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_facture->_guid}}')">
          {{$_facture->_view}}
            {{if $_facture->_current_fse}}({{tr}}CConsultation-back-fses{{/tr}}: n° {{$_facture->_current_fse_number}}){{/if}}
        </span>
        </td>
        <td class="text {{$line_class}}" rowspan="{{$rowspan}}">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_facture->_ref_patient->_guid}}')">
          {{$_facture->_ref_patient->nom}}
        </span>
        </td>
        <td class="text {{$line_class}}" rowspan="{{$rowspan}}">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_facture->_ref_patient->_guid}}')">
          {{$_facture->_ref_patient->prenom}}
        </span>
        </td>
        {{if $use_bill_ch}}
            <td class="{{$line_class}}" rowspan="{{$rowspan}}">{{$_facture->_type_rbt}}</td>
            <td class="{{$line_class}} _assurance_patient_view"
                rowspan="{{$rowspan}}">{{$_facture->_assurance_patient_view}}</td>
            <td class="{{$line_class}}" rowspan="{{$rowspan}}">
                {{if $_facture->statut_pro == "enceinte"}}
                    {{tr}}CSejour-_grossesse{{/tr}}
                {{else}}
                    {{mb_value object=$_facture field=type_facture}}
                {{/if}}
            </td>
        {{/if}}
        <td class="{{$line_class}}" rowspan="{{$rowspan}}">
            {{if $facture->_class == "CFactureEtablissement"}}
                <span onmouseover="ObjectTooltip.createEx(this, '{{$_facture->_ref_last_sejour->_guid}}')">
            {{$_facture->_ref_last_sejour->entree_prevue|date_format:$conf.date}}
          </span>
            {{else}}
                <span onmouseover="ObjectTooltip.createEx(this, '{{$_facture->_ref_last_consult->_guid}}')">
            {{$_facture->_ref_last_consult->_date|date_format:$conf.date}}
          </span>
            {{/if}}
        </td>
        <td class="{{$line_class}}" rowspan="{{$rowspan}}">{{mb_value object=$_facture field=_montant_avec_remise}}</td>
        <td class="{{$line_class}}" rowspan="{{$rowspan}}">{{mb_value object=$_facture field=_reglements_total}}</td>
        <td class="{{$line_class}}" rowspan="{{$rowspan}}">{{mb_value object=$_facture field=_du_restant}}</td>
        {{if $use_bill_ch}}
            <td class="{{$line_class}}" rowspan="{{$rowspan}}">
                {{if $_facture->request_date}}
                    {{mb_value object=$_facture field=request_date}}
                {{elseif $_facture->statut_envoi === 'envoye'}}
                    {{tr}}Yes{{/tr}}
                {{/if}}
            </td>
        {{/if}}
        <td class="{{$line_class}}" {{if $rowspan == 2}}rowspan="{{$rowspan}}" {{else}}colspan="2"
            {{/if}}rowspan="{{$rowspan}}">
            {{mb_value object=$_facture field=_statut_view}}
        </td>
        {{if $rowspan == 2}}
            <td class="{{$line_class}}"
                style="color:{{if $_facture->bill_date_printed}}green{{else}}maroon{{/if}}; cursor: help;">
                {{mb_include module=facturation template=inc_printed_bill
                facture=$_facture field=bill_date_printed deny_callback="Facture.refreshList"}}
            </td>
            </tr>
            <tr>
                <td class="{{$line_class}}"
                    style="color:{{if $_facture->justif_date_printed}}green{{else}}maroon{{/if}}; cursor: help;">
                    {{mb_include module=facturation template=inc_printed_bill
                    facture=$_facture field=justif_date_printed deny_callback="Facture.refreshList"}}
                </td>
            </tr>
        {{/if}}
        </tr>
        {{foreachelse}}
        <tr>
            <td colspan="15" class="empty">
                {{tr}}{{$facture->_class}}.none{{/tr}}
            </td>
        </tr>
    {{/foreach}}
</table>
