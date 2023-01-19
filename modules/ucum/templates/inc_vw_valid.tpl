{{**
 * @package Mediboard\Ucum
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 *}}

<div style="margin: 5px;">
    <fieldset>
        <legend>{{tr}}common-Validity{{/tr}}</legend>
        <table class="main form">
            <tbody>
            <tr>
                <td>
                    <label for="isValid">{{tr}}common-Unit{{/tr}} : </label>
                    <input type="text" class="ucumField" name="isValid" placeholder="Exemple : [in_i]"
                           value="{{$isValid}}">
                </td>
            </tr>
            <tr>
            <tr>
                <td>
                    {{tr}}Result{{/tr}} : {{$validation}}
                </td>
            </tr>
            </tbody>
        </table>
        <button type="button" class="submit me-primary" onclick="return Ucum.updateValid('{{$sourceSearch}}');">Valider
        </button>
    </fieldset>
</div>
