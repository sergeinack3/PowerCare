{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<input type="hidden" name="_category_id" value="{{$consult->categorie_id}}">

<table class="form">
  <tr>
    <th>
      {{mb_label object=$consult field=duree}}
    </th>
    <td id="consult-edit-duree">
      {{mb_include module=planningOp template=dhe/consultation/inc_edit_duree plage_id=$consult->plageconsult_id}}
    </td>
  </tr>

  {{if 'maternite'|module_active && @$modules.maternite->_can->read}}
    <tr>
      <th>
        {{mb_label object=$sejour field=grossesse_id}}
      </th>
      <td>
        {{mb_include module=maternite template=inc_input_grossesse object=$consult patient=$patient}}
      </td>
    </tr>
  {{/if}}
  <tr>
    <th>
      {{mb_label object=$consult field=adresse_par_prat_id}}
    </th>
    <td>
      {{mb_field object=$consult field=adresse_par_prat_id hidden=true onchange="DHE.changeAdressePar('consult'); DHE.consult.syncView(this);" view="_adresse_par_view"}}
      <input type="hidden" name="_adresse_par_view" value="{{if $consult->adresse_par_prat_id}}{{$consult->_ref_adresse_par_prat}}{{/if}}">

      <select name="_correspondants_medicaux" onchange="$V(this.form.adresse_par_prat_id, $V(this));">
        <option value=""{{if !$consult->adresse_par_prat_id}} selected{{/if}}>&mdash; {{tr}}Choose{{/tr}}</option>
        {{if $patient->_ref_medecin_traitant && $patient->_ref_medecin_traitant->_id}}
          <option value="{{$patient->_ref_medecin_traitant->_id}}"{{if $consult->adresse_par_prat_id == $patient->_ref_medecin_traitant->_id}} selected{{/if}} data-view="{{$patient->_ref_medecin_traitant}}">
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

      {{mb_include module=patients template=inc_adresse_par_prat medecin_adresse_par=$consult->_ref_adresse_par_prat}}
    </td>
  </tr>
  <tr>
    <th>
      {{mb_label object=$consult field=chrono}}
    </th>
    <td>
      {{mb_field object=$consult field=chrono onchange="DHE.consult.syncView(this);"}}
    </td>
  </tr>
  <tr>
    <th>
      {{mb_label object=$consult field=visite_domicile}}
    </th>
    <td>
      {{mb_field object=$consult field=visite_domicile onchange="DHE.consult.syncViewFlag(this);"}}
    </td>
  </tr>
  <tr>
    <th>
      {{mb_label object=$consult field=premiere}}
    </th>
    <td>
      {{mb_field object=$consult field=premiere onchange="DHE.consult.syncViewFlag(this);"}}
    </td>
  </tr>
  <tr>
    <th>
      {{mb_label object=$consult field=derniere}}
    </th>
    <td>
      {{mb_field object=$consult field=derniere onchange="DHE.consult.syncViewFlag(this);"}}
    </td>
  </tr>
  <tr>
    <th>
      {{mb_label object=$consult field=si_desistement}}
    </th>
    <td>
      {{mb_field object=$consult field=si_desistement onchange="DHE.consult.syncViewFlag(this);"}}
    </td>
  </tr>

  <tbody id="categories_list">
  </tbody>
</table>
