/**
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Deals categories of 'on call' shifts
 */
Categories = {
  /**
   * Updates categories using a form
   */
  refreshCategories: function () {
    new Url('astreintes', 'listCategorieAstreintes')
      .requestUpdate('listCategories');
  },

  /**
   * Event which will open the modal to create a category
   */
  newCategory: function () {
    $('new_category').observe('click', this._editCategory.bind(null));
  },

  /**
   * Event to open the edit modal
   */
  editCategories: function () {
    var buttons = Array.from($$('.categories .edit'));
    buttons.invoke('observe', 'click', (event) => this._editCategory(event.target.dataset.id));
  },

  /**
   * Open the edit modal
   *
   * @param {string} id
   *
   * @private
   */
  _editCategory: function (id) {
    new Url('astreintes', 'editCategorie')
      .addParam('category_id', id)
      .requestModal(250, null, {
        onClose: Categories.refreshCategories
      });
  },

  /**
   * Action buttons will shut the modal (saving and deleting)
   */
  actionsCategory: function () {
    var button_save = $$('.modal .save')[0];
    button_save.observe('click', this._actionEditCategory.bind(this));

    var button_del = $$('.modal .trash');
    if (button_del.length > 0) {
        button_del[0].observe('click', this._actionDeleteCategory.bind(this));
    }
  },

  /**
   * Closes and updates the list of category when deleting a category
   *
   * @param {Object} event - the event
   *
   * @private
   */
  _actionDeleteCategory: function () {
    this.closeModal();
  },

  /**
   * Closes the modal and updates the list of categories
   *
   * @param {Object} event - the event
   *
   * @private
   */
  _actionEditCategory: function (event) {
    return onSubmitFormAjax(event.target.form, {
      onComplete: function () {
        Categories.closeModal();
      }
    });
  },

  /**
   * Closes a modal
   *
   * @param event
   */
  closeModal: function () {
    Control.Modal.close();
  }
};
