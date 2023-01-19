{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<fieldset id="prestation_cip">
    <legend>{{tr}}CCotation-prestationCIP-legend{{/tr}}</legend>
    <table class="main">
        <tr>
            <th>{{tr}}CCotation-prestationCIP-code{{/tr}}</th>
            <td>
                <input type="text" name="code" class="notNull">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-prestationCIP-libelle{{/tr}}</th>
            <td>
                <input type="text" name="libelle" class="notNull">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-prestationCIP-medicamentSpecifique{{/tr}}</th>
            <td>
                <select name="medicamentSpecifique" class="notNull">
                    <option value="">{{tr}}Choose{{/tr}}</option>
                    <option value="0">{{tr}}CCotation-prestationCIP-medicamentSpecifique.0{{/tr}}</option>
                    <option value="1">{{tr}}CCotation-prestationCIP-medicamentSpecifique.1{{/tr}}</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-prestationCIP-grandConditionnement{{/tr}}</th>
            <td>
                <select name="grandConditionnement" class="notNull">
                    <option value="">{{tr}}Choose{{/tr}}</option>
                    <option value="0">{{tr}}CCotation-prestationCIP-grandConditionnement.0{{/tr}}</option>
                    <option value="1">{{tr}}CCotation-prestationCIP-grandConditionnement.1{{/tr}}</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-prestationCIP-typeCode{{/tr}}</th>
            <td>
                <input type="text" name="typeCode">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-prestationCIP-indicateurDelivrance{{/tr}}</th>
            <td>
                <input type="number" name="indicateurDelivrance">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-prestationCIP-indicateurSubstitution{{/tr}}</th>
            <td>
                <input type="text" name="indicateurSubstitution">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-prestationCIP-deconditionnement{{/tr}}</th>
            <td>
                <select name="deconditionnement">
                    <option value="">{{tr}}Choose{{/tr}}</option>
                    <option value="0">{{tr}}CCotation-prestationCIP-deconditionnement.0{{/tr}}</option>
                    <option value="1">{{tr}}CCotation-prestationCIP-deconditionnement.1{{/tr}}</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-prestationCIP-deconditionnementQte{{/tr}}</th>
            <td>
                <input type="number" name="deconditionnementQte">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-prestationCIP-deconditionnementPU{{/tr}}</th>
            <td>
                <input type="number" name="deconditionnementPU">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-prestationCIP-deconditionnementBR{{/tr}}</th>
            <td>
                <input type="number" name="deconditionnementBR">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-prestationCIP-deconditionnementNB{{/tr}}</th>
            <td>
                <input type="number" name="deconditionnementNB">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-prestationCIP-deconditionnementTA{{/tr}}</th>
            <td>
                <input type="number" name="deconditionnementTA">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-prestationCIP-modePrescription{{/tr}}</th>
            <td>
                <input type="text" name="modePrescription">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-prestationCIP-typePrescrit{{/tr}}</th>
            <td>
                <input type="text" name="typePrescrit">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-prestationCIP-codePrescrit{{/tr}}</th>
            <td>
                <input type="text" name="codePrescrit">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-prestationCIP-conditionPEC{{/tr}}</th>
            <td>
                <input type="text" name="conditionPEC">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-prestationCIP-tarifAjuste{{/tr}}</th>
            <td>
                <input type="number" name="tarifAjuste">
            </td>
        </tr>
    </table>
</fieldset>
