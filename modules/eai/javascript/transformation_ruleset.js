/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Transformation ruleset
 */
EAITransformationRuleSet = {
  edit: function (transformation_ruleset_id) {
    new Url("eai", "ajax_edit_transformation_ruleset")
      .addParam("transformation_ruleset_id", transformation_ruleset_id)
      .requestModal(600);
  },

  onSubmit: function (form) {
    return onSubmitFormAjax(form, Control.Modal.close);
  },

  refreshList: function () {
    new Url("eai", "ajax_refresh_list_transformation_ruleset")
      .requestUpdate("list-transformation-ruleset");
  },

  refreshTransformationRuleList: function (transformation_ruleset_id) {
    new Url("eai", "ajax_refresh_list_transformation_rules")
      .addParam("transformation_ruleset_id", transformation_ruleset_id)
      .requestUpdate("transformation_rules", EAITransformationRuleSet.refreshList);
  },

  displayDetails: function (transformation_ruleset_id) {
    new Url("eai", "ajax_display_details_transformation_ruleset")
      .addParam("transformation_ruleset_id", transformation_ruleset_id)
      .requestUpdate("transformation_ruleset_details", EAITransformationRuleSet.refreshList);
  }
};