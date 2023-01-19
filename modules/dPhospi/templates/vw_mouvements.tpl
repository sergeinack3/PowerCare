{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  window.sejour_selected = null;
  window.lit_selected = null;
  window.affectation_selected = null;
  
  Main.add(function () {
    Calendar.regField(getForm("filterMouv").date);

    var vp_height = document.viewport.getHeight() - 40;

    var view_affectations = $("view_affectations");
    view_affectations.setStyle({
      height: vp_height * 0.59 + "px"
    });

    var list_affectations = $("list_affectations");
    list_affectations.setStyle({
      height: vp_height * 0.3 + "px"
    });

    if (!window.events_attached) {
      Event.observe(window, "resize", function (e) {
        $("time_line_temporelle").style.width = $("tableau_vue_temporel").getWidth() + "px";
      });

      window.events_attached = true;
    }

    refreshMouvements(loadNonPlaces.curry(function () {
      var time_line_temporelle = $("time_line_temporelle");
      var time_line_temporelle_na = $("time_line_temporelle_non_affectes");

      window.top_tempo = time_line_temporelle.getStyle("top");
      time_line_temporelle.setStyle({
        top: window.top_tempo
      });

      window.top_tempo_na = time_line_temporelle_na.getStyle("top");
      time_line_temporelle_na.setStyle({
        top: window.top_tempo_na
      });

      var tableau_vue_temporelle = $("tableau_vue_temporel");
      time_line_temporelle.setStyle({
        width: tableau_vue_temporelle.getWidth() + "px"
      });

      var view_affectations = $("view_affectations");

      if (!Prototype.Browser.IE) {
        view_affectations.on("scroll", function () {
          time_line_temporelle.setClassName("scroll_shadow", view_affectations.scrollTop);
        });
      }
      else {
        view_affectations.on("scroll", function () {
          var style = view_affectations.scrollTop > 0 ?
            "progid:DXImageTransform.Microsoft.Shadow(color='#969696', Direction=180, Strength=6)" : "";
          time_line_temporelle.setStyle({
            "filter": style
          });
        });
      }
    }));

    // Zones des non placés redimensionnables
    var grippie = $("grippie_tempo");
    if (grippie) {
      height_temporel = $("temporel").getHeight() - $("temporel_filtre").getHeight() - 8;
      grippie.observe("mousedown", function (e) {
        Event.stop(e);
        staticOffset = $("view_affectations").getHeight() - e.pointerY();

        document.observe("mousemove", performDrag)
          .observe("mouseup", endDrag);
      });
    }
  });

  performDrag = function (e) {
    Event.stop(e);

    var h = Math.min(Math.max(100, staticOffset + e.pointerY()), height_temporel - 100);
    var h2 = height_temporel - h - 4;
    $("view_affectations").setStyle({height: h + "px"});
    $("list_non_places").setStyle({height: (h2 - 21) + "px"});
    $("list_affectations").setStyle({height: h2 + "px"});
  };

  endDrag = function (e) {
    Event.stop(e);
    document.stopObserving("mousemove", performDrag)
      .stopObserving("mouseup", endDrag);
  };

  refreshMouvements = function (after_refresh, lit_id) {
    if (!after_refresh) {
      after_refresh = Prototype.emptyFunction;
    }
    if (lit_id) {
      var form = getForm("filterMouv");
      var lit_area = $("CLit-" + lit_id);

      if (!lit_area) {
        after_refresh();
        return;
      }

      var url = new Url("hospi", "ajax_refresh_line_lit");
      url.addParam("lit_id", lit_id);
      url.addParam("date", $V(form.date));
      url.addParam("mode_vue_tempo", $V(form.mode_vue_tempo));
      url.addParam("prestation_id", $V(form.prestation_id));
      url.addParam("granularite", $V(form.granularite));
      url.addParam("nb_ticks", lit_area.select("td.mouvement_lit").length);
      url.addParam("date_min", lit_area.down("td").get("date"));
      url.requestUpdate(lit_area, function () {
        after_refresh();
        {{if !$readonly && "reservation"|module_active}}
        $("CLit-" + lit_id).select("td").each(function (elt) {
          elt.observe("dblclick", function () {
            var datetime = elt.get("date").split(" ");
            window.save_date = datetime[0];
            window.save_hour = datetime[1].split(":")[0];
            window.save_lit_guid = elt.up("tr").id;
            chooseIntervSejour();
          });
        });
        {{/if}}
      });
    }
    else {
      Droppables.reset();

      {{if "reservation"|module_active}}
      var tableau_vue_temporel = $("tableau_vue_temporel");
      if (tableau_vue_temporel) {
        tableau_vue_temporel.select(".mouvement_lit").invoke("stopObserving", "dblclick");
      }
      {{/if}}

      var target = $("view_affectations");
      Placement.scrollAffectations = target.scrollTop;

      return onSubmitFormAjax(getForm("filterMouv"), after_refresh, target);
    }
  };
</script>

<div style="display: none;" id="choose_interv_sejour">
  <table class="tbl">
    <tr>
      <th class="title" colspan="2">
        Création de :
      </th>
    <tr>
      <td>
        <button type="button" class="new" onclick="createIntervention()">{{tr}}COperation{{/tr}}</button>
      </td>
      <td>
        <button type="button" class="new" onclick="createSejour()">{{tr}}CSejour{{/tr}}</button>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button type="button" class="cancel" onclick="Control.Modal.close()">{{tr}}Close{{/tr}}</button>
      </td>
    </tr>
  </table>
</div>

<form name="filterMouv" method="get" onsubmit="return false;">
  <input type="hidden" name="m" value="hospi" />
  <input type="hidden" name="a" value="ajax_vw_mouvements" />
  
  <!-- Filtre -->
  <div id="temporel_filtre" style="width: 100%;" class="me-small-fields">
    Granularité :
    {{foreach from=$granularites item=_granularite}}
      <label>
        <input type="radio" name="granularite" value="{{$_granularite}}" onclick="refreshMouvements(loadNonPlaces);"
               {{if $_granularite == $granularite}}checked{{/if}} />
        {{tr}}CService-granularite-{{$_granularite}}{{/tr}}
      </label>
    {{/foreach}}
    
    &mdash;
    
    Date :
    <input type="hidden" class="dateTime notNull" name="date" value="{{$date}}"
           onchange="$V(this.form.date_da, new Date(Date.fromDATETIME($V(this))).toLocaleDateTime()); refreshMouvements(loadNonPlaces); $V(this.form.sens, '');">
    <input type="hidden" name="sens" value="" />
    <input type="hidden" name="date_min" value="{{$date_min}}" />
    &mdash;
    
    Vue :
    <label>
      <input type="radio" name="mode_vue_tempo" value="classique" onclick="refreshMouvements()"
             {{if $mode_vue_tempo == "classique"}}checked{{/if}} /> Classique
    </label>
    <label>
      <input type="radio" name="mode_vue_tempo" value="compacte" onclick="refreshMouvements()"
             {{if $mode_vue_tempo == "compacte"}}checked{{/if}} /> Compacte
    </label>

    {{if "dPhospi prestations systeme_prestations"|gconf == "expert"}}
      &mdash;

      Axe de prestation :
      <select name="prestation_id" onchange="savePrefAndReload(this.value);">
        <option value="">&mdash; {{tr}}None{{/tr}}</option>
        <option value="all" {{if $prestation_id == "all"}}selected{{/if}}>{{tr}}All{{/tr}}</option>
        {{foreach from=$prestations_journalieres item=_prestation}}
          <option value="{{$_prestation->_id}}"
                  {{if $_prestation->_id == $prestation_id}}selected{{/if}}>{{$_prestation->nom}}</option>
        {{/foreach}}
      </select>
    {{/if}}

    <button type="button" class="pause notext me-small" onclick="togglePlayPause(this);"
            title="{{tr}}CAffectation-play_pause_temporel{{/tr}}"></button>
  </div>

</form>

<div id="view_affectations" class="me-padding-0" style="overflow-x: auto; overflow-y: scroll;" onscroll="syncBars(this);"></div>
<div id="grippie_tempo" class="grippie-h" style="margin-top: 3px; margin-bottom: 3px; height: 6px"></div>
<div id="list_affectations"></div>
