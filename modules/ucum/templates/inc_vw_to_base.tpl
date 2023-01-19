{{**
 * @package Mediboard\Ucum
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 *}}

<div style="margin: 5px;">
    <fieldset>
        <legend>{{tr}}common-BaseUnit{{/tr}}</legend>
        <table class="main form">
            <tbody>
            <tr>
                <td>
                    <label for="toBaseUnit">{{tr}}common-Unit{{/tr}} : </label>
                    <input type="text" class="ucumField" name="toBaseUnit" placeholder="Exemple : [cml_i]"
                           value="{{$toBaseUnit}}">
                </td>
            </tr>
            <tr>
                <td>
                    {{tr}}Result{{/tr}} : {{$toBase}}
                </td>
            </tr>
            </tbody>
        </table>
        <button type="submit" class="submit me-primary" onclick="return Ucum.updateToBase('{{$sourceSearch}}');">Valider
        </button>
    </fieldset>
</div>
