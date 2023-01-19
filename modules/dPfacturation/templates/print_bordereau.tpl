{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=banque value=$praticien->_ref_banque}}
{{if !$banque->_id}}
    <div class="small-warning">{{tr}}CMediusers-banque_id-none{{/tr}}</div>
{{/if}}

<table class="tbl">
    <tr>
        <th class="title">
            {{$banque}} <br>
            {{$banque->adresse}} {{$banque->cp}} {{$banque->ville}}
        </th>
        <th class="title" colspan="8">
            <button type="button" class="notext print not-printable"
                    onclick="window.print();">{{tr}}Print{{/tr}}</button>
            {{tr}}compta-print_bordereau-title{{/tr}}
        </th>
    </tr>

    <tr>
        <th>{{tr}}Date{{/tr}}</th>
        <th>{{tr}}compta-code_banque{{/tr}}</th>
        <th>{{tr}}compta-code_guichet{{/tr}}</th>
        <th>{{tr}}CMediusers-adherent{{/tr}}</th>
        <th>{{tr}}CMediusers-compte-long{{/tr}}</th>
        <th colspan="4">{{tr}}compta-titulaire{{/tr}}</th>
    </tr>

    <tr style="text-align: center">
        <td>{{$dnow|date_format:$conf.date}}</td>
        <td>{{$compte_banque}}</td>
        <td>{{$compte_guichet}}</td>
        <td>{{$compte_numero}}</td>
        <td>{{$compte_cle}}</td>
        <td colspan="3">
            {{if $view_function}}
                {{$praticien->_ref_function}}
            {{else}}
                {{$praticien}}
            {{/if}}
        </td>
    </tr>

    <tr>
        <th colspan="2" class="title">{{tr}}compta-tireur{{/tr}}</th>
        <th colspan="2" class="title">{{tr}}compta-reference{{/tr}}</th>
        <th colspan="3" class="title">{{tr}}compta-etab_pay{{/tr}}</th>
        <th class="title narrow">{{tr}}CFacture-montant{{/tr}}</th>
    </tr>

    {{foreach from=$reglements item=_reglement}}
        <tr>
            <td colspan="2">
                {{if $_reglement->tireur}}
                    {{$_reglement->tireur}}
                {{else}}
                    {{$_reglement->_ref_object->_ref_patient}}
                {{/if}}
            </td>
            <td colspan="2">{{$_reglement->reference}}</td>
            <td colspan="3">{{$_reglement->_ref_banque}}</td>
            <td style="text-align: right;">{{mb_value object=$_reglement field=montant}}</td>
        </tr>
    {{/foreach}}

    <tr style="text-align: right; font-weight: bold;">
        <td colspan="4"></td>
        <td>{{tr}}CFacture-nb_remise{{/tr}}</td>
        <td>{{$reglements|@count}}</td>
        <td>{{tr}}CFactureCabinet-montant_total{{/tr}}</td>
        <td>{{$montantTotal|currency}}</td>
    </tr>
</table>

<fieldset style="float: left; width: 47%; height: 6em;">
    <legend>{{tr}}compta-visa_banque{{/tr}}</legend>
</fieldset>

<fieldset style="float: right; width: 47%; height: 6em;">
    <legend>{{tr}}compta-visa_client{{/tr}}</legend>
</fieldset>
