/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Main.add(function () {
  var elements;
  var divGauche = $('list-chambres-non-placees');

  //Tous les éléments draggables: les div
  elements = $$('div.chambre');
  elements.each(function (e) {
    new Draggable(e, {revert: true});
  });

  //Toutes les zones droppables: les td
  Droppables.add(divGauche, {onDrop: TraiterDrop});

  elements = $$('td.conteneur-chambre');
  elements.each(function (e) {
    Droppables.add(e, {onDrop: TraiterDrop});
  });
});

function TraiterDrop(element, zoneDrop) {
  zoneDrop.insert(element);// Ajouter un fils à 'zoneDrop'
  savePlan(element);// sauvegarde automatique à chaque déplacement  
}

function savePlan(element) {
  var chambre_id = element.get("chambre-id");
  var form = getForm("Emplacement-" + chambre_id);

  //Si la chambre se situe sur la grille
  if (element.parentNode.get("x") && element.parentNode.get("y")) {
    form.plan_x.value = element.parentNode.get("x");
    form.plan_y.value = element.parentNode.get("y");
  }
  else {
    form.del.value = "1";
  }
  return onSubmitFormAjax(form, PlanEtage.refreshPlan);
}

PlanEtage = {
  show:        function (chambre_id) {
    var url = new Url('hospi', 'ajax_vw_emplacement');
    url.addParam('chambre_id', chambre_id);
    url.requestModal(300);
  },
  refreshPlan: function () {
    var form = getForm('planEtage');
    var url = new Url('hospi', 'vw_plan_etage');
    url.addParam("refresh", 1);
    url.addParam("services_id", [$V(form.services_id)].flatten().join(","));
    url.requestUpdate('plan_etage_service');
  },
  onSubmit:    function (form) {
    return onSubmitFormAjax(form, function () {
      Control.Modal.close();
      PlanEtage.refreshPlan();
    });
  }
};