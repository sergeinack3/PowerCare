/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Lieu = {
  loadLieuxByPrat: function(plageconsult_id, praticien_id) {
    new Url('cabinet', 'ajax_vw_agendas_by_praticien')
      .addParam('plageconsult_id', plageconsult_id)
      .addParam('praticien_id', praticien_id)
      .requestUpdate($('lieux'));
  },
  loadLieux:  function (praticien_id) {
    var url = new Url('cabinet', 'ajax_load_lieux');
    url.addParam("praticien_id", praticien_id);
    url.requestUpdate($('lieux'));
  },
  editLieux:  function (lieu_id, prat_id) {
    var url = new Url('cabinet', 'ajax_vw_edit_lieux');
    url.addParam("lieu_id", lieu_id);
    url.addParam("prat_id", prat_id);

    url.requestModal('40%', '70%', {onClose: Lieu.loadLieux.curry(prat_id)});
  },
  agendaLieux: function (lieu_id) {
    var url = new Url('cabinet', 'ajax_vw_agendas_lieux');
    url.addParam("lieu_id", lieu_id);
    url.requestModal('40%', '70%', {onClose: Lieu.loadLieux});
  },
};