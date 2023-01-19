/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ObjectPseudonymiser = {
  setNextStart: function (last_id) {
    var form = getForm('pseudonymise-form');
    $V(form.elements.last_id, last_id);
  },

  nextPseudonymise: function () {
    var form = getForm('pseudonymise-form');

    if ($V(form.elements.continue) == 1) {
      form.onsubmit();
    }
  },

  doPseudonymiseSome: function () {
    Modal.confirm(
      $T('CObjectPseudonymiser-message-confirm pseudonymise'), {
        onOK: function() {
          var form = getForm('pseudonymise-form');
          $V(form.elements.continue, 1);

          $('pseudonymise-stop-btn').enable();
          $('pseudonymise-start-btn').disable();

          form.onsubmit();
        }
      }
    );
  },

  stopPseudonymise: function () {
    var form = getForm('pseudonymise-form');
    $V(form.elements.continue, 0);

    $('pseudonymise-stop-btn').disable();
    $('pseudonymise-start-btn').enable();
  },

  goToTablePrenom: function() {
    Control.Tabs.activateTab("prenom_sexe-maintenance")
  },

  changeClassSelected: function (elem) {
    var url = new Url('system', 'ajax_vw_pseudonymise');
    url.addParam('class_selected', $V(elem));
    url.requestUpdate('pseudonymise-vw');
  },



  displayPseudonymise: function(selected_class) {
    var url = new Url('system', 'ajax_vw_pseudonymise');
    url.addParam('class_selected', selected_class);
    url.requestModal('400');
  }
};