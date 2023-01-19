{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=vue_alternative value="maternite general vue_alternative"|gconf}}

{{mb_script module=cabinet    script=edit_consultation}}
{{mb_script module=planningOp script=operation}}
{{mb_script module=planningOp script=protocole_selector}}
{{mb_script module=cim10      script=CIM}}
{{mb_script module=planningOp script=ccam_selector}}
{{mb_script module=planningOp script=plage_selector}}
{{mb_script module=soins      script=soins}}
{{mb_script module=maternite  script=tdb}}
{{mb_script module=maternite  script=grossesse}}
{{mb_script module=patients   script=identity_validator}}

{{if $vue_alternative}}
  {{mb_script module=admissions  script=admissions}}
  {{mb_script module=maternite   script=naissance}}
  {{mb_script module=compteRendu script=document}}
  {{mb_script module=compteRendu script=modele_selector}}
  {{mb_script module=files       script=file}}
  {{mb_script module=sante400    script=Idex}}
{{/if}}

<script>
  afterEditConsultMater = function () {
    Control.Modal.close();
    Tdb.views.listConsultations();
  };

  zoomViewport = function (span) {
    var _td = $(span).up("td.viewport");
    $$('td.viewport').each(function (elt) {
      $(elt).toggleClassName("width50");
    });
    _td.toggleClassName("width75");
  };

  Consultation.useModal();
  Operation.useModal();

  Main.add(function () {
    Tdb.vue_alternative = {{$vue_alternative}};
    Tdb.views.date = "{{$date_tdb}}";

    Grossesse.mode_tdb = 1;

    if (Tdb.vue_alternative) {
      $("termes_prevus").fixedTableHeaders(0.5);
      Tdb.views.initListTermesPrevus();
    } else {
      $("grossesses").fixedTableHeaders(0.5);
      $("consultations").fixedTableHeaders(0.5);
      Tdb.views.initListGrossesses();
    }

    $("hospitalisations").fixedTableHeaders();
    $("accouchements").fixedTableHeaders();

    Calendar.regField(getForm("changeDate").date_tdb, null, {noView: true});
  });
</script>

<form name="changeSalleForOp" method="post" onsubmit="return onSubmitFormAjax(this, Tdb.views.listAccouchements);">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dosql" value="do_operation_aed" />
  <input type="hidden" name="operation_id" value="" />
  <input type="hidden" name="salle_id" value="" />
</form>

<form name="changeAnesthForOp" method="post" onsubmit="return onSubmitFormAjax(this, Tdb.views.listAccouchements);">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dosql" value="do_operation_aed" />
  <input type="hidden" name="operation_id" value="" />
  <input type="hidden" name="anesth_id" value="" />
</form>

<form name="changeStatusConsult" method="post" onsubmit="return onSubmitFormAjax(this, Tdb.views.listConsultations);">
  <input type="hidden" name="m" value="cabinet" />
  <input type="hidden" name="dosql" value="do_consultation_aed" />
  <input type="hidden" name="consultation_id" value="" />
  <input type="hidden" name="chrono" value="" />
</form>

<table class="main">
  <tr>
    <th colspan="2">
      <div style="float:left;" class="me-small-fields">
        <input type="text" name="fast_search" id="_seek_patient" placeholder="recherche rapide" onkeyup="Tdb.views.filterByText();"
               onchange="Tdb.views.filterByText();" />
        <button class="cleanup notext me-tertiary me-dark me-small" onclick="$V('_seek_patient', '', true);"></button>
      </div>

      <a id="vw_day_date_a" href="?m={{$m}}&tab={{$tab}}&date_tdb={{$prec}}">&lt;&lt;&lt;</a>
      <form name="changeDate" action="?m={{$m}}" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="tab" value="{{$tab}}" />
        {{$date_tdb|date_format:$conf.longdate}}
        <input type="hidden" name="date_tdb" class="date" value="{{$date_tdb}}" onchange="this.form.submit();" />
      </form>
      <a href="?m={{$m}}&tab={{$tab}}&date_tdb={{$suiv}}">&gt;&gt;&gt;</a>
    </th>
  </tr>

  <tbody class="viewported">
  <tr>
    {{if $vue_alternative}}
      <td class="viewport width100" colspan="2">
        <div id="termes_prevus" class="tdbDiv"></div>
      </td>
    {{else}}
      <!--  Grossesses en cours -->
      <td class="viewport width50">
        <div id="grossesses" class="tdbDiv"></div>
      </td>
      <!-- Consultations -->
      <td class="viewport width50">
        <div id="consultations" class="tdbDiv"></div>
      </td>
    {{/if}}
  </tr>

  <tr>
    <!-- Accouchements -->
    <td class="viewport width50">
      <div id="accouchements" style="overflow: auto;" class="tdbDiv"></div>
    </td>

    <!-- Hospitalisations -->
    <td class="viewport width50">
      <div id="hospitalisations" style="overflow: auto;" class="tdbDiv"></div>
    </td>
  </tr>
  </tbody>
</table>
