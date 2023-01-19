{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=addform value=""}}
{{mb_default var=type_see value=""}}
{{mb_default var=dossier_anesth_id value=""}}

<script>
  updateFieldsComposant = function(selected) {
    var composant = selected.down('.view').getText();
    var code_composant = selected.get("code");

    var oFormAllergie = getForm("editAntFrm{{$addform}}");
    $V(oFormAllergie.type, "alle");
    $V(oFormAllergie.appareil, "");
    $V(oFormAllergie.rques, composant);

    $V(oFormAllergie._idex_code, code_composant);
    $V(oFormAllergie._idex_tag, "BCB_COMPOSANT");

    return onSubmitAnt(oFormAllergie, '{{$type_see}}');
  };

  toggleChange = function (element) {
    var form = element.form;
    switch (element.name) {
      case "important" :
        form.__majeur.checked = false;
        $V(form.majeur,0,false);
        break;
      case "majeur" :
        form.__important.checked = false;
        $V(form.important,0,false);
    }
  };

  toggleOriginAutre = function(elt) {
    if ($V(elt) == "autre") {
      $("origin_autre").show();
    }
    else {
      $("origin_autre").hide();
    }
  };

  Main.add(function() {
    if (window.DossierMedical) {
      if (!DossierMedical.patient_id) {
        DossierMedical.sejour_id  = '{{$sejour_id}}';
        DossierMedical._is_anesth = '{{$_is_anesth}}';
        DossierMedical.patient_id = '{{$patient->_id}}';
        DossierMedical.dossier_anesth_id = '{{$dossier_anesth_id}}';
      }
      {{if $type_see}}
      DossierMedical.reloadDossierPatient(null, '{{$type_see}}');
      {{else}}
      DossierMedical.reloadDossiersMedicaux();
      {{/if}}
    }

    if ($('tab_atcd{{$addform}}')) {
      Control.Tabs.create('tab_atcd{{$addform}}', false);
    }

    // Autocomplete des composants
    if ($("composant_autocomplete{{$addform}}")) {
      var urlAuto = new Url("medicament", "ajax_composant_autocomplete");
      urlAuto.autoComplete(getForm('editAntFrm{{$addform}}').keywords_composant, "composant_autocomplete{{$addform}}", {
        minChars: 3,
        updateElement: updateFieldsComposant
      } );
    }

    getForm('editAntFrm{{$addform}}').family_link.value="";
  });
