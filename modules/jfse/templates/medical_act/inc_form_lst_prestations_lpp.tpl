{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
    Main.add(function () {
        const fs = $('lst_prestation_lpp'),
          date_debut = fs.down('input[name=dateDebut]'),
          date_fin = fs.down('input[name=dateFin]');

        Calendar.regField(date_debut);
        Calendar.regField(date_fin);
    })
</script>

<fieldset id="lst_prestation_lpp">
    <legend>{{tr}}CCotation-lstPrestationLPP-legend{{/tr}}</legend>
    <table class="main">
        <tr>
            <th>{{tr}}CCotation-lstPrestationLPP-typePrestation{{/tr}}</th>
            <td>
                <select name="typePrestation">
                    <option value="">{{tr}}Choose{{/tr}}</option>
                    <option value="A">{{tr}}CCotation-lstPrestationLPP-typePrestation.achat{{/tr}}</option>
                    <option value="E">{{tr}}CCotation-lstPrestationLPP-typePrestation.entretien{{/tr}}</option>
                    <option value="L">{{tr}}CCotation-lstPrestationLPP-typePrestation.location{{/tr}}</option>
                    <option value="P">{{tr}}CCotation-lstPrestationLPP-typePrestation.frais_de_port{{/tr}}</option>
                    <option value="R">{{tr}}CCotation-lstPrestationLPP-typePrestation.reparation{{/tr}}</option>
                    <option value="S">{{tr}}CCotation-lstPrestationLPP-typePrestation.service{{/tr}}</option>
                    <option value="V">{{tr}}CCotation-lstPrestationLPP-typePrestation.livraison{{/tr}}</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-lstPrestationLPP-libelle{{/tr}}</th>
            <td>
                <input type="text" name="libelle">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-lstPrestationLPP-prixUnitaireTTC{{/tr}}</th>
            <td>
                <input type="number" name="prixUnitaireTTC">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-lstPrestationLPP-prixUnitaireRef{{/tr}}</th>
            <td>
                <input type="number" name="prixUnitaireRef">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-lstPrestationLPP-montantTotalTTC{{/tr}}</th>
            <td>
                <input type="number" name="montantTotalTTC">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-lstPrestationLPP-code{{/tr}}</th>
            <td>
                <input type="text" name="code">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-lstPrestationLPP-quantite{{/tr}}</th>
            <td>
                <input type="number" name="quantite">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-lstPrestationLPP-noSiret{{/tr}}</th>
            <td>
                <input type="text" name="noSiret">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-lstPrestationLPP-dateDebut{{/tr}}</th>
            <td>
                <input type="hidden" name="dateDebut">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-lstPrestationLPP-dateFin{{/tr}}</th>
            <td>
                <input type="hidden" name="dateFin">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-lstPrestationLPP-prixLimiteVente{{/tr}}</th>
            <td>
                <input type="number" name="prixLimiteVente">
            </td>
        </tr>
    </table>
</fieldset>
