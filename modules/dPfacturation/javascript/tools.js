/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

FactuTools = {
  seeFactEtab: function(see, page){
    if (page || !see) {
      Control.Modal.close();
    }
    new Url('facturation', 'ajax_corrected_fact_etab')
      .addParam('see', see)
      .addParam('page', page)
      .requestModal();
  },
  seeFactEtabPage: function(page){
    self.seeFactEtab(1, page);
  },
  /**
   * Affichage des consultations et evenements patient en erreur
   */
  showElements: function(params) {
    if (params && params.autoClose) {
      Control.Modal.close();
    }
    var url = new Url('facturation', (params && params.custom_page) ? params.custom_page : 'tools_elements')
      .addNotNullParam('date_min',      (params) ? params.date_min : null)
      .addNotNullParam('date_max',      (params) ? params.date_max : null)
      .addNotNullParam('praticien_id',  (params) ? params.praticien_id : null)
      .addNotNullParam('current',       (params) ? params.current : null)
      .addNotNullParam('element_class', (params) ? params.type : null);
    if (params && params.container) {
      url.requestUpdate(params.container);
    }
    else {
      url.requestModal(1000, '90%');
    }
    return false;
  },

  showElementsPage: function(type, current) {
    return this.showElements(
      {
        current: current,
        container: 'tt_'+type+'_container',
        type: type
      }
    );
  },

  showMultiFactures: function(params) {
    var customPage = {custom_page : 'tools_multi_factures'};
    return this.showElements(params ? Object.extend(customPage, params) : customPage);
  },
  showPaidFactures: function(params) {
    var url = new Url('facturation', 'tools_paid_factures')
      .addNotNullParam('date_min',      (params) ? params.date_min : null)
      .addNotNullParam('date_max',      (params) ? params.date_max : null)
      .addNotNullParam('praticien_id',  (params) ? params.praticien_id : null)
      .addNotNullParam('lite',          (params && params.container) ? 1 : 0)
      .addNotNullParam('current',       (params) ? params.current : null);
    if (params && params.container) {
      url.requestUpdate(params.container);
    }
    else {
      url.requestModal(1200, '90%');
    }
    return false;
  },
  openElement: function(elementClass, elementId, patientId) {
    if (elementClass === 'CConsultation') {
      Consultation.editModal(elementId, null, '');
    }
    else if (elementClass === 'CEvenementPatient') {
      EvtPatient.showEvenementsPatient(patientId, null, false);
    }
    else {
      return false;
    }
  },
  unlinkFactureCabinet: function(elementClass, elementId, factureId, button) {
    if (confirm($T('CFactureLiaison-confirm-delete'))) {
      button.disabled = true;
      button.removeClassName('magic_wand').addClassName('loading');
      new Url('facturation', 'tools_unlink_facture')
        .addParam('element_id', elementId)
        .addParam('element_class', elementClass)
        .addParam('facture_id', factureId)
        .requestJSON(
          function(response) {
            this.reponseButtonAction(button, response);
          }.bind(this)
        );
    }
  },
  reponseButtonAction: function(button, response) {
    if (response.state === 1) {
      var buttonClass = 'tick';
      var responseClass = 'small-success';
    }
    else {
      var buttonClass = 'warning';
      var responseClass = 'small-warning';
    }
    button.removeClassName('loading')
      .addClassName(buttonClass);
    window.parent.SystemMessage.notify('<div class="' + responseClass + '">' +response.msg+ '</div>');
  },
  showMultiFacturesPage: function(type, current) {
    return this.showMultiFactures(
      {
        current: current,
        container: 'tt_'+type+'_container',
        type: type
      }
    );
  },
  showPaidFacturesPage: function(container, current) {
    return this.showPaidFactures(
      {
        container: container,
        current: current
      }
    );
  },

  /**
   * Gestionnaire de liaison de factures
   */
  FactuLiaisonManager: {
    /**
     * Formulaire de filtre utilisé
     */
    currentFilterForm: null,
    /**
     * Guid de l'objet sélectionné
     */
    selectedObjectGuid: null,
    /**
     * Signature de l'objet sélectionné
     */
    selectedObjectSignature: null,
    /**
     * Guid de l'objet en fast-unlink
     */
    fastObjectGuid: null,
    /**
     * Guid de la facture de l'objet en fast-unlink
     */
    fastFactureObjectGuid: null,
    /**
     * Rafraichissement de la liste (objets, factures ou les deux)
     *
     * @param form Formulaire de filtre
     * @param list Liste à rafraichir
     */
    refreshList: function(form, list) {
      var target = typeof(list) === 'undefined' ? 'factureliaison_lists' : list;
      form = typeof(form) === 'undefined' ? this.currentFilterForm : form;
      this.currentFilterForm = form;
      new Url('facturation', 'vw_factureliaison_manager')
        .addFormData(form)
        .addParam('praticien_id', $V(form.chirSel))
        .addNotNullParam('selected_guid', this.fastObjectGuid)
        .addNotNullParam('facture_selected_guid', this.fastFactureObjectGuid)
        .addNotNullParam('target', target)
        .requestUpdate(
          target,
          function() {
            if (list === 'factures_list' && this.selectedObjectGuid) {
              this.setFacturesSelectable();
            }
            this.triggerFastFactureSelection();
          }.bind(this)
        );
    },
    /**
     * Sélection d'un objet
     *
     * @param button     Bouton cliqué
     * @param objectGuid Guid de l'objet
     * @param signature  Signature de l'objet
     */
    selectObject: function(button, objectGuid, signature) {
      if (this.selectedObjectGuid === objectGuid) {
        return this.unselectObject(button);
      }
      this.selectedObjectGuid = objectGuid;
      button.setAttribute('title', $T('CFactureLiaison.Manager cancel object link'));
      button.addClassName('selected')
        .down('i').removeClassName('fa-arrow-right')
        .addClassName('fa-times');
      button.up('td').select('.r-actions').each(
        function(e) {
          if (!e.down('.fa-arrow-right')) {
            return;
          }
          e.addClassName('disabled');
        }
      );
      this.setFacturesSelectable(signature);
    },
    /**
     * Désélection d'un objet
     *
     * @param button Bouton cliqué
     */
    unselectObject: function(button) {
      this.selectedObjectGuid = null;
      $$('.factureliaison-facture').invoke('show');
      if (button) {
        button.setAttribute('title', $T('CFactureLiaison.Manager link to an invoice'));
        button.removeClassName('selected')
          .down('i').addClassName('fa-arrow-right')
          .removeClassName('fa-times');
        button.up('td').select('.disabled').invoke('removeClassName', 'disabled');
      }
      this.setFacturesDisabled();
    },
    /**
     * Sélection d'une facture
     *
     * @param factureGuid Guid de la facture
     */
    selectFacture: function(button, factureGuid) {
      this.linkObject(button, this.selectedObjectGuid, factureGuid);
    },
    /**
     * Configuration des factures pour être sélectionnable
     *
     * @param signature Signature des factures sélectionnables
     */
    setFacturesSelectable: function(signature) {
      if (signature) {
        this.selectedObjectSignature = signature;
      }
      else {
        signature = this.selectedObjectSignature;
      }
      $$('.factureliaison-facture').invoke('hide');
      $$('.factureliaison-' + signature).invoke('show');
      $('factures_list').select('.l-actions.disabled').invoke('removeClassName', 'disabled');
    },
    /**
     * Configuration des factures pour qu'elles ne soient pas sélectionnables
     */
    setFacturesDisabled: function() {
      $('factures_list').select('.l-actions').invoke('addClassName', 'disabled');
    },
    /**
     * Raccourcis pour délier un objet d'une facture
     *
     * @param objectGuid Guid de l'objet
     */
    objectFastUnlink: function(objectGuid) {
      top.location.href = new Url('facturation', 'vw_factureliaison_manager', 'tab')
        .addParam('fast_object_guid', objectGuid)
        .make();
    },
    /**
     * Liaison d'un objet avec une facture
     *
     * @param objectGuid  Guid de l'objet
     * @param factureGuid Guid de la facture
     */
    linkObject: function(button, objectGuid, factureGuid) {
      if (!confirm($T('CFactureLiaison.Manager confirm object and facture link'))) {
        return;
      }
      new Url('facturation', 'ajax_factureliaison_manager_link_object')
        .addParam('facture_guid', factureGuid)
        .addParam('object_guid', objectGuid)
        .requestUpdate(
          button.up('div'),
          function() {
            this.refreshList();
          }.bind(this)
        );
    },
    /**
     * Rupture de la liaison entre un objet et une facture
     *
     * @param button      Bouton cliqué
     * @param objectGuid  Guid de l'objet
     * @param factureGuid Guid de la facture
     */
    unlinkObject: function(button, objectGuid, factureGuid) {
      if (!confirm($T('CFactureLiaison.Manager confirm object and facture unlink'))) {
        return;
      }
      new Url('facturation', 'ajax_factureliaison_manager_unlink_object')
        .addParam('facture_guid', factureGuid)
        .addParam('object_guid', objectGuid)
        .requestUpdate(
          button.up('div'),
          function() {
            this.refreshList();
          }.bind(this)
        );
    },
    /**
     * Rupture des liaisons entre une facture et tous ses objets
     *
     * @param button      Bouton cliqué
     * @param factureGuid Guid de la facture
     */
    unlinkAllByFacture: function(button, factureGuid) {
      if (!confirm($T('CFactureLiaison.Manager confirm facture unlinks'))) {
        return;
      }
      new Url('facturation', 'ajax_factureliaison_manager_unlink_all')
        .addParam('facture_guid', factureGuid)
        .requestUpdate(
          button.up('div'),
          function() {
            this.refreshList();
          }.bind(this)
        );
    },
    /**
     * Affichage des éléments enfant d'une facture
     *
     * @param button Bouton cliqué
     */
    showChildren: function(container) {
      if (container.hasClassName('actions')) {
        container = container.up('.factureliaison-element-container')
      }

      var actionContainer = container.down('.r-actions');
      var actionContainerSec = container.down('.r-actions-sec');
      if (container.hasClassName('show-children')) {
        container.removeClassName('show-children');
        actionContainer.setAttribute('title', $T('CFactureLiaison.Manager show children'));
        actionContainer.down('i').removeClassName('fa-arrow-up')
          .addClassName('fa-list');
        if (actionContainerSec) {
          actionContainerSec.hide();
        }
      }
      else {
        container.addClassName('show-children');
        actionContainer.setAttribute('title', $T('CFactureLiaison.Manager hide children'));
        actionContainer.down('i').addClassName('fa-arrow-up')
          .removeClassName('fa-list');
        if (actionContainerSec) {
          actionContainerSec.show();
        }
      }
    },
    /**
     * Déclenchement de la sélection rapide
     *
     * @param fastObjectGuid        Guid de l'objet sélectionné
     * @param fastFactureObjectGuid Guid de la facture associée
     */
    triggerFastSelection: function(fastObjectGuid, fastFactureObjectGuid) {
      this.fastObjectGuid = fastObjectGuid;
      this.fastFactureObjectGuid = fastFactureObjectGuid;
      this.triggerFastFactureSelection();
    },
    /**
     * Traitement du déclenchement de la sélection rapide pour la partie facture
     */
    triggerFastFactureSelection: function() {
      if (this.fastFactureObjectGuid !== null && this.fastFactureObjectGuid !== '') {
        var factureContainer = $$('.factureliaison-' + this.fastFactureObjectGuid);
        if (factureContainer.length > 0 && !factureContainer[0].hasClassName('show-children')) {
          this.showChildren(factureContainer[0]);
        }
      }
    }
  }
};
