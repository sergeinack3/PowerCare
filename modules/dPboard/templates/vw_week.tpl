{{*
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=edit_consultation}}
{{mb_script module=planningOp script=operation}}

<script>
  Consultation.useModal();

  function viewItem(guid, id, date, oTd) {
    oTd = $(oTd);

    oTd.up('table').select('.event').invoke('removeClassName', 'selected');
    oTd.up('.event').addClassName('selected');

    // Affichage de la partie droite correspondante
    var sClass = guid.split("-")[0];

    viewList(date, id, sClass);
  }

  function viewList(date, id, sClass) {
    var url = new Url();
    url.addParam('board'     , '1');
    url.addParam('boardItem' , '1');

    url.addParam('date'    , date);

    switch (sClass) {
      case 'CPlageconsult':
        url.setModuleAction('cabinet', 'httpreq_vw_list_consult');
        url.addParam('chirSel' , '{{$chirSel}}');
        url.addParam('plageconsult_id', id);
        url.addParam('selConsult'     , '');
        url.addParam('withClosed', '1');
        break;
      case "CPlageOp":
        url.setModuleAction('planningOp', 'httpreq_vw_list_operations');
        url.addParam('pratSel' , '{{$chirSel}}');
        url.addParam('urgences', '0');
        break;
      default:
        return;
    }
    url.requestUpdate('viewTooltip');
  }

  updateListOperations = function() {
    new Url('planningOp', 'httpreq_vw_list_operations')
      .addParam('pratSel' , '{{$chirSel}}')
      .addParam('urgences', '0')
      .addParam('board'   , '1')
      .requestUpdate("viewTooltip");
  };
</script>

{{mb_script module=ssr script=planning}}

<table class="main">
  <tr>
    <th class="me-color-black-high-emphasis">
      <span style="float: right;"><button class="search button" onclick="Modal.open('legend_week_plages');">{{tr}}Legend{{/tr}}</button></span>
      <div style="display: none" id="legend_week_plages">
        <table class="tbl">
          <tr>
            <td style="background-color:#BFB;">&nbsp;&nbsp;</td>
            <td>Plage de consultation</td>
          </tr>
          <tr>
            <td style="background-color:#BCE;">&nbsp;&nbsp;</td>
            <td>Plage opératoire</td>
          </tr>
          <tr>
            <td style="background-color:#748dee;">&nbsp;&nbsp;</td>
            <td>Plage opératoire (Autre Etab.)</td>
          </tr>

          <tr>
            <td style=" background-image: url('../../../images/icons/ray_blue.gif');">&nbsp;&nbsp;</td>
            <td>Plage partagée avec le personnel soignant</td>
          </tr>

          <tr>
            <td colspan="2"><button onclick="Control.Modal.close()" class="close">{{tr}}Close{{/tr}}</button></td>
          </tr>
        </table>
      </div>
      <a href="?m={{$m}}&tab={{$tab}}&date={{$prec}}" >&lt;&lt;&lt;</a>
      Semaine du {{$debut|date_format:$conf.longdate}} au {{$fin|date_format:$conf.longdate}}
      <form name="changeDate" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="tab" value="{{$tab}}" />
        <input type="hidden" name="date" class="date" value="{{$debut}}" onchange="this.form.submit()" />
      </form>
      <a href="?m={{$m}}&tab={{$tab}}&date={{$suiv}}">&gt;&gt;&gt;</a>
      <br />
      <a href="?m={{$m}}&tab={{$tab}}&date={{$today}}">Aujourd'hui</a>
    </th>
  </tr>

  <tr>
    <td id="semainiertdb" style="height:600px;">
      <div id="weekly-planning" class="me-padding-0">
        {{mb_include module=system template=calendars/vw_week}}
      </div>
    </td>
    <td id="viewTooltip" style="min-width: 300px; width: 33%;"></td>
  </tr>
</table>

<script>
  Main.add(function() {
    Calendar.regField(getForm("changeDate").date, null, {noView: true});
    ViewPort.SetAvlHeight("weekly-planning", 1);
    var height = $("weekly-planning").getDimensions().height;
    var planning = window["planning-{{$planning->guid}}"];
    planning.setPlanningHeight(height);
    planning.scroll();
    planning.onMenuClick = function(guid, id, oTd) {

      if (oTd.title != "operation" && oTd.title != "consultation") {
        viewItem(guid, id, oTd.title, oTd);
      }
      else{
        var url;

        if (oTd.title == "operation") {
          url = new Url("dPplanningOp", "vw_idx_planning", "tab");
          url.addParam("selPrat" , "{{$chirSel}}");
        }
        else if (oTd.title == "consultation") {
          url = new Url("dPcabinet", "edit_consultation", "tab");
          url.addParam("pratSel" , "{{$chirSel}}");
        }
        url.addParam("date" , guid);
        url.redirectOpener();
      }
    }
  });
</script>