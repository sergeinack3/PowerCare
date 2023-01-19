{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th>{{tr}}CService{{/tr}}</th>
    <th>Nombre de patients</th>
    <th>Nombre d'AMBU</th>
    <th>Nombre d'Hospi. Comp.</th>
  </tr>
  {{foreach from=$results item=_result key=service}}
    <tr>
      <td>
        {{$service}}
      </td>
      <td>
        {{if isset($_result.patients|smarty:nodefaults)}}
          {{$_result.patients}}
        {{else}}
          0
        {{/if}}
      </td>
      <td>
        {{if isset($_result.ambu|smarty:nodefaults)}}
          {{$_result.ambu}}
        {{else}}
          0
        {{/if}}
      </td>
      <td>
        {{if isset($_result.hospi|smarty:nodefaults)}}
          {{$_result.hospi}}
        {{else}}
          0
        {{/if}}
      </td>
    </tr>
  {{/foreach}}
</table>