{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title">Date</th>
    <th class="title">
      Nombre d'admissions non placées
      {{if $type_admission}}
        <br />
        ({{tr}}CSejour._type_admission.{{$type_admission}}{{/tr}})
      {{/if}}
    </th>
  </tr>
  {{foreach from=$list key=date item=sejour}}
    <tr>
      <td>{{$date|date_format:$conf.longdate}}</td>
      <td>{{$sejour|@count}} admission(s)</td>
    </tr>
  {{/foreach}}
</table>