/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ObjectNavigation = window.ObjectNavigation || {
    url_active: null,
    urls: [],
    start: 0,
    step: 200,

    openModalObject: function (classe, id) {
      if (!id) {
        return;
      }
      // Ouvre la modale
      var url = new Url('system', 'ajax_details_class');
      url.addParam('class_name', classe);
      url.addParam('class_id', id);
      url.requestModal(1000, 800, {
        title: classe + ' #' + id
      });
      this.url_active = url;
    },

    classShow: function (class_name, class_id) {
      var url = new Url('system', 'ajax_details_class');
      url.addParam('class_name', class_name);
      url.addParam('class_id', class_id);
      url.requestModal(1000, 800, {
        title: class_name + ' #' + class_id
      });
      this.url_active = url;
      this.urls.push({class_select: class_name, id_select: class_id, url: url});
    },

    changePage: function (page, arg) {
      $V(getForm("filter_back_" + arg).start, page);
    },
  }
;
