/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

PatientSignature = window.PatientSignature || {
  checkOnlyTwoSelected: function (checkbox, signature) {
    if (!this[signature]) {
      this[signature] = [];
    }

    this[signature] = this[signature].without(checkbox);

    if (checkbox.checked) {
      this[signature].push(checkbox);
    }

    if (this[signature].length > 2) {
      this[signature].shift().checked = false;
    }
  },

  pageChange: function (page, args) {
    $V(getForm("show_table_" + args + "_patients").start, page);
  },

  mergePatientsModal: function (form, signature) {
    var url = new Url();
    url.addFormData(form);
    var checkbox = $$("input.select-pat-" + signature);

    var nb_check = 0;
    for (var i = 0; i < checkbox.length; i++) {
      if (checkbox[i].checked) {
        url.addParam('objects_id[' + nb_check + ']', checkbox[i].value);
        nb_check++;
      }
    }

    if (nb_check == 2) {
      url.addParam("fields_ok", '1');
    }

    if (!url.oParams.fields_ok) {
      alert("Il faut choisir deux patients pour la fusion.");
      return false;
    }

    url.modal(800, 600);
    return false;
  },

  setPatientsHomonymes: function (form, signature) {
    var checkbox = $$("input.select-pat-" + signature);
    var inputs = $$("input.pat-homonyme-" + signature);
    inputs.each(function (input) {
      input.value = "";
    });

    for (var i = 0; i < checkbox.length; i++) {
      if (checkbox[i].checked) {
        if (!inputs[0].value) {
          inputs[0].value = checkbox[i].value;
        } else {
          if (!inputs[1].value) {
            inputs[1].value = checkbox[i].value;
          }
        }
      }
    }
  }
};