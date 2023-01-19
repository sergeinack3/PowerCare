/**
 * @package Mediboard\Provenance
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Provenance = {
  /**
   * Edition d'une provenance
   *
   * @param provenance_id
   */
  edit:                  function (provenance_id) {
    new Url('provenance', 'ajax_edit_provenance')
      .addParam('provenance_id', provenance_id)
      .requestModal(400, 300);
  },
  /**
   * Affichage du formulaire de la provenance du patient
   */
  editProvenancePatient: function () {
    $$('.provenanceForm').forEach(
      function (element) {
        element.toggle();
      }
    );
  },
  /**
   * Tri des provenances
   *
   * @param order_col
   * @param order_way
   */
  provSortBy:            function (order_col, order_way) {
    new Url('provenance', 'vw_provenances')
      .addParam("order_way", order_way)
      .addParam("order_col", order_col)
      .requestUpdate('listProvenances');
  },
  /**
   * Liste les provenances de l'établissement
   */
  listProvenances:       function () {
    new Url('provenance', 'vw_provenances')
      .requestUpdate('listProvenances');
  }
};