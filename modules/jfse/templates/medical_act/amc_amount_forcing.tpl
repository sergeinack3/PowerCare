{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit{{$act->_guid}}-amount_AMC" method="post" action="?" onsubmit="return false;">
    <fieldset>
        <legend>
            <span onclick="MedicalActs.toggleView(this, 'amc_amount_forcing');" style="cursor: pointer; float: right; margin-right: 5px;">
                <i class="fa-chevron-right fa fa-g"></i>
            </span>
            {{tr}}CJfseActView-title-force_amount_amc{{/tr}}
        </legend>
        <table id="amc_amount_forcing-container" class="form" style="display: none;">
            <tr>
                <td colspan="3">
                    <div class="small-info">
                        {{tr}}CJfseActView-msg-force_amounts{{/tr}}
                    </div>
                </td>
            </tr>
            <tr>
                {{me_form_field nb_cells=1 layout=true label='CJfseActView-computed_amount_amc'}}
                    <input type="radio" name="amount_amc_choice" value="0"{{if $medical_act->amc_amount_forcing->choice == 0}} checked{{/if}}>
                    <input type="number" name="amount_amc_computed" value="{{$medical_act->amc_amount_forcing->computed_insurance_part}}" disabled="disabled"/> &euro;
                {{/me_form_field}}
                {{me_form_field nb_cells=1 layout=true label='CJfseActView-modified_amount_amc'}}
                    <input type="radio" name="amount_amc_choice" value="1"{{if $medical_act->amc_amount_forcing->choice == 1}} checked{{/if}}>
                    <input type="number" name="amount_amc_modified" value="{{$medical_act->amc_amount_forcing->modified_insurance_part}}"/> &euro;
                {{/me_form_field}}
                {{me_form_field nb_cells=1 layout=true label='CJfseActView-modified_global_amount_amc'}}
                    <input type="radio" name="amount_amc_choice" value="2"{{if $medical_act->amc_amount_forcing->choice == 2}} checked{{/if}}>
                    <input type="number" name="amount_amc_global" value="{{$medical_act->amc_amount_forcing->modified_insurance_part}}"/> &euro;
                {{/me_form_field}}
            </tr>
        </table>
    </fieldset>
</form>
