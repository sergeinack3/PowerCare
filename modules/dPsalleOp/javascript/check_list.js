/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CheckList = {

  reloadGroup: null,
  currentType: null,
  currentListTypeId: null,

  updateObject: function(select) {
    var form = select.form;
    var parts = $V(select).split(/-/);
    $V(form.object_class, parts[0]);
    $V(form.object_id,   (parts[1] === 'none' ? '' : parts[1]));
  },

  editItemCategory: function(list_type_id, cat_id, callback) {
    new Url('dPsalleOp', 'vw_daily_check_item_category')
      .addParam('list_type_id', list_type_id)
      .addParam('item_category_id', cat_id)
      .requestModal(800, 700, {
        onClose: function() {
          if (callback == '1'){
            Control.Modal.refresh();
          }
          else {
            this.showType(list_type_id);
          }
        }.bind(this)
      });
  },

  callbackItemCategory: function(id, obj) {
    Control.Modal.close();
    CheckList.editItemCategory(obj.list_type_id, id, this.reloadGroup);
  },

  editItemType: function(category_id, item_type_id) {
    new Url('dPsalleOp', 'vw_daily_check_item_type')
      .addParam('item_category_id', category_id)
      .addParam('item_type_id', item_type_id)
      .requestModal(600, 450, {onClose: Control.Modal.refresh});
  },

  preview: function(object_class, object_id, type) {
    new Url('dPsalleOp', 'vw_daily_check_list_preview')
      .addParam('object_class' , object_class)
      .addParam('object_id'    , object_id)
      .addParam('type'         , type)
      .requestModal(900, 700);
  },

  showType: function(list_type_id, type, dialog) {
    new Url('dPsalleOp', 'vw_daily_check_list_type')
      .addParam('list_type_id', list_type_id)
      .addParam('type', type)
      .addParam('dialog', dialog)
      .addParam('edit_mode', 1)
      .requestUpdate('edit_check_list_container');
  }
};
