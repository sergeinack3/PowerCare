
{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="formula-selection" method="post" action="?" onsubmit="return false;">
    <input type="hidden" name="invoice_id" value="{{$invoice_id}}">
    <table class="form">
        {{if $assistant->formulas_service_message}}
            <tr>
                <td colspan="4">
                    <div class="small-{{if count($assistant->formulas)}}info{{else}}warning{{/if}}">
                        {{$assistant->conventions_service_message}}
                    </div>
                </td>
            </tr>
        {{/if}}
        {{if count($assistant->formulas)}}
            {{mb_include module=jfse template=invoicing/formulas_list formulas=$assistant->formulas}}
            <tr>
                <td class="button" colspan="4">
                    <button type="button" class="tick me-primary" onclick="ThirdPartyPayment.selectFormula(this.form);">{{tr}}Validate{{/tr}}</button>
                </td>
            </tr>
        {{/if}}
    </table>
</form>
