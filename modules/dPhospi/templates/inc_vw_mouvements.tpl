{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$readonly}}
  <script>
    Main.add(function () {
      var tableau_vue_temporelle = $("tableau_vue_temporel");
      var time_line_temporelle = $('time_line_temporelle');
      var view_affectations = $("view_affectations");
      var open_choix_action = parseInt({{"dPhospi vue_temporelle open_fenetre_action"|gconf|@json}});

      view_affectations.scrollTop = 0;

      time_line_temporelle.style.width = $("tableau_vue_temporel").getWidth() + "px";
      time_line_temporelle.up('div').style.height = time_line_temporelle.getHeight() + 'px';

      view_affectations.select(".droppable").each(function (tr) {
        Droppables.add(tr, {
          onDrop:     function (div, tr, event) {
            window.div_lit = null;
            if (!tr.isVisible(view_affectations)) {
              return;
            }

            var lit_id = tr.get("lit_id");

            // Création d'une affectation pour bloquer un lit
            if (div.id == "lit_bloque") {
              Affectation.edit(null, lit_id, 0);
            }
            else if (div.id == "lit_urgence") {
              Affectation.edit(null, lit_id, 1);
            }
            else {
              var affectation_id = div.get("affectation_id");
              var sejour_id = div.get("sejour_id");
              var curr_lit_id = div.get("lit_id");

              // Si la config de choix d'action est activée et que l'affectation existe,
              // ouverture de la modale pour demander quoi faire
              if (open_choix_action && affectation_id && curr_lit_id) {
                selectAction(affectation_id, lit_id);
              }
              // Sinon déplacement de l'affectation si c'est vers un autre lit
              else if (lit_id != div.get("lit_id")) {
                moveAffectation(affectation_id, lit_id, sejour_id, div.get("lit_id"));
              }
            }
            if (div.get("lit_id") && lit_id != div.get("lit_id") && !event.ctrlKey) {
              window.div_lit = div;
            }
          },
          hoverclass: "lit_hover",
          accept:     "draggable"
        });
        // Bug de firefox
        tr.setStyle({position: "static"});
      });

      $("view_affectations").scrollTop = Placement.scrollAffectations;

      {{if "reservation"|module_active}}
      tableau_vue_temporelle.select(".mouvement_lit").each(function (elt) {
        elt.observe("dblclick", function () {
          var datetime = elt.get("date").split(" ");
          window.save_date = datetime[0];
          window.save_hour = datetime[1].split(":")[0];
          window.save_lit_guid = elt.up("tr").id;
          chooseIntervSejour();
        });
      });
      {{/if}}

      {{if "dPImeds"|module_active}}
      ImedsResultsWatcher.loadResults();
      {{/if}}
    });
  </script>
{{/if}}

<script>
  changeDate = function (date, sens) {
    var form = getForm("filterMouv");
    $V(form.date_min, "{{$date_min}}");
    $V(form.date_max, "{{$date_max}}");
    $V(form.sens, sens);
    $V(form.date, date);
  }
</script>

{{if $prestation_id}}
  {{math equation=x+2 x=$nb_ticks assign=colspan}}
{{else}}
  {{math equation=x+1 x=$nb_ticks assign=colspan}}
{{/if}}
{{math equation=x-1 x=$nb_ticks assign=nb_ticks_r}}

<form name="affectationResize" method="post">
  <input type="hidden" name="m" value="hospi" />
  <input type="hidden" name="dosql" value="do_affectation_aed" />
  <input type="hidden" name="affectation_id" />
  <input type="hidden" name="sortie" />
</form>

