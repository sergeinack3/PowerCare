/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

AffectationUf = {
  modal: null,

  edit: function (object_guid) {
    new Url('hospi', 'ajax_affectation_uf')
      .addParam('object_guid', object_guid)
      .requestModal(450, 350);
  },

  affecter: function (curr_affectation_id, lit_id, callback) {
    new Url('hospi', 'ajax_vw_association_uf')
      .addParam('curr_affectation_id', curr_affectation_id)
      .addParam('lit_id', lit_id)
      .requestModal(600, 400, {onClose: callback || Prototype.emptyFunction});
  },

  onSubmitRefresh: function (form, option) {
    return onSubmitFormAjax(form, function () {
      if (!option) {
        return;
      }

      // Préselection de l'uf médicale du praticien si possible
      var uf_medicale_id = option.get("uf_medicale_id");

      if (!uf_medicale_id) {
        return;
      }

      var input = option.up("form").down("input[name=uf_medicale_id_radio_view][value=" + uf_medicale_id + "]");

      if (!input) {
        return;
      }

      input.click();
    });
  },

  onDeletion: function (form, callback = null) {
    return confirmDeletion(form,
      {typeName: 'l\'affectation d\'UF'},
      callback
    );
  },

  reloadPratUfMed: function (uf_medicale, object_id, lit_id, see_validate) {
    new Url('hospi', 'ajax_select_prat_uf')
      .addParam('curr_affectation_id', object_id)
      .addParam('lit_id', lit_id)
      .addParam('see_validate', see_validate)
      .addParam('uf_medicale_id', uf_medicale.value)
      .requestUpdate("select_prat_uf_med");
  }
};
