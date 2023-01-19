{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var="patient" value=$_sejour->_ref_patient}}

<td>
    {{if $type_externe == "depart"}}
      {{if $_aff->_ref_prev->_id}}
        {{assign var=_affectation value=$_aff->_ref_prev}}
      {{else}}
        {{assign var=_affectation value=$_aff}}
      {{/if}}

      <button type="button" class="tick me-primary" onclick="Soins.askDepartEtablissement('{{$_affectation->_id}}');">
          {{tr}}CAffectation-action-Validate the departure{{/tr}}
      </button>
    {{else}}
      <button type="button" class="tick me-primary" onclick="Soins.askRetourEtablissement(
      '{{$_aff->_ref_prev->_id}}',
      '{{$_aff->_id}}',
       {{if $_aff->_in_permission_sup_48h}}1{{else}}0{{/if}},
      '0'
      );">{{tr}}CAffectation-action-Validate the return{{/tr}}</button>
    {{/if}}
</td>

<td colspan="2" class="text">
  <span class="CPatient-view" onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
    {{$patient}}
  </span>
</td>

<td class="text">
  {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}
</td>

<td>
  <div style="float: right;">
    
  </div>
  <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
    {{$_aff->entree|date_format:$conf.time}}
  </span>
</td>

{{if $type_externe == "depart"}}
  <td class="text">
    {{$_aff->_ref_prev->_ref_lit->_view}}
  </td>
  <td class="text">
    {{$_aff->_ref_lit->_view}}
  </td>

{{else}}
  <td class="text">
    {{$_aff->_ref_lit->_view}}
  </td>
  <td class="text">
    {{if $_aff->_ref_next->_id}}
      {{$_aff->_ref_next->_ref_lit->_view}}
    {{/if}}
  </td>
{{/if}}

<td class="text" >
  {{$_aff->_duree}} jour(s)
</td>
