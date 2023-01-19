{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function () {
        const date_field = $('entente_prealable').down('input[name=dateEnvoi]');
        Calendar.regField(date_field);
    })
</script>

<fieldset id="entente_prealable">
    <legend>{{tr}}CCotation-entente_prealable-legend{{/tr}}</legend>
    <table class="main">
        <tr>
            <th>{{tr}}CCotation-entente_prealable-valeur{{/tr}}</th>
            <td>
                <select name="valeur">
                    <option value="">{{tr}}Choose{{/tr}}</option>
                    <option value="0">{{tr}}CCotation-entente_prealable-valeur-0{{/tr}}</option>
                    <option value="1">{{tr}}CCotation-entente_prealable-valeur-1{{/tr}}</option>
                    <option value="2">{{tr}}CCotation-entente_prealable-valeur-2{{/tr}}</option>
                    <option value="3">{{tr}}CCotation-entente_prealable-valeur-3{{/tr}}</option>
                    <option value="4">{{tr}}CCotation-entente_prealable-valeur-4{{/tr}}</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-entente_prealable-dateEnvoi{{/tr}}</th>
            <td>
                <input type="hidden" name="dateEnvoi">
            </td>
        </tr>
    </table>
</fieldset>
