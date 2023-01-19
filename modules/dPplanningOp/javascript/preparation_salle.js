/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

PreparationSalle = {
  form: null,
  numero_panier_mandatory: null,

  refreshList: function(order_col, order_way, operation_id) {
    if (order_col) {
      $V(this.form._prepa_order_col, order_col);
    }

    if (order_way) {
      $V(this.form._prepa_order_way, order_way);
    }

    if (operation_id) {
      $V(this.form.operation_id, operation_id);
    }

    return onSubmitFormAjax(
      this.form,
      (function() { $V(this.form.operation_id, '') }).bind(this),
      operation_id ? ('panier_' + operation_id) : 'ops_materiel'
    );
  },

  showPanier: function(operation_id) {
    if (PreparationSalle.numero_panier_mandatory) {
      var form = getForm('editNoPanier' + operation_id);

      if (!$V(form.numero_panier) && confirm($T('COperation-Numero panier mandatory for validation'))) {
        return;
      }
    }
    new Url('planningOp', 'viewMaterielOperation')
      .addParam('operation_id', operation_id)
      .addParam('mode', 'validation')
      .requestModal('80%', '80%', {onClose: (function() { this.refreshList(null, null, operation_id) }).bind(this)});
  },

  makeAutocompletes: function(form) {
    if (!form) {
      form = this.form;
    }

    new Url('mediusers', 'ajax_users_autocomplete')
      .addParam('praticiens', '1')
      .addParam('input_field', '_prepa_chir_id_view')
      .autoComplete(
        form._prepa_chir_id_view,
        null,
        {
          minChars: 0,
          method: 'get',
          select: 'view',
          dropdown: true,
          afterUpdateElement: function(field, selected) {
            var id = selected.getAttribute('id').split('-')[2];
            $V(form._prepa_chir_id, id);
            $V(form._prepa_spec_id, '', false);
            $V(form._prepa_spec_id_view, '');
          }
        }
      );

    new Url('mediusers', 'ajax_functions_autocomplete')
      .addParam('edit', '1')
      .addParam('input_field', '_prepa_spec_id_view')
      .addParam('view_field', 'text')
      .autoComplete(
        form._prepa_spec_id_view,
        null,
        {
          minChars: 0,
          method: 'get',
          select: 'view',
          dropdown: true,
          afterUpdateElement: function(field, selected) {
            var id = selected.getAttribute('id').split('-')[2];
            $V(form._prepa_spec_id, id);
            $V(form._prepa_chir_id, '', false);
            $V(form._prepa_chir_id_view, '');
          }
        }
      );
  },

  toggleCheckAll: function(input) {
    input.up('table').select('input.panier_op:not(:disabled)').invoke('writeAttribute', 'checked', input.checked);
  },

  toggleCheckBoxLine: function(form) {
    if (!PreparationSalle.numero_panier_mandatory) {
      return;
    }

    var numero_panier = $V(form.numero_panier);
    var input = form.up('tr').down('input.panier_op');

    input.writeAttribute('disabled', numero_panier ? null : 'disabled');

    if (!numero_panier) {
      input.checked = false;
    }
  },

  validateSelection: function() {
    new Url('planningOp', 'do_valide_paniers', 'dosql')
      .addParam('operations_ids', $('ops_materiel').select('input.panier_op:checked').pluck('value').join('-'))
      .requestUpdate('systemMsg', {method: 'post', onComplete: this.refreshList.bind(this)});
  }
};
