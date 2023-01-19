{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if ((!$curr_sejour->pathologie && $affichage_patho=="non_complet") || ($affichage_patho=="tous") ) }}
  <tr>
    <td class="patient" colspan="2" style="background:#{{$curr_sejour->_ref_praticien->_ref_function->color}}">
      {{if $curr_sejour->pathologie}}
        {{me_img src="tick.png" icon="tick" class="me-success"}}
      {{else}}
        {{me_img src="cross.png" icon="cancel" class="me-error"}}
      {{/if}}

      <strong>{{$curr_sejour->_ref_patient}}</strong>
      {{if in_array($curr_sejour->type, array("ambu", "exte"))}}
        ({{$curr_sejour->type|truncate:1:""|capitalize}})
      {{else}}
        ({{$curr_sejour->_duree_prevue}}j)
      {{/if}}
      {{if $curr_sejour->_couvert_c2s || $curr_sejour->_couvert_ald}}
        <div style="float: right;"><strong>
            {{if $curr_sejour->_couvert_c2s}}
              C2S
            {{/if}}
            {{if $curr_sejour->_couvert_ald}}
              ALD
            {{/if}}
          </strong></div>
      {{/if}}
    </td>
  </tr>
  <tr>
    <td class="text" colspan="2" style="background:#{{$curr_sejour->_ref_praticien->_ref_function->color}}"><em>Age</em>
      : {{$curr_sejour->_ref_patient->_age}}</td>
  </tr>
  <tr>
    <td class="text" colspan="2" style="background:#{{$curr_sejour->_ref_praticien->_ref_function->color}}">
      <em>Dr {{$curr_sejour->_ref_praticien}}</em></td>
  </tr>
  <tr>
    <td class="text" colspan="2">
      {{foreach from=$curr_sejour->_ref_operations item=curr_operation}}
        {{if $curr_operation->libelle}}
          <em>[{{$curr_operation->libelle}}]</em>
          <br />
        {{/if}}
        {{foreach from=$curr_operation->_ext_codes_ccam item=curr_code}}
          <em>{{$curr_code->code}}</em>
          : {{$curr_code->libelleLong}}
          <br />
        {{/foreach}}
      {{/foreach}}
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <em>Entrée</em> : {{$curr_sejour->entree_prevue|date_format:"%A %d %B %Hh%M"}}<br />
      <em>Sortie</em> : {{$curr_sejour->sortie_prevue|date_format:"%A %d %B %Hh%M"}}
    </td>
  </tr>
  <tr>
    <td class="text">
      <form id="sejourForm-{{$curr_sejour->sejour_id}}" name="sejourForm-{{$curr_sejour->sejour_id}}" method="post">
        <input type="hidden" name="m" value="planningOp" />
        <input type="hidden" name="otherm" value="hospi" />
        <input type="hidden" name="dosql" value="do_sejour_aed" />
        <input type="hidden" name="sejour_id" value="{{$curr_sejour->sejour_id}}" />
        <em>Pathologie:</em>
        <select name="pathologie">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$pathos->_specs.categorie->_locales item=curr_patho}}
            <option value="{{$curr_patho}}" {{if $curr_patho == $curr_sejour->pathologie}}selected{{/if}}>
              {{$curr_patho}}
            </option>
          {{/foreach}}
        </select>
        <br />
        <input type="radio" name="septique" value="0" {{if $curr_sejour->septique == 0}}checked{{/if}} />
        <label for="septique_0" title="Intervention propre">Propre</label>
        <input type="radio" name="septique" value="1" {{if $curr_sejour->septique == 1}}checked{{/if}} />
        <label for="septique_1" title="Séjour septique">Septique</label>
        <button type="button"
                onclick="onSubmitFormAjax(this.form, reloadPatient.curry($('sejourForm-{{$curr_sejour->sejour_id}}')));"
                class="submit">{{tr}}Validate{{/tr}}</button>
      </form>
    </td>
  </tr>
  <!-- Affichage des remarques -->

  {{assign var=operations_rques value=false}}
  {{foreach from=$curr_sejour->_ref_operations item=curr_operation}}
    {{if $curr_operation->rques}}
      {{assign var=operations_rques value=true}}
    {{/if}}
  {{/foreach}}

  {{if $curr_sejour->rques || $operations_rques || $curr_sejour->_ref_patient->rques}}
    <tr>
      <td class="text">
        {{if $curr_sejour->rques}}
          <em>Séjour</em>
          : {{$curr_sejour->rques|nl2br}}
          <br />
        {{/if}}
        {{foreach from=$curr_sejour->_ref_operations item=curr_operation}}
          {{if $curr_operation->rques}}
            <em>Intervention</em>
            : {{$curr_operation->rques|nl2br}}
            <br />
          {{/if}}
        {{/foreach}}
        {{if $curr_sejour->_ref_patient->rques}}
          <em>Patient</em>
          : {{$curr_sejour->_ref_patient->rques|nl2br}}
          <br />
        {{/if}}
      </td>
    </tr>
  {{/if}}

{{/if}}