</script>
{{assign var=create_antecedent_only_prat value=0}}
{{if "dPpatients CAntecedent create_antecedent_only_prat"|gconf && !$app->user_prefs.allowed_to_edit_atcd &&
  !$app->_ref_user->isPraticien() && !$app->_ref_user->isSageFemme()}}
  <div class="small-info text">{{tr}}CTraitement-msg-You are not allowed to enter a antecedent and diagnosis{{/tr}}</div>
  {{assign var=create_antecedent_only_prat value=1}}
{{/if}}
<fieldset {{if $create_antecedent_only_prat}}style="display:none;"{{/if}} class="me-margin-bottom-12 me-padding-top-22">
  <legend>{{tr}}CAntecedent-Antecedent and allergy|pl{{/tr}}</legend>
  <form name="editAntFrm{{$addform}}" action="?m=cabinet" method="post" onsubmit="return onSubmitAnt(this, '{{$type_see}}');">
    <input type="hidden" name="m" value="patients" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="dosql" value="do_antecedent_aed" />
    <input type="hidden" name="_patient_id" value="{{$patient->_id}}" />
    <input type="hidden" name="reaction_indesirable" value="" />

    <!-- dossier_medical_id du sejour si c'est une consultation_anesth -->
    {{if $sejour_id}}
      <!-- On passe _sejour_id seulement s'il y a un sejour_id -->
      <input type="hidden" name="_sejour_id" value="{{$sejour_id}}" />
    {{/if}}

    <ul id="tab_atcd{{$addform}}" class="control_tabs small">
      <li><a href="#atcd_texte_simple{{$addform}}">{{tr}}CAntecedent-Free text{{/tr}}</a></li>
      {{if "dPprescription"|module_active && "dPprescription show_chapters tp med"|gconf}}
        {{if in_array('Ox\Mediboard\Medicament\CMedicament::getBase'|static_call:null, array("bcb", "vidal"))}}
          <li><a href="#atcd_base_med{{$addform}}">{{tr}}CAntecedent-Allergy to a component{{/tr}}</a></li>
        {{/if}}
      {{/if}}
    </ul>

    <table class="layout main" id="atcd_texte_simple{{$addform}}">
      <tr>
        {{if $app->user_prefs.showDatesAntecedents}}
          {{me_form_field nb_cells=2 mb_object=$antecedent mb_field=date}}
            {{mb_field object=$antecedent field=date form=editAntFrm$addform register=true}}
          {{/me_form_field}}
        {{else}}
          <td colspan="2"></td>
        {{/if}}
        <td rowspan="3" style="width: 100%">
          {{mb_field object=$antecedent field="rques" rows="4" form="editAntFrm$addform"
            aidesaisie="filterWithDependFields: false, validateOnBlur: 0"}}

          <fieldset style="display: none;">
            <legend>{{tr}}Links{{/tr}}</legend>
            <div class="hypertext_links_area"></div>
          </fieldset>
        </td>
      </tr>
      <tr style="display: none">
        {{me_form_field nb_cells=2 mb_object=$antecedent mb_field=date_fin}}
          {{mb_field object=$antecedent field=date_fin form=editAntFrm$addform register=true}}
        {{/me_form_field}}
      </tr>
      <tr>
        {{me_form_field nb_cells=2 mb_object=$antecedent mb_field=type field_class="me-margin-top-8"}}
          {{mb_field object=$antecedent field="type" emptyLabel="None" alphabet="1" style="width: 9em;" onchange="Antecedent.verifyType(this.form,'tr_family_link')"}}
        {{/me_form_field}}
      </tr>
      <tr id="tr_family_link" style="display: none">
          {{me_form_field nb_cells=2 mb_object=$antecedent mb_field=family_link field_class="me-margin-top-8"}}
            {{mb_field object=$antecedent field="family_link"}}
          {{/me_form_field}}
      </tr>
      <tr>
        {{me_form_field nb_cells=2 mb_object=$antecedent mb_field="appareil" field_class="me-margin-top-8"}}
          {{mb_field object=$antecedent field="appareil" emptyLabel="None" alphabet="1" style="width: 9em;"}}
        {{/me_form_field}}
      </tr>
      <tr>
        {{me_form_field layout=true nb_cells=3 label="CAntecedent-Level" title_label="CAntecedent-Level-desc" field_class="me-margin-top-8"}}
          {{mb_field object=$antecedent field="important" form=editAntFrm$addform typeEnum="checkbox" onchange="toggleChange(this)"}}
          {{mb_label object=$antecedent typeEnum="checkbox" field="important"}}
          <span class="me-margin-left-12">
              {{mb_field object=$antecedent field="majeur" form=editAntFrm$addform typeEnum="checkbox" onchange="toggleChange(this)"}}
              {{mb_label object=$antecedent typeEnum="checkbox" field="majeur"}}
          </span>
        {{/me_form_field}}
      </tr>
      <tr>
        {{me_form_field nb_cells=2 mb_object=$antecedent mb_field=origin field_class="me-margin-top-8"}}
          {{mb_field object=$antecedent field=origin onchange="toggleOriginAutre(this);"}}
        {{/me_form_field}}
        <td style="{{if $antecedent->origin != "autre"}}display: none;{{/if}}" id="origin_autre">
          <input type="text" name="origin_autre" value="{{$antecedent->origin_autre}}" size="30" />
        </td>
      </tr>
      <tr style="display: none;">
        {{me_form_field nb_cells=2 mb_object=$antecedent mb_field="degree_certainty"}}
          <select name="degree_certainty" id="degree_certainty">
            {{foreach from=$antecedent->_specs.degree_certainty->_list item=_degree}}
              <option value="{{$_degree}}">
                {{tr}}CAntecedent.degree_certainty.{{$_degree}}{{/tr}}
              </option>
            {{/foreach}}
          </select>
        {{/me_form_field}}
      </tr>
      {{if "dPpatients CAntecedent display_antecedents_non_presents"|gconf}}
        <tr>
          <th class="me-no-display"></th>
          <td class="text" colspan="3">
            <label>
              {{mb_field object=$antecedent field=absence emptyLabel="None" typeEnum=checkbox}} ({{tr}}CAntecedent-absence-desc{{/tr}})
            </label>
          </td>
        </tr>
      {{/if}}
      <tr>
        <td class="button" colspan="3">
          <button id="inc_ant_consult_trait_button_add_atcd" class="tick me-primary" type="button" onclick="this.form.onsubmit();">
            {{tr}}CAntecedent-action-Add the antecedent{{/tr}}
          </button>
        </td>
      </tr>
    </table>

    {{if in_array('Ox\Mediboard\Medicament\CMedicament::getBase'|static_call:null, array("bcb", "vidal"))}}
      <table class="form" id="atcd_base_med{{$addform}}" style="display: none;">
        <tr>
          <th>
            {{tr}}CAntecedent-Component{{/tr}}
          </th>
          <td>
            <input type="text" name="keywords_composant" value="" size="50" class="autocomplete" />
            <div style="display:none; width: 350px;" class="autocomplete" id="composant_autocomplete{{$addform}}"></div>
            <input type="hidden" name="_idex_code" value="" />
            <input type="hidden" name="_idex_tag" value="" />
          </td>
        </tr>
      </table>
    {{/if}}
  </form>
</fieldset>
