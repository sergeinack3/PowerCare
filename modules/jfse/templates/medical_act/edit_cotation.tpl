{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form id="setCotationForm" method="post">
    <table class="main tbl">
        <tr>
            <th>{{tr}}CCotation-idFacture{{/tr}}</th>
            <td>
                <input type="text" name="idFacture">
            </td>
        </tr>
    </table>
    <table class="main">
        <tr>
            <td>
                {{mb_include module=Jfse template=personal_cotation/inc_form_cotation}}
            </td>
            <td>
                {{mb_include module=Jfse template=personal_cotation/inc_form_prestation_cip}}
                {{mb_include module=Jfse template=personal_cotation/inc_form_entente_prealable}}
                {{mb_include module=Jfse template=personal_cotation/inc_form_prevention_commune}}
                {{mb_include module=Jfse template=personal_cotation/inc_form_formule list_formules=$list_formules}}
            </td>
        </tr>
        <tr>
            <td>
                {{mb_include module=Jfse template=personal_cotation/inc_form_forcage_montants}}
                {{mb_include module=Jfse template=personal_cotation/inc_form_executant}}
            </td>
            <td>
                {{mb_include module=Jfse template=personal_cotation/inc_form_lst_prestations_lpp}}
                {{mb_include module=Jfse template=personal_cotation/inc_form_renouvellement}}
            </td>
        </tr>
    </table>
    <table class="main tbl">
        <tr>
            <td colspan="2" class="me-text-align-center">
                <button type="button" onclick="MedicalActs.setMedicalAct(this.form)">
                    {{tr}}Save{{/tr}}
                </button>
            </td>
        </tr>
    </table>
</form>
<div id="setCotationResult"></div>
