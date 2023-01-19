{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    GroupePatient.elementPrescriptionAutocomplete(getForm('editCategoryGroup'));
  });
</script>

{{assign var=elements value=$plage_groupe_patient->_ref_elements_prescription}}

<form name="editCategoryGroup" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{if !$plage_groupe_patient->_id}}
    <input type="hidden" name="m" value="{{$m}}"/>
    <input type="hidden" name="dosql" value="do_create_multi_plage_groupe_patient_aed"/>
  {{/if}}

  {{mb_key   object=$plage_groupe_patient}}
  {{mb_class object=$plage_groupe_patient}}
  <input type="hidden" name="groupe_days" value=""/>

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$plage_groupe_patient}}
    <tr>
      <th>{{mb_label object=$plage_groupe_patient field=categorie_groupe_patient_id}}</th>
      <td>{{mb_field object=$plage_groupe_patient field=categorie_groupe_patient_id options=$categories_groupe emptyLabel="Choose"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$plage_groupe_patient field=nom}}</th>
      <td>{{mb_field object=$plage_groupe_patient field=nom}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$plage_groupe_patient field=groupe_day}}</th>
      <td>
        {{if $plage_groupe_patient->_id}}
          {{mb_field object=$plage_groupe_patient field=groupe_day}}
        {{else}}
          <select name="_groupe_days" class="str notNull" multiple="1" size="7" onchange="GroupePatient.selectManyDays(this.form);">
            <option value="monday">{{tr}}Monday{{/tr}}</option>
            <option value="tuesday">{{tr}}Tuesday{{/tr}}</option>
            <option value="wednesday">{{tr}}Wednesday{{/tr}}</option>
            <option value="thursday">{{tr}}Thursday{{/tr}}</option>
            <option value="friday">{{tr}}Friday{{/tr}}</option>
            <option value="saturday">{{tr}}Saturday{{/tr}}</option>
            <option value="sunday">{{tr}}Sunday{{/tr}}</option>
          </select>
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$plage_groupe_patient field=heure_debut}}</th>
      <td>{{mb_field object=$plage_groupe_patient field=heure_debut register=true form=editCategoryGroup}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$plage_groupe_patient field=heure_fin}}</th>
      <td>{{mb_field object=$plage_groupe_patient field=heure_fin register=true form=editCategoryGroup}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$plage_groupe_patient field=elements_prescription}}</th>
      <td>
        {{mb_field object=$plage_groupe_patient field=elements_prescription hidden=true}}
        <input type="text" name="libelle" placeholder="&mdash; {{tr}}CPrescription.select_element{{/tr}}" class="autocomplete"/>
        <div class="autocomplete" id="element_prescription_view" style="text-align: left; display: none;"></div>

        <div style="max-width:250px; white-space: normal !important;">
          <ul id="show_tags_element" class="tags">
            {{foreach from=$elements item=_element}}
              <li id="li_element_{{$_element->_id}}" class="tag element_selected" style="cursor: default;">
                <span>{{$_element->_view}}</span>
                <i class="fas fa-times" type="button" style="margin-left: 10px; cursor: pointer;"
                   title="Supprimer" onclick="$(this).up('li').remove(); GroupePatient.deleteElementTag();"></i>
                <input type="hidden" id="editCategoryGroup__element_id[{{$_element->_id}}]" name="element[]" value="{{$_element->_id}}">
              </li>
            {{/foreach}}
          </ul>
        </div>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$plage_groupe_patient field=equipement_id}}</th>
      <td>
        <select name="equipement_id">
          <option value="">&mdash; {{tr}}CEquipement.select{{/tr}}</option>
          {{foreach from=$plateaux item=_plateau}}
            <optgroup label="{{$_plateau->_view}}">
              {{foreach from=$_plateau->_ref_equipements item=_equipement}}
                <option value="{{$_equipement->_id}}" {{if $plage_groupe_patient->equipement_id == $_equipement->_id}}selected="selected"{{/if}}>
                  {{$_equipement->_view}}
                </option>
              {{/foreach}}
            </optgroup>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$plage_groupe_patient field=commentaire}}</th>
      <td>{{mb_field object=$plage_groupe_patient field=commentaire}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$plage_groupe_patient field=actif}}</th>
      <td>{{mb_field object=$plage_groupe_patient field=actif}}</td>
    </tr>
    <tr>
     {{mb_include module=system template=inc_form_table_footer object=$plage_groupe_patient options="{typeName: 'la catégorie', objName: '`$plage_groupe_patient->_view`'}" options_ajax="Control.Modal.close"}}
    </tr>
  </table>
</form>