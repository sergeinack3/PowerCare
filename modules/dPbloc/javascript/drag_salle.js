/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Main.add(function () {
  var elements;
  var divGauche = $('list-salles-non-placees');

  //Tous les éléments draggables: les div
  elements = $$('div.salle');
  elements.each(function (e) {
    new Draggable(e, {revert: true});
  });

  //Toutes les zones droppables: les td
  Droppables.add(divGauche, {onDrop: PlanEtageBloc.TraiterDrop});

  elements = $$('td.conteneur-salle');
  elements.each(function (e) {
    Droppables.add(e, {onDrop: PlanEtageBloc.TraiterDrop});
  });
});

PlanEtageBloc = {
  /**
   * Show the room location
   *
   * @param salle_id
   */
  show: function (salle_id) {
    var url = new Url('bloc', 'ajax_vw_emplacement_salle');
    url.addParam('salle_id', salle_id);
    url.requestModal(300);
  },
  /**
   * Refresh the plan
   */
  refreshPlan: function () {
    var form = getForm('planEtageBloc');
    var url = new Url('bloc', 'vw_plan_etage_blocs');
    url.addParam("refresh", 1);
    url.addParam("blocs_id", [$V(form.blocs_id)].flatten().join(","));
    url.requestUpdate('plan_etage_blocs');
  },
  /**
   * Save the plan
   *
   * @param element
   * @returns {Boolean}
   */
  savePlan: function (element) {
    var salle_id = element.get("salle-id");
    var form = getForm("EmplacementSalle-" + salle_id);

    //Si la salle se situe sur la grille
    if (element.parentNode.get("x") && element.parentNode.get("y")) {
      form.plan_x.value = element.parentNode.get("x");
      form.plan_y.value = element.parentNode.get("y");
    }
    else {
      form.del.value = "1";
    }
    return onSubmitFormAjax(form, PlanEtageBloc.refreshPlan);
  },
  /**
   * Treatment for the drag and drop
   *
   * @param element
   * @param zoneDrop
   * @constructor
   */
  TraiterDrop: function(element, zoneDrop) {
    zoneDrop.insert(element);// Ajouter un fils à 'zoneDrop'
    PlanEtageBloc.savePlan(element);// sauvegarde automatique à chaque déplacement
  },
  /**
   * Submit the form
   *
   * @param form
   * @returns {Boolean}
   */
  onSubmit:    function (form) {
    return onSubmitFormAjax(form, function () {
      Control.Modal.close();
      PlanEtageBloc.refreshPlan();
    });
  }
};
