{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr id="lit_sortie_transfert" {{if !$rpu->mutation_sejour_id && $sejour->mode_sortie != "mutation"}} style="display:none;" {{/if}}>
  <th>Lit</th>
  <td>
    <select name="lit_id" style="width: 20em;" onchange="Fields.modif(this.value);"  >
      <option value="">&mdash; Choisir Lit </option>
      {{foreach from=$blocages_lit item=blocage_lit}}
        <option id="{{$blocage_lit->_ref_lit->_guid}}" value="{{$blocage_lit->lit_id}}"
                class="{{$blocage_lit->_ref_lit->_ref_chambre->_ref_service->_guid}}-{{$blocage_lit->_ref_lit->_ref_chambre->_ref_service->nom}}"
                {{if $blocage_lit->_ref_lit->_view|strpos:"indisponible"}}disabled{{/if}}
          {{if $blocage_lit->lit_id == $sejour->_ref_curr_affectation->lit_id}}selected{{/if}}>
          {{$blocage_lit->_ref_lit->_view}}
        </option>
      {{/foreach}}
    </select>
  </td>
</tr>