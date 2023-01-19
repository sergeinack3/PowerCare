/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Transformation rule sequence
 */
EAITransformationRuleSequence = {
  modal          : null,
  standards_flat : [],
  selects        : ["standard", "domain", "profil", "transaction", "message_type"],

  edit: function (transformation_ruleset_id, transformation_rule_sequence_id) {
    new Url("eai", "ajax_edit_transformation_rule_sequence")
      .addParam("transformation_ruleset_id", transformation_ruleset_id)
      .addParam("transformation_rule_sequence_id", transformation_rule_sequence_id)
      .requestModal(1000);
  },

  stats: function(transformation_rule_id) {
    new Url("eai", "ajax_show_stats_transformations")
      .addParam("transformation_rule_id", transformation_rule_id)
      .requestModal(600);
  },

  onSubmit: function(form) {
    return onSubmitFormAjax(form, Control.Modal.close);
  },

  moveRowUp: function(row) {
    if (row.previous() === row.up().childElements()[1]) {
      return;
    }

    row.previous().insert({before: row});
  },

  moveRowDown: function(row) {
    if (row.next()) {
      row.next().insert({after: row});
    }
  },

  fillSelect: function(select_name, traduction, value, create) {
    var select = $("EAITransformationRuleSequence-"+select_name);
    select.update();

    if (!create) {
      value = null;
    }

    $A(EAITransformationRuleSequence.standards_flat).pluck(select_name).uniq().each(function(pair){
      if (pair === "none") {
        return;
      }

      var option_text = traduction ? $T(pair+traduction)+' ('+pair+')' : $T(pair);
      select.insert(
        DOM.option({
          value:   pair,
          onclick: "EAITransformationRuleSequence.showFillSelect(this.up())",
          selected: value == pair
        }).update(option_text)
      );
    });
  },

  showFillSelect : function(select) {
    var select_name  = select.name;

    var selects = select.form.select("select.EAITransformationRuleSequence-select[name != "+select_name+"][name != standard]");

    EAITransformationRuleSequence.selects.each(function(selectname) {
      if (selectname === select_name) {
        return;
      }

      var other_select = select.form[selectname];

      var old_selected_value = "";
      if (other_select.selectedIndex !== -1) {
        old_selected_value = $V(other_select);
      }

      var filtered = EAITransformationRuleSequence.standards_flat.filter(
        EAITransformationRuleSequence.isValueExist.curry(select, selects)).pluck(other_select.name).uniq();

      if (filtered.length === 0) {
        EAITransformationRuleSequence.fillSelect(selectname);

        return;
      }

      other_select.update();

      filtered.each(function(option) {
        var option_text = $T(option);
        if (selectname === "domain" || selectname === "profil") {
          option_text = $T(option+'-desc')+' ('+option+')'
        }

        if (option === "none") {
          return;
        }

        other_select.insert(
          DOM.option({
            value: option,
            onclick: "EAITransformationRuleSequence.showFillSelect(this.up())"}
          ).update(option_text)
        );
      });

      if (old_selected_value) {
        $V(other_select, old_selected_value);
      }
    });
  },

  isValueExist : function(select, selects, element) {
    var select_value = $V(select);
    var select_name  = select.name;

    var flag = true;
    selects.each(function(other_select) {
      var value_select = $V(other_select); /*|| (other_select.options.length == 1 ? other_select.options[0].value : "");*/

      if (value_select && element[other_select.name] !== value_select) {
        flag = false;
      }
    });

    return (element[select_name] === select_value) && flag;
  },

  showVersions : function(transformation_rule_sequence_id, standard_name, profil_name) {
    new Url("eai", "ajax_show_transformation_rule_sequence_versions")
      .addParam("transformation_rule_sequence_id", transformation_rule_sequence_id)
      .addParam("standard_name", standard_name)
      .addParam("profil_name", profil_name)
      .requestUpdate("EAITransformationRuleSequence-version");
  },

  displayDetails: function (transformation_ruleset_id, transformation_rule_sequence_id, display_type) {
    new Url("eai", "ajax_display_details_transformation_rule_sequence")
      .addParam("transformation_ruleset_id", transformation_ruleset_id)
      .addParam("transformation_rule_sequence_id", transformation_rule_sequence_id)
      .addParam("display_type", display_type)
      .requestUpdate("transformation_rule_sequence_details");
  },

  linkActorToSequence : function (receiver_guid, rule_sequence_id, delete_link_id) {
    new Url('eai', 'controllers/do_link_actor_sequence')
      .addParam("receiver_guid", receiver_guid)
      .addParam("rule_sequence_id", rule_sequence_id)
      .addParam("delete_link_id", delete_link_id)
      .requestUpdate("systemMsg", {onComplete : function() {EAITransformationRuleSequence.refreshListActors(rule_sequence_id)}});

    return false;
  },

  refreshListActors : function (rule_sequence_id) {
    new Url('eai', 'ajax_refresh_list_actors')
      .addParam("rule_sequence_id", rule_sequence_id)
      .requestUpdate("list_actors");

    return false;
  },

  play : function (sequence_id) {
    new Url('eai', 'ajax_play_sequence')
      .addParam("sequence_id", sequence_id)
      .requestUpdate("result_transformations_sequence");

    return false;
  }
};
