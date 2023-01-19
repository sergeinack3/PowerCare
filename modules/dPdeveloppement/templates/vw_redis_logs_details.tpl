{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>{{tr}}common-key{{/tr}} : {{$key}}</h2>

<table class="main tbl">
  <tr>
    <th>{{tr}}common-date{{/tr}}</th>
    <th>{{tr}}common-key{{/tr}}</th>
    <th>{{tr}}dPdeveloppement-redis-command{{/tr}}</th>
    <th>{{tr}}common-value{{/tr}}</th>
  </tr>

  {{foreach from=$occurences item=_occu}}
    <tr>
      <td class="narrow">{{$_occu.timestamp}}</td>
      <td class="text">{{$_occu.key}}</td>
      <td>{{$_occu.command}}</td>
      <td>{{$_occu.value}}</td>
    </tr>
  {{/foreach}}
</table>