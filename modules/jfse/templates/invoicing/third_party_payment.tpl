{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="thirdPartyPayment" method="post" onsubmit="return false;">
    <input type="hidden" name="invoice_id" value="{{$invoice->id}}">
    <table>
        <tr>
            {{me_form_bool nb_cells=1 class='halfPane me-padding-5' mb_object=$complementary mb_field=third_party_amo}}
                {{mb_field object=$complementary field=third_party_amo onclick="Invoicing.setThirdPartyPayment(this.form)"}}
            {{/me_form_bool}}
            {{me_form_field nb_cells=1 class='halfPane me-padding-5' mb_object=$complementary mb_field=third_party_amc}}
                {{mb_field object=$complementary field=third_party_amc onchange="Invoicing.editThirdPartyPayment('"|cat:$invoice->id|cat:"', \$V(this))"}}
            {{/me_form_field}}
            <td class="narrow">
                <button type="button" class="edit notext" onclick="Invoicing.editThirdPartyPayment('{{$invoice->id}}');">
                    {{tr}}CComplementaryHealthInsurance-action-edit{{/tr}}
                </button>
            </td>
        </tr>
        {{if $complementary->health_insurance && $complementary->health_insurance->label}}
          <tr>
              <td class="empty" colspan="3" style="text-align: center;">
                  {{mb_value object=$complementary->health_insurance field=label}}
              </td>
          </tr>
        {{elseif $complementary->additional_health_insurance && $complementary->additional_health_insurance->label}}
            <tr>
              <td class="empty" colspan="3" style="text-align: center;">
                  {{mb_value object=$complementary->additional_health_insurance field=label}}
              </td>
            </tr>
        {{elseif $complementary->amo_service && $complementary->amo_service->code != '00'}}
            <tr>
                <td class="empty" colspan="3" style="text-align: center;">
                    {{mb_value object=$complementary->amo_service field=label}}
                </td>
            </tr>
        {{elseif $complementary->attack_victim}}
            <tr>
                <td class="empty" colspan="3" style="text-align: center;">
                    {{tr}}CComplementaryHealthInsurance.paper_mode.attack_victim{{/tr}}
                </td>
            </tr>
        {{/if}}
    </table>
</form>
