/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ObjectIndexer = {
  displayIndex: function(index_name) {
    var url = new Url('system', 'ajax_vw_object_indexer');
    url.addParam('index_name', index_name);
    url.requestModal('80%', '80%');
  },

  displayObjects: function(index_name, token) {
    var url = new Url('system', 'ajax_list_indexer_objects');
    url.addParam('index_name', index_name);
    url.addParam('token', token);
    url.requestUpdate('container_objects');
  },

  remove: function(index_name) {
    var url = new Url('system', 'do_remove_index', 'dosql');
    url.addParam('index_name', index_name);
    url.requestUpdate('systemMsg', {method: "post", onComplete: function () {
        Control.Modal.refresh();
      }}
    );
  },

  filter: function (input, filter) {
    var elements = $$('._object-indexer');
    elements.invoke('show');

    var term = $V(input);
    if (!term) {
      return;
    }

    elements.invoke('hide');
    elements.each(function (e) {
      if (e.down('._object-indexer_' + filter).getText().like(term)) {
        e.show();
      }
    });
  },

  displayTiming(object_count, search_time) {
    ObjectIndexer.clearTiming();

    var txt = document.createTextNode($T('CObjectIndexer-msg-%s objects found in %s ms', object_count, search_time));
    $('result_infos').appendChild(txt);
  },

  clearTiming() {
    var span = $('result_infos');
    if (span.hasChildNodes()) {
      span.removeChild(span.firstChild);
    }
  }
};