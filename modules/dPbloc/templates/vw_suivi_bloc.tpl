{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=operation}}

<script>
  modalTimingSuivi = null;
  modalTimingSSPiSuivi = null;
  viewLegende = function() {
    Modal.open('tooltip-legende-suivi', {title: 'Légende', showClose: true});
  };

  reloadTimingsSuivi = function(interv_id) {
    modalTimingSuivi = new Url("salleOp", "httpreq_vw_timing");
    modalTimingSuivi.addParam("submitTiming", "submitTimingSuivi");
    modalTimingSuivi.addParam("operation_id", interv_id);
    modalTimingSuivi.requestModal("50%", null, {onClose: refreshSuiviBloc});
  };

  submitTimingSuivi = function(form) {
    onSubmitFormAjax(form, function() {
      modalTimingSuivi.refreshModal();
    });
  };

  reloadTimingsSSPISuivi = function(interv_id) {
    modalTimingSSPiSuivi = new Url("salleOp", "ajax_vw_timings_sspi");
    modalTimingSSPiSuivi.addParam("operation_id", interv_id);
    modalTimingSSPiSuivi.addParam("submitTimingSSPI", "submitTimingSSPISuivi");
    modalTimingSSPiSuivi.requestModal("50%", null, {onClose: refreshSuiviBloc});
  };

  submitTimingSSPISuivi = function(form) {
    onSubmitFormAjax(form, function() {
      modalTimingSSPiSuivi.refreshModal();
    });
  };

  refreshSuiviBloc = function(periodical) {
    var form = getForm('formSuiviBloc');
    var url = new Url("bloc", "vw_suivi_bloc");
    url.addParam("date"   , $V(form.date));
    url.addParam("period"   , $V(form.period));
    url.addParam("blocs_ids[]", $V(form.blocs_ids), true);
    url.addParam("services_ids[]", $V(form.services_ids), true);
    url.addParam("reload" , 1);
    if (periodical) {
      url.periodicalUpdate("suivi_bloc", { frequency: periodical });
    }
    else {
      url.requestUpdate('suivi_bloc');
    }
  };

  Main.add(function () {
    Calendar.regField(getForm("formSuiviBloc").date);
    var frequency = '{{"dPbloc other refresh_period_suivi_bloc"|gconf}}';
    refreshSuiviBloc.delay(frequency, frequency);
  });
</script>

<form action="?" name="formSuiviBloc" method="get">
  <table class="form main">
    <tr>
      <th><strong>{{tr}}Date{{/tr}}</strong></th>
      <td>
        <input type="hidden" name="date" class="date" value="{{$date}}" onchange="refreshSuiviBloc();" />
      </td>
      <th><strong>Période</strong></th>
      <td>
        <select name="period" onchange="refreshSuiviBloc();">
          <option value=""      {{if !$period          }}selected{{/if}}>&mdash; {{tr}}dPAdmission.admission all the day{{/tr}}</option>
          <option value="matin" {{if $period == "matin"}}selected{{/if}}>{{tr}}dPAdmission.admission morning{{/tr}}</option>
          <option value="soir"  {{if $period == "soir" }}selected{{/if}}>{{tr}}Apres-midi{{/tr}}</option>
        </select>
      </td>
      <th><strong>{{tr}}CBlocOperatoire{{/tr}}</strong></th>
      <td>
        <select name="blocs_ids" onchange="refreshSuiviBloc();" multiple="3">
          <option value="" {{if !is_array($blocs_ids) || !$blocs_ids|@count}}selected{{/if}}>Tous les blocs</option>
          {{foreach from=$blocs item=_bloc}}
            <option value="{{$_bloc->_id}}" {{if is_array($blocs_ids) && in_array($_bloc->_id, $blocs_ids)}}selected{{/if}}>
              {{$_bloc->nom}}
            </option>
          {{/foreach}}
        </select>
      </td>
      <th><strong>{{tr}}CService{{/tr}}</strong></th>
      <td>
        <select name="services_ids" onchange="refreshSuiviBloc();" multiple="3">
          <option value="" {{if !is_array($services_ids) || !$services_ids|@count}}selected{{/if}}>{{tr}}CService.all{{/tr}}</option>
          {{foreach from=$services item=currService}}
            <option value="{{$currService->_id}}"
                    {{if (is_array($services_ids) && in_array($currService->_id, $services_ids))}}selected{{/if}}>
              {{$currService}}
            </option>
          {{/foreach}}
        </select>
      </td>
      <td class="button" style="width: 50%;">
        <button type="button" class="search" style="float: right;" onclick="viewLegende();">Légende</button>
        <button type="button" class="new" style="float: right;" onclick="Operation.editModal(null, null);">
          {{tr}}COperation-title-create-urgence{{/tr}}
        </button>
        <div id="tooltip-legende-suivi" style="display: none;">
          {{mb_include module=bloc template=vw_legende_suivi}}
        </div>
      </td>
    </tr>
  </table>
</form>

<div id="suivi_bloc">
  {{mb_include module=bloc template=inc_vw_suivi_bloc}}
</div>