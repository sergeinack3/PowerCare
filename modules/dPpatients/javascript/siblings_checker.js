/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Check for siblings or too different text
 */
SiblingsChecker = {
  form:        null,
  // Submit
  submit:      0,
  link:        0,
  patient_ids: [],

  // Send Ajax request
  request: function (oForm) {
    this.form = oForm;

    var url = new Url("patients", "ajax_get_siblings");
    url.addElement(oForm.patient_id);
    url.addParam('nom', $V(oForm.nom) ? $V(oForm.nom) : $V(oForm.nom_jeune_fille));
    url.addElement(oForm.nom_jeune_fille);
    url.addElement(oForm.prenom);
    url.addElement(oForm.prenoms);
    url.addParam("submit", this.submit);
    if (oForm.naissance) {
      url.addParam("naissance", $(oForm.naissance).getFormatted("99/99/9999", "$3-$2-$1"));
    }

    if (this.submit) {
      url.addParam("json_result", "1");
      url.requestJSON((function (data) {
        if (data) {
          url.addParam("json_result", "0");
          url.requestModal(300, 240);
        } else {
          this.submitForm();
        }
      }).bind(this));
    } else {
      url.requestUpdate("doublon-patient", {
        waitingText: ""
      });
    }
  },

  confirmCreate: function () {
    if (this.link) {
      var link_selector_form = getForm('linkSelector');
      var form = (link_selector_form) ? link_selector_form : getForm('doubloonSelector');
      var field = (link_selector_form) ? $V(form.sibling_id) : $V(form._doubloon_ids);
      if (field) {
        $V(this.form._doubloon_ids, field);
      } else {
        alert('Afin de pouvoir associer le patient, vous devez sélectionner un doublon.');
        return false;
      }
    }

    this.submitForm();
  },

  submitForm: function () {
    if ($V(this.form.modal) === "1") {
      Control.Modal.close();

      if (this.form._handle_files) {
        $V(this.form._handle_files, '1');
      }

      // Depuis le patient selector, on ne veut pas lancer le callback de suite
      // si l'on souhaite ajouter des correspondants
      var open_corresp = $V(this.form._open_corresp) === "1";
      if (open_corresp) {
        $V(this.form.callback, "");

        Form.onSubmitComplete = function (patient_guid, patient) {
          document.location += "&patient_id=" + patient_guid.split('-')[1] + "#correspondance";
        };
      }

      return onSubmitFormAjax(this.form, open_corresp ? null : function () {
        window.parent.Control.Modal.close();
      });
    }

    this.form.submit();
  }
};

