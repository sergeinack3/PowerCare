/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * JS function mediuser
 */
CMediusers = {
  filter_same_function:   true,
  no_association:         null,
  ldap_user_actif:        null,
  ldap_user_deb_activite: null,
  ldap_user_fin_activite: null,

  editMediuser: function(user_id, element) {
    if (element) {
      element.up('tr').addUniqueClassName('selected');
    }

    new Url("mediusers", "viewEditMediuser")
      .addParam("user_id", user_id)
      .requestModal(800, 700);
  },

  /**
   *
   * @param form
   */
  confirmMediuserEdition: function(form) {
    Modal.confirm(
      $T('CMediusers-msg-warning_edit_robot_user') + "\n" + $T('CMediusers-msg-robot_user_edit_might_lead_to_malfunctions'),
      {
        className: 'modal confirm big-warning',
        onOK: function() {
          return form.onsubmit();
        }
      }
    );
  },

  /**
   *
   * @param form
   * @param is_robot
   */
  confirmMediuserDeletion: function(form, is_robot) {
    var msg = '';
    if (is_robot === '1') {
      msg = $T('CMediusers-msg-warning_delete_robot_user') + ' ' + $T('CMediusers-msg-robot_user_edit_might_lead_to_malfunctions');
    }
    else {
      msg = $T('CMediusers-msg-warning_delete_human_user');
    }

    Modal.confirm(
      msg,
      {
        className: 'modal confirm big-warning',
        onOK: function() {
          $V(form.elements.del, 1);
          return form.onsubmit();
        }
      }
    );
  },

  doesMediuserExist: function(adeli) {
    if (!adeli) {
      return false;
    }

    new Url('mediusers', 'ajax_does_mediuser_exist')
      .addParam('adeli', adeli)
      .requestJSON(
        function (id) {
          if (id) {
            CMediusers.editMediuser(id);
          }
          else {
            SystemMessage.notify("<div class='error'>" + $T('CMediusers-doesnt-exist') + "</div>");
          }
        }
      );

    return false;
  },

  /**
   * Standard frameworked autocomplete for mediusers with no permission checking
   * Supports function id search (add function_id to the form)
   */
  standardAutocomplete: function(form_name, id_name, input_name) {
    var form = getForm(form_name);
    var id_element    = form[id_name];
    var input_element = form[input_name];

    var url = new Url('system', 'ajax_seek_autocomplete');
    url.addParam('object_class', 'CMediusers');
    url.addParam('input_field', input_name);
    if (this.filter_same_function && form.function_id) {
      url.addParam('function_id', form.function_id.value)
    }
    url.autoComplete(
      input_element,
      null,
      {
        minChars: 3,
        method: 'get',
        select: 'view',
        dropdown: true,
        afterUpdateElement: function(field, selected) {
          var id = selected.getAttribute('id').split("-")[2];
          $V(id_element, id);
        }
      }
    );
  },
  /**
   * Change the correspondant page
   *
   * @param page
   * @param type
   */
  changePageCorrespondants: function (page, type) {
    var form = getForm('search-medecin');
    var url = new Url('mediusers', 'viewDoctorsDirectory');
    url.addFormData(form);
    url.addParam('start', page);
    url.addParam('type', type);
    url.requestUpdate('result-search-medecin-' + type);
  },

  /**
   * Fill the mediuser fields with CMedecin ou CPersonneExercice
   *
   * @param medecin_id
   * @param personne_exercice_id
   * @param user_id
   */
  fillMediuserFields: function (medecin_id, personne_exercice_id, user_id) {
    var url = new Url('mediusers', 'viewEditMediuser');
    url.addParam('medecin_id', medecin_id);
    url.addParam('personne_exercice_id', personne_exercice_id);
    url.addParam('user_id', user_id);
    url.requestModal(800, '70%');
  },
  /**
   * Change the doctors in the directory page
   *
   * @param page
   */
  changePageMedecinAnnuaire: function (page) {
    let form = getForm('search-medecin');

    new Url('mediusers', 'viewDoctorsDirectory')
      .addFormData(form)
      .addParam('start', page)
      .requestUpdate('result-search-medecins');
  },
  /**
   * Search doctors in the directory
   *
   * @param user_id
   * @param rpps
   */
  searchMedecinAnnuaire: function (user_id, rpps) {
    new Url('mediusers', 'viewFilterSearchDoctors')
    .addParam('user_id', user_id)
    .addParam('rpps', rpps)
    .requestModal('70%', '80%');
  },
  /**
   * Unlink the mediuser from health directory
   *
   * @param user_id
   */
  unlinkMedecinAnnuaire: function (user_id) {
    new Url('mediusers', 'unlinkMediuserFromHealthDirectory', 'dosql')
      .addParam('user_id', user_id)
      .requestUpdate('systemMsg', {method: 'post', onComplete: Control.Modal.refresh});
  },
  /**
   * Show doctors in the directory
   *
   * @param form
   */
  showMedecinsAnnuaire: function (form) {
    new Url('mediusers', 'viewDoctorsDirectory')
      .addFormData(form)
    .requestUpdate('result-search-medecins');
  },
  /**
   * Edit a mediuser
   *
   * @param user_id
   * @param element
   */
  editMediuser: function(user_id, element) {
    if (element) {
      element.up('tr').addUniqueClassName('selected');
    }

    let url = new Url("mediusers", "viewEditMediuser");
    window.urlMediuserEdit = url;
    url.addParam("user_id", user_id);
    url.addParam("no_association", this.no_association);
    url.addParam("ldap_user_actif", this.ldap_user_actif);
    url.addParam("ldap_user_deb_activite", this.ldap_user_deb_activite);
    url.addParam("ldap_user_fin_activite", this.ldap_user_fin_activite);
    url.requestModal(800, 750);
    url.modalObject.observe("afterClose", function() {
      getForm('listFilter').onsubmit();
    });
  }
};
