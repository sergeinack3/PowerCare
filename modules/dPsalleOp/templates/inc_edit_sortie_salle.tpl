{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=use_concentrator value=false}}
{{if "patientMonitoring"|module_active && "patientMonitoring CMonitoringConcentrator active"|gconf}}
  {{assign var=use_concentrator value=true}}
{{/if}}

{{assign var=validate_datetimes    value='Ox\Mediboard\PlanningOp\COperation::getValidatingTimings'|static_call:"`$operation->_id`":"sortie_salle"}}
{{assign var=validate_datetime_min value=$validate_datetimes.min}}
{{assign var=validate_datetime_max value=$validate_datetimes.max}}
{{assign var=last_timing           value=$validate_datetimes.last_timing}}
{{assign var=entree_sejour         value=$operation->_ref_sejour->entree}}
{{assign var=sortie_sejour         value=$operation->_ref_sejour->sortie}}

{{assign var=isAdmin value=$app->_ref_user->isAdmin()}}

{{if $use_concentrator}}
  {{assign var=current_session value='Ox\Mediboard\PatientMonitoring\CMonitoringSession::getCurrentSession'|static_call:"`$operation`"}}
{{/if}}

<div class="small-warning">
  {{tr}}COperation-msg-The entry of the room exit is final it will not be possible to modify it{{/tr}}
</div>

<form name="edit_sortie_salle" method="post">
  {{mb_key   object=$operation}}
  {{mb_class object=$operation}}

  <table class="form">
    <tr>
      <th>
        {{mb_label object=$operation field=sortie_salle}}
      </th>
      <td>
        <input type="text" name="sortie_salle_da" readonly
               value="{{$operation->sortie_salle|date_format:$conf.datetime}}"/>
        <input type="hidden" name="sortie_salle" value="{{$operation->sortie_salle}}" class="dateTime"
               onchange=""/>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button type="button" id="timing-sortie_salle" class="submit me-primary singleclick">
          {{tr}}Save{{/tr}}
        </button>
        <button type="button" class="cancel me-tertiary singleclick"
                onclick="Control.Modal.close();">
          {{tr}}Cancel{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>
<script>
  Main.add(function () {
      let options = {
        datePicker: true,
        timePicker: true,
        minHours:   '{{$validate_datetime_min|date_format:"%H"}}',
        maxHours:   '{{$validate_datetime_max|date_format:"%H"}}',
      };

      let dates = {
        limit: {
          start: '{{$validate_datetime_min}}',
          stop:  '{{$validate_datetime_max}}'
        }
      };

    Calendar.regField(getForm('edit_sortie_salle').sortie_salle {{if !$isAdmin}}, dates, options{{/if}});

    $("timing-sortie_salle").observe("click", function (e) {
      var button = Event.element(e);
      var form = button.form;
      var date = new Date();
      var current_datetime = date.toDATETIME(true);

      var entree_sejour = '{{$entree_sejour}}';
      var sortie_sejour = '{{$sortie_sejour}}';
      var last_timing = '{{$last_timing}}';

      if (SalleOp.checkTimingOperation(entree_sejour, sortie_sejour, form.sortie_salle, '{{$operation->_id}}', last_timing)) {
        {{if $use_concentrator && $operation|instanceof:'Ox\Mediboard\PlanningOp\COperation'}}
        var start_session = 1;

        var stop_session = true;

        {{if $current_session}}
         stop_session = confirm($T('CMonitoringConcentrator-msg-Do you want to stop session in progress'));
        {{/if}}

        if (stop_session) {
          App.loadJS({module: "patientMonitoring", script: "concentrator_common"}, function () {
            ConcentratorCommon.stopCurrentSession("{{$operation->_id}}", function () {
            });
          });
        }

        onSubmitFormAjax(form, {
          onComplete: function () {
            Control.Modal.close();

            App.loadJS({module: "patientMonitoring", script: "concentrator_common"}, function () {
              ConcentratorCommon.askPosteConcentrator(
                "{{$operation->_id}}",
                "{{if $operation->_ref_salle}}{{$operation->_ref_salle->bloc_id}}{{/if}}",
                'perop',
                form,
                function () {
                  ConcentratorCommon.importDataToConstants('{{$operation->_id}}', 'perop');
                },
                start_session,
                null,
                'sortie_salle'
              );
            });

            if (window.reloadSurveillance) {
              if ($('surveillance_perop')) {
                reloadSurveillance.perop();
              }
            }
          }
        });
        {{else}}
        onSubmitFormAjax(form, Control.Modal.close);
        {{/if}}
      } else {
        Control.Modal.reload();
      }
    });
  });
</script>
