/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Technicien = {
  current_m: 'ssr',
  edit: function(plateau_id, technicien_id) {
    new Url(Technicien.current_m, 'ajax_edit_technicien') .
      addParam('technicien_id', technicien_id) .
      addParam('plateau_id', plateau_id) .
      requestUpdate('edit-techniciens');
  },

  onSubmit: function(form) {
    return onSubmitFormAjax(form, { 
      onComplete: Technicien.edit.curry($V(form.plateau_id), '')
    } );
  },

  confirmTransfer: function(form, count) {
    var select = form._transfer_id;
    var option = select.options[select.selectedIndex];
    if (option.value == '') {
       Element.getLabel(select).addClassName('error');
       return false;
    }

    return confirm($T('CTechnicien-_transfer_id-confirm', count, option.innerHTML));
  },

  updateTab: function(count) {
    var tab = $('tab-techniciens');
    tab.down('a').setClassName('empty', !count);
    tab.down('a small').update('('+count+')');
  }
};