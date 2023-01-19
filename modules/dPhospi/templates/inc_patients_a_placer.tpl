{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="calendar-container" class="me-small-calendar"></div>

{{if !$can->edit}}
  {{mb_return}}
{{/if}}

<form name="chgFilter" method="get" onsubmit="return onSubmitFormAjax(this, null, 'tableau');">
  <input type="hidden" name="m" value="hospi" />
  <input type="hidden" name="a" value="vw_affectations" />
  <table class="form me-no-box-shadow me-no-align me-margin-top-8">
    <tr>
      <td class="me-padding-left-0 me-padding-right-0">
        {{mb_field object=$emptySejour field="_type_admission" style="width: 16em;" class="me-w100" onchange="this.form.onsubmit()"}}
      </td>
    </tr>
    <tr>
      <td class="me-padding-left-0 me-padding-right-0">
        <select name="triAdm" style="width: 16em;" onchange="this.form.onsubmit()" class="me-w100">
          <option value=""> &mdash; {{tr}}Choose{{/tr}}</option>
          <option value="praticien" {{if $triAdm == "praticien"}} selected{{/if}}>Tri par praticien</option>
          <option value="date_entree" {{if $triAdm == "date_entree"}}selected{{/if}}>Tri par heure d'entrée</option>
          <option value="patient" {{if $triAdm == "patient"}} selected{{/if}}>Tri par patient</option>
        </select>
      </td>
    </tr>
    <tr>
      <td class="me-padding-left-0 me-padding-right-0">
        <select name="filterFunction" style="width: 16em;" onchange="this.form.onsubmit()" class="me-w100">
          <option value=""> &mdash; Toutes les fonctions</option>
          {{mb_include module=mediusers template=inc_options_function list=$functions_filter selected=$filterFunction}}
        </select>
      </td>
    </tr>
    {{if $systeme_presta == "expert" && $prestations_journalieres|@count}}
      <tr>
        <td class="me-padding-left-0 me-padding-right-0">
          <select name="prestation_id" style="width: 16em;" class="me-w100"
                  onchange="Prestation.savePrestationIdHospiPref(this.value, function() {this.form.onsubmit();}.bind(this));">
            <option value="" {{if !$prestation_id}}selected{{/if}}>&mdash; {{tr}}None{{/tr}}</option>
            <option value="all" {{if $prestation_id == "all"}}selected{{/if}}>{{tr}}All{{/tr}}</option>
            {{foreach from=$prestations_journalieres item=_prestation}}
              <option value="{{$_prestation->_id}}" {{if $_prestation->_id == $prestation_id}}selected{{/if}}>{{$_prestation}}</option>
            {{/foreach}}
          </select>
        </td>
      </tr>
    {{/if}}
  </table>
</form>

<form name="addAffectationsejour" action="?m={{$m}}" method="post">
  <input type="hidden" name="m" value="hospi" />
  <input type="hidden" name="dosql" value="do_affectation_aed" />
  <input type="hidden" name="lit_id" value="" />
  <input type="hidden" name="sejour_id" value="" />

  <table class="form sejourcollapse treegrid me-margin-top-8 me-no-border" id="sejour_bloque">
    <tbody>
      <tr>
        <td class="selectsejour me-no-border-right">
          <input type="radio" id="hospitalisation" onclick="selectHospitalisation()" />
          <script>new Draggable('sejour_bloque', {revert: true})</script>
        </td>
        <td class="patient me-no-border-left" onclick="flipSejour('bloque')">
          <strong><a name="sejourbloque" class="tree-folding">[BLOQUER UN LIT]</a></strong>
        </td>
      </tr>
      <tr>
        <td class="me-no-border-right me-no-border-bottom"><em>Entrée</em></td>
        <td class="me-no-border-left me-no-border-bottom">{{mb_field object=$affectation field="entree" class="me-105" form="addAffectationsejour" register=true}}</td>
      </tr>
      <tr>
        <td class="me-no-border-right me-no-border-top me-no-border-bottom"><em>Sortie</em></td>
        <td class="me-no-border-left me-no-border-top me-no-border-bottom">{{mb_field object=$affectation field="sortie" class="me-105" form="addAffectationsejour" register=true}}</td>
      </tr>
      <tr>
        <td class="date highlight me-no-border-top" colspan="2">
          <label for="rques">Remarques</label> :
          <textarea name="rques"></textarea>
        </td>
      </tr>
    </tbody>
  </table>
</form>

{{foreach from=$groupSejourNonAffectes key=group_name item=sejourNonAffectes}}
  {{if $group_name == "couloir"}}
    {{mb_include module=hospi template=inc_affectations_couloir}}
  {{else}}
    {{mb_include module=hospi template=inc_affectations_liste}}
  {{/if}}
{{/foreach}}
