/**
 * @package Mediboard\
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

KeyMetadata = {
  generate: function (metadata_id) {
    var url = new Url('system', 'generateKey', 'dosql');
    url.addParam('metadata_id', metadata_id);

    url.requestUpdate('systemMsg', {
      method:     'post',
      onComplete: function () {
        KeyMetadata.refreshMetadata(metadata_id);
      }
    });
  },

  refreshMetadata: function (metadata_id) {
    var url = new Url('system', 'refreshMetadata');
    url.addParam('metadata_id', metadata_id);

    url.requestUpdate('key-metadata-' + metadata_id);
  }
};
