/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Relance = {
  form_relance: null,
  create: function(form) {
    return onSubmitFormAjax(form, {
      onComplete : function() {
        if (!$('load_facture')) {
          Control.Modal.refresh();
        }
        else {
          var url = new Url('facturation', 'ajax_view_facture');
          url.addParam('facture_id'   , form.object_id.value);
          url.addParam('object_class' , form.object_class.value);
          url.requestUpdate("load_facture");
        }
      }
    });
  },
  pdf: function(relance_id) {
    var url = new Url('facturation', 'ajax_edit_bvr');
    url.addParam('suppressHeaders' , '1');
    url.addParam('type_pdf'        , 'relance');
    url.addParam('relance_id'      , relance_id);
    url.popup(1000, 600);
  },
  modify: function(relance_id) {
    var url = new Url('facturation', 'ajax_edit_relance');
    url.addParam('relance_id', relance_id);
    url.requestModal(500, 300);
  },
  printRelance: function(facture_class, facture_id, type_pdf, relance_id) {
    var url = new Url('facturation', 'ajax_edit_bvr');
    url.addParam('facture_class', facture_class);
    url.addParam('facture_id'   , facture_id);
    url.addParam('relance_id'   , relance_id);
    url.addParam('type_pdf'     , type_pdf);
    url.addParam('suppressHeaders', '1');
    url.popup(1000, 600);
  }
};

ListeFacture = {
  load: function(facture_class, type_relance) {
    var form = document.printFrm;
    if(!form.chir.value) {
      alert($T('Compta.choose_prat'));
      return false;
    }
    var url = new Url('facturation', 'ajax_vw_list_facture_to_relance');
    url.addElement(form._date_min);
    url.addElement(form._date_max);
    url.addElement(form.chir);
    url.addParam('type_relance', type_relance);
    url.addParam("facture_class", facture_class);
    url.requestModal(1400, 550);
  },
  changePage: function(page) {
    var url = new Url("facturation" , "ajax_vw_list_facture_to_relance");
    url.addParam('page', page);
    url.requestUpdate("liste_factures");
  },
  view: function(facture_class, type_relance, etat_relance) {
    var form = document.printFrm;
    if(!form.chir.value) {
      alert($T('Compta.choose_prat'));
      return false;
    }
    var url = new Url('facturation', 'ajax_vw_relances');
    url.addElement(form._date_min);
    url.addElement(form._date_max);
    url.addElement(form.chir);
    url.addParam('relance'		, '1');
    url.addParam('type_relance'	, type_relance);
    url.addParam("facture_class", facture_class);
    url.addParam("etat_relance", etat_relance);
    url.requestModal(1200, 550);
  }
};
