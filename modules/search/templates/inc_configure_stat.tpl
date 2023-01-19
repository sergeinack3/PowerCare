{{*
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th class="category" colspan="3">
      <span> Nom de l'index : {{$index_name}}</span>
    </th>
  </tr>

  {{foreach from=$stats key=k item=stat}}
    <tr>
      <th class="section">{{$k}}</th>
      <td class="text compact">
        {{$stat|highlight:json}}
      </td>
    </tr>
  {{/foreach}}

</table>