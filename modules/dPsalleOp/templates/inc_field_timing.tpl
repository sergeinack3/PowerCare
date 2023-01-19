{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=inline             value=false}}
{{mb_default var=disabled           value=false}}
{{mb_default var=use_disabled       value=false}}
{{mb_default var=show_label         value=true}}
{{mb_default var=table_class        value='form'}}
{{mb_default var=center_field       value=false}}
{{mb_default var=graph_lock         value=0}}
{{mb_default var=width              value=""}}
{{mb_default var=current_monitoring value=""}}
{{mb_default var=current_session    value=null}}

{{assign var=use_concentrator value=false}}
{{if "patientMonitoring"|module_active && "patientMonitoring CMonitoringConcentrator active"|gconf}}
  {{assign var=use_concentrator value=true}}
{{/if}}

{{if $object|instanceof:'Ox\Mediboard\PlanningOp\COperation'}}
  {{assign var=validate_datetimes    value='Ox\Mediboard\PlanningOp\COperation::getValidatingTimings'|static_call:"`$object->_id`":"`$field`"}}
  {{assign var=validate_datetime_min value=$validate_datetimes.min}}
  {{assign var=validate_datetime_max value=$validate_datetimes.max}}
  {{assign var=last_timing           value=$validate_datetimes.last_timing}}

  {{if $use_concentrator}}
    {{assign var=current_session value='Ox\Mediboard\PatientMonitoring\CMonitoringSession::getCurrentSession'|static_call:"`$object`"}}
  {{/if}}
{{/if}}
{{if $use_disabled == "yes" && "dPsalleOp timings use_check_timing"|gconf}}
  {{assign var=disabled value="yes"}}
{{/if}}

{{assign var=check_timing value="`$submit`(this.form);"}}
{{if $object|instanceof:'Ox\Mediboard\PlanningOp\COperation' && $object->$field && $modif_operation && $field != "sortie_salle"}}
  {{assign var=check_timing value="if (SalleOp.checkTimingOperation('`$object->_ref_sejour->entree`', '`$object->_ref_sejour->sortie`', this, '`$object->_id`', '`$last_timing`')) {`$check_timing`}"}}
{{/if}}

