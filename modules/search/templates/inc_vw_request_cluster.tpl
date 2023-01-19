{{*
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th class="category"> {{$type|upper}} {{$request}}</th>
  </tr>
  <tr>
    <td class="text">{{$content|highlight:json}}</td>
  </tr>
</table>