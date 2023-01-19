{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th colspan="4" class="category">
      Liste des allaitements
    </th>
  </tr>
  {{foreach from=$allaitements item=_allaitement}}
    <tr>
      <td>
        <a href="#1" onclick="Allaitement.editAllaitement('{{$_allaitement->_id}}')">{{$_allaitement}}</a>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="4">{{tr}}CAllaitement.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>