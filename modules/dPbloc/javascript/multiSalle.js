/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

MultiSalle = {
  salles_ids: null,
  chir_id:    null,
  date:       null,

  reloadOps: function(distinct_plages) {
    var url = new Url('bloc', 'ajax_list_operations_multisalle');
    url.addParam('salles_ids[]', MultiSalle.salles_ids, true);
    url.addParam('date', MultiSalle.date);
    url.addParam('chir_id', MultiSalle.chir_id);
    if (!Object.isUndefined(distinct_plages)) {
      url.addParam('distinct_plages', distinct_plages);
    }
    url.requestUpdate('list_ops');
  },

  reloadPlanning: function() {
    new Url('bloc', 'ajax_vw_planning_operations')
      .addParam('salles_ids[]', MultiSalle.salles_ids, true)
      .addParam('date', MultiSalle.date)
      .addParam('chir_id', MultiSalle.chir_id)
      .requestUpdate('planning_ops');
  },

  reloadOpsPlanning: function() {
    MultiSalle.reloadOps();
    MultiSalle.reloadPlanning();
  },

  onMenuClick: function (event, object_id, elt) {
    var form = getForm('alterOp');

    switch (event) {
      case 'pause':
        MultiSalle.editPause(object_id);
        break;
      case 'cancel':
        $V(form._move, 'out');
        $V(form.operation_id, object_id);
        onSubmitFormAjax(form, MultiSalle.reloadOpsPlanning);
        break;
      case 'hslip':
        if ($('planningWeek').select('.plage_planning').length > 2) {
          MultiSalle.choosePlage(object_id, MultiSalle.date, MultiSalle.chir_id);
        }
        else {
          form = getForm("moveOp");
          $V(form.operation_id, object_id);
          onSubmitFormAjax(form, MultiSalle.reloadOpsPlanning);
        }
        break;
      case 'down':
        $V(form._move, 'after');
        $V(form.operation_id, object_id);
        onSubmitFormAjax(form, MultiSalle.reloadOpsPlanning);
        break;
      case 'up':
        $V(form._move, 'before');
        $V(form.operation_id, object_id);
        onSubmitFormAjax(form, MultiSalle.reloadOpsPlanning);
    }
  },

  choosePlage: function(operation_id, date, chir_id, plageop_id, salle_id) {
    new Url('bloc', 'ajax_choose_plage')
      .addParam('operation_id', operation_id)
      .addParam('date', date)
      .addParam('chir_id', chir_id)
      .addParam('plageop_id', plageop_id)
      .addParam('salle_id', salle_id)
      .requestModal('40%', '40%');
  },

  editPause: function(operation_id) {
    new Url('bloc', 'ajax_edit_pause')
      .addParam('operation_id', operation_id)
      .requestModal('30%', '30%');
  },

  changePlage: function(plageop_id, operation_id) {
    var form = getForm('moveOp');
    $V(form.operation_id, operation_id);
    $V(form.plageop_id, plageop_id);
    onSubmitFormAjax(
      form,
      function() {
        Control.Modal.close();
        MultiSalle.reloadPlanning();
      }
    );
  }
};