/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

StatProtocole = {
  form: null,
  protocole_id_stat: null,

  refreshStats: function() {
    new Url('planningOp', 'ajax_stats_protocoles')
      .addFormData(this.form)
      .requestUpdate('result_stats');
  },

  detailSejours: function(protocole_id_stat, only_list, page) {
    if (protocole_id_stat) {
      this.protocole_id_stat = protocole_id_stat;
    }

    if (Object.isUndefined(page)) {
      page = 0;
    }

    var url = new Url('planningOp', 'vw_detail_sejours')
      .addParam('protocole_id_stat', this.protocole_id_stat)
      .addElement(this.form.debut_stat)
      .addElement(this.form.fin_stat)
      .addParam('page', page);

    if (only_list) {
      return url.requestUpdate(Control.Modal.stack[Control.Modal.stack.length - 1].container.down('div.content'));
    }

    return url.requestModal('70%', '70%');
  },

  changePage: function(page) {
    this.detailSejours(null, 1, page);
  }
};