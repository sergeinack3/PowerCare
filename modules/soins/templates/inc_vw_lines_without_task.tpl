{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title">
      Lignes sans tâches associées ({{$lines|@count}})
    </th>
  </tr>   
  {{foreach from=$lines item=_line}}
    <tr>
      <td>
        {{$_line->_view}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty">
        {{tr}}CPrescriptionLineElement.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>