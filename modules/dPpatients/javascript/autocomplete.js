/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

InseeFields = {
  initCPVille: function (form_name, field_cp, field_commune, field_insee, field_pays, field_focus) {
    var form = getForm(form_name);

    // Populate div creation for CP
    var field = form.elements[field_cp];

    if (field) {
      // Autocomplete for CP
      new Url('patients', 'autocomplete_cp_commune')
        .addParam('column', 'code_postal')
        .addParam('name_input', field_cp)
        .autoComplete(field, null, {
            width:         '250px',
            minChars:      2,
            updateElement: function (selected) {
              InseeFields.updateCPVille(selected, form_name, field_cp, field_commune, field_insee, field_pays, field_focus);
            }
          }
        );
    }

    // Populate div creation for Commune
    field = form.elements[field_commune];

    if (field) {
      // Autocomplete for Commune
      new Url('patients', 'autocomplete_cp_commune')
        .addParam('column', 'commune')
        .addParam('name_input', field_commune)
        .autoComplete(field, null, {
            width:         "250px",
            minChars:      3,
            updateElement: function (selected) {
              InseeFields.updateCPVille(selected, form_name, field_cp, field_commune, field_insee, field_pays, field_focus);
            }
          }
        );
    }
  },

  updateCPVille: function (selected, form_name, field_cp, field_commune, field_insee, field_pays, field_focus) {
    let form = getForm(form_name),
        cp = selected.down(".cp"),
        commune = selected.down(".commune"),
        insee = selected.down('.insee'),
        pays = selected.dataset.pays;

    // Valuate CP and Commune
    if (field_cp) {
      $V(form.elements[field_cp], cp.getText().strip(), true);
    }

    $V(form.elements[field_commune], commune.getText().strip(), true);

    if (field_insee) {
      $V(form.elements[field_insee], insee.getText().strip(), true);
      $V(form.elements["commune_naissance_insee"], (selected.get("numeric") == 250) ? insee.getText().strip() : null, true)
    }

    if (field_pays) {
      $V(form.elements[field_pays], pays, true);
    }

    // Give focus
    if (field_focus) {
      $(form.elements[field_focus]).tryFocus();
    }
  },

  initCSP: function (sFormName, sFieldCSP) {
    var oForm = getForm(sFormName);

    // Populate div creation for CSP
    var oField = oForm.elements[sFieldCSP];

    if (!oField) {
      return;
    }

    new Url('ppatients', 'ajax_csp_autocomplete')
      .autoComplete(oField, null, {
        width:              "250px",
        minChars:           3,
        dropdown:           true,
        afterUpdateElement: function (input, selected) {
          $V(oForm.csp, selected.get("id"));
        }
      });
  },

  initCodeInsee: function (formName, fieldCodeInsee, prefixeField = '') {
    let form  = getForm(formName),
        field = form.elements[fieldCodeInsee];

    new Url('patients', 'autocompleteCodeInsee')
      .addNotNullParam('field_name', field.name)
      .autoComplete(field, null, {
        minChars: 3,
        afterUpdateElement: function (input, selected) {
          $V(field, selected.get("insee"));
          if (selected.get("class") === "CPaysInsee") {
            $V(form.elements[prefixeField + '_pays_naissance_insee'], selected.get("country"));
            $V(form.elements[prefixeField + 'lieu_naissance'], "");
            $V(form.elements[prefixeField + 'cp_naissance'], "");
            $V(form.elements[prefixeField + 'commune_naissance_insee'], "")
          } else {
            $V(form.elements[prefixeField + 'lieu_naissance'], selected.get("place"));
            $V(form.elements[prefixeField + 'cp_naissance'], selected.get("zipcode"));
            $V(form.elements[prefixeField + 'commune_naissance_insee'], selected.get("insee"));
            $V(form.elements[prefixeField + '_pays_naissance_insee'], selected.get("country"));
          }
        }
      })
  }

};

updateFields = function (selected, sFormName, sFieldFocus, sFirstField, sSecondField) {
  Element.cleanWhitespace(selected);
  var dn = selected.childNodes;
  $V(sFormName + '_' + sFirstField, dn[0].firstChild.firstChild.nodeValue, true);

  if (sSecondField) {
    $V(sFormName + '_' + sSecondField, dn[2].firstChild.firstChild.nodeValue, true);
  }

  if (sFieldFocus) {
    $(sFormName + '_' + sFieldFocus).focus();
  }
};

initPaysField = function (sFormName, sFieldPays, sFieldFocus) {
    let sFieldId = sFormName + '_' + sFieldPays,
        sCompleteId = sFieldPays + '_auto_complete';

    if (!$(sFieldId) || !$(sCompleteId)) {
        return;
    }

    new Url('patients', 'autocompletePaysInsee')
      .addParam("fieldpays", sFieldPays)
      .autoComplete(sFieldId, sCompleteId, {
        method:    "get",
        frequency: 0.15,
        minChars:  2,
        afterUpdateElement: function (input, selected) {
          if (sFieldPays === "_pays_naissance_insee") {
            $V(sFieldId, selected.get("name"));
            if (selected.get("numeric") != 250) {
              $V(sFormName + "__code_insee", selected.get("insee"));
              $V(sFormName + "_commune_naissance_insee", "");
            }
          } else {
            updateFields(input, sFormName, sFieldFocus, sFieldPays);
          }
        }
      });
};
