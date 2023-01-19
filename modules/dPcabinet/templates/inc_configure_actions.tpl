{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=slot}}

<table class="tbl">
  <tr>
    <th class="category">{{tr}}CPlageconsult{{/tr}}</th>
  </tr>

  <tr>
    <td class="button">
      <script>
        PlageConsult = {
          transfert: function() {
            var url = new Url();
            url.setModuleAction("dPcabinet", "transfert_plageconsult");
            url.popup(500, 600, "transfert");
          }
        }
      </script>
      <button class="modify" type="button" onclick="PlageConsult.transfert();">
        {{tr}}mod-dPcabinet-tab-transfert_plageconsult{{/tr}}
      </button>
    </td>
  </tr>
  <tr>
    <td class="button">
      <script>
        correctionPlagesConsult = function(resolve) {
          var url = new Url("cabinet", "ajax_move_consult_plage");
          url.addParam("resolve", resolve);
          url.requestModal(500);
        };
      </script>
      <button class="modify" type="button" onclick="correctionPlagesConsult(0);">
        {{tr}}CConsult-action-Move consultation|pl{{/tr}}
      </button>
    </td>
  </tr>
  <tr>
    <td class="button">
      <button class="search" type="button" onclick="Slot.modalReplaySlot();">
          {{tr}}CSlot-correct_slot_consult{{/tr}}
      </button>
    </td>
  </tr>
  <tr>
    <td class="button">
      <button class="search" type="button" onclick="Slot.modalReplayConsultationToSlot();">
          {{tr}}CSlot-correct_link_consultations_slot{{/tr}}
      </button>
    </td>
  </tr>

  <tr>
    <th class="category">{{tr}}CConsultation{{/tr}}</th>
  </tr>

  <tr>
    <td class="button">
      <script>
        Main.add(function() {
          Calendar.regField(getForm("MacroStats").date);
        });
      </script>

      <form name="MacroStats" method="get">
        <input type="hidden" name="date" value="{{$date}}" />
        <select name="period">
          <option value="day"  >{{tr}}Day  {{/tr}}</option>
          <option value="week" >{{tr}}Week {{/tr}}</option>
          <option value="month">{{tr}}Month{{/tr}}</option>
          <option value="year" >{{tr}}Year {{/tr}}</option>
        </select>
        <select name="type">
          <option value="RDV">{{tr}}CConsultation-rdv{{/tr}}</option>
          <option value="consult">{{tr}}CConsultation{{/tr}}</option>
          <option value="FSE" style="display: none;">{{tr}}CFSE{{/tr}}</option>
        </select>
        <label>
          <input type="checkbox" name="consult_no_sejour"  checked/>
          {{tr}}CConsult-Without stay|pl{{/tr}}
        </label>
        <label>
          <input type="checkbox" name="consult_sejour_consult" checked/>
          {{tr}}CConsult-Consultation stay{{/tr}}
        </label>
        <label>
          <input type="checkbox" name="consult_sejour_urg" />
          {{tr}}CConsult-Emergency stay{{/tr}}
        </label>
        <label>
          <input type="checkbox" name="consult_sejour_ext" />
          {{tr}}CConsult-External stay{{/tr}}
        </label>
        <label>
          <input type="checkbox" name="consult_sejour_autre" />
          {{tr}}CConsult-Other stay{{/tr}}
        </label>
        <button class="modify" type="button" onclick="Consultation.macroStats(this);">
          {{tr}}mod-cabinet-tab-user_stats{{/tr}}
        </button>
      </form>
    </td>
  </tr>

  <tr>
    <td class="button">
      <button type="button" class="search" onclick="Consultation.createSejours();">{{tr}}mod-dPcabinet-tab-vw_create_sejours_for_consults{{/tr}}</button>
    </td>
  </tr>

  <tr>
    <td class="button">
      <button class="search" type="button" onclick="Consultation.checkParams();">
        {{tr}}mod-cabinet-tab-check_params{{/tr}}
      </button>
    </td>
  </tr>

  <tr>
    <td class="button">
      <form name="import-consultations" method="get">
        <select name="prat_id" class="ref notNull">
          <option value=""> &mdash; </option>
          {{foreach from=$praticiens item=_prat}}
            <option value="{{$_prat->_id}}">{{$_prat}}</option>
          {{/foreach}}
        </select>
        <button class="modify" type="button" onclick="Consultation.importPlanning($V(this.form.prat_id));">
          {{tr}}CConsultation-action-Import planning{{/tr}}
        </button>
      </form>
    </td>
  </tr>

  <tr>
    <td class="button">
      <button class="modify" type="button" onclick="Consultation.importPlanningLite();">
        {{tr}}CConsultation-action-Import lite planning{{/tr}}
      </button>
    </td>
  </tr>
</table>

<script>
  cleanPlages = function() {
    var url = new Url("cabinet", "controllers/do_clean_plages");
    url.addParam("praticien_id", $V("clean_plage_praticien_id"));
    url.addParam("date"        , $V("clean_plage_date"));
    url.addParam("limit"       , $V("clean_plage_limit"));

    // Give some rest to server
    var onComplete = $('clean_plage_auto').checked ? cleanPlages : Prototype.emptyFunction;
    url.requestUpdate("resultCleanPlages", function () { onComplete.delay(2) } );
  };
