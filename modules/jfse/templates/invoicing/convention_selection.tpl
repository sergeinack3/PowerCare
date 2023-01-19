{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="convention-selection" method="post" action="?" onsubmit="return false;">
    <input type="hidden" name="invoice_id" value="{{$invoice_id}}">
    <table class="form">
        <tr>
            <th class="title">{{tr}}CComplementaryHealthInsurance-action-convention_selection{{/tr}}</th>
        </tr>
        {{if $assistant->conventions_service_message}}
            <tr>
                <td>
                    <div class="small-{{if count($assistant->conventions)}}info{{else}}warning{{/if}}">
                        {{$assistant->conventions_service_message}}
                    </div>
                </td>
            </tr>
        {{/if}}
        {{if count($assistant->conventions)}}
            <tr>
                <td>
                    <ul style="list-style-type: none;">
                        {{foreach from=$assistant->conventions item=convention}}
                          <li>
                              <label>
                                  <input type="radio" name="convention_id" value="{{$convention->convention_id}}">
                                  {{$convention->signer_organization_label}} &mdash; {{$convention->signer_organization_number}} / Type: {{$convention->convention_type}}
                                  {{if $convention->secondary_criteria}}
                                    / Critère secondaire : {{$convention->secondary_criteria}}
                                  {{/if}}
                                  {{if $convention->amc_number}}
                                    <div class="small-info">N° AMC : {{$convention->amc_number}}</div>
                                  {{/if}}
                              </label>
                          </li>
                        {{/foreach}}
                    </ul>
                </td>
            </tr>
            <tr>
                <td class="button">
                    <button type="button" class="tick me-primary" onclick="ThirdPartyPayment.selectConvention(this.form);">{{tr}}Validate{{/tr}}</button>
                </td>
            </tr>
        {{elseif !$assistant->conventions_service_message}}
            <tr>
                <td class="empty">
                    <div class="small-warning">
                        {{tr}}CComplementaryHealthInsurance-msg-no_convention_found{{/tr}}
                    </div>
                </td>
            </tr>
        {{/if}}
    </table>
</form>
