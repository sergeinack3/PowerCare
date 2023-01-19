{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th class="halfPane">
      {{mb_label object=$sejour field=DP}}
    </th>
    <td>
      <input type="text" name="_DP_view" value="{{$sejour->DP}}" style="width: 10em;" data-libelle="{{if $sejour->DP}}{{$sejour->_ext_diagnostic_principal->libelle}}{{/if}}">
      <button type="button" class="cancel notext" onclick="DHE.emptyField(this.form.DP);">{{tr}}Empty{{/tr}}</button>
      <button type="button" class="search notext" onclick="">{{tr}}button-CCodeCIM10-choix{{/tr}}</button>
      <input type="hidden" name="DP" value="{{$sejour->DP}}" onchange="DHE.sejour.syncViewDiagnostic(this);" data-libelle="{{if $sejour->DP}}{{$sejour->_ext_diagnostic_principal->libelle}}{{/if}}" data-view="_DP_view">
    </td>
  </tr>
  <tr>
    <th class="halfPane">
      {{mb_label object=$sejour field=DR}}
    </th>
    <td>
      <input type="text" name="_DR_view" value="{{$sejour->DR}}" style="width: 10em;" data-libelle="{{if $sejour->DR}}{{$sejour->_ext_diagnostic_relie->libelle}}{{/if}}">
      <button type="button" class="cancel notext" onclick="DHE.emptyField(this.form.DR);">{{tr}}Empty{{/tr}}</button>
      <button type="button" class="search notext" onclick="">{{tr}}button-CCodeCIM10-choix{{/tr}}</button>
      <input type="hidden" name="DR" value="{{$sejour->DR}}" onchange="DHE.sejour.syncViewDiagnostic(this);" data-libelle="{{if $sejour->DR}}{{$sejour->_ext_diagnostic_relie->libelle}}{{/if}}" data-view="_DR_view">
    </td>
  </tr>
  {{* @todo: Gérer l'affichage des diagnostics associées, stockés dans le CDossierMedical relié au séjour *}}
</table>