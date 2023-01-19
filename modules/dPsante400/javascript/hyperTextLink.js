/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

HyperTextLink = {
  edit: function (object_id, object_class, link_id, show_widget) {
    var url = new Url('sante400', 'ajax_edit_hypertext_link');
    url.addParam('object_id', object_id);
    url.addParam('object_class', object_class);
    if (link_id) {
      url.addParam('hypertext_link_id', link_id);
    }
    if (!Object.isUndefined(show_widget)) {
      url.addParam('show_widget', show_widget);
    }

    url.requestModal();
  },

  accessLink: function (name, link) {
    new Url().popup(1024, 768, name, null, null, link);
    return false;
  },

  getListFor: function (object_id, object_class, show_widget) {
    var url = new Url('sante400', 'ajax_list_hypertextlinks');
    url.addParam('object_id', object_id);
    url.addParam('object_class', object_class);
    url.addParam('show_only', 0);
    if (!Object.isUndefined(show_widget)) {
      url.addParam('show_widget', show_widget);
    }
    url.requestUpdate('list-hypertext_links');
  },

  getListForGuid: function (object_id, object_class, show_only, element_guid, count_links) {
    var url = new Url('sante400', 'ajax_list_hypertextlinks');
    url.addParam('object_id', object_id);
    url.addParam('object_class', object_class);
    url.addParam('show_only', show_only);
    if (!Object.isUndefined(count_links)) {
      url.addParam('count_links', count_links);
    } else {
      url.addParam('count_links', 1);
    }
    url.requestUpdate('list-hypertext_links-' + element_guid);
  }
};