{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=_props_mode_entree value=$sejour->_props.mode_entree}}
{{if 'dPplanningOp CSejour required_mode_entree'|gconf}}
  {{assign var=_props_mode_entree value="$_props_mode_entree notNull"}}
{{/if}}

<div id="sejour-edit-duree" style="margin-top: 5px;">
  <table class="form">
    <tr>
      <th style="width: 50%;">
        {{mb_label object=$sejour field=_duree_prevue}}
      </th>
      <td>
        <span id="sejour-edit-duree-unit-nights"{{if !$sejour->_duree_prevue}} style="display: none;"{{/if}}>
          {{mb_field object=$sejour field=_duree_prevue increment=true form=sejourEdit size=2 prop='num min|0' onchange="DHE.sejour.setAdmissionDates(this, 'edit'); DHE.sejour.loadListSejour();"}}
          nuit(s)
        </span>
        <span id="sejour-edit-duree-unit-hours"{{if $sejour->_duree_prevue_heure <= 0}} style="display: none;"{{/if}}>
          {{mb_field object=$sejour field=_duree_prevue_heure increment=true form=sejourEdit size=2 prop='num min|0 max|23' onchange="DHE.sejour.setAdmissionDates(this, 'edit'); DHE.sejour.loadListSejour();"}}
          heure(s)
        </span>
        &mdash; <span id="sejour-edit-view-days">{{$sejour->_duree_prevue + 1}}</span> jour(s)
      </td>
    </tr>
  </table>
</div>

