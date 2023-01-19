{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<fieldset id="forcage_montants">
    <legend>{{tr}}CCotation-forcageMontants-legend{{/tr}}</legend>
    <table class="main">
        <tr>
            <th>{{tr}}CCotation-forcageMontants-idActe{{/tr}}</th>
            <td>
                <input type="number" name="idActe" class="notNull">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-forcageMontants-type{{/tr}}</th>
            <td>
                <select name="type" class="notNull">
                    <option value="">{{tr}}Choose{{/tr}}</option>
                    <option value="AMO">{{tr}}CCotation-forcageMontants-type.AMO{{/tr}}</option>
                    <option value="AMC">{{tr}}CCotation-forcageMontants-type.AMC{{/tr}}</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-forcageMontants-choix{{/tr}}</th>
            <td>
                <select name="choix" class="notNull">
                    <option value="">{{tr}}Choose{{/tr}}</option>
                    <option value="0">{{tr}}CCotation-forcageMontants-choix.0{{/tr}}</option>
                    <option value="1">{{tr}}CCotation-forcageMontants-choix.1{{/tr}}</option>
                    <option value="2">{{tr}}CCotation-forcageMontants-choix.2{{/tr}}</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-forcageMontants-montant{{/tr}}</th>
            <td>
                <input type="number" name="montant" class="notNull">
            </td>
        </tr>
    </table>
</fieldset>
