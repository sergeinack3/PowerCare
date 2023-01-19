{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="selectCodeSituation" method="post" action="?" onsubmit="return false;">
    <input type="hidden" name="invoice_id" value="{{$invoice_id}}">
    <input type="hidden" name="securing_mode" value="{{$securing_mode}}">

    <table class="form">
        <tr>
            {{mb_include module=jfse template='invoicing/situation_code_field' nb_cells=1}}
        </tr>
        <tr>
            <td class="button">
                <button type="button" class="tick me-primary" onclick="Control.Modal.close(); Invoicing.selectSecuringMode('{{$invoice_id}}', '{{$securing_mode}}', $V(this.form.elements['situation_code']));">
                    {{tr}}Valider{{/tr}}
                </button>
            </td>
        </tr>
    </table>
</form>