</script>

<form name="clean-CPlageConsult" method="get">
  <table class="form">
    <tr>
      <th colspan="3" class="category">{{tr}}CPlageConsult clean{{/tr}}</th>
    </tr>
    <tr>
      <th class="halfPane">Date de départ</th>
      <td>
        <script>
          Main.add(function() {
            Calendar.regField(getForm("clean-CPlageConsult").debut);
          });
        </script>
        <input id="clean_plage_date" type="hidden" name="debut" value="{{$debut}}" />
      </td>
      <td rowspan="5" class="greedyPane" id="resultCleanPlages">
      </td>
    </tr>
    <tr>
      <th>Praticien</th>
      <td>
        <select id="clean_plage_praticien_id" name="praticien_id" style="width: 14em;">
          <option value="">&mdash; Tous les praticiens</option>
          {{foreach from=$praticiens item=_prat}}
            <option value="{{$_prat->_id}}">{{$_prat}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>Nombre maximum de plages à traiter</th>
      <td>
        <input id="clean_plage_limit" type="text" name="limit" value="{{$limit}}" style="width: 14em;" />
      </td>
    </tr>

    <tr>
      <th>
        <label for="clean_plage_auto">Auto</label>
      </th>
      <td>
        <input id="clean_plage_auto" type="checkbox" name="auto" value="1" />
      </td>
    </tr>

    <tr>
      <td class="button" colspan="3">
        <button type="button" class="trash" onclick="cleanPlages()">{{tr}}Clean{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<script type="text/javascript">
  importTarifs = function() {
    var url = new Url('cabinet', 'ajax_import_export_tarifs');
    url.addParam('action', 'import');
    url.requestModal(400, 300, {
      title: $T('CTarif-action-import')
    });
  };

  exportTarifs = function() {
    var url = new Url('cabinet', 'ajax_import_export_tarifs');
    url.addParam('action', 'export');
    url.requestModal(400, 300, {
      title: $T('CTarif-action-export')
    });
  };
</script>

<table class="form">
  <tr>
    <th class="category">{{tr}}Tarifs action|pl{{/tr}}</th>
  </tr>
  <tr>
    <td class="button">
      <form name="recalculTarifs" method="post" onsubmit="return onSubmitFormAjax(this);">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="dosql" value="do_tarif_aed" />
        <input type="hidden" name="reloadAlltarifs" value="1" />
        <button class="reboot">Recalculer l'ensemble des tarifs</button>
      </form>
    </td>
  </tr>
  <tr>
    <td class="button">
      <form name="modifTaux2014" method="post" action="?" onsubmit="return onSubmitFormAjax(this);">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="dosql" value="do_tarif_aed" />
        <input type="hidden" name="modifTauxVingPct" value="1" />
        <button class="change">Passer tous les tarifs ayant un taux de TVA de 19,6% à 20%</button>
      </form>
    </td>
  </tr>
  <tr>
    <td class="button">
      <button class="download" onclick="exportTarifs();" style="text-align: ">
        {{tr}}CTarif-action-export{{/tr}}
      </button>
      <button type="button" class="upload" onclick="importTarifs();">
        {{tr}}CTarif-action-import{{/tr}}
      </button>
    </td>
  </tr>
</table>


<h2>{{tr}}CConsult-Maintenance action|pl{{/tr}}</h2>

<script>
  createConsultAnesth = function() {
    var url = new Url("cabinet", "ajax_create_missing_consult_anesth");
    url.addParam("anesth_id", $V($("anesth_id")));
    url.requestUpdate("result-create_consult_anesth", { onComplete: function() {
      repeatActions("createConsultAnesth");
    }});
  };

   repeatActions = function (func) {
    if ($V($("check_repeat_actions"))) {
      window[func]();
    }
  };

  cleanDoublonsConsultAnesth = function() {
    var url = new Url("cabinet", "ajax_delete_doublons_consult_anesth");
    url.requestUpdate("result_doublons_consult_anesth");
  }
</script>

<table class="tbl">
  <tr>
    <th>{{tr}}Action{{/tr}}</th>
    <th>{{tr}}Status{{/tr}}</th>
  </tr>
  <tr>
    <td class="narrow">
      <button class="search" onclick="createConsultAnesth()">
        {{tr}}CConsult-action-Create anesthesia records for consultation|pl{{/tr}}
      </button> <br />
      <select name="anesth_id" id="anesth_id">
        {{foreach from=$anesths item=_anesth}}
          <option value="{{$_anesth->_id}}">{{$_anesth->_view}}</option>
        {{/foreach}}
      </select><br />
      <input type="checkbox" name="repeat_actions" id="check_repeat_actions"/> {{tr}}CConsult-Automatically restart{{/tr}}
    </td>
    <td id="result-create_consult_anesth"></td>
  </tr>
  <tr>
    <td class="narrow">
      <button class="trash" onclick="cleanDoublonsConsultAnesth()">
      {{tr}}CConsult-action-Deleting duplicate anesthesia record|pl{{/tr}}
    </td>
    <td id="result_doublons_consult_anesth"></td>
  </tr>
</table>
