{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th id="technicien-" class="title text">
      <script>
        Repartition.registerTechnicien('',{{$readonly}});
      </script>
      {{tr}}ssr-sejour_non_repartis{{/tr}}
      <small class="count">(-)</small>
    </th>
  </tr>
  {{if $techniciens.nb == 1}}
    {{assign var=plateau_id value=$techniciens.plateau}}
    {{assign var=plateau value=$plateaux.$plateau_id}}
    {{assign var=alone_technicien value=$plateau->_ref_techniciens|@first}}
    <tr id="repartition_auto_alone_technicien" style="display:none;">
      <td class="button">
        <button type="button" class="left" onclick="Repartition.repartitionAutoBilanSSR('{{$alone_technicien->_id}}')"
                title="{{tr}}ssr-repartition_patients_auto-desc{{/tr}} {{$alone_technicien->_ref_kine->_view}}">
          {{tr}}ssr-repartition_patients_auto{{/tr}}
        </button>
      </td>
    </tr>
  {{/if}}
  
  <tbody id="sejours-technicien-"></tbody>
</table>
