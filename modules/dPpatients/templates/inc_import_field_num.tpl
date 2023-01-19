{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th>
    <label for="{{$field}}">{{tr}}{{$trad}}{{/tr}}</label>
  </th>
  <td>
    <input type="number" name="{{$field}}" size="5" value="{{$value}}" />
  </td>
</tr>