/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CacheManager = {
  getMessage:       function (cache, target, module) {
    let message = $T('CacheManager-msg-confirm delete');

    if (cache) {
      message += '<br> - ';
      if (!module) {
        message += $T('CacheManager-cache_values.' + cache);
      } else {
        message += cache;
      }
    }

    if (module) {
      message += '<br>' + $T('CacheManager-msg-for module') + '<br> - ' + module;
    }

    if (target) {
      message += '<br>' + $T('CacheManager-msg-on targets') + '<br> - ' + target;
    }

    message += '<br>';

    return message;
  },
  clear:            function (cache, target, layer) {
    const url = new Url("system", "clear");

    url.addParam('cache', cache);
    url.addParam('target', target);
    url.addParam('layer', layer);

    url.requestUpdate('CacheManagerOutputs');
  },
  openModalConfirm: function (cache, target, layer, module, keys) {
    const url = new Url('system', 'ajax_show_modal_clear_cache');

    url.addParam('cache', cache);
    url.addParam('target', target);
    url.addParam('layer', layer);
    url.addParam('module', module);
    url.addParam('keys', keys);

    url.requestModal('80%', '90%')
  },
};
