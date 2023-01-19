{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th class="halfPane">
      {{mb_label object=$sejour field=service_id}}
    </th>
    <td>
      {{if $sejour->_id && $sejour->_ref_curr_affectation}}
        {{$sejour->_ref_curr_affectation->_ref_service}} - {{$sejour->_ref_curr_affectation}}
      {{else}}
        {{mb_field object=$sejour field=service_id hidden=true onchange="DHE.sejour.syncView(this);" view="_service_view"}}
        <input type="text" name="_service_view" value="{{$sejour->_ref_service}}" onchange="if (!this.value) {$V(this.form.elements['service_entree_id'], '', true);}">
        <button type="button" class="cancel notext" onclick="DHE.emptyField(this.form.service_id)">{{tr}}Empty{{/tr}}</button>
      {{/if}}
    </td>
  </tr>
  <tr id="sejour-edit-occupation">
    <th>
      Taux d'occupation
    </th>
    <td>
      <div id="occupation_rate" style="max-width: 100px;"></div>
    </td>
  </tr>

  {{if $can->admin}}
    <tr>
      <th class="halfPane">
        {{mb_label object=$sejour field=_unique_lit_id}}
      </th>
      <td>
        {{mb_field object=$sejour field=_unique_lit_id hidden=true onchange="DHE.sejour.syncView(this);" view="_unique_lit_view"}}
        <input type="text" name="_unique_lit_view" value="" onchange="if (!this.value) {$V(this.form.elements['_unique_lit_id'], '', true);}">
        <button type="button" class="cancel notext" onclick="DHE.emptyField(this.form._unique_lit_id)">{{tr}}Empty{{/tr}}</button>
      </td>
    </tr>
  {{/if}}

  <tr>
    <th class="halfPane">
      {{mb_label object=$sejour field=uf_hebergement_id}}
    </th>
    <td>
      {{mb_field object=$sejour field=uf_hebergement_id hidden=true onchange="DHE.sejour.syncView(this);" view="_uf_hebergement_view"}}
      <input type="text" name="_uf_hebergement_view" value="{{$sejour->_ref_uf_hebergement}}">
      <button type="button" class="cancel notext" onclick="DHE.emptyField(this.form.uf_hebergement_id)">{{tr}}Empty{{/tr}}</button>
    </td>
  </tr>
  <tr>
    <th class="halfPane">
      {{mb_label object=$sejour field=uf_soins_id}}
    </th>
    <td>
      {{mb_field object=$sejour field=uf_soins_id hidden=true onchange="DHE.sejour.syncView(this);" view="_uf_soins_view"}}
      <input type="text" name="_uf_soins_view" value="{{$sejour->_ref_uf_soins}}">
      <button type="button" class="cancel notext" onclick="DHE.emptyField(this.form.uf_soins_id)">{{tr}}Empty{{/tr}}</button>
    </td>
  </tr>
  <tr>
    <th class="halfPane">
      {{mb_label object=$sejour field=uf_medicale_id}}
    </th>
    <td>
      {{mb_field object=$sejour field=uf_medicale_id hidden=true onchange="DHE.sejour.syncView(this);" view="_uf_medicale_view"}}
      <input type="text" name="_uf_medicale_view" value="{{$sejour->_ref_uf_medicale}}">
      <button type="button" class="cancel notext" onclick="DHE.emptyField(this.form.uf_medicale_id)">{{tr}}Empty{{/tr}}</button>
    </td>
  </tr>

  <tr>
    <th class="halfPane">
      {{mb_label object=$sejour field=facturable}}
    </th>
    <td>
      {{mb_field object=$sejour field=facturable onchange="DHE.sejour.syncViewFlag(this, null, ['0']);"}}
    </td>
  </tr>

  <tr>
    <th class="halfPane">
      {{mb_label object=$sejour field=aide_organisee}}
    </th>
    <td>
      {{mb_field object=$sejour field=aide_organisee onchange="DHE.sejour.syncViewFlag(this, \$T('CSejour-aide_organisee') + ': ' + \$T('CSejour.aide_organisee.' + \$V(this)));"}}
    </td>
  </tr>
  <tr>
    <th class="halfPane">
      {{mb_label object=$sejour field=lit_accompagnant}}
    </th>
    <td>
      {{mb_field object=$sejour field=lit_accompagnant onchange="DHE.sejour.syncViewFlag(this);"}}
    </td>
  </tr>
  <tr>
    <th class="halfPane">
      {{mb_label object=$sejour field=television}}
    </th>
    <td>
      {{mb_field object=$sejour field=television onchange="DHE.sejour.syncViewFlag(this);"}}
    </td>
  </tr>

  {{if 'dPhospi prestations systeme_prestations'|gconf == 'standard'}}
    <tr>
      <th class="halfPane">
        {{mb_label object=$sejour field=chambre_seule}}
      </th>
      <td>
        {{mb_field object=$sejour field=chambre_seule onchange="DHE.sejour.syncViewFlag(this);"}}
      </td>
    </tr>
    <tr>
      <th class="halfPane">
        {{mb_label object=$sejour field=prestation_id}}
      </th>
      <td>
        <select name="prestation_id" style="width: 15em;" onchange="DHE.sejour.syncView(this);">
          <option value="">{{tr}}Choose{{/tr}}</option>
          {{foreach from=$prestations item=_presta}}
            <option value="{{$_presta->_id}}"{{if $sejour->prestation_id == $_presta->_id}} selected{{/if}}>
              {{$_presta}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
  {{elseif $sejour->_id}}
    <tr>
      <th class="halfPane">Prestations</th>
      <td>
        <button type="button" class="edit notext" onclick="Prestations.edit('{{$sejour->_id}}');">{{tr}}Edit{{/tr}}</button>
      </td>
    </tr>
  {{/if}}

  <tr>
    <th class="halfPane">
      Régime alimentaire
    </th>
    <td>
      <button class="edit notext" onclick="DHE.sejour.openDiet();">{{tr}}Edit{{/tr}}</button>
    </td>
  </tr>
</table>

{{assign var=fields value='|'|explode:'hormone_croissance|repas_sans_sel|repas_sans_porc|repas_diabete|repas_sans_residu'}}
<div id="sejour-regime_alimentaire" style="display: none;">
  <table class="form">
    {{foreach from=$fields item=_field}}
      <tr>
        <th>
          {{mb_label object=$sejour field=$_field}}
        </th>
        <td>
          {{mb_field object=$sejour field=$_field}}
        </td>
      </tr>
    {{/foreach}}
    <tr>
      <td class="button" colspan="2">
        <button type="button" class="tick" onclick="Control.Modal.close();">{{tr}}Validate{{/tr}}</button>
      </td>
    </tr>
  </table>
</div>