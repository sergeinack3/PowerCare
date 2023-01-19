{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient value=$_sejour->_ref_patient}}
{{assign var=sejour_guid value=$_sejour->_guid}}
<tr class="sejour-type-default sejour-type-{{$_sejour->type}} {{if !$_sejour->facturable}}non-facturable{{/if}}">
  <td>
    <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')" {{if !$_sejour->entree_reelle }}class="patient-not-arrived"{{/if}}>
      {{$patient}}
    </span>

    {{mb_include module=patients template=inc_status_icon}}
    {{mb_include module=patients template=inc_icon_bmr_bhre}}
  </td>
  <td>{{mb_value object=$patient field=sexe}}</td>
  <td>{{mb_value object=$patient field=_age}}</td>
  <td>
    <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
      {{mb_value object=$_sejour field=entree_prevue}}
    </span>
  </td>
  <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}</td>
  <td>{{$_sejour->_ref_first_affectation->_view}}</td>
  <td class="button">
    <form name="formEntreeReelle{{$sejour_guid}}" method="post" action="?" onsubmit="return onSubmitFormAjax(this, function() { reloadLineSejourAccueil('{{$sejour_guid}}')});">
      {{mb_class object=$_sejour}}
      {{mb_key   object=$_sejour}}
      {{if $_sejour->entree_reelle}}
        {{mb_field object=$_sejour field=entree_reelle form="formEntreeReelle$sejour_guid" register=true onchange="this.form.onsubmit();"}}
      {{else}}
        <input type="hidden" name="entree_reelle" value="now"/>
        <button type="button" class="tick" onclick="this.form.onsubmit();">{{tr}}CSejour-entree_reelle-court{{/tr}}</button>
      {{/if}}
    </form>
  </td>
  <td class="button">
    <form name="formPecAccueil{{$sejour_guid}}" method="post" action="?" onsubmit="return onSubmitFormAjax(this, function() { reloadLineSejourAccueil('{{$sejour_guid}}')});">
      {{mb_class object=$_sejour}}
      {{mb_key   object=$_sejour}}
      {{if $_sejour->pec_accueil}}
        {{mb_field object=$_sejour field=pec_accueil form="formPecAccueil$sejour_guid" register=true onchange="this.form.onsubmit();"}}
      {{else}}
        <input type="hidden" name="pec_accueil" value="now"/>
        <button type="button" class="tick" onclick="this.form.onsubmit();">{{tr}}CSejour-pec_accueil-court{{/tr}}</button>
      {{/if}}
    </form>
  </td>
  <td class="button">
    <form name="formPecService{{$sejour_guid}}" method="post" action="?" onsubmit="return onSubmitFormAjax(this, function() { reloadLineSejourAccueil('{{$sejour_guid}}')});">
      {{mb_class object=$_sejour}}
      {{mb_key   object=$_sejour}}
      {{if $_sejour->pec_service}}
        {{mb_field object=$_sejour field=pec_service form="formPecService$sejour_guid" register=true onchange="this.form.onsubmit();"}}
      {{else}}
        <input type="hidden" name="pec_service" value="now"/>
        <button type="button" class="tick" onclick="this.form.onsubmit();">{{tr}}CSejour-pec_service-court{{/tr}}</button>
      {{/if}}
    </form>
  </td>
</tr>
