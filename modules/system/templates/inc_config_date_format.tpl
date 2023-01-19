{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th style="width: 50%">
    <label for="{{$var}}" title="{{tr}}config-{{$var}}-desc{{/tr}}">
      {{tr}}config-{{$var}}{{/tr}}
    </label>  
  </th>
  <td>
    <input type="text" name="{{$var}}" value="{{$conf.$var}}" />
    {{$dtnow|date_format:$conf.$var}}
  </td>
</tr>
