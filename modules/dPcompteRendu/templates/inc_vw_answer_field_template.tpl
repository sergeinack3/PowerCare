{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$results key=key item=_field}}
  <tr>
    <td>
      <a href="#" ondblclick="insertField(this);" data-fieldHtml="{{$_field.fieldHTML}}">
        {{$key}}
      </a>
    </td>
  </tr>
{{foreachelse}}
  <tr><td class="empty">{{tr}}No result{{/tr}}</td></tr>
{{/foreach}}