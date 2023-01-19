{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit{{$act->_guid}}-amount_AMO" method="post" action="?" onsubmit="return false;">
    <fieldset>
        <legend>
            <span onclick="MedicalActs.toggleView(this, 'amo_amount_forcing');" style="cursor: pointer; float: right; margin-right: 5px;">
                <i class="fa-chevron-right fa fa-g"></i>
            </span>
            {{tr}}CJfseActView-title-force_amount_amo{{/tr}}
        </legend>
        <table id="amo_amount_forcing-container" class="form" style="display: none;">
            <tr>
                <td colspan="2">
                    <div class="small-info">
                        {{tr}}CJfseActView-msg-force_amounts{{/tr}}
                    </div>
                </td>
            </tr>
            <tr>
                {{me_form_field nb_cells=1 layout=true label='CJfseActView-computed_amount_amo'}}
                    <input type="radio" name="amount_amo_choice" value="0"{{if $medical_act->amo_amount_forcing->choice == 0}} checked{{/if}}>
                    <input type="number" name="amount_amo_computed" value="{{$medical_act->amo_amount_forcing->computed_insurance_part}}" disabled="disabled"/> &euro;
                {{/me_form_field}}
                {{me_form_field nb_cells=1 layout=true label='CJfseActView-modified_amount_amo'}}
                    <input type="radio" name="amount_amo_choice" value="1"{{if $medical_act->amo_amount_forcing->choice == 1}} checked{{/if}}>
                    <input type="number" name="amount_amo_modified" value="{{$medical_act->amo_amount_forcing->modified_insurance_part}}"/> &euro;
                {{/me_form_field}}
            </tr>
        </table>
    </fieldset>
</form>
