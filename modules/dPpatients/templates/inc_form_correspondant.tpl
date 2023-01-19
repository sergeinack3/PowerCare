{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="patients" script="autocomplete" ajax=true}}
{{mb_default var=mode_modele value=0}}

{{assign var=function_distinct value=$conf.dPpatients.CPatient.function_distinct}}

<script>
    InseeFields.initCPVille("editCorrespondant", "cp", "ville", null, null, "tel");

    copyFieldsPatient = function () {
        var form = getForm("editCorrespondant");

        if (($V(form.adresse) || $V(form.cp) || $V(form.ville) || $V(form.tel) || $V(form.mob) || $V(form.email))
          && !confirm($T("CCorrespondantPatient.ask_copy_fields"))) {
            return;
        }

        $V(form.adresse, '{{$patient->adresse|smarty:nodefaults|JSAttribute}}');
        $V(form.cp, '{{$patient->cp}}');
        $V(form.ville, '{{$patient->ville|smarty:nodefaults|JSAttribute}}');
        $V(form.tel, '{{$patient->getFormattedValue("tel")}}');
        $V(form.mob, '{{$patient->getFormattedValue("tel2")}}');
        $V(form.email, '{{$patient->email}}');
    };

    Main.add(function () {
        var form = getForm("editCorrespondant");
        Calendar.regField(form.date_debut);
        Calendar.regField(form.date_fin);

        {{if !$correspondant->_id}}
        $(form.relation).onchange();
        {{/if}}


        updateFieldsCorrespondant = function (form, selected) {
            if (selected.innerHTML) {
                $V(form.surnom, selected.get("surnom"));
                $V(form.nom, selected.get("nom"));
                $V(form.nom_jeune_fille, selected.get("nom_jeune_fille"));
                $V(form.prenom, selected.get("prenom"));
                $V(form.adresse, selected.get("adresse"));
                $V(form.cp, selected.get("cp"));
                $V(form.ville, selected.get("ville"));
                $V(form.tel, selected.get("tel"));
                $V(form.mob, selected.get("mob"));
                $V(form.fax, selected.get("fax"));
                $V(form.urssaf, selected.get("urssaf"));
                $V(form.parente, selected.get("parente"));
                $V(form.email, selected.get("email"));
                $V(form.remarques, selected.get("remarques"));
            }
        };

        {{if !$mode_modele}}
        // Autocomplete sur le nom du correspondant
        var url = new Url("patients", "correspondant_autocomplete");
        {{if $conf.dPpatients.CPatient.function_distinct}}
        {{if $function_distinct == 1}}
        url.addParam("function_id", "{{$app->_ref_user->function_id}}");
        {{elseif $function_distinct == 2}}
        url.addParam("group_id", "{{$g}}");
        {{/if}}
        {{/if}}
        url.autoComplete(form.nom, null, {
            minChars:           2,
            method:             "get",
            select:             "view",
            callback:           function (input, queryString) {
                var form = getForm("editCorrespondant");
                return queryString + "&relation=" + $V(form.relation);
            },
            updateElement:      function (selectedElement) {
                this.afterUpdateElement(form.nom, selectedElement)
            },
            afterUpdateElement: function (field, selected) {
                var form = field.form;
                var selected = selected.select(".view")[0];
                updateFieldsCorrespondant(form, selected);
            }
        });

        // Autocomplete sur le surnom du correspondant
        var url_surnom = new Url("patients", "correspondant_autocomplete");
        {{if $conf.dPpatients.CPatient.function_distinct}}
        {{if $function_distinct == 1}}
        url_surnom.addParam("function_id", "{{$app->_ref_user->function_id}}");
        {{else}}
        url_surnom.addParam("group_id", "{{$g}}");
        {{/if}}
        {{/if}}
        url_surnom.autoComplete(form.surnom, null, {
            minChars:           2,
            method:             "get",
            select:             "view",
            callback:           function (input, queryString) {
                var form = getForm("editCorrespondant");
                return queryString + "&relation=" + $V(form.relation);
            },
            updateElement:      function (selectedElement) {
                this.afterUpdateElement(form.surnom, selectedElement)
            },
            afterUpdateElement: function (field, selected) {
                var form = field.form;
                var selected = selected.select(".view")[0];
                updateFieldsCorrespondant(form, selected);
            }
        });
        {{/if}}

    });


    showElem = function (eltList) {
        $(eltList).each(function (elt) {
            $(elt).setStyle({display: "table-row"});
        });
    };

    hideElem = function (eltList) {
        $(eltList).each(function (elt) {
            $(elt).setStyle({display: "none"});
        });
    };

    toggleUrrsafParente = function (elt) {
        $("parente").toggle();
        if ($V(elt) == "employeur") {
            showElem(["urssaf"]);
            hideElem(["parente", "parente_autre"]);
            var form = getForm("editCorrespondant");
            $V(form.parente_autre, "");
            $V(form.relation_autre, "");
            elt.form.parente.selectedIndex = 0;
        } else if ($V(elt) == "assurance" || $V(elt) == "transport") {
            hideElem(["urssaf", "parente", "parente_autre"]);
        } else {
            showElem(["parente"]);
            hideElem(["urssaf"]);
            $V(elt.form.urrsaf, "");
        }
    };

    toggleRelationAutre = function (elt) {
        if ($V(elt) == "autre") {
            $("relation_autre").setStyle({display: "inline"});
        } else {
            hideElem(["relation_autre"]);
        }
    };

    toggleParenteAutre = function (elt) {
        if ($V(elt) == "autre") {
            $('parente_autre').setStyle({display: "inline"});
        } else {
            hideElem(["parente_autre"]);
            $V(getForm("editCorrespondant").parente_autre, '');
        }
    };

    toggleConfiancePrevenir = function (elt) {
        if ($V(elt) == "confiance" || $V(elt) == "representant_th" || $V(elt) == "parent_proche") {
            showElem(["nom_jeune_fille", "prenom", "naissance"]);
        } else if ($V(elt) == "prevenir") {
            showElem(["prenom", "naissance"]);
            hideElem(["nom_jeune_fille"]);
        } else {
            showElem(["prenom", "naissance"]);
            hideElem(["nom_jeune_fille"]);
        }
    };

    toggleAssurance = function (elt) {
        if ($V(elt) == "assurance") {
            hideElem(["prenom"]);
        } else if ($V(elt) == "employeur") {
            hideElem(["prenom"]);
        } else {
            showElem(["prenom"]);
        }
    }
