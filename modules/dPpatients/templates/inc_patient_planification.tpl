{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=praticien_id value="0"}}
{{mb_default var=patient_id   value="0"}}
{{mb_default var=consult_id   value="0"}}
{{mb_default var=consult      value="0"}}
{{mb_default var=sejour       value="0"}}
{{mb_default var=mode_cabinet value=0}}

<script>
    showSejourButtons = function () {
        var options = {
            title: "Nouvelle DHE",
            showClose: true
        };
        modal("sejour-buttons", options);
    };

    newOperation = function (chir_id, pat_id, consult_id) {
        new Url("planningOp", "vw_edit_planning")
            .addParam("chir_id", chir_id)
            .addParam("pat_id", pat_id)
            .addParam("consult_related_id", consult_id)
            .addParam("operation_id", 0)
            .addParam("sejour_id", 0)
            .modal({
                width: "95%",
                height: "95%"
            });
    };

    newHorsPlage = function (chir_id, pat_id, consult_id) {
        new Url("planningOp", "vw_edit_urgence")
            .addParam("chir_id", chir_id)
            .addParam("pat_id", pat_id)
            .addParam("consult_related_id", consult_id)
            .addParam("operation_id", 0)
            .addParam("sejour_id", 0)
            .modal({
                width: "95%",
                height: "95%"
            });
    };

    newSejour = function (chir_id, pat_id, consult_id) {
        new Url("planningOp", "vw_edit_sejour")
            .addParam("praticien_id", chir_id)
            .addParam("patient_id", pat_id)
            .addParam("consult_related_id", consult_id)
            .addParam("sejour_id", 0)
            .modal({
                width: "95%",
                height: "95%"
            });
    };

    newConsultation = function (chir_id, pat_id, consult_urgence_id) {
        new Url("cabinet", "edit_planning")
            .addParam("chir_id", chir_id)
            .addParam("pat_id", pat_id)
            .addParam("consult_urgence_id", consult_urgence_id)
            .addParam("consultation_id", null)
            .modal({
                width: "95%",
                height: "95%"
            });
    };
</script>

{{if !$app->user_prefs.simpleCabinet}}
    {{if "ecap"|module_active && $current_group|idex:"ecap"|is_numeric}}
        {{if ($sejour && in_array($sejour->type, 'Ox\Mediboard\PlanningOp\CSejour::getTypesSejoursUrgence'|static_call:$sejour->praticien_id))
        || ($consult && $consult->_ref_sejour && in_array($consult->_ref_sejour->type, 'Ox\Mediboard\PlanningOp\CSejour::getTypesSejoursUrgence'|static_call:$consult->_ref_sejour->praticien_id))}}
            {{mb_default var=show_dhe_ecap value=0}}
        {{/if}}

        {{mb_include module=ecap template=inc_button_dhe patient_id=$patient_id praticien_id=$praticien_id show_non_prevue=false}}
    {{/if}}
    {{if $m != "dPurgences"
    && ((!"ecap"|module_active || !$current_group|idex:"ecap"|is_numeric)
    || ("ecap"|module_active && $current_group|idex:"ecap"|is_numeric && 'ecap Display show_buttons_dhe'|gconf))}}
        <button class="new me-primary me-no-display" type="button" onclick="showSejourButtons();">
            {{tr}}CSejour-title-new{{/tr}}
        </button>
        <div id="sejour-buttons" style="display: none;">
            <button class="big me-margin-4 me-primary me-width-auto" type="button"
                    onclick="newOperation('{{$praticien_id}}', '{{$patient_id}}', '{{$consult->_id}}');"
                    style="width: 20em;">
                {{tr}}COperation-title-create{{/tr}}
            </button>
            <br/>
            <button class="big me-margin-4 me-big me-primary me-width-auto" type="button"
                    onclick="newHorsPlage('{{$praticien_id}}', '{{$patient_id}}', '{{$consult->_id}}');"
                    style="width: 20em;">
                {{tr}}COperation-title-create-horsplage{{/tr}}
            </button>
            <br/>
            <button class="big me-margin-4 me-big me-primary me-width-auto" type="button"
                    onclick="newSejour('{{$praticien_id}}', '{{$patient_id}}', '{{$consult->_id}}');"
                    style="width: 20em;">
                {{tr}}CSejour-title-create{{/tr}}
            </button>
            {{me_button icon=new old_class="big" mediboard_ext_only="1" label="COperation-title-create" onclick="newOperation('$praticien_id', '$patient_id', '`$consult->_id`');"}}
            {{me_button icon=new old_class="big" mediboard_ext_only="1" label="COperation-title-create-horsplage" onclick="newHorsPlage('$praticien_id', '$patient_id', '`$consult->_id`');"}}
            {{me_button icon=new old_class="big" mediboard_ext_only="1" label="CSejour-title-create" onclick="newSejour('$praticien_id', '$patient_id', '`$consult->_id`');"}}
        </div>
    {{/if}}
{{/if}}

{{if !$sejour || !in_array($sejour->type, 'Ox\Mediboard\PlanningOp\CSejour::getTypesSejoursUrgence'|static_call:$sejour->praticien_id)}}
    <br class="me-no-display"/>
    {{me_button icon=new label="CConsultation-title-create" onclick="newConsultation('$praticien_id', '$patient_id');"}}
    <br class="me-no-display"/>
    {{assign var=callback value=""}}
    {{if $mode_cabinet || $app->user_prefs.UISTYLE == "tamm"}}
        {{assign var=callback value="window.parent.openNewConsult"}}
    {{/if}}
    {{mb_include module="cabinet" template="inc_button_consult_immediate" patient_id=$patient->_id type="" consult_id=$consult_id tme_primary="false" callback=$callback}}
    {{if $consult->_id}}
    <button class="duplicate me-primary" id="duplicate_rdv_planning_button" type="button"
            onclick="Consultation.openDuplicateConsult('{{$patient_id}}', '{{$callback}}', '{{$consult->_id}}');">
        {{tr}}dPCabinet-Planning-action-duplicate_rdv{{/tr}}
    </button>
    <br />
    {{/if}}
{{/if}}
{{me_dropdown_button container_class="me-dropdown-button-left" button_label="common-action-Plan" button_icon="add" button_class="me-primary me-margin-2"}}