<div>
  <div id="time_line_temporelle" class="me-bg-elevation-6" style="background: #fff;z-index: 200; position: absolute;">
    <strong>
      <a href="#1" onclick="changeDate('{{$date_before}}', '-24 hour');">
        &lt;&lt;&lt; {{$date_before_view|date_format:$conf.date}}
      </a>
      {{if $can->admin}}
        <span>{{$nb_affectations}} affectation(s)</span>
      {{/if}}
      <a href="#1" style="float: right" onclick="changeDate('{{$date_after}}', '+24 hour');">
        {{$date_after_view|date_format:$conf.date}} &gt;&gt;&gt;
      </a>
    </strong>
    <table class="tbl me-no-align me-small" style="table-layout: fixed;">
      {{if $prestation_id}}
        <col style="width: 5%;" />
        <col style="width: 10%;" />
      {{else}}
        <col style="width: 15%;" />
      {{/if}}
      <tr>
        {{if $granularite == "day"}}
          <th class="title" {{if $prestation_id}}colspan="2"{{/if}}></th>
          <th class="title me-text-align-center" colspan="{{$nb_ticks}}">
            <a href="#1" style="float: left; margin-right: 5px; " onclick="changeDate('{{$date_before_hour}}', '-1 hour');">
              {{me_img src="prev.png" icon="arrow-left" class="me-primary" alt="&lt;"}}
            </a>
            <a href="#1" style="float: right; margin-left: 5px;  " onclick="changeDate('{{$date_after_hour}}', '+1 hour');">
              {{me_img src="next.png" icon="arrow-right" class="me-primary" alt="&gt;"}}
            </a>

            {{if $date_min|date_format:$conf.date == $date_max|date_format:$conf.date}}
              {{$date|date_format:$conf.longdate}}
            {{else}}
              <span style="float: left">
              {{$date_min|date_format:$conf.longdate}}
            </span>
              <span style="float: right">
              {{$date_max|date_format:$conf.longdate}}
            </span>
            {{/if}}
          </th>
        {{else}}
          <th class="title" {{if $prestation_id}}colspan="2"{{/if}}></th>
          {{foreach from=$days item=_day key=_datetime}}
            {{if $granularite == "48hours"}}
              {{assign var=colspan_tick value=12}}
            {{elseif $granularite == "72hours"}}
              {{assign var=colspan_tick value=8}}
            {{elseif $granularite == "week"}}
              {{assign var=colspan_tick value=4}}
            {{else}}
              {{assign var=colspan_tick value=7}}
            {{/if}}
            <th class="title" colspan="{{$colspan_tick}}">
              {{if $granularite == "week"}}
                {{$_day|date_format:"%a"}} {{$_day|date_format:$conf.date}}
              {{elseif $granularite == "4weeks"}}
                {{if isset($change_month.$_day|smarty:nodefaults)}}
                  {{if isset($change_month.$_day.left|smarty:nodefaults)}}
                    <span style="float: left;">
                    {{$change_month.$_day.left|date_format:"%B"}}
                  </span>
                  {{/if}}
                  {{if isset($change_month.$_day.right|smarty:nodefaults)}}
                    <span style="float: right;">
                    {{$change_month.$_day.right|date_format:"%B"}}
                  </span>
                  {{/if}}
                {{/if}}
                {{tr}}Week{{/tr}} {{$_day}}
              {{else}}
                {{$_day|date_format:$conf.longdate}}
              {{/if}}
            </th>
          {{/foreach}}
        {{/if}}
      </tr>
      <tr>
        <th style="text-align: left;" {{if $prestation_id}}colspan="2"{{/if}}>
          <input type="text" style="width: 7em;" onkeyup="filter(this, 'tableau_vue_temporel')" class="search me-small" />
        </th>
        {{foreach from=$datetimes item=_date}}
          <th class="me-border-width-1 me-text-align-center">
            {{if $granularite == "4weeks"}}
              {{$_date|date_format:"%a"|upper|substr:0:1}} {{$_date|date_format:"%d"}}
            {{else}}
              {{$_date|date_format:"%H"}}h
            {{/if}}
          </th>
        {{/foreach}}
      </tr>
    </table>
  </div>
</div>

<div class="small-info" id="alerte_tableau_vue_temporel" style="display: none">
  {{tr}}CSejour-partial_view{{/tr}}
</div>

<table class="tbl layout_temporel me-no-align" id="tableau_vue_temporel" style="table-layout: fixed; position: relative;">
  {{if $prestation_id}}
    <col style="width: 5%;" />
    <col style="width: 10%;" />
  {{else}}
    <col style="width: 15%;" />
  {{/if}}
  {{foreach from=$services item=_service}}
    <tr>
      <th class="section me-category me-text-align-center {{if $_service->externe}}service_externe{{/if}}" colspan="{{$colspan}}">{{$_service}}</th>
    </tr>
    {{foreach from=$_service->_ref_chambres item=_chambre}}
      {{foreach from=$_chambre->_ref_lits item=_lit}}
        <tr data-lit_id="{{$_lit->_id}}" id="{{$_lit->_guid}}" class="droppable line">
          {{mb_include module=hospi template=inc_line_lit}}
        </tr>
      {{/foreach}}
    {{/foreach}}
  {{/foreach}}
</table>
