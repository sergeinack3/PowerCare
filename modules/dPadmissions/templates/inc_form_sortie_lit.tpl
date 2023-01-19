{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th>Lit</th>
  <td colspan="3">
    <select name="lit_id" style="width: 15em;" onchange="Admissions.choisirLit(this);">
      <option value="">&mdash; Choisir Lit </option>
      {{foreach from=$lits item=_lit}}
        {{assign var=chambre value=$_lit->_ref_chambre}}
        {{assign var=service value=$chambre->_ref_service}}
        <option id="{{$_lit->_guid}}" value="{{$_lit->lit_id}}" data-service_id="{{$service->_id}}" data-name="{{$service->nom}}"
                {{if $_lit->_view|strpos:"bloqué"}}disabled{{/if}}
                {{if $_lit->lit_id == $sejour->_ref_patient->_ref_curr_affectation->lit_id}}selected{{/if}}>
          {{$_lit->_view}}
        </option>
      {{/foreach}}
    </select>
  </td>
</tr>