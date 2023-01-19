/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CacheViewer = {
  showDetail: function(trigger) {
    var tbody = trigger.up("tbody").next(".cache-detail");

    if (!trigger.hasClassName("unfold")) {
      var prefix = trigger.get("prefix");
      var type = trigger.get("type");

      var url = new Url("system", "ajax_vw_cache_detail");
      url.addParam("type", type);
      url.addParam("prefix", prefix);
      url.requestUpdate(tbody);

      trigger.addClassName("unfold");
    }
    else {
      trigger.removeClassName("unfold");
      tbody.update();
    }
  },

  showKeyDetail: function (elt) {
    var key = elt.get("key");
    var type = elt.get("type");
    var ttl = elt.get("ttl");

    var url = new Url("system", "ajax_vw_cache_entry_value");
    url.addParam("key", key);
    url.addParam("type", type);
    url.addParam("ttl", ttl);
    url.requestModal(600);
  },

  removeKey: function (elt) {
    var key = elt.get("key");
    var type = elt.get("type");

    new Url("system", "remove_cache_entry")
      .addParam('type', type)
      .addParam('key', key)
      .requestUpdate("systemMsg", {
        onComplete: function () {
          var trigger = $$("a[data-prefix='"+key.split('-')[0]+"']")[0];
          trigger.removeClassName("unfold");
          CacheViewer.showDetail(trigger);
        }
      });
  }
};