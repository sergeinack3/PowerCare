{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main form">
  <tr>
    <th colspan="2" class="category">
      {{$code}}
    </th>
  </tr>
  {{foreach from=$tree item=_code}}
    <tr>
      <th>{{$_code}}</th>
      <td>{{$codes.$_code}}</td>
    </tr>
  {{/foreach}}
</table>
