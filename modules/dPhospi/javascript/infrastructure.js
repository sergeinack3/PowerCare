/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Infrastructure = window.Infrastructure || {
  modal_url: {},
  hide_inactives: false,

  showInfrastructure: function (type_id, valeur_id, update_name) {
    var url = new Url("dPhospi", "inc_vw_infrastructure");
    url.addParam(type_id, valeur_id);
    if (update_name) {
      url.requestUpdate(update_name);
    }
    else {
      var uf_type = $$('.uf_types .active')[0].key;
      url.addParam('uf_type', uf_type)
        .requestModal();
    }
  },

  toggleService: function(id, button, count_cancelled_elements) {
    this.hide_inactives = (button.get("hide") === "0");
    var services = $(id).select('td.service_cancelled');
    services.forEach(function(e) {
      if (e.parentElement.hasClassName("triggerHide")) {
        e.click();
      }
      e.parentElement.toggle();

    });
    $(id).select("td.chambre_cancelled").forEach(function(e) {
      e.parentElement.toggle();
    });
    $(id).select("span.lit_cancelled").forEach(function(e) {
      e.toggle();
    });

    if (this.hide_inactives) {
      button.innerText = $T("CService-see_cancelled", count_cancelled_elements);
      button.removeClassName("zoom-out");
      button.addClassName("zoom-in");
      $('services_title').innerText = $T('CService-not_cancelled.all');
    }
    else {
      button.innerText = $T("CService-hide_cancelled");
      button.removeClassName("zoom-in");
      button.addClassName("zoom-out");
      $('services_title').innerText = $T('CService.all');
    }
    button.set("hide", this.hide_inactives ? "1" : "0");
  },

  initShow: function() {
    if (this.hide_inactives) {
      $("show_button").click();
    }
  },

  addeditSecteur: function (secteur_id) {
    var width = 800;
    var height = '90%';
    if (secteur_id == 0) {
      width = 300;
      height = 270;
    }
    var url = new Url("dPhospi", "ajax_addedit_secteur");
    url.addParam('secteur_id', secteur_id);
    url.requestModal(width, height, {
      onClose: function () {
        var url = new Url("dPhospi", "ajax_list_infrastructure");
        url.addParam("type_name", 'secteurs');
        url.requestUpdate('secteurs');
      }
    });
  },

  addeditService: function (service_id) {
    var url = new Url("dPhospi", "ajax_addedit_service");
    url.addParam('service_id', service_id);
    url.requestModal('80%', '90%', {
      onClose: function () {
        var url = new Url("dPhospi", "ajax_list_infrastructure");
        url.addParam("type_name", 'services');
        url.requestUpdate('services');
      }
    });
  },

  addeditChambre: function (chambre_id, service_id) {
    var width = 900;
    var height = 600;
    if (chambre_id == 0) {
      width = 500;
      height = 300;
    }

    var url = new Url("dPhospi", "ajax_addedit_chambre");
    url.addParam('chambre_id', chambre_id);
    url.addParam('service_id', service_id);
    this.modal_url = url;
    url.requestModal(width, height, {
      onClose: function () {
        var url = new Url("dPhospi", "ajax_list_infrastructure");
        url.addParam("type_name", 'services');
        url.requestUpdate('services');
      }
    });
  },

  addeditChambreCallback: function (chambre_id, obj) {
    Infrastructure.addeditChambre(chambre_id, obj.service_id);
  },

  addLit: function (chambre_id, lit_id, update_name) {
    var url = new Url("dPhospi", "ajax_addedit_lit");
    url.addParam('chambre_id', chambre_id);
    url.addParam('lit_id', lit_id);
    if (!$('line_lit-CLit-none')) {
      url.requestUpdate(update_name, {
        insertion: "bottom", onComplete: function () {
          $('nom').focus();
          $('nom').focus();
        }
      });
    }
  },

  reloadLitLine:      function (lit_id, chambre_id) {
    var container = "line_lit-CLit-" + lit_id;
    var url = new Url("dPhospi", "ajax_addedit_lit");
    url.addParam('chambre_id', chambre_id);
    url.addParam('lit_id', lit_id);
    (lit_id) ? url.requestUpdate(container) : this.modal_url.refreshModal();
  },
  confirmDeletionLit: function (form) {
    Modal.confirm(
      $T("CLit-confirm-Delete %s?", $V(form.nom)),
      {
        onOK: function () {
          Infrastructure.deleteLit(
            form,
            {
              onComplete: function () {
                Infrastructure.modal_url.refreshModal();
              }
            }
          );
        }
      }
    );
  },

  deleteLit: function (form, callback) {
    $V(form.del, "1");
    return onSubmitFormAjax(form, callback);
  },

  editLitLiaisonItem: function (lit_id) {
    var container = "edit_liaisons_items-" + lit_id;
    var url = new Url("dPhospi", "ajax_edit_liaisons_items");
    url.addParam("lit_id", lit_id);
    url.requestUpdate(container);
  },

  setValueForm:         function (name_form, name_input, value_input) {
    var form = getForm(name_form);
    $V(form[name_input], value_input);
  },
  loadListUsersService: function (service_id) {
    var url = new Url("hospi", "vw_list_user_service");
    url.addParam("service_id", service_id);
    url.requestUpdate("affectation_user");
  },

  viewStatUf: function (uf_id) {
    new Url("hospi", "vw_stats_uf")
      .addParam("uf_id", uf_id)
      .requestModal(800, 600);
  },

  importUF: function () {
    new Url('hospi', 'ajax_import_uf')
      .requestModal();
  },

  importUfLink: function () {
    new Url('hospi', 'ajax_import_uf_link')
      .requestModal();
  },

  exportUF: function () {
    new Url('hospi', 'vw_export_uf')
      .requestModal();
  },

  removeServiceFromSecteur: function (service_id) {
    var oForm = getForm('delService');
    $V(oForm.service_id, service_id);
    if (confirm($T('CSecteur-remove_service'))) {
      oForm.onsubmit();
    }
  },

  reloadSecteurServices: function (secteur_id) {
    return new Url('dPhospi', 'ajax_services_secteur')
      .addParam('secteur_id', secteur_id)
      .requestUpdate('services_secteur');
  },

  initSecteurEditForm: function (group_id) {
    return new Url("system", "httpreq_field_autocomplete")
      .addParam('class', 'CService')
      .addParam('field', 'service_id')
      .addParam('view_field', 'nom')
      .addParam('show_view', true)
      .addParam('where[group_id]', group_id)
      .addParam("input_field", "_service_autocomplete")
      .autoComplete(getForm('addService').elements._service_autocomplete, null, {
        minChars:           2,
        method:             "get",
        select:             "view",
        dropdown:           true,
        afterUpdateElement: function (field, selected) {
          $V(field.form['service_id'], selected.getAttribute('id').split('-')[2]);
          field.form.onsubmit();
          $V(field.form._service_autocomplete, '');
        }
      });
  },

  UF: {
    refreshList: function () {
      getForm('display-ufs').onsubmit();
      Control.Modal.close();
    }
  }
};
