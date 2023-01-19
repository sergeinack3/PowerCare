{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $allergies|@count}}
  <table class="tbl" style="min-width:100px;">
    <tr>
      <th>
        {{tr}}CAntecedent-Allergie|pl{{/tr}}
        <small>({{$allergies|@count}})</small>
      </th>
    </tr>
    {{foreach from=$allergies item=_allergie}}
      <tr>
        <td class="text">
          {{if $_allergie->date}}
            {{$_allergie->date|date_format:$conf.date}}:
          {{/if}}
          {{$_allergie->rques}}
        </td>
      </tr>
    {{/foreach}}
  </table>
{{/if}}

{{if $all_absence|@count}}
  <table class="tbl" style="min-width:100px;">
    <tr>
      <th>
        {{tr}}CAntecedent-No allergy|pl{{/tr}}
        <small>({{$all_absence|@count}})</small>
      </th>
    </tr>
    {{foreach from=$all_absence item=_allergie}}
      <tr>
        <td class="text">
          {{if $_allergie->date}}
            {{$_allergie->date|date_format:$conf.date}}:
          {{/if}}
          {{$_allergie->rques}}
        </td>
      </tr>
    {{/foreach}}
  </table>
{{/if}}
