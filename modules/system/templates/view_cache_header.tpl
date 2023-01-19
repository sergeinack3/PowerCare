{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $outputs === ''}}
    {{mb_return}}
{{/if}}

<table class="tbl">
  <tr>
    <th>Adresse</th>
    <th>Results</th>
  </tr>
  <tr>
    <td>{{$actual_host}}</td>
    <td>{{$outputs|smarty:nodefaults}}</td>
  </tr>
</table>