<span id="sejour-edit-entree" style="width: 50%; display: inline-block; vertical-align: top;">
  <fieldset style="height: 220px;">
    <legend>Entr�e</legend>
    <table class="form">
      <tr>
        <th>
          {{mb_label object=$sejour field=entree_prevue}}
        </th>
        <td>
          {{mb_field object=$sejour field=entree_prevue form="sejourEdit" register=true onchange="DHE.sejour.setAdmissionDates(this, 'edit'); DHE.sejour.loadListSejour();"}}
        </td>
      </tr>
      {{if $sejour->entree_reelle}}
        <tr>
          <th>
            {{mb_label object=$sejour field=entree_reelle}}
          </th>
          <td>
            {{mb_value object=$sejour field=entree_reelle}}
          </td>
        </tr>
      {{/if}}

      <tr>
        <th>
          {{mb_label object=$sejour field=presence_confidentielle}}
        </th>
        <td>
          {{mb_field object=$sejour field=presence_confidentielle onchange="DHE.sejour.syncViewFlag(this);"}}
        </td>
      </tr>

      <tr>
        <th>{{mb_label object=$sejour field=mode_entree prop=$_props_mode_entree}}</th>
        <td>
          {{if $conf.dPplanningOp.CSejour.use_custom_mode_entree && $modes_entree|@count}}
            {{mb_field object=$sejour field=mode_entree hidden=true prop=$_props_mode_entree onchange="DHE.sejour.syncView(this); DHE.sejour.changeModeEntree();"}}

            <select name="mode_entree_id" class="{{$sejour->_props.mode_entree_id}}" style="width: 15em;" onchange="DHE.sejour.syncView(this, this.down('option:selected').innerHTML); $V(this.form.elements['mode_entree'], this.down('option:selected').get('mode'), true); $V(this.form.elements['provenance'], this.down('option:selected').get('provenance'), true);">
              <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
              {{foreach from=$modes_entree item=_mode}}
                <option value="{{$_mode->_id}}" data-mode="{{$_mode->mode}}" data-provenance="{{$_mode->provenance}}"
                        {{if $sejour->mode_entree_id == $_mode->_id}}selected{{/if}}>
                  {{$_mode}}
                </option>
              {{/foreach}}
            </select>
          {{else}}
            {{mb_field object=$sejour field=mode_entree onchange="DHE.sejour.syncView(this); DHE.sejour.changeModeEntree();" typeEnum=radio prop=$_props_mode_entree}}
          {{/if}}
        </td>
      </tr>

      <tbody id="sejour-entree-fields-transfert"{{if $sejour->mode_entree != '7'}} style="display: none;"{{/if}}>
        <tr>
          <th>
            {{mb_label object=$sejour field=etablissement_entree_id}}
          </th>
          <td>
            {{mb_field object=$sejour field=etablissement_entree_id hidden=true onchange="DHE.sejour.syncView(this);" view="_etablissement_entree_view"}}
            <input type="text" name="_etablissement_entree_view" value="{{$sejour->_ref_etablissement_provenance}}">
            <button type="button" class="cancel notext" onclick="DHE.emptyField(this.form.etablissement_entree_id)">{{tr}}Empty{{/tr}}</button>
          </td>
        </tr>

        {{assign var=provenance_props value=$sejour->_props.provenance}}
        {{assign var=provenance_obligatory value='dPadmissions admission provenance_transfert_obligatory'|gconf}}
        {{if $provenance_obligatory}}
          {{assign var=provenance_props value="$provenance_props notNull"}}
        {{/if}}
        <tr>
          <th>
            {{mb_label object=$sejour field=provenance props=$provenance_props}}
          </th>
          <td>
            {{mb_field object=$sejour field=provenance onchange="DHE.sejour.syncView(this);" emptyLabel='Choose' style='width: 15em;' props=$provenance_props}}
          </td>
        </tr>

        {{assign var=date_entree_provenance_props value=$sejour->_props.date_entree_reelle_provenance}}
        {{assign var=date_entree_provenance_obligatory value='dPadmissions admission date_entree_transfert_obligatory'|gconf}}
        {{if $date_entree_provenance_obligatory}}
          {{assign var=date_entree_provenance_props value="$date_entree_provenance_props notNull"}}
        {{/if}}
        <tr>
          <th>
            {{mb_label object=$sejour field=date_entree_reelle_provenance props=$date_entree_provenance_props}}
          </th>
          <td>
            {{mb_field object=$sejour field=date_entree_reelle_provenance onchange="DHE.sejour.syncView(this);" form='sejourEdit' register=true props=$date_entree_provenance_props}}
          </td>
        </tr>
      </tbody>

      <tbody id="sejour-entree-fields-mutation"{{if $sejour->mode_entree != '6'}} style="display: none;"{{/if}}>
        <tr>
          <th>
            {{mb_label object=$sejour field=service_entree_id}}
          </th>
          <td>
            {{mb_field object=$sejour field=service_entree_id hidden=true onchange="DHE.sejour.syncView(this);" view="_service_entree_view"}}
            <input type="text" name="_service_entree_view" value="{{$sejour->_ref_service_provenance}}" onchange="if (!this.value) {$V(this.form.elements['service_entree_id'], '', true);}">
            <button type="button" class="cancel notext" onclick="DHE.emptyField(this.form.service_entree_id)">{{tr}}Empty{{/tr}}</button>
          </td>
        </tr>
      </tbody>

      <tr>
        <th>
          {{mb_label object=$sejour field=adresse_par_prat_id}}
        </th>
        <td>
          {{mb_field object=$sejour field=adresse_par_prat_id hidden=true onchange="DHE.changeAdressePar('sejour'); DHE.sejour.syncView(this);" view="_adresse_par_view"}}
          <input type="hidden" name="_adresse_par_view" value="{{if $sejour->adresse_par_prat_id}}{{$sejour->_ref_adresse_par_prat}}{{/if}}">

          <select name="_correspondants_medicaux" onchange="$V(this.form.adresse_par_prat_id, $V(this));">
            <option value=""{{if !$sejour->adresse_par_prat_id}} selected{{/if}}>&mdash; {{tr}}Choose{{/tr}}</option>
            {{if $patient->_ref_medecin_traitant && $patient->_ref_medecin_traitant->_id}}
              <option value="{{$patient->_ref_medecin_traitant->_id}}"{{if $sejour->adresse_par_prat_id == $patient->_ref_medecin_traitant->_id}} selected{{/if}} data-view="{{$patient->_ref_medecin_traitant}}">
                Traitant : {{$patient->_ref_medecin_traitant}}
              </option>
            {{/if}}
            {{foreach from=$patient->_ref_medecins_correspondants item=_medecin}}
              <option value="{{$_medecin->_ref_medecin->_id}}"{{if $sejour->adresse_par_prat_id == $_medecin->_ref_medecin->_id}} selected{{/if}} data-view="{{$_medecin->_ref_medecin}}">
                {{$_medecin->_ref_medecin}}
              </option>
            {{/foreach}}
          </select>
          <button type="button" class="search notext" onclick="Medecin.edit(this.form);">Autres</button>

          {{mb_include module=patients template=inc_adresse_par_prat medecin_adresse_par=$sejour->_ref_adresse_par_prat}}
        </td>
      </tr>
    </table>
  </fieldset>
</span>

