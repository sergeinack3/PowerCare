{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{if "cerfa General use_cerfa"|gconf && "cerfa"|module_active}}
  {{mb_script module=cerfa script=Cerfa register=true}}
{{/if}}

{{mb_script module=cabinet script=accident_travail register=true}}

<form name="editAccidentTravail" method="post" action="?" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_key   object=$accident_travail}}
  {{mb_class object=$accident_travail}}
  {{mb_field object=$accident_travail field=object_id    hidden=true}}
  {{mb_field object=$accident_travail field=object_class hidden=true}}
  <input type="hidden" name="datetime_at_mp" value="now" />

  <table class="form" style="width: 100%;">
    {{mb_include module=system template=inc_form_table_header object=$accident_travail}}

    <tr>
      <th>{{mb_label object=$accident_travail field=type}}</th>
      <td>{{mb_field object=$accident_travail field=type}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$accident_travail field=nature}}</th>
      <td>{{mb_field object=$accident_travail field=nature}}</td>
    </tr>
    <tr>
      <th style="width: 30%">{{mb_label object=$accident_travail field=date_constatations}}</th>
      <td>{{mb_field object=$accident_travail field=date_constatations form=editAccidentTravail register=true}}</td>
    </tr>
    <tr>
      <th style="width: 30%">{{mb_label object=$accident_travail field=date_debut_arret}}</th>
      <td>{{mb_field object=$accident_travail field=date_debut_arret form=editAccidentTravail register=true}}</td>
    </tr>
    <tr>
      <th><label for="duree" title="{{tr}}Duration{{/tr}}">{{tr}}Duration{{/tr}}</label></th>
      <td>
        <input type="number" class="num" name="duree" size="4" min="1" max="1092" onchange="AccidentTravail.updateEndDate();" {{if $accident_travail->_duree}}value="{{$accident_travail->_duree}}"{{/if}}/>
        <select name="unite_duree" onchange="AccidentTravail.updateMaxDuree(); AccidentTravail.updateEndDate();">
          <option value="j" {{if $accident_travail->_unite_duree && $accident_travail->_unite_duree == 'j'}}selected{{/if}}>{{tr}}Day{{/tr}}</option>
          <option value="m" {{if $accident_travail->_unite_duree && $accident_travail->_unite_duree == 'm'}}selected{{/if}}>{{tr}}Month{{/tr}}</option>
          <option value="y" {{if $accident_travail->_unite_duree && $accident_travail->_unite_duree == 'a'}}selected{{/if}}>{{tr}}Year{{/tr}}</option>
        </select>
      </td>
    </tr>
    <tr>
      <th style="width: 30%">{{mb_label object=$accident_travail field=date_fin_arret}}</th>
      <td>{{mb_field object=$accident_travail field=date_fin_arret readonly=true}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$accident_travail field=num_organisme}}</th>
      <td>{{mb_field object=$accident_travail field=num_organisme size=9}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$accident_travail field=feuille_at}}</th>
      <td>{{mb_field object=$accident_travail field=feuille_at}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$accident_travail field=constatations}}</th>
      <td>{{mb_field object=$accident_travail field=constatations form="editAccidentTravail" aidesaisie="validateOnBlur: 0"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$accident_travail field=consequences}}</th>
      <td>{{mb_field object=$accident_travail field=consequences}}</td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$accident_travail field=sorties_autorisees}}
      </th>
      <td>
        {{mb_field object=$accident_travail field=sorties_autorisees onchange="AccidentTravail.checkSortiesAutorisees(this.form);"}}
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <div class="small-info">
          {{tr}}CAccidentTravail-msg-legal-sorties_autorisees{{/tr}}
        </div>
      </td>
    </tr>
    <tbody id="sorties_autorisees" {{if !$accident_travail->sorties_autorisees}}style="display: none;"{{/if}}>
    <tr class="sorties_autorisees_details">
      <th>
        {{mb_label object=$accident_travail field=sorties_restriction}}
      </th>
      <td>
        {{mb_field object=$accident_travail field=sorties_restriction typeEnum="checkbox" onchange="AccidentTravail.checkSortiesAutoriseesType(this, 'restriction');"}}
      </td>
    </tr>
    <tr id="sorties_autorisees_restriction" {{if !$accident_travail->sorties_restriction}}style="display: none;"{{/if}}>
      <th>
        {{mb_label object=$accident_travail field=date_sortie}}
      </th>
      <td>
        {{mb_field object=$accident_travail field=date_sortie form="editAccidentTravail" register=true onchange="AccidentTravail.checkDateSortie(this, 'restriction');"}}
      </td>
    </tr>
    <tr class="sorties_autorisees_details">
      <th>
        {{mb_label object=$accident_travail field=sorties_sans_restriction}}
      </th>
      <td>
        {{mb_field object=$accident_travail field=sorties_sans_restriction typeEnum="checkbox" onchange="AccidentTravail.checkSortiesAutoriseesType(this, 'sans_restriction');"}}
      </td>
    </tr>
    <tbody id="sorties_autorisees_sans_restriction" {{if !$accident_travail->sorties_sans_restriction}}style="display: none;"{{/if}}>
    <tr>
      <th>
        {{mb_label object=$accident_travail field=date_sortie_sans_restriction}}
      </th>
      <td>
        {{mb_field object=$accident_travail field=date_sortie_sans_restriction form="editAccidentTravail" register=true onchange="AccidentTravail.checkDateSortie(this, 'sans_restriction');"}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$accident_travail field=motif_sortie_sans_restriction}}
      </th>
      <td>
        {{mb_field object=$accident_travail field=motif_sortie_sans_restriction form="editAccidentTravail" aidesaisie="validateOnBlur: 0"}}
      </td>
    </tr>
    </tbody>
    </tbody>

    <tr>
      <td class="button" colspan="2">
        {{if "cerfa General use_cerfa"|gconf && "cerfa"|module_active && $show_button_cerfa}}
          <button type="button" onclick="AccidentTravail.saveAndOpenCerfa(this.form, '{{$accident_travail->object_class}}', '{{$accident_travail->object_id}}');">
            <i class="fas fa-check" style="color: forestgreen;"></i> {{tr}}CAccidentTravail-action-Save and open cerfa{{/tr}}
          </button>
        {{/if}}
        <button class="save">{{tr}}Save{{/tr}}</button>
        {{if $accident_travail->_id}}
          <button class="trash" type="button" onclick="
            confirmDeletion(this.form,
            {typeName: '', objName: '{{$accident_travail->_view}}'}
            , Control.Modal.close);">{{tr}}Delete{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
