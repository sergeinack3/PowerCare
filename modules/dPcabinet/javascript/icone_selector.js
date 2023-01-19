/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// $Id: $

IconeSelector = {
  sForm   : null,
  sView   : null,
  options : {
    width : 400,
    height: 150
  },

  pop: function() {
    var url = new Url('cabinet', 'icone_selector');
    url.popup(this.options.width, this.options.height, 'Icone');
  },

  set: function(view) {
    var oForm = getForm(this.sForm);
    
    // Champs text qui contient le nom de l'icone
    $V(oForm[this.sView], view);
    
    // Affichage de l'icone
    $('iconeBackground').src = './modules/dPcabinet/images/categories/'+view;
  },
  
  changeCategory: function(consult_id, span) {
    var url = new Url('cabinet', 'change_categorie');
    url.addParam('consult_id', consult_id);
    url.requestModal(300);
    url.modalObject.observe('afterClose', IconeSelector.refreshCategory.curry(consult_id, span));
  },

  refreshCategory: function(consult_id, span) {
    var url = new Url('cabinet', 'show_categorie');
    url.addParam('consult_id', consult_id);
    url.requestUpdate(span);
  }
};