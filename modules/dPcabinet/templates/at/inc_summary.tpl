{{*
 * @package Mediboard\Ameli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th class="title" colspan="2">
      {{tr}}CAccidentTravail-title-summary{{/tr}}
    </th>
  </tr>
  <tr>
    <th>
      {{tr}}CAccidentTravail-_prescripteur{{/tr}}
    </th>
    <td>
      {{$physician.first_name}} {{$physician.last_name}} ({{$physician.psam}})
    </td>
  </tr>
  <tr>
    <th>
      {{tr}}CAccidentTravail-_beneficiary{{/tr}}
    </th>
    <td>
      {{$beneficiary.first_name}}
      {{if $beneficiary.usual_name}}
        {{$beneficiary.usual_name}} ({{$beneficiary.last_name}})
      {{else}}
        {{$beneficiary.last_name}}
      {{/if}}
      (NIR: {{$beneficiary.nir_num}} {{$beneficiary.nir_key}})
    </td>
  </tr>
  <tr>
    <th class="category" colspan="2">
      {{tr}}CAccidentTravail-title-context{{/tr}}
    </th>
  </tr>
  <tr>
    <th>
      <label for="summary-type">{{tr}}CAccidentTravail-type{{/tr}}</label>
    </th>
    <td>
      <input type="text" name="summary-type" value="{{tr}}CAccidentTravail.type.{{$at->type}}{{/tr}}" readonly>
    </td>
  </tr>
  <tr>
    <th>
      <label for="summary-nature">{{tr}}CAccidentTravail-nature{{/tr}}</label>
    </th>
    <td>
      <input type="text" name="summary-nature" value="{{if $at->nature}}{{tr}}CAccidentTravail.nature.{{$at->nature}}{{/tr}}{{/if}}" readonly>
    </td>
  </tr>
  <tr id="at_summary-feuille_at{{$uid}}">
    <th>
      <label for="summary-feuille_at">{{tr}}CAccidentTravail-feuille_at{{/tr}}</label>
    </th>
    <td>
      <input type="text" size="2" name="summary-feuille_at" value="{{tr}}{{if $at->feuille_at}}Yes{{else}}No{{/if}}{{/tr}}" readonly>
    </td>
  </tr>
  <tr id="at_summary-constatations{{$uid}}" {{if !$at->constatations}}style="display: none;"{{/if}}>
    <th>
      <label for="summary-constatations">{{tr}}CAccidentTravail-constatations{{/tr}}</label>
    </th>
    <td>
      <textarea rows="3" name="summary-constatations" readonly>{{$at->constatations}}</textarea>
    </td>
  </tr>
  <tr id="at_summary-consequences{{$uid}}">
    <th>
      <label for="summary-consequences">{{tr}}CAccidentTravail-consequences{{/tr}}</label>
    </th>
    <td>
      <input type="text" name="summary-consequences" value="{{$at->consequences}}" readonly>
    </td>
  </tr>
  <tr>
  <tr>
    <th class="category" colspan="2">
      {{tr}}CAccidentTravail-title-duration{{/tr}}
    </th>
  </tr>
  </tr>
  <tr>
    <th>
      <label for="summary-date_constatations">{{tr}}CAccidentTravail-date_constatations{{/tr}}</label>
    </th>
    <td>
      <input type="text" name="summary-date_constatations" value="{{$at->date_constatations|date_format:$conf.date}}" readonly>
    </td>
  </tr>
  <tr>
    <th>
      <label for="summary-duree">{{tr}}CAccidentTravail-_duree{{/tr}}</label>
    </th>
    <td>
      <input type="text" name="summary-duree" value="{{$at->_duree}}" size="3" readonly>
      <input type="text" name="summary-unite_duree" size="5" value="{{$at->_unite_duree}}" readonly>
    </td>
  </tr>
  <tr>
    <th>
      <label for="summary-date_fin_arret">{{tr}}CAccidentTravail-date_fin_arret{{/tr}}</label>
    </th>
    <td>
      <input type="text" name="summary-date_fin_arret" value="{{$at->date_fin_arret|date_format:$conf.date}}" readonly>
    </td>
  </tr>
    <tr>
      <th class="category" colspan="2">
        {{tr}}CAccidentTravail-title-patient_situation{{/tr}}
      </th>
    </tr>
    <tr id="at_summary-patient_employeur_nom{{$uid}}" {{if $at->patient_employeur_nom}}style="display: none;"{{/if}}>
      <th>
        <label for="summary-patient_employeur_nom">{{tr}}CAccidentTravail-patient_employeur_nom{{/tr}}</label>
      </th>
      <td>
        <input type="text" name="summary-patient_employeur_nom" value="{{$at->patient_employeur_nom}}" readonly>
      </td>
    </tr>
  <tr id="at_summary-sorties_autorisees{{$uid}}" {{if !$at->sorties_autorisees && !$at->sorties_sans_restriction}}style="display: none;"{{/if}}>
    <th class="category" colspan="2">
      {{tr}}CAccidentTravail-title-sorties{{/tr}}
      <input type="hidden" name="summary-sorties_autorisees" readonly>
    </th>
  </tr>
  <tr id="at_summary-sorties_restriction{{$uid}}" {{if !$at->sorties_restriction}}style="display: none;"{{/if}}>
    <th>
      <label for="summary-sorties_restriction">{{tr}}CAccidentTravail-sorties_restriction{{/tr}}</label>
    </th>
    <td>
      <input type="text" size="2" name="summary-sorties_restriction" value="{{tr}}{{if $at->sorties_restriction}}Yes{{else}}No{{/if}}{{/tr}}" readonly> {{tr}}date.from_long{{/tr}}
      <input type="text" name="summary-date_sortie" value="{{$at->date_sortie|date_format:$conf.date}}" readonly>
    </td>
  </tr>
  <tr id="at_summary-sorties_sans_restriction{{$uid}}" {{if !$at->sorties_sans_restriction}}style="display: none;"{{/if}}>
    <th>
      <label for="summary-sorties_sans_restriction">{{tr}}CAccidentTravail-sorties_sans_restriction{{/tr}}</label>
    </th>
    <td>
      <input type="text" size="2" name="summary-sorties_sans_restriction"
             value="{{tr}}{{if $at->sorties_sans_restriction}}Yes{{else}}No{{/if}}{{/tr}}" readonly> {{tr}}date.from_long{{/tr}}
      <input type="text" name="summary-date_sortie_sans_restriction"
             value="{{$at->date_sortie_sans_restriction|date_format:$conf.date}}" readonly>
      <br>
      <input type="text" name="summary-motif_sortie_sans_restriction"
             value="{{$at->motif_sortie_sans_restriction}}" readonly>
    </td>
  </tr>
  <tr>
    <td class="button" colspan="2">
      <select name="at_navigation_summary" onchange="AccidentTravail.displayView($V(this), this);">
        <option value="">&mdash; {{tr}}Goto{{/tr}}</option>
        <option value="at_context{{$uid}}">
          {{tr}}CAccidentTravail-title-context{{/tr}}
        </option>
        <option value="at_duration{{$uid}}">
          {{tr}}CAccidentTravail-title-duration{{/tr}}
        </option>
        <option value="at_patient_situation{{$uid}}">
          {{tr}}CAccidentTravail-title-patient_situation{{/tr}}
        </option>
        <option value="at_sorties{{$uid}}">
          {{tr}}CAccidentTravail-title-sorties{{/tr}}
        </option>
        <option value="at_summary{{$uid}}">
          {{tr}}CAccidentTravail-title-summary{{/tr}}
        </option>
      </select>
      {{if "cerfa General use_cerfa"|gconf && "cerfa"|module_active}}
        <button type="button" onclick="AccidentTravail.saveAndOpenCerfa(this.form, '{{$at->object_class}}', '{{$at->object_id}}');">
          <i class="fas fa-check" style="color: forestgreen;"></i> {{tr}}CAccidentTravail-action-Save and open cerfa{{/tr}}
        </button>
      {{/if}}
      <button type="button" class="save" onclick="AccidentTravail.confirmSaving();">
        {{tr}}Save{{/tr}}
      </button>
    </td>
  </tr>
</table>
