{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=planning}}
{{mb_script module=ssr script=planification}}

<style type="text/css">
  @media print {
    div.planning_kine {
      display: block !important;
      width: 100% !important;
      height: auto !important;
      font-size: 8pt !important;
    }
    table {
      display: table !important;
      font-size: inherit !important;
      width: 100% !important;
      height: auto !important;
      border: none !important;
    }
    div.week-container {
      display: block !important;
      height: auto !important;
      width: 100% !important;
      position: static !important;
    }
    div.time, div.body, div.event, div.planning {
      font-size: 6pt !important;
      top: auto !important;
      left: auto !important;
      width: 100% !important;
      height: auto !important;
      position: static !important;
      padding: auto !important;
    }
    div#print_plannings {
      display: block;
    }
  }
  
  @media screen {
    div#print_plannings {
      display: none;
    }
  }
</style>

<script>
  Main.add(Control.Tabs.create.curry("tabs_plannings_kines"));

  showPlanningOffline = function(kine_id, kine_guid, type){
    $(kine_guid+'-'+type).down('.week-container').setStyle({height: '600px' });
    (function(){
      if(type == "tech"){
        window['tab-'+kine_id].setActiveTab('planning_technicien_'+kine_id);
      }
      window['planning-'+kine_guid+'-'+type].updateEventsDimensions();
    }).defer();
  };

  printPlanning = function(kine_id) {
    var print_plannings = $("print_plannings").update();

    $("planning_technicien_"+kine_id).select("table").each(function(table) {
      print_plannings.insert(table.clone(true));
    });

    print_plannings.insert(DOM.br({style: "page-break-before: always;"}));

    $("planning_surveillance_"+kine_id).select("table").each(function(table) {
      print_plannings.insert(table.clone(true));
    });
    print_plannings.print();
  };

  printPlannings = function() {
    var print_plannings = $("print_plannings").update();
    var plannings = $$(".planning_kine");
    plannings.each(function(planning) {
      planning.select("table").each(function(table) {
        print_plannings.insert(table.clone(true));
      });
      if (planning !== $A(plannings).last()) {
        print_plannings.insert(DOM.br({style: "page-break-before: always;"}));
      }
    });
    print_plannings.print();
  };
</script>

<h1 style="text-align: center;">
  {{tr}}Week{{/tr}} {{$date|date_format:'%U'}},
  {{assign var=month_min value=$monday|date_format:'%B'}}
  {{assign var=month_max value=$sunday|date_format:'%B'}}
  {{$month_min}}{{if $month_min != $month_max}}-{{$month_max}}{{/if}}
  {{$date|date_format:'%Y'}}
  <button class="print notext" onclick="printPlannings()"></button>
</h1>

<table class="main">
  <tr>
    <td style="width: 10%">
      <ul id="tabs_plannings_kines" class="control_tabs_vertical">
        {{foreach from=$plannings key=kine_id item=_planning}}
          {{assign var=kine value=$kines.$kine_id}}
          <li onmouseup="showPlanningOffline('{{$kine->_id}}', '{{$kine->_guid}}', 'tech');">
            <span style="float: left;">
              <button class="print notext" onclick="printPlanning('{{$kine->_id}}');"></button>
              </span>
            <a href="#planning_{{$kine_id}}">{{$kine->_view}}</a>
          </li>
        {{/foreach}}
      </ul>
    <td>
      {{foreach from=$plannings key=kine_id item=_planning}}
        {{assign var=kine value=$kines.$kine_id}}
      
        <div id="planning_{{$kine_id}}" style="display: none;">
          <script>
            Main.add(function () {
              window['tab-{{$kine_id}}'] = Control.Tabs.create("tabs_plannings_select_{{$kine_id}}");
            });
          </script>

          <ul id="tabs_plannings_select_{{$kine_id}}" class="control_tabs small">
            <li><a href="#planning_technicien_{{$kine_id}}">Planning rééducateur</a></li>
            <li onmouseup="showPlanningOffline('{{$kine->_id}}', '{{$kine->_guid}}', 'surv');">
              <a href="#planning_surveillance_{{$kine_id}}">{{tr}}ssr-planning_surveillance{{/tr}}</a>
            </li>
          </ul>

          <div id="planning_technicien_{{$kine_id}}" style="display: none;" class="planning_kine">
            {{$_planning.technicien|smarty:nodefaults}}     
          </div>

          <div id="planning_surveillance_{{$kine_id}}" style="display: none;" class="planning_kine">
            {{$_planning.surveillance|smarty:nodefaults}}
          </div>
        </div>
      {{/foreach}}
    </td>
  </tr>
</table>

<div id="print_plannings">
</div>