/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

var DisplayGraph = {
  filterForm: null,
  lastUrl:    null,

  getFilterForm: function () {
    DisplayGraph.filterForm = getForm("stats_params")
  },

  launchStats: function (type_graph) {
    DisplayGraph.getFilterForm();
    var url = new Url("stats", "vw_graph_std");
    url.addParam("type_graph", type_graph);
    this.addFiltersParam(url);
    url.requestModal();
    DisplayGraph.lastUrl = url;
  },

  addFiltersParam: function (url) {
  }

};