/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Transformation
 */
EAITransformation = {
  modal : null,

  onSubmit: function(form) {
    return onSubmitFormAjax(form, function() {
      Control.Modal.close();
      EAITransformation.modal.refreshModal().bindAsEventListener(EAITransformation.modal);
    });
  },

  moveRowUp: function(row) {
    if (row.previous() == row.up().childElements()[1]) {
      return;
    }

    row.previous().insert({before: row});
  },

  moveRowDown: function(row) {
    if (row.next()) {
      row.next().insert({after: row});
    }
  },

  link: function(message_class, event_class, actor_guid) {
    new Url("eai", "ajax_link_transformation_rules")
      .addParam("event_class"  , event_class)
      .addParam("message_class", message_class)
      .addParam("actor_guid", actor_guid)
      .requestModal(800);
  },

  list: function(message_class, event_class, actor_guid) {
    EAITransformation.modal = new Url("eai", "ajax_list_transformations")
      .addParam("event_class"  , event_class)
      .addParam("message_class", message_class)
      .addParam("actor_guid"   , actor_guid)
      .requestModal(700);
  },

  refreshList: function(message_class, event_class, actor_guid, readonly) {
    new Url("eai", "ajax_refresh_transformations")
      .addParam("message_class", message_class)
      .addParam("event_class"  , event_class)
      .addParam("actor_guid"   , actor_guid)
      .addParam("readonly"     , readonly)
      .requestUpdate("transformations");
  }
}
