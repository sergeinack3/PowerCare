{{**
 * @package Mediboard\Ucum
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 *}}

<div style="margin: 5px;">
    <fieldset>
        <legend>{{tr}}Conversion{{/tr}}</legend>
        <table class="main form">
            <tbody>
            <tr>
                <td>
                    <label for="quantity">{{tr}}Quantity{{/tr}} : </label>
                    <input type="text" name="quantity" placeholder="Exemple : 1" value="{{$quantity}}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="from">{{tr}}common-since{{/tr}} : </label>
                    <input type="text" class="ucumField" name="from" placeholder="Exemple : mm[Hg]"
                           value="{{$from}}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="to">{{tr}}to{{/tr}} : </label>
                    <input type="text" class="ucumField" name="to" placeholder="Exemple : kg/(m.s2)" value="{{$to}}">
                </td>
            </tr>
            <tr>
                <td>
                    {{tr}}Result{{/tr}} : {{$conversion}}
                </td>
            </tr>
            </tbody>
        </table>
        <button type="button" class="submit me-primary" onclick="return Ucum.updateConversion('{{$sourceSearch}}');">Valider
        </button>
    </fieldset>
</div>
