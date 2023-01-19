/**
 * @package Mediboard\Labl
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Action = {
  module: 'labo',

  update: function (sName) {
    new Url(this.module, this.Requests[sName])
      .requestUpdate('action-' + sName);
  },

  Requests: {
    'importCatalogues': 'httpreq_import_catalogue',
    'importPacks': 'httpreq_import_pack'
  }
};