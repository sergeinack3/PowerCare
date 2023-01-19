{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=cabinet script=accident_travail register=true}}

<fieldset>
  <legend><i class="fas fa-car-crash"></i> {{tr}}accident_travail{{/tr}}</legend>

  {{if $accident_travail->_id}}
    <table class="form me-no-box-shadow">
      <tr>
        <th style="width: 30%">{{mb_label object=$accident_travail field=date_constatations}}</th>
        <td>{{mb_value object=$accident_travail field=date_constatations form=editAccidentTravail}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$accident_travail field=nature}}</th>
        <td>
          {{mb_value object=$accident_travail field=nature}}
        </td>
      </tr>
      <tr>
        <th>{{mb_label object=$accident_travail field=type}}</th>
        <td>
          {{mb_value object=$accident_travail field=type}}
        </td>
      </tr>
      <tr>
        <th>{{mb_label object=$accident_travail field=num_organisme}}</th>
        <td>{{mb_value object=$accident_travail field=num_organisme}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$accident_travail field=feuille_at}}</th>
        <td>{{mb_value object=$accident_travail field=feuille_at}}</td>
      </tr>
      <tr>
        <td colspan="2" style="text-align: center;">
          <button type="button" class="edit" onclick="AccidentTravail.editAccidentTravail('{{$accident_travail->_id}}', '{{$consult_id}}', '{{$sejour_id}}', '{{$object_class}}');">{{tr}}CAccidentTravail-action-modify{{/tr}}</button>
        </td>
      </tr>
    </table>
  {{else}}
    <button type="button" class="new" onclick="AccidentTravail.editAccidentTravail(0, '{{$consult_id}}', '{{$sejour_id}}', '{{$object_class}}');">{{tr}}CAccidentTravail-action-create{{/tr}}</button>
  {{/if}}
</fieldset>