<span id="sejour-edit-sortie" style="width: 49%; display: inline-block; vertical-align: top;">
  <fieldset style="height: 220px;">
    <legend>Sortie</legend>
    <table class="form">
      <tr>
        <th>
          {{mb_label object=$sejour field=sortie_prevue}}
        </th>
        <td>
          {{mb_field object=$sejour field=sortie_prevue form="sejourEdit" register=true onchange="DHE.sejour.setAdmissionDates(this, 'edit'); DHE.sejour.loadListSejour();"}}
        </td>
      </tr>
      {{if $sejour->sortie_reelle}}
        <tr>
          <th>
            {{mb_label object=$sejour field=sortie_reelle}}
          </th>
          <td>
            {{mb_value object=$sejour field=sortie_reelle}}
          </td>
        </tr>
      {{/if}}

      <tr>
        <th>
          {{mb_label object=$sejour field=mode_sortie prop=$_props_mode_entree}}
        </th>
        <td>
          {{if $conf.dPplanningOp.CSejour.use_custom_mode_sortie && $modes_sortie|@count}}
            {{mb_field object=$sejour field=mode_sortie hidden=true onchange="DHE.sejour.syncView(this); DHE.sejour.changeModeEntree();"}}

            <select name="mode_sortie_id" class="{{$sejour->_props.mode_sortie_id}}" style="width: 15em;" onchange="DHE.sejour.syncView(this, this.down('option:selected').innerHTML); $V(this.form.elements['mode_entree'], this.down('option:selected').get('mode'), true);">
              <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
              {{foreach from=$modes_sortie item=_mode}}
                <option value="{{$_mode->_id}}" data-mode="{{$_mode->mode}}" data-destination="{{$_mode->destination}}" data-orientation="{{$_mode->orientation}}"{{if $sejour->mode_sortie_id == $_mode->_id}}selected{{/if}}>
                  {{$_mode}}
                </option>
              {{/foreach}}
            </select>
          {{else}}
            {{mb_field object=$sejour field=mode_sortie onchange="DHE.sejour.syncView(this); DHE.sejour.changeModeSortie();" typeEnum=radio}}
          {{/if}}
        </td>
      </tr>
      <tr id="sejour-edit-etablissement_sortie_id"{{if $sejour->mode_sortie != 'transfert'}} style="display: none;"{{/if}}>
        <th>
          {{mb_label object=$sejour field=etablissement_sortie_id}}
        </th>
        <td>
          {{mb_field object=$sejour field=etablissement_sortie_id hidden=true onchange="DHE.sejour.syncView(this);" view="_etablissement_sortie_view"}}
          <input type="text" name="_etablissement_sortie_view" value="{{$sejour->_ref_etablissement_transfert}}">
          <button type="button" class="cancel notext" onclick="DHE.emptyField(this.form.etablissement_sortie_id)">{{tr}}Empty{{/tr}}</button>
        </td>
      </tr>
      <tr id="sejour-edit-service_sortie_id"{{if $sejour->mode_sortie != 'mutation'}} style="display: none;"{{/if}}>
        <th>
          {{mb_label object=$sejour field=service_sortie_id}}
        </th>
        <td>
          {{mb_field object=$sejour field=service_sortie_id hidden=true onchange="DHE.sejour.syncView(this);" view="_service_sortie_view"}}
          <input type="text" name="_service_sortie_view" value="{{$sejour->_ref_service_mutation}}" onchange="if (!this.value) {$V(this.form.elements['service_sortie_id'], '', true);}">
          <button type="button" class="cancel notext" onclick="DHE.emptyField(this.form.service_sortie_id)">{{tr}}Empty{{/tr}}</button>
        </td>
      </tr>
      <tbody id="sejour-edit-transport_sortie"{{if $sejour->mode_sortie == 'mutation'}} style="display: none;"{{/if}}>
        <tr>
          <th>
            {{mb_label object=$sejour field=transport_sortie}}
          </th>
          <td>
            {{mb_field object=$sejour field=transport_sortie onchange="DHE.sejour.syncView(this);"}}
          </td>
        </tr>
        <tr>
          <th>
            {{mb_label object=$sejour field=rques_transport_sortie}}
          </th>
          <td>
            {{mb_field object=$sejour field=rques_transport_sortie}}
          </td>
        </tr>
      </tbody>
      <tr>
        <th>
          {{mb_label object=$sejour field=commentaires_sortie}}
        </th>
        <td>
          {{mb_field object=$sejour field=commentaires_sortie}}
        </td>
      </tr>
      <tr>
        <th>
          {{mb_label object=$sejour field=destination}}
        </th>
        <td>
          {{mb_field object=$sejour field=destination onchange="DHE.sejour.syncView(this);" emptyLabel='Choose'}}
        </td>
      </tr>
      <tr id="sejour-edit-_date_deces"{{if $sejour->mode_sortie != 'deces'}} style="display: none;"{{/if}}>
        <th>
          {{mb_label object=$sejour field=_date_deces}}
        </th>
        <td>
          {{mb_field object=$sejour field=_date_deces form=sejourEdit register=true onchange="DHE.sejour.syncView(this);"}}
        </td>
      </tr>
    </table>
  </fieldset>
</span>
