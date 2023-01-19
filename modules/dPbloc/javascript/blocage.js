/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

var Blocage = {
  edit: function(blocage_id) {
    var url = new Url('dPbloc', 'ajax_edit_blocage');
    url.addParam('blocage_id', blocage_id);
    url.requestUpdate('edit_blocage');
  },
  refreshList: function(blocage_id, date_replanif) {
    var url = new Url('dPbloc', 'ajax_list_blocages');
    if (date_replanif) {
      url.addParam("date_replanif", date_replanif);
    }
    url.addParam('blocage_id', blocage_id);
    url.requestUpdate('list_blocages');
  },
  afterEditBlocage: function(blocage_id) {
    this.edit(blocage_id);
    this.refreshList(blocage_id);
  },
  updateSelected: function(tr) {
    $('list_blocages').select('tr').each(function(elt) {
      elt.removeClassName('selected');
    });
    if (tr) {
      tr.addClassName('selected');
    }
  },
  refreshPlageToDelete: function(form) {
    var url = new Url("dPbloc", "ajax_update_plages_to_delete");
    url.addParam("salle_id", $V(form.salle_id));
    url.addParam("deb"     , $V(form.deb));
    url.addParam("fin"     , $V(form.fin));
    url.requestUpdate("plages_deleted");
  }
};
