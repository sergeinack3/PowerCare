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
   * Sélection et chargement du tableau de bord en fonction du type de facture (etab ou cabinet)
   *
   * @param factureClass Class de facture sélectionné
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
   * @param page      Page sélectionnée
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
   * Coche/Décoche l'ensemble des inputs de la liste de facture
   *
   * @param input        Checkbox activée
   * @param factureClass Classe de facture concernée
   */
  toggleCheckAll: function(input) {
    this[input.checked ? "checkAll" : "uncheckAll"]();
  },
  /**
   * Sélection de toutes les factures
   *
   * @param factureClass Classe de facture concernée
   * @param toCheck      (opt) Fixe à cochée, ou à décochée (défaut à cochée)
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
   * Désélection de toutes les factures
   *
   * @param factureClass Classe de facture concernée
   */
  uncheckAll: function() {
    this.checkAll(false);
    this.showMultiActions(false);
  },
  /**
   * Controle l'état de la checkbox générale
   *
   * @param factureClass Classe de facture concernée
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
   * Active les boutons de la barre d'action de la sélection
   *
   * @param show (opt) Permet de passer en mode activer / désactiver (défaut : true)
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
   * @param factureClass Classe de facture concernée
   * @returns {HTMLElement}
   */
  getListContainer: function() {
    return $('tdb_facturiere_' + this.selectedFactureClass);
  },
  /**
   * Retourne la liste des checkbox d'une liste de facture
   *
   * @param factureClass Classe de facture concernée
   * @returns Array
   */
  getCheckboxes: function() {
    return this.getListContainer().select('.tdb-facturiere-checkbox');
  },
  /**
   * Lance une action globale en fonction de l'action renseignée
   *
   * @param action Action à appliquer à la sélection
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
   * Récupération de l'indicateur d'une ligne en fonction de sa checkbox
   * @param input Checkbox
   * @returns {HTMLElement}
   */
  getIndicatorByInput: function(input) {
    return input.up('td').down('.tdb-facturiere-indicator');
  },

  /**
   * Récupération des guid sélectionnés
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
