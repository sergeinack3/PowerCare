/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ProtocoleCleaner = {
  form: null,

  initAutocompletes: function () {
    new Url('mediusers', 'ajax_users_autocomplete')
      .addParam('praticiens', '1')
      .addParam('input_field', '_chir_id_view')
      .autoComplete(
        this.form._chir_id_view,
        null,
        {
          minChars:           0,
          method:             'get',
          select:             'view',
          dropdown:           true,
          afterUpdateElement: (function (field, selected) {
            var id = selected.getAttribute('id').split('-')[2];
            $V(this.form.chir_id, id);
            $V(this.form.function_id, '', false);
            $V(this.form._function_id_view, '');
          }).bind(this)
        }
      );

    new Url('mediusers', 'ajax_functions_autocomplete')
      .addParam('input_field', '_function_id_view')
      .addParam('view_field', 'text')
      .autoComplete(
        this.form._function_id_view,
        null,
        {
          minChars:           0,
          method:             'get',
          select:             'view',
          dropdown:           true,
          afterUpdateElement: (function (field, selected) {
            var id = selected.getAttribute('id').split('-')[2];
            $V(this.form.function_id, id);
            $V(this.form.chir_id, '', false);
            $V(this.form._chir_id_view, '');
          }).bind(this)
        }
      );
  },

  /**
   * Refresh the list according to the filters
   *
   * @returns {Boolean}
   */
  refreshList: function () {
    return onSubmitFormAjax(this.form, null, 'protocoles_area');
  },

  /**
   * Delete selected protocoles
   */
  deleteSelected: function () {
    var protocoles_ids = $$('input.protocole:checked').pluck('value');

    if (!protocoles_ids.length) {
      alert($T('CProtocole-Need at least one protocole to delete'));
      return;
    }

    if (!confirm($T('CProtocole-Alert confirm delete protocoles'))) {
      return;
    }

    new Url('planningOp', 'do_delete_protocoles_unused', 'dosql')
      .addParam('protocoles_ids', protocoles_ids.join('-'))
      .requestUpdate(
        'systemMsg',
        {
          method:     'post',
          onComplete: (this.refreshList).bind(this)
        }
      );
  }
};