{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th colspan="4" class="title">
      {{tr}}CINSPatient.list{{/tr}}
    </th>
  </tr>
  <tr>
    <th>
      {{tr}}CINSPatient-ins{{/tr}}
    </th>
    <th>
      {{tr}}CINSPatient-type{{/tr}}
    </th>
    <th>
      {{tr}}CINSPatient-date{{/tr}}
    </th>
    <th>
      {{tr}}CINSPatient-provider{{/tr}}
    </th>
  </tr>
  {{foreach from=$list_ins item=_ins name=loop}}
    <tr {{if !$smarty.foreach.loop.first}}class="hatching" {{/if}}>
      <td>
        {{$_ins->ins}}
      </td>
      <td>
        {{$_ins->type}}
      </td>
      <td>
        {{$_ins->date}}
      </td>
      <td>
        {{$_ins->provider}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="4" class="empty">
        {{tr}}CINSPatient.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>