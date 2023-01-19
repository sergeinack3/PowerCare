/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Salutation = {
  object_class: null,
  object_id: null,
  owner_id: null,

  editSalutation: function (salutation_id, callback) {
    new Url('patients', 'ajax_edit_salutation')
      .addParam('salutation_id', salutation_id)
      .addParam('object_class', this.object_class)
      .addParam('object_id', this.object_id)
      .addParam('owner_id', this.owner_id)
      .requestModal(500, 300, callback);
  },

  submitSalutation: function (form) {
    return onSubmitFormAjax(form, {
      onComplete: function () {
        Control.Modal.close();
      }
    });
  },

  manageSalutations: function (object_class, object_id, owner_id) {
    this.object_class = object_class;
    this.object_id = object_id;
    this.owner_id = owner_id;
    new Url('patients', 'vw_manage_salutations')
      .addParam('object_class', object_class)
      .addParam('object_id', object_id)
      .addParam('owner_id', owner_id)
      .requestModal(800, 600);
  },

  reloadList: function (form) {
    form = form || getForm('search_salutations');
    return form.onsubmit();
  },

  filterContent: function (input, classe) {
    tr = $$(classe);

    tr.each(
      function (e) {
        e.show();
      }
    );

    var terms = $V(input);
    if (!terms) {
      return;
    }

    tr.each(
      function (e) {
        e.hide();
      }
    );

    terms = terms.split(",");
    tr.each(function (e) {
      terms.each(function (term) {
        if (e.getText().like(term)) {
          e.show();
        }
      });
    });
  },

  onFilterContent: function (input, classe) {
    if (input.value == "") {
      // Click on the clearing button
      Salutation.filterContent(input, classe);
    }
  }
};