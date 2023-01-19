/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

FactureAvoir = window.FactureAvoir || {
  editCallback: Prototype.emptyFunction,
  /**
   * Ajout ou modification d'un avoir
   *
   * @param factureAvoirId Identifiant de l'avoir
   * @param callback       Fonction retour à appeler à l'enregistrement / suppression
   * @param factureClass   Classe de la facture (ajout)
   * @param factureId      Identifiant de la facture (ajout)
   */
  edit: function(factureAvoirId, callback, factureClass, factureId) {
    this.editCallback = callback ? callback : Prototype.emptyFunction;
    new Url('facturation', 'ajax_avoir_edit')
      .addNotNullParam('facture_avoir_id', factureAvoirId)
      .addNotNullParam('facture_id', factureId)
      .addNotNullParam('facture_class', factureClass)
      .requestModal(300, null);
  },
  /**
   * Sauvegarde de l'avoir
   *
   * @param form Formulaire de modification de l'avoir
   * @returns {Boolean}
   */
  save: function(form) {
    return onSubmitFormAjax(form, this.editCallbackLauncher.bind(this));
  },
  /**
   * Suppression de l'avoir
   *
   * @param form Formulaire de modification de l'avoir
   */
  delete: function(form) {
    return confirmDeletion(form, {ajax: 1, typeName: $T('CFactureAvoir')}, this.editCallbackLauncher.bind(this));
  },
  /**
   * Lance la fonction retour du formulaire de modification d'un avoir
   */
  editCallbackLauncher: function() {
    Control.Modal.close();
    if (typeof(this.editCallback) === 'function') {
      this.editCallback();
    }
  },
  /**
   * Affiche le document d'impression de l'avoir
   *
   * @param avoirId Identifiant de l'avoir
   */
  print: function(avoirId) {
    new Url('facturation', 'ajax_avoir_print')
      .addParam('facture_avoir_id', avoirId)
      .popup(1000, 1000);
  }
};