{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function () {
        const fs = $('cotation'),
          date = fs.down('input[name=date]'),
          date_achevement = fs.down('input[name=dateAchevement]');
        Calendar.regField(date);
        Calendar.regField(date_achevement);
    })
</script>

<fieldset id="cotation">
    <legend>{{tr}}CCotation-cotation-legend{{/tr}}</legend>
    <table class="main">
        <tr>
            <th>{{tr}}CCotation-id{{/tr}}</th>
            <td>
                <input type="text" name="id">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-externalId{{/tr}}</th>
            <td>
                <input type="text" name="externalId">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-idSeance{{/tr}}</th>
            <td>
                <input type="text" name="idSeance">
            </td>
        </tr>
        <tr>
            <th class="notNull">{{tr}}CCotation-date{{/tr}}</th>
            <td>
                <input type="hidden" name="date">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-dateAchevement{{/tr}}</th>
            <td>
                <input type="hidden" name="dateAchevement">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-codeActe{{/tr}}</th>
            <td>
                <input type="text" name="codeActe" class="notNull">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-quantite{{/tr}}</th>
            <td>
                <input type="number" name="quantite">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-coefficient{{/tr}}</th>
            <td>
                <input type="number" name="coefficient">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-qualificatifDepense{{/tr}}</th>
            <td>
                <select name="qualificatifDepense">
                    <option value="">{{tr}}Choose{{/tr}}</option>
                    <option value="F">{{tr}}CCotation-qualificatifDepense.F{{/tr}}</option>
                    <option value="E">{{tr}}CCotation-qualificatifDepense.E{{/tr}}</option>
                    <option value="D">{{tr}}CCotation-qualificatifDepense.D{{/tr}}</option>
                    <option value="B">{{tr}}CCotation-qualificatifDepense.B{{/tr}}</option>
                    <option value="A">{{tr}}CCotation-qualificatifDepense.A{{/tr}}</option>
                    <option value="G">{{tr}}CCotation-qualificatifDepense.G{{/tr}}</option>
                    <option value="N">{{tr}}CCotation-qualificatifDepense.N{{/tr}}</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-montantDepassement{{/tr}}</th>
            <td>
                <input type="number" name="montantDepassement">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-montantTotal{{/tr}}</th>
            <td>
                <input type="number" name="montantTotal">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-lieuExecution{{/tr}}</th>
            <td>
                <select name="lieuExecution">
                    <option value="">{{tr}}Choose{{/tr}}</option>
                    <option value="0">{{tr}}CCotation-lieuExecution.0{{/tr}}</option>
                    <option value="1">{{tr}}CCotation-lieuExecution.1{{/tr}}</option>
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-complement{{/tr}}</th>
            <td>
                <select name="complement">
                    <option value="">{{tr}}Choose{{/tr}}</option>
                    <option value="N">{{tr}}CCotation-complement.N{{/tr}}</option>
                    <option value="F">{{tr}}CCotation-complement.F{{/tr}}</option>
                    <option value="U">{{tr}}CCotation-complement.U{{/tr}}</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-codeActivite{{/tr}}</th>
            <td>
                <input type="number" name="codeActivite">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-codePhase{{/tr}}</th>
            <td>
                <input type="number" name="codePhase">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-modificateurs{{/tr}}</th>
            <td>
                <input type="text" name="modificateurs">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-codeAssociation{{/tr}}</th>
            <td>
                <select name="codeAssociation">
                    <option value="">{{tr}}Choose{{/tr}}</option>
                    <option value="1">{{tr}}CCotation-codeAssociation.1{{/tr}}</option>
                    <option value="2">{{tr}}CCotation-codeAssociation.2{{/tr}}</option>
                    <option value="3">{{tr}}CCotation-codeAssociation.3{{/tr}}</option>
                    <option value="4">{{tr}}CCotation-codeAssociation.4{{/tr}}</option>
                    <option value="5">{{tr}}CCotation-codeAssociation.5{{/tr}}</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-supplementCharge{{/tr}}</th>
            <td>
                <select name="supplementCharge">
                    <option value="">{{tr}}Choose{{/tr}}</option>
                    <option value="0">{{tr}}CCotation-supplementCharge.0{{/tr}}</option>
                    <option value="1">{{tr}}CCotation-supplementCharge.1{{/tr}}</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-remboursementExceptionnel{{/tr}}</th>
            <td>
                <select name="remboursementExceptionnel">
                    <option value="">{{tr}}Choose{{/tr}}</option>
                    <option value="0">{{tr}}CCotation-remboursementExceptionnel.0{{/tr}}</option>
                    <option value="1">{{tr}}CCotation-remboursementExceptionnel.1{{/tr}}</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-dents{{/tr}}</th>
            <td>
                <input type="text" name="dents">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-prixUnitaire{{/tr}}</th>
            <td>
                <input type="number" name="prixUnitaire">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-baseRemboursement{{/tr}}</th>
            <td>
                <input type="number" name="baseRemboursement">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-utilisationReferentiel{{/tr}}</th>
            <td>
                <select name="utilisationReferentiel">
                    <option value="">{{tr}}Choose{{/tr}}</option>
                    <option value="0">{{tr}}CCotation-utilisationReferentiel.0{{/tr}}</option>
                    <option value="1">{{tr}}CCotation-utilisationReferentiel.1{{/tr}}</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-codeRegroupement{{/tr}}</th>
            <td>
                <input type="text" name="codeRegroupement">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-exonerationTMParticuliere{{/tr}}</th>
            <td>
                <select name="exonerationTMParticuliere">
                    <option value="">{{tr}}Choose{{/tr}}</option>
                    <option value="-1">{{tr}}CCotation-exonerationTMParticuliere.-1{{/tr}}</option>
                    <option value="31">{{tr}}CCotation-exonerationTMParticuliere.31{{/tr}}</option>
                    <option value="33">{{tr}}CCotation-exonerationTMParticuliere.33{{/tr}}</option>
                    <option value="4">{{tr}}CCotation-exonerationTMParticuliere.4{{/tr}}</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-libelle{{/tr}}</th>
            <td>
                <input type="text" name="libelle">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-taux{{/tr}}</th>
            <td>
                <input type="number" name="taux">
            </td>
        </tr>
    </table>
</fieldset>
