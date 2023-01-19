{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<fieldset id="prevention_commune">
    <legend>{{tr}}CCotation-preventionCommune-legend{{/tr}}</legend>
    <table class="main">
        <tr>
            <th>{{tr}}CCotation-preventionCommune-topPrevention{{/tr}}</th>
            <td>
                <select name="topPrevention" class="notNull">
                    <option value="">{{tr}}Choose{{/tr}}</option>
                    <option value="0">{{tr}}CCotation-preventionCommune-topPrevention.0{{/tr}}</option>
                    <option value="1">{{tr}}CCotation-preventionCommune-topPrevention.1{{/tr}}</option>
            </td>
        </tr>
        <tr>
            <th>{{tr}}CCotation-preventionCommune-qualifiant{{/tr}}</th>
            <td>
                <input type="text" name="qualifiant">
            </td>
        </tr>
    </table>
</fieldset>