{{assign var=use_poste value=$conf.dPplanningOp.COperation.use_poste}}
<div class="button"
     style="{{if $width}}width: {{$width}}%;{{/if}} {{if $inline}}display: inline-block; vertical-align: middle;{{/if}}">
  <div class="field-timing-container
              {{if $object->$field}}show-content{{elseif $modif_operation}}show-button{{/if}}
              {{if !$show_label}} only-field{{/if}}
              {{if $inline}} inline-container{{/if}}">
    {{if $show_label}}
      <div class="field-timing-title">
        {{mb_label object=$object field=$field}}
      </div>
    {{/if}}
    <div class="field-timing-value" {{if $center_field}}style="text-align: center;"{{/if}}>
      {{if $object->$field}}
        {{if $modif_operation}}
          {{if $field == "sortie_salle" && !$allow_edit_sortie_salle}}
            {{mb_value object=$object field=$field}}
          {{else}}
            {{mb_field object=$object field=$field form=$form register=true onchange="$check_timing" readonly=$graph_lock}}
          {{/if}}
        {{else}}
          {{mb_value object=$object field=$field}}
        {{/if}}
        {{if in_array($field, array("fin_op", "sortie_sans_sspi")) && $object|instanceof:'Ox\Mediboard\PlanningOp\COperation' && $modif_operation}}
          {{assign var=name_form value="fin_intervention"}}

          {{if $field == "sortie_sans_sspi"}}
            {{assign var=name_form value="sortie_sans_sspi_auto"}}
          {{/if}}
          {{mb_include module=forms template=inc_widget_ex_class_register object=$object event_name=$name_form cssStyle="display: inline-block;"}}
        {{/if}}
      {{elseif $modif_operation}}
        {{unique_id var=timing_uid}}

        <input type="hidden" name="{{$field}}" value="" onchange="{{$submit}}(this.form);" disabled/>
        <input type="hidden" name="_set_{{$field}}" value="1"/>
        {{* Custom flag to tell we are setting the value (module formulaires) *}}
        <button type="button" class="submit notext not-printable me-primary"
                id="timing-{{$timing_uid}}" {{if $disabled == "yes" || $graph_lock}}disabled{{/if}}>
        </button>
        {{if $object|instanceof:'Ox\Mediboard\PlanningOp\COperation' && $field === "entree_reveil"}}
          {{mb_field object=$object field=sspi_id hidden=true}}
        {{/if}}
        <script>
          Main.add(function () {
            $("timing-{{$timing_uid}}").observe("click", function (e) {
              var button = Event.element(e);
              var form = button.form;
              var date = new Date();
              var current_datetime = date.toDATETIME(true);
              {{if $object->_class == "COperation"}}
              var entree_sejour = '{{$object->_ref_sejour->entree}}';
              var sortie_sejour = '{{$object->_ref_sejour->sortie}}';
              var last_timing = '{{$last_timing}}';
              var type_graph = 'perop';

              {{if !$object->$field && $field != "sortie_salle"}}
              SalleOp.checkTimingOperation(entree_sejour, sortie_sejour, form.{{$field}}, '{{$object->_id}}', last_timing);
              {{elseif !$object->$field && $field == "sortie_salle"}}
              SalleOp.editSortieSalle('{{$object->_id}}', form);
              return false;
              {{/if}}
              {{/if}}

              {{if $use_concentrator && $object|instanceof:'Ox\Mediboard\PlanningOp\COperation' && ($field == "entree_salle" || $field == "fin_prepa_preop")}}

              var start_session = 1;

              var stop_session = true;

              {{if $field != "sortie_salle" && $object->_class == "COperation"}}
              form.{{$field}}.disabled = null;
              $V(form.{{$field}}, 'current', true);
              {{/if}}

              {{if $object->_class == "COperation"}}
              if (stop_session)
              {
                App.loadJS({module: "patientMonitoring", script: "concentrator_common"}, function () {
                  ConcentratorCommon.stopCurrentSession("{{$object->_id}}", function () {
                    form.{{$field}}.disabled = null;
                    $V(form.{{$field}}, 'current', true);
                  });
                });

                form.{{$field}}.disabled = null;
                $V(form.{{$field}}, 'current', true);
              }
              {{/if}}

              {{if $field == "fin_prepa_preop"}}
                type_graph = 'preop';
              {{/if}}
              //concentrator.js
              App.loadJS({module: "patientMonitoring", script: "concentrator_common"}, function () {
                ConcentratorCommon.askPosteConcentrator(
                  "{{$object->_id}}",
                  "{{if $object->_ref_salle}}{{$object->_ref_salle->bloc_id}}{{/if}}",
                  type_graph,
                  form,
                  function () {
                    {{if $field == "entree_salle"}}
                      ConcentratorCommon.importDataToConstants('{{$object->_id}}', type_graph);
                    {{/if}}
                    if (window.reloadSurveillance) {
                      if ($('surveillance_preop') && type_graph == 'preop') {
                        reloadSurveillance.preop();
                      }
                      if ($('surveillance_perop') && type_graph == 'perop') {
                        reloadSurveillance.perop();
                      }
                    }
                  },
                  start_session,
                  null,
                  "{{$field}}"
                );
              });
              {{else}}
              window.submitFormTiming = function (sspi_id) {
                if (sspi_id) {
                  $V(form.sspi_id, sspi_id);
                }
                form
              .{{$field}}.
                disabled = null;
                $V(form.{{$field}}, 'current', true);

                window.submitFormTiming = null;
              };

              {{if $use_poste && $field === "entree_reveil"}}
              new Url('salleOp', 'ajax_count_sspis')
                .addParam('bloc_id', '{{$object->_bloc_id}}')
                .requestJSON(
                  (function (result) {
                    if (result.sspi_id || !result.count) {
                      return window.submitFormTiming(result.sspi_id);
                    }

                    new Url('salleOp', 'ajax_select_sspi')
                      .addParam('bloc_id', '{{$object->_bloc_id}}')
                      .requestModal(null, null);
                  }).bind(this)
                );
              {{else}}
              {{if $use_concentrator && $field == 'sortie_reveil_reel'}}
              var stop_session = true;
              type_graph = 'sspi';

              {{if $current_session}}
                stop_session = confirm($T('CMonitoringConcentrator-msg-Do you want to stop session in progress'));
              {{/if}}
              //concentrator.js
              if (stop_session) {
                App.loadJS({module: "patientMonitoring", script: "concentrator_common"}, function () {
                  ConcentratorCommon.stopCurrentSession("{{$object->_id}}", function () {
                    ConcentratorCommon.askPosteConcentrator(
                      "{{$object->_id}}",
                      "{{if $object->_ref_salle}}{{$object->_ref_salle->bloc_id}}{{/if}}",
                      'sspi',
                      form,
                      function () {
                        Concentrator.importDataToConstants('{{$object->_id}}', type_graph);
                      },
                      0,
                      null,
                      "{{$field}}"
                    );
                  });
                });
              } else {
                //concentrator.js
                App.loadJS({module: "patientMonitoring", script: "concentrator_common"}, function () {
                  ConcentratorCommon.askPosteConcentrator(
                    "{{$object->_id}}",
                    "{{if $object->_ref_salle}}{{$object->_ref_salle->bloc_id}}{{/if}}",
                    'sspi',
                    form,
                    function () {
                      ConcentratorCommon.importDataToConstants('{{$object->_id}}', type_graph);
                    },
                    1,
                    null,
                    "{{$field}}"
                  );
                });
              }
              {{/if}}

              window.submitFormTiming();
              {{/if}}
              {{/if}}
            });
          });
        </script>
      {{else}}
        -
      {{/if}}
    </div>
  </div>
</div>
