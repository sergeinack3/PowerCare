/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Tarif = {
  chir_id: null,
  function_id: null,
  group_id: null,
  modal: null,
  url: null,
  refreshTarifs: function() {},

  reloadListTarifs: function(prat_id, mode) {
    var url = new Url('cabinet', 'ajax_list_tarifs');
    url.addParam('prat_id', prat_id);
    url.addParam('mode', mode);
    url.requestUpdate($('tarifs_'+mode));
  },

  newCodable: function(codable_id, codable_class, prat_id) {
    var url = new Url('cabinet', 'ajax_edit_tarif');
    url.addParam('codable_id',    codable_id);
    url.addParam('codable_class', codable_class);
    url.addParam('prat_id',       prat_id);
    url.requestModal();
    Tarif.modal = url.modalObject;
  },

  edit: function(tarif_id, prat_id) {
    var url = new Url('cabinet', 'ajax_edit_tarif');
    url.addParam('tarif_id', tarif_id);
    url.addParam('prat_id', prat_id);
    url.requestModal(0, 0, {onClose : this.refreshTarifs});
    this.url = url;
  },

  submit: function(form) {
      console.log('Tarif.submit()');
    return onSubmitFormAjax(form, {
      onComplete : function() {
        if (Tarif.modal) {
          Tarif.modal.close();
        }
        else {
          Control.Modal.close();
        }
      }
    });
  },

  recalcul: function(form) {
    return onSubmitFormAjax(form, {
      onComplete : function() {
        Tarif.url.refreshModal();
      }
    });
  },

  updateTotal: function () {
    var form = getForm("editFrm");
    if (!form.secteur1 || !form.secteur1) {
      return;
    }

    var secteur1 = form.secteur1.value;
    var secteur2 = form.secteur2.value;
    var secteur3 = form.secteur3.value;
    var taux_tva = form.taux_tva.value;

    if (secteur1 == ""){
      secteur1 = 0;
    }
    if (secteur2 == ""){
      secteur2 = 0;
    }
    if (secteur3 == ""){
      secteur3 = 0;
    }

    $V(form._du_tva, (secteur3*(taux_tva)/100).toFixed(2));
    form._somme.value = parseFloat(secteur1) + parseFloat(secteur2) + parseFloat(secteur3) + parseFloat(form._du_tva.value);
    form._somme.value = Math.round(form._somme.value*100)/100;
  },

  updateSecteur2: function() {
    var form = getForm("editFrm");
    var secteur1 = form.secteur1.value;
    var secteur3 = form.secteur3.value;
    var du_tva = form._du_tva.value;
    var somme    = form._somme.value;

    if (somme == "") {
      somme = 0;
    }
    if (secteur1 == "") {
      secteur1 = 0;
    }
    if (secteur3 == "") {
      secteur3 = 0;
    }
    if (du_tva == "") {
      du_tva = 0;
    }

    form.secteur2.value = parseFloat(somme) - parseFloat(secteur1) - parseFloat(secteur3) - parseFloat(du_tva);
    form.secteur2.value = Math.round(form.secteur2.value*100)/100;
  },

  updateOwner: function() {
    var form = getForm("editFrm");
    var type = $V(form._type);

    if (type == "chir") {
      $V(form.chir_id, this.chir_id);
      $V(form.function_id, "");
      $V(form.group_id, "");
      $('edit_actes_tarif').enable();
    }

    if (type == "function") {
      $V(form.chir_id, "");
      $V(form.function_id, this.function_id);
      $V(form.group_id, "");
      $('edit_actes_tarif').enable();
    }
    if (type == "group") {
      $V(form.chir_id, "");
      $V(form.function_id, "");
      $V(form.group_id, this.group_id);
      $('edit_actes_tarif').disable();
    }
  },

  forceRecompute: function() {
    $("force-recompute").show();
    var form = getForm("editFrm");
    // form.save.disabled = true;
  },

  editActes: function(form) {
    var url = new Url('cabinet', 'ajax_codage_tarif');

    if ($V(form.tarif_id)) {
      url.addElement(form.tarif_id);
    }
    else {
      url.addElement(form.chir_id);
      url.addElement(form.function_id);
      url.addElement(form.description);

      if (form.codes_ccam) {
        url.addElement(form.codes_ccam);
      }
      if (form.codes_ngap) {
        url.addElement(form.codes_ngap);
      }
      if (form.codes_lpp) {
        url.addElement(form.codes_lpp);
      }
    }

    url.requestModal(-200, 400, {
      showClose: false,
    });
  },

  switchOwner: function(from, to, prat_id) {
    var ids = [];
    $$('input[name="move_tarifs-' + from + '"]:checked').each(function(checkbox) {
      ids.push(checkbox.get('tarif_id'));
    });
    if (ids.length == 0) {
      Modal.alert('Veuillez sélectionner les tarifs à déplacer');
    }
    else {
      var url = new Url('cabinet', 'do_switch_tarif_owner', 'dosql');
      url.addParam('mode', to);
      url.addParam('tarif_ids', ids.join('|'));
      url.addParam('prat_id', prat_id);
      url.requestUpdate('systemMsg', {
        method: 'post',
        getParameters: {m: 'cabinet', dosql: 'do_switch_tarif_owner'},
        onComplete: function() {
          Tarif.reloadListTarifs(prat_id, 'CMediusers');
          Tarif.reloadListTarifs(prat_id, 'CFunctions');
        }
      });
    }
  },

  toggleTarifs: function(input, mode) {
    var checked = input.checked;
    $$('input[name="move_tarifs-' + mode + '"]').each(function(checkbox) {
      checkbox.checked = checked;
    });
  },

  selectPrat: function(pratId, showTarifsEtab) {
    this.refreshTarifs = function() {
      Tarif.reloadListTarifs(pratId, 'CMediusers');
      Tarif.reloadListTarifs(pratId, 'CFunctions');
      if (showTarifsEtab) {
        Tarif.reloadListTarifs(pratId, 'CGroups');
      }
    };
    this.refreshTarifs();
  }
};
