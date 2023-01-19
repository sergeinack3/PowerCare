/**
 * @package Mediboard\Import
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
ImportMapping = window.ImportMapping || {
  copieUser: function (form) {
    var next_form = form.up('tr').next('tr').down('form');
    if (next_form) {
      $V(next_form.user_id, $V(form.user_id));
      $V(next_form._user_id_autocomplete_view, $V(form._user_id_autocomplete_view));
    }
  },

  changePage: function (start, args) {
    args = JSON.parse(args);

    new Url(args.m, args.a)
      .addParam('import_campaign_id', args.import_campaign_id)
      .addParam('start', start)
      .addParam('import_type', args.import_type)
      .requestUpdate(args.refresh);
  },

  nextImport: function (type, start, ended) {
    var form = getForm('import-' + type);
    if (form) {
      $V(form.start, start);

      if (!ended && $V(form.continue)) {
        form.onsubmit();
      }
    }
  },

  refreshCount: function (type, module) {
    var elem = $(type + '-count');
    var form = getForm('import-' + type);

    var url = new Url(module, 'ajax_count_with_condition');
    url.addParam('type', type);
    url.addParam('import_campaign_id', $V($('import-campaign-select')));
    url.addParam('patient_id', $V(form.patient_id));
    url.addParam('import_type', $V(form.import_type));
    url.requestUpdate(elem);
  }
};
