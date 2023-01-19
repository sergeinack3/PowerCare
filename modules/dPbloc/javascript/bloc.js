/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Bloc = {
  reloadSSPIs: function() {
    new Url('bloc', 'ajax_list_sspis')
      .requestUpdate('list_sspis');
  },

  reloadSSPIBlocs: function(sspi_id) {
    new Url('bloc', 'ajax_list_sspi_blocs')
      .addParam('sspi_id', sspi_id)
      .requestUpdate('sspi_bloc_' + sspi_id);
  },

  reloadLists: function(sspi_id) {
    Bloc.reloadSSPIs();
    Bloc.reloadSSPIBlocs(sspi_id);
  },

  editPoste: function(poste_id, sspi_id, show_sspi) {
    if (Object.isUndefined(show_sspi)) {
      show_sspi = 0;
    }

    new Url('bloc', 'ajax_edit_poste')
      .addParam('poste_id', poste_id)
      .addParam('sspi_id', sspi_id)
      .addParam('show_sspi', show_sspi)
      .requestModal('40%', null, {onClose: show_sspi ? Bloc.displayListPostesPreop : Bloc.reloadSSPIPostes.curry(sspi_id) });
  },

  reloadSSPIPostes: function(sspi_id) {
    new Url('bloc', 'ajax_list_sspi_postes')
      .addParam('sspi_id', sspi_id)
      .requestUpdate('sspi_postes_' + sspi_id);
  },

  /**
   * Affichage de la vue d'import de salles de bloc
   */
  popupImport: function() {
    new Url("dPbloc", "salles_import_csv")
      .popup(800, 600, "Import des Salles");
  },

  /**
   * Affiche la liste des blocs opératoires
   *
   * @param {string} bloc_id   - bloc sélectionné
   */
  displayListBlocs: function(bloc_id) {
    new Url('bloc', 'ajax_list_blocs')
      .addParam('bloc_id', bloc_id)
      .requestUpdate('blocs');
  },

  /**
   * Ouvre une modale pour modifier un bloc
   *
   * @param bloc_id - bloc à modifier
   */
  editBloc: function (bloc_id) {
    new Url('bloc', 'ajax_edit_bloc')
      .addParam('bloc_id', bloc_id)
      .requestModal(800);
  },

  /**
   * Met à jour la ligne sélectionnée
   *
   * @param table_name - ID de la table contenant les lignes
   * @param tr         - ligne (tr) sélectionnée
   */
  updateSelectedRow: function(table_name, tr) {
    $(table_name).select('tr').invoke("removeClassName", "selected");
    if (tr) {
      tr.addClassName("selected");
    }
  },

  /**
   * Met à jour le bloc sélectionné
   *
   * @param bloc_tr - bloc (tr) sélectionné
   */
  updateSelectedBloc: function (bloc_tr) {
    Bloc.updateSelectedRow('blocs', bloc_tr);
  },

  /**
   * Recharge la liste des blocs après ajout ou modification
   *
   * @param bloc_id - bloc ajouté ou modifié
   */
  afterEditBloc: function (bloc_id) {
    Control.Modal.close();
    Bloc.displayListBlocs(bloc_id);
  },

  /**
   * Affiche la liste des salles des blocs
   *
   * @param {string} salle_id  - salle sélectionnée
   */
  displayListSalles: function(salle_id) {
    new Url('bloc', 'ajax_list_salles')
      .addParam('salle_id', salle_id)
      .requestUpdate('salles');
  },

  /**
   * Ouvre une modale pour modifier une salle
   *
   * @param salle_id - salle à modifier
   */
  editSalle: function (salle_id) {
    new Url('bloc', 'ajax_edit_salle')
      .addParam('salle_id', salle_id)
      .requestModal(800);
  },

  /**
   * Met à jour la salle sélectionnée
   *
   * @param salle_tr - salle (tr) sélectionnée
   */
  updateSelectedSalle: function (salle_tr) {
    Bloc.updateSelectedRow('salles', salle_tr);
  },

  /**
   * Recharge la liste des salles après ajout ou modification
   *
   * @param salle_id - salle ajoutée ou modifiée
   */
  afterEditSalle: function (salle_id) {
    Control.Modal.close();
    Bloc.displayListSalles(salle_id);
  },

  /**
   * Affiche la liste des SSPIs
   *
   * @param {string} sspi_id  - SSPI sélectionnée
   */
  displayListSSPIS: function(sspi_id) {
    new Url('bloc', 'vw_list_sspis')
      .addParam('sspi_id', sspi_id)
      .requestUpdate('sspis');
  },

  /**
   * Ouvre une modale pour modifier une SSPI
   *
   * @param sspi_id - SSPI à modifier
   */
  editSSPI: function (sspi_id) {
    new Url('bloc', 'ajax_edit_sspi')
      .addParam('sspi_id', sspi_id)
      .requestModal(800);
  },

  /**
   * Met à jour la SSPI sélectionnée
   *
   * @param sspi_tr - SSPI (tr) sélectionnée
   */
  updateSelectedSSPI: function (sspi_tr) {
    Bloc.updateSelectedRow('sspis', sspi_tr);
  },

  /**
   * Recharge la liste des SSPIs après ajout ou modification
   *
   * @param sspi_id - SSPI ajoutée ou modifiée
   */
  afterEditSSPI: function (sspi_id) {
    Control.Modal.close();
    Bloc.displayListSSPIS(sspi_id);
  },

  displayListPostesPreop: function() {
    new Url('bloc', 'vw_list_postes_preop')
      .requestUpdate('postes_preop');
  },

  changeChecklistDefaut: function (element) {
    var form = getForm('salle');
    elementDecoupe = element.split('-');
    if (element === "0") {
      $V(form.checklist_defaut_id, "");
      $V(form.checklist_defaut_has, "");
    }
    else if (elementDecoupe[1] === "id") {
      $V(form.checklist_defaut_id, elementDecoupe[0]);
      $V(form.checklist_defaut_has, "");
    }
    else if (elementDecoupe[1] === "has") {
      $V(form.checklist_defaut_has, elementDecoupe[0]);
      $V(form.checklist_defaut_id, "");
    }
  }
};