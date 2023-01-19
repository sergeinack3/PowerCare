/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Details = {
  statOwner: function (doc_class, doc_id, owner_guid, date_min, date_max, period, factory) {
    new Url('files', 'stats_details') .
      addParam('doc_class', doc_class) .
      addParam('doc_id', doc_id) .
      addParam('owner_guid', owner_guid) .
      addParam('date_min', date_min) .
      addParam('date_max', date_max) .
      addParam('period', period) .
      addParam('factory', factory).
      requestModal(800, 500);
  },

  statPeriodicalOwner: function (doc_class, doc_id, owner_guid, category_id, object_class, target, factory) {
    new Url('files', 'stats_periodical_details') .
      addParam('doc_class', doc_class) .
      addParam('doc_id', doc_id) .
      addParam('owner_guid', owner_guid) .
      addParam('category_id', category_id) .
      addParam('factory', factory).
      addParam('object_class', object_class)
      [target ? 'requestUpdate' : 'requestModal'](target ? target : null);
  }
};
