{{*
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=edit_consultation}}
{{mb_script module=planningOp script=operation}}

{{if "planSoins"|module_active}}
    {{mb_script module=planSoins script=plan_soins}}
{{/if}}
{{mb_script module=soins script=soins}}

{{mb_script module=compteRendu script=document}}
{{mb_script module=system script=alert}}


{{if "dPprescription"|module_active}}
    {{mb_script module=prescription script=prescription}}
    {{mb_script module=prescription script=element_selector}}
{{/if}}

{{if "dPmedicament"|module_active}}
    {{mb_script module=medicament script=medicament_selector}}
    {{mb_script module=medicament script=equivalent_selector}}
{{/if}}

{{if "messagerie"|module_active}}
    {{mb_script module=messagerie script=UserEmail}}
{{/if}}

{{if "dPImeds"|module_active}}
    {{mb_script module=Imeds script=Imeds_results_watcher}}
{{/if}}

<script>
  Consultation.useModal();
  Operation.useModal();
  let frequency = 300;

  updateListConsults = function(withClosed) {
      new Url('cabinet', 'httpreq_vw_list_consult')
          .addParam("chirSel"    , "{{$prat->_id}}")
          .addParam("functionSel", "{{$function->_id}}")
          .addParam("date"       , "{{$date}}")
          .addParam("vue2"       , "{{$vue}}")
          .addParam("selConsult" , "")
          .addParam("board"      , "1")
          .addParam('withClosed' , withClosed)
          .requestUpdate("tab-consultations");
  };

  updateListPrescriptions = function () {
      new Url('board', 'sejoursOtherResponsable')
          .addParam('pratSel', '{{$prat->_id}}')
          .addParam('functionSel', '{{$function->_id}}')
          .addParam('date', '{{$date}}')
          .addParam('board', '1')
          .requestUpdate("tab-autre-responsable");
  };

  updateListOperations = function() {
      new Url('planningOp', 'httpreq_vw_list_operations')
          .addParam('pratSel'    , '{{$prat->_id}}')
          .addParam('functionSel', '{{$function->_id}}')
          .addParam('date'       , '{{$date}}')
          .addParam('urgences'   , '0')
          .addParam('board'      , '1')
          .requestUpdate("tab-operations");
  };

  updateListHospi = function() {
      new Url('board', 'httpreq_vw_hospi')
          .addParam('chirSel'    , '{{$prat->_id}}')
          .addParam('functionSel', '{{$function->_id}}')
          .addParam('date'       , '{{$date}}')
          .requestUpdate('tab-hospitalisations');
  };

  updateWorkList = function () {
    new Url('board', 'ajaxWorklist')
      .addParam('chirSel', '{{$prat->_id}}')
      .addParam('date', '{{$date}}')
      .addParam('function_id', '{{$function->_id}}')
      .requestUpdate('worklist');
  };

  showDossierSoins = function (sejour_id, date, default_tab) {
    let url = new Url('soins', 'viewDossierSejour')
      .addParam('sejour_id', sejour_id)
      .addParam('modal', '1');
    if (default_tab) {
      url.addParam('default_tab', default_tab);
    }
    url.requestModal('100%', '100%', {
      onClose: function () {
        TabsPrescription.updatePrescriptions();
        TabsPrescription.updateInscriptions();
        TabsPrescription.updateAntibios();
        TabsPrescription.updateComPharma();
        Board.refreshLineSejour(sejour_id, true, "", true, true, true, {{"dPImeds"|module_active}});
      }
    });
    modalWindow = url.modalObject;
  };

  Main.add(function () {
    tabsEvents = Control.Tabs.create('tabs-prat-events', true);
    var prat_events = $('prat_events');
    ViewPort.SetAvlHeight(prat_events, 0.5);
      {{if $prat->_id || $function->_id}}
    Board.initUpdateListConsults("{{$prat->_id}}", "{{$function->_id}}", "{{$date}}", "{{$vue}}", "", "1", frequency);
    Board.initUpdateListPrescriptions('{{$prat->_id}}', '{{$function->_id}}', '{{$date}}', '1', frequency);
    Board.initUpdateListOperations('{{$prat->_id}}', '{{$function->_id}}', '{{$date}}', '0', '1', frequency);
    Board.updateListHospi('{{$prat->_id}}', '{{$function->_id}}', '{{$date}}');
    Board.updateCanceledSurgeries('{{$prat->_id}}', '{{$function->_id}}', '{{$date}}');
    updateWorkList();
      {{/if}}
    $('tab-consultations').fixedTableHeaders({container: prat_events, refTop: tabsEvents.activeContainer});
    $('tab-operations').fixedTableHeaders({container: prat_events, refTop: tabsEvents.activeContainer});
    $('tab-canceled-operations').fixedTableHeaders({container: prat_events, refTop: tabsEvents.activeContainer});
    $('tab-hospitalisations').fixedTableHeaders({container: prat_events, refTop: tabsEvents.activeContainer});
    ViewPort.SetAvlHeight('worklist', 1.0);
    Calendar.regField(getForm('changeDate').date, null, {noView: true});
  });
