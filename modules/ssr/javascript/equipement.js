/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Equipement = {
  edit: function(plateau_id, equipement_id) {
    new Url('ssr', 'ajax_edit_equipement') .
      addParam('equipement_id', equipement_id) .
      addParam('plateau_id', plateau_id) .
      requestUpdate('edit-equipements');
  },

  onSubmit: function(form) {
    return onSubmitFormAjax(form, { 
      onComplete: Equipement.edit.curry($V(form.plateau_id), '0')
    } );
  },

  updateTab: function(count) {
    var tab = $('tab-equipements');
    tab.down('a').setClassName('empty', !count);
    tab.down('a small').update('('+count+')');
  }
};