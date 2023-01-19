{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="20">
      <form name="exportVacationDetails" action="?" method="get" target="_blank">
        <input type="hidden" name="m" value="stats" />
        <input type="hidden" name="a" value="ajax_vacation_details" />
        <input type="hidden" name="export" value="csv" />
        <input type="hidden" name="suppressHeaders" value="1" />
        <input type="hidden" name="plage_id" value="{{$plage->_id}}" />

        {{mb_field object=$stats field=operations_to_display hidden=true}}
        {{mb_field object=$stats field=plages_to_display hidden=true}}

        <button type="submit" class="download" style="float: right;">{{tr}}common-action-Export{{/tr}}</button>
      </form>
      Détails des opérations de la vacation du
      {{mb_value object=$plage field=date}} de {{mb_value object=$plage field=debut}} à {{mb_value object=$plage field=fin}}
    </th>
  </tr>
  <tr>
    <th class="narrow" colspan="5">
      {{tr}}COperation{{/tr}}
    </th>
    <th class="narrow" colspan="4">
      {{tr}}CPatient{{/tr}}
    </th>
    <th class="narrow" colspan="5">
      {{tr}}CSejour{{/tr}}
    </th>
    <th class="narrow" rowspan="2">
      {{mb_title class=COperation field=type_anesth}}
    </th>
    <th class="narrow" rowspan="2">
      {{mb_title class=COperation field=ASA}}
    </th>
    <th class="narrow" rowspan="2">
      {{mb_title class=COperation field=entree_reveil}}
    </th>
    <th class="narrow" rowspan="2">
      {{mb_title class=COperation field=sortie_reveil_reel}}
    </th>
    <th class="narrow" rowspan="2">
      {{mb_title class=COperation field=entree_salle}}
    </th>
    <th class="narrow" rowspan="2">
      {{mb_title class=COperation field=sortie_salle}}
    </th>
  </tr>
  <tr>
    <th class="narrow">
      {{mb_title class=COperation field=date}}
    </th>
    <th class="narrow">
      {{mb_title class=COperation field=libelle}}
    </th>
    <th class="narrow">
      {{mb_title class=COperation field=salle_id}}
    </th>
    <th class="narrow">
      {{mb_title class=COperation field=chir_id}}
    </th>
    <th class="narrow">
      {{mb_title class=COperation field=anesth_id}}
    </th>
    <th class="narrow">
      {{mb_title class=CSejour field=patient_id}}
    </th>
    <th class="narrow">
      {{mb_title class=CPatient field=_age}}
    </th>
    <th class="narrow">
      {{mb_title class=CConstantesMedicales field=poids}}
    </th>
    <th class="narrow">
      {{mb_title class=CConstantesMedicales field=taille}}
    </th>
    <th class="narrow">
      {{mb_title class=CSejour field=_NDA}}
    </th>
    <th class="narrow">
      {{mb_title class=CSejour field=type}}
    </th>
    <th class="narrow">
      {{mb_title class=CSejour field=_duree}}
    </th>
    <th class="narrow">
      {{mb_title class=CSejour field=entree}}
    </th>
    <th class="narrow">
      {{mb_title class=CSejour field=sortie}}
    </th>
  </tr>
  {{foreach from=$results item=_operation}}
    {{assign var=_patient value=$_operation->_ref_patient}}
    {{assign var=_sejour value=$_operation->_ref_sejour}}
    <tr class="alternate">
      <td class="narrow">
        {{mb_value object=$_operation field=date}}
      </td>
      <td class="narrow text">
        {{mb_value object=$_operation field=libelle}}
      </td>
      <td class="narrow">
        {{$_operation->_ref_salle}}
      </td>
      <td class="narrow">
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_chir}}
      </td>
      <td class="narrow">
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_anesth}}
      </td>
      <td class="narrow">
        {{$_patient}}
      </td>
      <td class="narrow">
        {{mb_value object=$_patient field=_age}}
      </td>
      <td class="narrow">
        {{mb_value object=$_patient field=_poids}}
      </td>
      <td class="narrow">
        {{mb_value object=$_patient field=_taille}}
      </td>
      <td class="narrow">
        {{mb_value object=$_sejour field=_NDA}}
      </td>
      <td class="narrow">
        {{mb_value object=$_sejour field=type}}
      </td>
      <td class="narrow">
        {{mb_value object=$_sejour field=_duree}} {{tr}}{{if $_sejour->_duree > 1}}days{{else}}day{{/if}}{{/tr}}
      </td>
      <td class="narrow">
        {{if $_sejour->entree_reelle}}
          {{mb_value object=$_sejour field=entree_reelle}}
        {{else}}
          {{mb_value object=$_sejour field=entree_prevue}}
        {{/if}}
      </td>
      <td class="narrow">
        {{if $_sejour->entree_reelle}}
          {{mb_value object=$_sejour field=sortie_reelle}}
        {{else}}
          {{mb_value object=$_sejour field=sortie_prevue}}
        {{/if}}
      </td>
      <td class="narrow">
        {{mb_value object=$_operation field=type_anesth}}
      </td>
      <td class="narrow">
        {{mb_value object=$_operation field=ASA}}
      </td>
      <td class="narrow">
        {{mb_value object=$_operation field=entree_reveil}}
      </td>
      <td class="narrow">
        {{if $_operation->sortie_reveil_reel}}
          {{mb_value object=$_operation field=sortie_reveil_reel}}
        {{else}}
          {{mb_value object=$_operation field=sortie_reveil_possible}}
        {{/if}}
      </td>
      <td class="narrow">
        {{mb_value object=$_operation field=entree_salle}}
      </td>
      <td class="narrow">
        {{mb_value object=$_operation field=sortie_salle}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" style="text-align: center;" colspan="22">
        {{tr}}No result{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>