</script>

<form name="editCorrespondant" method="post" action="?" onsubmit="return Correspondant.onSubmit(this);">
    {{mb_class object=$correspondant}}
    {{mb_key   object=$correspondant}}
    {{if $mode_modele}}
        <input type="hidden" name="callback" value="CorrespondantModele.afterSave"/>
        <input type="hidden" name="group_id" value="{{$g}}"/>
    {{else}}
        {{mb_field object=$correspondant field="patient_id" hidden=true}}
    {{/if}}
    <input type="hidden" name="del" value="0"/>
    <table class="form">
        <tr>
            {{if $correspondant->_id}}
                <th class="title modify text" colspan="2">
                    {{mb_include module=system template=inc_object_idsante400 object=$correspondant}}
                    {{mb_include module=system template=inc_object_history object=$correspondant}}

                    {{tr}}CCorrespondantPatient-title-modify{{/tr}} '{{$correspondant}}'
                </th>
            {{else}}
                <th class="title me-th-new" colspan="2">
                    <button type="button" class="import" style="float: right;"
                            onclick="copyFieldsPatient();">{{tr}}CCorrespondantPatient-copyFieldsPatient{{/tr}}</button>
                    {{tr}}CCorrespondantPatient-title-create{{/tr}}
                </th>
            {{/if}}
        </tr>

        <tr>
            {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="relation"}}

            {{mb_field object=$correspondant field=relation onchange="toggleRelationAutre(this); toggleUrrsafParente(this); toggleConfiancePrevenir(this); toggleAssurance(this);" alphabet=true}}
                <span style="{{if $correspondant->relation != "autre"}}display: none;{{/if}}" id="relation_autre">
          <input type="text" name="relation_autre" value="{{$correspondant->relation_autre}}" size="30"/>
        </span>
            {{/me_form_field}}
        </tr>

        <tr {{if $correspondant->relation == "employeur"}}style="display: none;"{{/if}} id="parente">
            {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="parente"}}
            {{mb_field object=$correspondant field="parente" emptyLabel="Choose" onchange="toggleParenteAutre(this);"}}
                <span {{if $correspondant->parente != "autre"}} style="display: none;"{{/if}} id="parente_autre">
          {{mb_field object=$correspondant field="parente_autre"}}
        </span>
            {{/me_form_field}}
        </tr>

        <tr>
            {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="nom"}}
            {{if $mode_modele}}
                {{mb_field object=$correspondant field="nom"}}
            {{else}}
                <input type="text" name="nom" class="autocomplete notNull" value="{{$correspondant->nom}}"/>
            {{/if}}
            {{/me_form_field}}
        </tr>


        <tr {{if $correspondant->relation != "assurance"}}style="display: none;"{{/if}} id="surnom">
            {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="surnom"}}
            {{if $mode_modele}}
                {{mb_field object=$correspondant field="surnom"}}
            {{else}}
                <input type="text" name="surnom" class="autocomplete" value="{{$correspondant->surnom}}"/>
            {{/if}}
            {{/me_form_field}}
        </tr>

        <tr {{if $correspondant->relation != "confiance" && $correspondant->relation != "representant_th" &&
        $correspondant->relation != "parent_proche" && $correspondant->relation != "representant_legal"}}style="display: none;"{{/if}} id="nom_jeune_fille">
            {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="nom_jeune_fille"}}
            {{mb_field object=$correspondant field="nom_jeune_fille"}}
            {{/me_form_field}}
        </tr>

        <tr id="prenom"
            {{if !$correspondant->_id || ($correspondant->relation == "employeur" || $correspondant->relation == "assurance")}}style="display: none;"{{/if}}>
            {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="prenom"}}
            {{mb_field object=$correspondant field="prenom"}}
            {{/me_form_field}}
        </tr>

        <tr id="num_assure"
            {{if $correspondant->relation != "employeur" || !$correspondant->_id || $conf.ref_pays == 1}}style="display: none;"{{/if}}>
            {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="num_assure"}}
            {{mb_field object=$correspondant field="num_assure"}}
            {{/me_form_field}}
        </tr>

        <tr
          id="employeur" {{if ($correspondant->relation != "assurance" && $correspondant->_id) || $conf.ref_pays == 1 || !$mode_modele}} style="display: none;"{{/if}}>
            {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="employeur"}}
                <select name="employeur">
                    <option value="">-- {{tr}}Choose{{/tr}}</option>
                    {{foreach from=$patient->_ref_correspondants_patient item=_correspondant}}
                        {{if $_correspondant->relation == "employeur"}}
                            <option value="{{$_correspondant->_id}}"
                                    {{if $correspondant->employeur == $_correspondant->_id}}selected="selected"{{/if}}>{{$_correspondant->nom}}</option>
                        {{/if}}
                    {{/foreach}}
                </select>
            {{/me_form_field}}
        </tr>

        <tr {{if $correspondant->relation != "confiance" && $correspondant->relation != "representant_th" &&
        $correspondant->relation != "parent_proche" && $correspondant->relation != "representant_legal"}}style="display: none;"{{/if}}
            id="naissance">
            {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="naissance"}}
            {{mb_field object=$correspondant field="naissance" form="editCorrespondant" register=true}}
            {{/me_form_field}}
        </tr>

        <tr>
            {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="adresse"}}
            {{mb_field object=$correspondant field="adresse"}}
            {{/me_form_field}}
        </tr>

        <tr>
            {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="cp"}}
            {{mb_field object=$correspondant field="cp"}}
            {{/me_form_field}}
        </tr>
        <tr>
            {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="ville"}}
            {{mb_field object=$correspondant field="ville"}}
            {{/me_form_field}}
        </tr>
        <tr>
            {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="tel"}}
            {{mb_field object=$correspondant field="tel"}}
            {{/me_form_field}}
        </tr>
        <tr>
            {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="mob"}}
            {{mb_field object=$correspondant field="mob"}}
            {{/me_form_field}}
        </tr>
        <tr>
            {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="fax"}}
            {{mb_field object=$correspondant field="fax"}}
            {{/me_form_field}}
        </tr>
        <tr>
            {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="tel_autre"}}
            {{mb_field object=$correspondant field="tel_autre"}}
            {{/me_form_field}}
        </tr>

        <tr {{if $correspondant->relation != "employeur"}}style="display: none;"{{/if}}
            id="urssaf">
            {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="urssaf"}}
            {{mb_field object=$correspondant field="urssaf"}}
            {{/me_form_field}}
        </tr>

        <tr>
            {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="email"}}
            {{mb_field object=$correspondant field="email"}}
            {{/me_form_field}}
        </tr>
        <tr>
            {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="remarques"}}
            {{mb_field object=$correspondant field="remarques"}}
            {{/me_form_field}}
        </tr>
        <tr id="date_debut">
            {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="date_debut"}}
            {{mb_field object=$correspondant field="date_debut"}}
            {{/me_form_field}}
        </tr>

        <tr id="date_fin">
            {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="date_fin"}}
            {{mb_field object=$correspondant field="date_fin"}}
            {{/me_form_field}}
        </tr>

        <tr>
            <td colspan="2" class="button">
                <button type="submit" class="save">
                    {{if !$correspondant->_id}}
                        {{tr}}Create{{/tr}}
                    {{else}}
                        {{tr}}Save{{/tr}}
                    {{/if}}
                </button>
                {{if $correspondant->_id}}
                    <button type="button" onclick="Correspondant.confirmDeletion(this.form);"
                            class="cancel me-secondary">{{tr}}Delete{{/tr}}</button>
                {{/if}}
            </td>
        </tr>
    </table>
</form>
