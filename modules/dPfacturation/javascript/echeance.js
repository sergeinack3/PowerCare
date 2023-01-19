/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Echeance = {
  currentFactureId: null,
  currentFactureClass: null,
  /**
   * Rafrachissement de la liste des �ch�ances
   */
  refresh: function() {

    if (Facture) {
      Facture.callbackModif(null, this.currentFactureId, this.currentFactureClass);
    }
    else {
      if (!this.currentFactureId || !this.currentFactureClass) {
        return;
      }
      this.loadList(this.currentFactureId, this.currentFactureClass);
    }
  },
  /**
   * Affichage des �ch�ances en fonction d'une facture
   *
   * @param facture_id    Identifiant de facture
   * @param facture_class Classe de facture
   */
  loadList: function(factureId, factureClass) {
    this.currentFactureClass = factureClass;
    this.currentFactureId = factureId;
    new Url('facturation', 'vw_echeancier')
      .addParam('facture_id'   , factureId)
      .addParam('facture_class', factureClass)
      .requestUpdate('echeances-'+factureClass+'-'+factureId);
  },
  /**
   * Affichage de la modale d'ajout d'�ch�ance
   *
   * @param facture_id    Identifiant de facture
   * @param facture_class Classe de facture
   */
  create: function(factureId, factureClass) {
    new Url('facturation', 'ajax_edit_echeance')
      .addParam('facture_id'   , factureId)
      .addParam('facture_class', factureClass)
      .requestModal(500);
  },
  /**
   * Affichage de la modale de modification d'�ch�ance
   *
   * @param echeance_id Identifiant d'�ch�ance
   */
  edit: function(echeanceId) {
    new Url('facturation', 'ajax_edit_echeance')
      .addParam('echeance_id', echeanceId)
      .requestModal(500);
  },
  /**
   * Submit du fomulaire d'ajout/modification d'�ch�ance
   *
   * @param form Formulaire
   */
  submit: function(form) {
    return onSubmitFormAjax(
      form,
      {
        onComplete : function() {
          Control.Modal.close();
          Echeance.refresh();
        }
      }
    );
  },
  /**
   * Submit (en suppression) d'�ch�ance
   *
   * @param form Formulaire
   */
  delete: function(form) {
    return confirmDeletion(
      form,
      {
        typeName:'l\'�ch�ance du',
        objName: $V(form.date)
      },
      function() {
        Control.Modal.close();
        Echeance.refresh();
      }
    )
  },

  Monthly: {
    /**
     * Affichage de la modale de pr�paration � la g�n�ration d'�ch�ances mensuelles
     *
     * @param facture_id    Identifiant de la facture associ�e
     * @param facture_class Classe de la facture associ�e
     */
    launchGeneration: function(factureId, factureClass) {
      new Url('facturation', 'ajax_monthly_echeance')
        .addParam('facture_id', factureId)
        .addParam('facture_class', factureClass)
        .requestModal(
          500,
          null,
          {
            onClose: function() {
              Echeance.refresh();
            }
          });
    },
    /**
     * Mise � jour du montant de la facture affich�e
     *
     * @param form Formulaire contenant les informations d'�ch�ance mensuelle
     */
    updateFactureMontant: function(form) {
      var interest = $V(form.interest);
      var montant = $V(form.montant_total);

      interest = this.round(1 + (interest / 100), 3);
      form.down('#echeance_interest').update(interest);
      var montantInterest = this.round(montant * interest);
      $V(form.montant_total_interest, montantInterest);
      form.down('#facture_montant_total_interest').update(montantInterest);
      return this;
    },
    /**
     * Mise � jour des informations mensuelles
     *
     * @param form Formulaire contenant les informations d'�ch�ance mensuelle
     */
    updateFactureMonths: function(form) {
      var nbMonth = $V(form.nb_month) * 1;
      if (nbMonth === 0) {
        return;
      }
      var montantInterest = $V(form.montant_total_interest);
      var montantPerMonth = montantInterest / nbMonth;
      var montantPerMonthRound = this.round(montantPerMonth, 2);
      var montantLast = this.round(montantPerMonthRound + ((montantPerMonth - montantPerMonthRound)*nbMonth), 2);
      form.down('#montant_monthly').update(montantPerMonthRound);
      form.down('#montant_last_month').update(montantLast);
      form.down('#nb_month').update(nbMonth);
      return this;
    },
    /**
     * Arrondi les montant pour l'affichage des informations d'�ch�ances mensuelles
     *
     * @param input Montant de base
     * @param i     Decimals
     * @returns {number} Montant arrondi
     */
    round: function(input, i) {
      i = i ? i : 3;
      return Math.floor(input * Math.pow(10, i)) / Math.pow(10, i);
    },
    /**
     * Soumission du formulaire de g�n�ration des �ch�ances mensuelles
     *
     * @param form Formulaire contenant les informations d'�ch�ance mensuelles
     */
    generate: function(form) {
      new Url('facturation', 'ajax_monthly_echeance_generate')
        .addFormData(form)
        .requestUpdate(
          'monthly_echeance_result',
          Control.Modal.close
        );
    }
  }
};
