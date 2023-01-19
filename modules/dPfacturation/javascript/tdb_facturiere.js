/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Gestion du tableau de bord des facturieres
 * */
TdbFacturiere = {
  selectedFactureClass: null,
  currentPage: null,
  currentContainer: null,
  currentForm: null,
  /**
   * S�lection et chargement du tableau de bord en fonction du type de facture (etab ou cabinet)
   *
   * @param factureClass Class de facture s�lectionn�
   * @param container    Container dans lequel charger le retour
   */
  switchFacture: function(factureClass, container) {
    this.selectedFactureClass = factureClass;
    new Url('facturation', 'vw_tdb_facturiere')
      .addParam('get_list', 1)
      .addParam('facture_switch', factureClass)
      .requestUpdate(container);
  },
  /**
   * Chargement d'une liste de factures
   *
   * @param form      Formulaire des filtres
   * @param container Container dans lequel charger le retour
   * @param page      Page s�lectionn�e
   */
  refreshList: function(form, container, page) {
    page = page === undefined ? this.currentPage : page;
    form = form === undefined ? this.currentForm : form;
    container = container === undefined ? this.currentContainer : container;
    this.currentPage = page;
    this.currentForm = form;
    this.currentContainer = container;
    new Url('facturation', 'vw_tdb_facturiere')
      .addParam('get_list',  1)
      .addFormData(form)
      .addNotNullParam('statut[]', $V(form.statut) ? $V(form.statut) : null, true)
      .addParam('praticien_id', $V(form.chirSel))
      .addParam('page', page)
      .requestUpdate(
        container,
        function() {
          this.selectedGuids = [];
        }.bind(this)
      );
  },
  /**
   * Coche/D�coche l'ensemble des inputs de la liste de facture
   *
   * @param input        Checkbox activ�e
   * @param factureClass Classe de facture concern�e
   */
  toggleCheckAll: function(input) {
    this[input.checked ? "checkAll" : "uncheckAll"]();
  },
  /**
   * S�lection de toutes les factures
   *
   * @param factureClass Classe de facture concern�e
   * @param toCheck      (opt) Fixe � coch�e, ou � d�coch�e (d�faut � coch�e)
   */
  checkAll: function(toCheck) {
    toCheck = toCheck === undefined ? true : toCheck;
    this.getCheckboxes().each(
      function(input) {
        input.checked = toCheck;
      }
    );
    this.showMultiActions();
  },
  /**
   * D�s�lection de toutes les factures
   *
   * @param factureClass Classe de facture concern�e
   */
  uncheckAll: function() {
    this.checkAll(false);
    this.showMultiActions(false);
  },
  /**
   * Controle l'�tat de la checkbox g�n�rale
   *
   * @param factureClass Classe de facture concern�e
   */
  checkControl: function() {
    var allCheck = true;
    var showActions = false;
    this.getCheckboxes().each(
      function(input) {
        if (allCheck && !input.checked) {
          allCheck = false
        }
        if (!showActions && input.checked) {
          showActions = true;
        }
      }.bind(this)
    );
    this.getListContainer().down('.tdb-facturiere-allcheckbox').checked = allCheck;
    this.showMultiActions(showActions);
  },
  /**
   * Active les boutons de la barre d'action de la s�lection
   *
   * @param show (opt) Permet de passer en mode activer / d�sactiver (d�faut : true)
   */
  showMultiActions: function(show) {
    show = show === undefined ? true : show;
    this.getListContainer().down('.tdb-facturiere-multi-actions').select('button').each(
      function(button) {
        button.disabled = !show;
      }
    );
  },
  /**
   * Retourne le conteneur d'une liste de facture
   *
   * @param factureClass Classe de facture concern�e
   * @returns {HTMLElement}
   */
  getListContainer: function() {
    return $('tdb_facturiere_' + this.selectedFactureClass);
  },
  /**
   * Retourne la liste des checkbox d'une liste de facture
   *
   * @param factureClass Classe de facture concern�e
   * @returns Array
   */
  getCheckboxes: function() {
    return this.getListContainer().select('.tdb-facturiere-checkbox');
  },
  /**
   * Lance une action globale en fonction de l'action renseign�e
   *
   * @param action Action � appliquer � la s�lection
   */
  multiAction: function(action) {
    var url = new Url('facturation', 'ajax_tdb_facturiere_multi_action')
      .addParam('factures_guid[]', this.multiGetSelection(), true)
      .addParam('action', action);
    if (action === 'print') {
      url.addParam('suppressHeaders', true)
        .pop();
    }
    else {
      url.requestUpdate(
        'tdb_facturiere_' + this.selectedFactureClass + '_multi_result',
        function() {
          this.refreshList();
        }.bind(this)
      );
    }
  },
  /**
   * Ouverture de facture multiple
   */
  multiOpen: function() {
    this.multiAction('open');
  },
  /**
   * Ouverture de facture multiple
   */
  multiCotationOpen: function() {
    this.multiAction('cotationopen');
  },
  /**
   * Cloture de facture multiple
   */
  multiClose: function() {
    this.multiAction('close');
  },
  /**
   * Impression de facture multiple
   */
  multiPrint: function() {
    this.multiAction('print');
  },

  /**
   * R�cup�ration de l'indicateur d'une ligne en fonction de sa checkbox
   * @param input Checkbox
   * @returns {HTMLElement}
   */
  getIndicatorByInput: function(input) {
    return input.up('td').down('.tdb-facturiere-indicator');
  },

  /**
   * R�cup�ration des guid s�lectionn�s
   */
  multiGetSelection: function() {
    var guidList = [];
    this.getCheckboxes().each(
      function(input) {
        if (!input.checked) {
          return false;
        }
        guidList.push(input.get('facture-guid'));
      }
    );
    return guidList;
  },
};
