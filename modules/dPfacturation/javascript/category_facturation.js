/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CategoryFactu = {
  modal: null,
  refeshList: function() {
    var form = getForm('selectPratCategoryfactu');
    var url = new Url('facturation', 'vw_list_category_facturation');
    url.addFormData(form);
    url.requestUpdate('list_category_facturation');
  },
  edit: function(category_id, function_id) {
    var url = new Url('facturation', 'vw_edit_category_facturation');
    url.addParam('category_id', category_id);
    url.addParam('function_id', function_id);
    url.requestModal();
  },
  submit: function(form) {
    return onSubmitFormAjax(form, {
      onComplete : function() {
        Control.Modal.close();
        CategoryFactu.refeshList();
      }}
    );
  },
  confirmDeletion: function(form) {
    var options = {
      objName: $V(form.libelle),
      ajax: 1
    };
    var ajax = {
      onComplete: function() {
        Control.Modal.close();
        CategoryFactu.refeshList();
      }
    };
    confirmDeletion(form, options, ajax);
  }
};
