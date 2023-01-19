{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$sejour->_id}}
  <script>
    Main.add(function() {
      Modal.alert('Le numéro de dossier que vous avez saisi ne correspond à aucun séjour', {width: 400});
      $('sejour_new_line').remove();
      $('list-sejours').insert('<tr id="sejour_new_line" style="display: none;"></tr>');
    });
  </script>

  {{mb_return}}
{{/if}}

<script>
  Main.add(function() {
    var line = $('sejour_new_line');

    line.id = line.firstDescendant().readAttribute('data-guid');
    line.addClassName('sejour');
    line.show();

    $('list-sejours').insert('<tr id="sejour_new_line" style="display: none;"></tr>');

    if ($$('tr.sejour').length > 0) {
      $('sejour_empty').hide();
    }
  });
</script>

<td data-guid="{{$sejour->_guid}}">
  <span class="CSejour-view" onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');">
    [{{mb_value object=$sejour field=_NDA}}]
  </span>
</td>
<td>
  {{mb_value object=$sejour field=entree_reelle}}
</td>
<td>
  {{mb_value object=$sejour field=sortie_reelle}}
</td>
<td>
  <span class="CPatient-view" onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_ref_patient->_guid}}');">
    {{$sejour->_ref_patient}}
  </span>
</td>
<td>
  {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$sejour->_ref_praticien}}
</td>
<td>
  {{if in_array($sejour->type, 'Ox\Mediboard\PlanningOp\CSejour::getTypesSejoursUrgence'|static_call:$sejour->praticien_id)}}
    <a class="fa fa-ambulance event-icon" style="background-color: steelblue; float: right;" title="Passage aux urgences"></a>
  {{/if}}

  <span class="CSejour-view" onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');">
    {{$sejour->_shortview}}
  </span>

  <div class="compact text">
    {{$sejour->_motif_complet}}
  </div>
</td>
<td class="narrow">
  <button type="button" class="cancel notext" onclick="deleteLineSejour('{{$sejour->_guid}}');" title="Supprimer cette ligne"></button>
</td>
