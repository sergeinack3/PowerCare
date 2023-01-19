{{*
* @package Mediboard\Facturation
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<html>
<body>
<style type="text/css">
    {{$style|smarty:nodefaults}}
    @media print {
        div.body {
            height: {{$body_height}}px;
        }

        th.title {
            font-size: 0.8em;
        }

        th.text {
            font-size: 0.8em;
        }

        td {
            font-size: 0.8em;
        }

        div.header {
            height: {{$header_height}}px;
        }

        div.footer {
            height: {{$footer_height}}px;
        }
    }

    /* Form tables */
    table.form {
        width: 100%;
        border-spacing: 1px;
    }

    table.form th {
        color: #000;
        white-space: nowrap;
        background-color: #ffffff;
        vertical-align: middle;
        text-align: right;
        font-weight: normal;
        padding: 2px 3px;
        border: none;
    }

    table.form th.title {
        color: #fff;
        font-size: 1.2em;
        font-weight: bold;
        text-align: center;
        background-color: #888;
    }

    table.form th.category {
        font-weight: bold;
        text-align: center;
        background-color: #bbb;
        vertical-align: middle;
    }

    table.form th.section,
    table.tbl th.section {
        text-align: center;
        font-size: 0.9em;
        font-weight: normal;
        line-height: 100%;
        background-color: #ffffff;
        text-transform: uppercase;
    }

    table.form th.category label {
        font-weight: bold;
        text-align: center;
        background-color: transparent;
        vertical-align: middle;
    }

    table.form th.text {
        text-align: center;
    }

    table.form td {
        white-space: nowrap;
        vertical-align: top;
        background-color: #ffffff;
        text-align: left;
        padding: 1px 3px;
    }
</style>

<div class="header">
    {{if $header}}
        {{$header|smarty:nodefaults}}
    {{else}}
        <table class="form">
            <tr>
                <th class="category">{{tr}}CFacture{{/tr}}</th>
                <th style="text-align: left;">{{$facture->_view}}</th>
                <th>{{tr}}CFacture-create{{/tr}}</th>
                <th style="text-align: right;">{{mb_value object=$facture field=ouverture}}</th>
            </tr>
            <tr>
                <th class="category">{{tr}}CFacture-praticien_id{{/tr}}</th>
                <th style="text-align: left;">{{$facture->_ref_praticien}}</th>
                {{if $facture->_ref_praticien->rpps}}
                    <th>{{mb_title object=$facture->_ref_praticien field=rpps}}</th>
                    <th style="text-align: right;">{{mb_value object=$facture->_ref_praticien field=rpps}}</th>
                {{elseif $facture->_ref_praticien->adeli}}
                    <th>{{mb_title object=$facture->_ref_praticien field=adeli}}</th>
                    <th style="text-align: right;">{{mb_value object=$facture->_ref_praticien field=adeli}}</th>
                {{else}}
                    <th colspan="2"></th>
                {{/if}}
            </tr>
            <tr>
                <th class="category">{{tr}}CPatient{{/tr}}</th>
                <th style="white-space: normal !important; text-align: left; text-transform: capitalize;">{{$facture->_ref_patient}} <br /> {{mb_value object=$facture->_ref_patient field=tel2}}</th>
                <th>{{tr}}CPatient-_p_birth_date{{/tr}}</th>
                <th style="text-align: right;">{{mb_value object=$facture->_ref_patient field=naissance}}</th>
            </tr>
        </table>
    {{/if}}
</div>
<div class="body">
    <table style="width: 100%;" class="form">
        <tr>
            <th class="category" colspan="6">{{tr}}compta-print_facture-title{{/tr}}</th>
        </tr>
        <tr>
            <th class="category narrow">{{tr}}Date{{/tr}}</th>
            <th class="category narrow">{{tr}}CFacture-code{{/tr}}</th>
            <th class="category">{{tr}}CFacture-libelle{{/tr}}</th>
            <th class="category narrow">{{tr}}CFacture-base{{/tr}}</th>
            <th class="category narrow">{{tr}}CFacture-dh{{/tr}}</th>
            <th class="category narrow">{{tr}}CFacture-montant{{/tr}}</th>
        </tr>
        {{foreach from=$facture->_ref_items item=item}}
            <tr>
                <td style="text-align: center;">{{mb_value object=$item field="date"}}</td>
                <td style="text-align: center;">{{$item->code}}</td>
                <td style="text-align: center;">
                    {{$item->libelle|lower|wordwrap:60:"\n"|nl2br}}
                </td>
                <td style="text-align: right;">{{$item->montant_base|string_format:"%0.2f"}}</td>
                <td style="text-align: right;">{{$item->montant_depassement|string_format:"%0.2f"}}</td>
                <td style="text-align: right;">{{$item->_montant_facture|string_format:"%0.2f"|currency}}</td>
            </tr>
        {{/foreach}}
        <tr>
            <td colspan="3"></td>
            <td colspan="2">{{mb_label object=$facture field=du_patient}}</td>
            <td style="text-align: right;">{{mb_value object=$facture field=du_patient}}</td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td colspan="2">{{mb_label object=$facture field=du_tiers}}</td>
            <td style="text-align: right;">{{mb_value object=$facture field=du_tiers}}</td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td colspan="2"><i>{{tr}}CFacture-whose_tva{{/tr}} ({{$facture->taux_tva}}%)</i></td>
            <td style="text-align: right;"><i>{{mb_value object=$facture field=du_tva}}</i></td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td colspan="2"><b>{{tr}}CFactureCabinet-montant_total{{/tr}}</b></td>
            <td style="text-align: right;">{{mb_value object=$facture field=_montant_avec_remise}}</td>
        </tr>
    </table>
    <table style="width: 100%;top: {{math equation="x-50" x=$body_height}}px;position: absolute;" class="form">
        <tr>
            <th>{{tr}}CFacture-nb_reglements_patient{{/tr}}</th>
            <td style="text-align: right;">{{$facture->_ref_reglements_patient|@count}}</td>
        </tr>
        <tr>
            <th>{{tr}}CFacture-total_reglements_patient{{/tr}}</th>
            <td style="text-align: right;">{{mb_value object=$facture field=_reglements_total_patient}}</td>
        </tr>
        <tr>
            <th>{{tr}}CFacture-remain_pay{{/tr}}</th>
            <th><strong>{{mb_value object=$facture field=_du_restant_patient}}</strong></th>
        </tr>
    </table>
</div>

<div class="footer" style="position: absolute; bottom: 0;">
    {{if $footer}}
        {{$footer|smarty:nodefaults}}
    {{else}}
        <table class="form">
            <tr>
                <td>{{tr}}CFacture-pay_correctly{{/tr}} ({{mb_value object=$facture field=du_patient}}).</td>
            </tr>
            <tr>
                <td>{{tr}}CFacture-validity_during{{/tr}}</td>
            </tr>
        </table>
    {{/if}}
</div>
</body>
</html>
