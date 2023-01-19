{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<fieldset id="formule">
    <legend>{{tr}}CCotation-formule-legend{{/tr}}</legend>
    <input type="hidden" name="noPrestationAttachee" value="">
    <input type="hidden" name="noFormule" value="">
    <input type="hidden" name="libelle" value="">
    <input type="hidden" name="calculTheorique" value="">
    <input type="hidden" name="lstParametres" value="">
    <table class="main">
        <tr>
            <th>{{tr}}CCotation-formule-select-title{{/tr}}</th>
            <td>
                <select name="select_formule">
                    <option value="">{{tr}}Choose{{/tr}}</option>
                    {{foreach from=$list_formules key=key item=formule}}
                        <option data-noPrestationAttachee="{{$formule->noPrestationAttachee}}"
                                data-noFormule="{{$formule->noFormule}}" data-libelle="{{$formule->libelle}}"
                                data-calculTheorique="{{$formule->calculTheorique}}"
                                data-lstParametres="{{$formule->lstParametres}}"
                                value="{{$key}}" onselect="">
                            {{$formule->libelle}}
                        </option>
                    {{/foreach}}
                </select>
            </td>
        </tr>
    </table>
</fieldset>
