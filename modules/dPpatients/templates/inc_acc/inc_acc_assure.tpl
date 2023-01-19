{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table style="width: 100%">
  <tr>
    <td style="width: 50%">

      <table class="form me-no-box-shadow">
        <tr>
          <th class="category me-text-align-left"  colspan="2">
            {{tr}}CPatient-identite-assure-title{{/tr}}
          </th>
        </tr>
        <tr>
          <td colspan="2" style="text-align: center" class="me-text-align-left">
            <button type="button" class="cancel me-tertiary me-dark" onclick="Patient.delAssureValues()">
              {{tr}}CPatient-action-empty-field-assure{{/tr}}
            </button>
            <button type="button" class="tick me-tertiary" onclick="Patient.copieAssureValues()">
              {{tr}}CPatient-action-recopy-field-assure{{/tr}}
            </button>
          </td>
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="assure_nom"}}
            {{mb_field object=$patient field="assure_nom"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="assure_prenom"}}
            {{mb_field object=$patient field="assure_prenom"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="assure_prenoms"}}
            {{mb_field object=$patient field="assure_prenoms"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="assure_nom_jeune_fille"}}
            {{mb_field object=$patient field="assure_nom_jeune_fille"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field layout=true nb_cells=2 mb_object=$patient mb_field="assure_sexe"}}
            {{mb_field object=$patient field="assure_sexe" typeEnum=radio onchange="Patient.changeCivilite(true);"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="assure_naissance"}}
            {{mb_field object=$patient field="assure_naissance" onchange="Patient.changeCivilite(true);"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="assure_naissance_amo"}}
            {{mb_field object=$patient field="assure_naissance_amo"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="assure_civilite"}}
            {{assign var=civilite_locales value=$patient->_specs.assure_civilite}}
            <select name="assure_civilite">
              <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
              {{foreach from=$civilite_locales->_locales key=key item=curr_civilite}}
                <option value="{{$key}}" {{if $key == $patient->assure_civilite}}selected="selected"{{/if}}>
                  {{tr}}CPatient.civilite.{{$key}}-long{{/tr}} - ({{$curr_civilite}})
                </option>
              {{/foreach}}
            </select>
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="assure_rang_naissance"}}
            {{mb_field object=$patient field=assure_rang_naissance emptyLabel=Select}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="assure_cp_naissance"}}
            {{mb_field object=$patient field="assure_cp_naissance"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="assure_lieu_naissance"}}
            {{mb_field object=$patient field="assure_lieu_naissance"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="_assure_pays_naissance_insee"}}
            {{mb_field object=$patient field="_assure_pays_naissance_insee" class="autocomplete"}}
            <div style="display:none;" class="autocomplete" id="_assure_pays_naissance_insee_auto_complete"></div>
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="assure_profession"}}
            {{mb_field object=$patient field="assure_profession" form=editFrm}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{if $conf.ref_pays == 1}}
            {{me_form_field nb_cells=2 mb_object=$patient mb_field="assure_matricule"}}
              {{mb_field object=$patient field="assure_matricule"}}
            {{/me_form_field}}
          {{/if}}
        </tr>
      </table>
    </td>
    <td>
      <table class="form me-no-box-shadow">
        <tr>
          <th class="category me-text-align-left" colspan="2">{{tr}}CPatient-coordonnees-assure-title{{/tr}}</th>
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="assure_adresse"}}
            {{mb_field object=$patient field="assure_adresse"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="assure_cp"}}
            {{mb_field object=$patient field="assure_cp"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="assure_ville"}}
            {{mb_field object=$patient field="assure_ville"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="assure_pays"}}
            {{mb_field object=$patient field="assure_pays" size="31" class="autocomplete"}}
            <div style="display:none;" class="autocomplete" id="assure_pays_auto_complete"></div>
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="assure_tel"}}
            {{mb_field object=$patient field="assure_tel"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="assure_tel2"}}
            {{mb_field object=$patient field="assure_tel2"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=2 mb_object=$patient mb_field="assure_rques"}}
            {{mb_field object=$patient field="assure_rques" onblur="Patient.tabs.changeTabAndFocus('identite', this.form.nom)"}}
          {{/me_form_field}}
        </tr>
      </table>
    </td>
  </tr>
</table>
