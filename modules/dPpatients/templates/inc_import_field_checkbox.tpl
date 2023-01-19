{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=title value=false}}

<tr>
  <th>
    <label for="{{$field}}" {{if $title}}title="{{tr}}{{$trad}}-desc{{/tr}}"{{/if}}>{{tr}}{{$trad}}{{/tr}}</label>
  </th>
  <td>
    <input type="checkbox" name="{{$field}}" {{if $checked}}checked{{/if}} />
  </td>
</tr>