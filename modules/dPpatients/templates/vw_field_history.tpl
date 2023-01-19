{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th colspan="2">{{tr}}History{{/tr}} : {{tr}}{{$object_class}}-{{$field_name}}{{/tr}}</th>
  </tr>
  {{foreach from=$history key=_datetime item=_old_value}}
    <tr>
      <td>
        {{$_datetime}}
      </td>
      <td>
        {{$_old_value}}
      </td>
    </tr>
  {{/foreach}}
</table>