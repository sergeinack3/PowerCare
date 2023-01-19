{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=colspan value=2}}

<tr>
  <th>
    <label for="_bind_tag_view">{{tr}}CTag|pl{{/tr}}</label>
  </th>
  <td style="white-space: normal;" colspan="{{$colspan-1}}">
    {{mb_include module=system template=inc_tag_binder_widget}}
  </td>
</tr>