</script>

<table class="main">
  <tr>
    <th class="me-valign-middle" colspan="2">
      <a id="vw_day_date_a" href="?m={{$m}}&tab={{$tab}}&date={{$prec}}">&lt;&lt;&lt;</a>
      <form name="changeDate" action="?m={{$m}}" method="get">
        <input type="hidden" name="m" value="{{$m}}"/>
        <input type="hidden" name="tab" value="{{$tab}}"/>
          {{$date|date_format:$conf.longdate}}
        <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit();"/>
      </form>
      <a href="?m={{$m}}&tab={{$tab}}&date={{$suiv}}">&gt;&gt;&gt;</a>
    </th>
  </tr>
</table>

<button class="expand notext me-margin-bottom-8 me-float-left"
        onclick="App.savePref('alternative_display',{{if $app->user_prefs.alternative_display}}'0' {{else}} '1' {{/if}}, function(){getForm(changeDate).submit()});">
</button>

{{if "doctolib"|module_active && "doctolib staple_authentification client_access_key_id"|gconf}}
    {{mb_include module=doctolib template=buttons/inc_vw_buttons_appointments}}
{{/if}}

<!--  Consultations / Operations / Hospitalisations-->
<fieldset class="me-align-auto me-margin-bottom-8 me-margin-top-8 me-padding-top-0">
  <div id="prat_events">
    <div>
      <ul class="control_tabs me-margin-top-0 me-small" id="tabs-prat-events">
        <li><a href="#tab-hospitalisations" class="empty">{{tr}}CSejour|pl{{/tr}} <small>(&ndash;)</small></a></li>
        <li><a href="#tab-consultations" class="empty">{{tr}}CConsultation|pl{{/tr}} <small>(&ndash;)</small></a></li>
        <li><a href="#tab-operations" class="empty">{{tr}}COperation|pl{{/tr}} <small>(&ndash;)</small></a></li>
        <li><a href="#tab-canceled-operations" class="empty">{{tr}}COperation-annulee|pl{{/tr}} <small>(&ndash;)</small></a>
        </li>
        <li title="{{tr}}CSejour-other-in-charge-desc{{/tr}}"><a href="#tab-autre-responsable"
                                                                 class="empty">{{tr}}CSejour-other-in-charge{{/tr}}
            <small>(&ndash;)</small></a></li>
      </ul>
    </div>
    <div id="tab-hospitalisations" style="display: none;overflow: auto;" class="me-no-align"></div>
    <div id="tab-consultations" style="display: none;overflow: auto;" class="me-no-align"></div>
    <div id="tab-operations" style="display: none;overflow: auto;" class="me-no-align"></div>
    <div id="tab-canceled-operations" style="display: none;overflow: auto;" class="me-no-align"></div>
    <div id="tab-autre-responsable" style="display: none;overflow: auto;" class="me-no-align"></div>
  </div>
</fieldset>

<!-- Volet des worklists -->
<fieldset class="me-align-auto me-padding-top-0">
  <div id="worklist" style="overflow: auto"></div>
</fieldset>
