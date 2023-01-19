/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

RGPD = window.RGPD || {
  compareConfigurations: function () {
    new Url('admin', 'ajax_compare_rgpd_configurations').requestModal();
  },

  uploadProofFile: function (consent_id) {
    var url = new Url('admin', 'ajax_vw_upload_rgpd_file');
    url.addParam('consent_id', consent_id);

    url.requestModal(800, 300);
  },

  addConsent: function (object_class, object_id) {
    var url = new Url('admin', 'ajax_vw_upload_rgpd_file');
    url.addParam('object_class', object_class);
    url.addParam('object_id', object_id);

    url.requestModal(800, 300);
  },

  confirmPurge: function (form) {
    var onOK = function () {
      $V(form.elements.confirmed, '1');

      return onSubmitFormAjax(
        form,
        {
          method:        'post',
          getParameters: {m: 'admin', dosql: 'do_purge_rgpd_consents'}
        },
        'purge-consents-result'
      );
    };

    if ($V(form.elements.confirmed) !== '1') {
      Modal.confirm(
        'Êtes-vous certain de vouloir purger ces consents ? Cette action est irréversible.',
        {onOK: onOK}
      );

      return false;
    } else {
      return onOK();
    }
  }
};
