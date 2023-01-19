/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

DHEMultiple = {
  actual_rank: 1,
  max_ranks: 4,

  selRank: function(rank) {
    this.actual_rank = rank;

    $$(".tools_DHE").invoke("removeClassName", "selected");

    $("tools_DHE_" + rank).addClassName("selected");
  },

  removeSlot: function(rank) {
    $("prog_plageop_" + rank).update();
    $V(getForm("plageSelectorFrm"+rank).admission, "aucune");
  },

  resetSlots: function() {
    for (var i = 2 ; i <= this.max_ranks ; i++) {
      this.removeSlot(i);
    }
  },

  addSlot: function() {
    this.max_ranks ++;
    var list_plages = $("list_plages"),
        table       = list_plages.up("table"),
        trs         = table.select("tr.line_multiple"),
        last_tr     = trs[trs.length - 1],
        parent_tr   = last_tr.up();

    // Lignes de 4 séjours
    if (last_tr.select("tr.tools_DHE").length == 4) {
      list_plages.writeAttribute("rowspan", trs.length + 1);

      last_tr = DOM.tr({class: "line_multiple"});
      parent_tr.insert(last_tr);
    }

    var td = DOM.td();

    last_tr.insert(td);

    new Url("planningOp", "ajax_add_slot")
      .addParam("rank", this.max_ranks)
      .requestUpdate(td);
  },

  validate: function() {
    window.parent.statusDHE = [];

    var area_dhe_multiple = window.parent.$$(".area_dhe_multiple");

    area_dhe_multiple.invoke("update");

    for (var rank = 2 ; rank <= this.max_ranks ; rank++) {
      var form = getForm("plageSelectorFrm" + rank);

      if (!form._place_after_interv_id) {
        continue;
      }

      var structure = {
        "rank": rank,
        "form": form
      };

      // Pour l'affichage des différentes interventions et séjours dans la DHE
      var table = $("prog_plageop_" + rank).down("table");
      var plage_date = table.get("plage_date");
      var plage_view = table.get("plage_view");
      var plage_debut = table.get("plage_debut");

      var admission_view = "Ne pas modifier";

      if ($V(structure.form.admission) == "jour") {
        var hour_jour = $V(structure.form.hour_jour);
        var min_jour = $V(structure.form.min_jour);

        if (hour_jour.length == 1) {
          hour_jour = "0" + hour_jour;
        }
        if (min_jour.length == 1) {
          min_jour = "0" + min_jour;
        }

        admission_view = "Le jour même à " + hour_jour + "h" + min_jour;
      }
      else {
        var hour_veille = $V(structure.form.hour_veille);
        var min_veille = $V(structure.form.min_veille);

        if (hour_veille.length == 1) {
          hour_veille = "0" + hour_veille;
        }
        if (min_veille.length == 1) {
          min_veille = "0" + min_veille;
        }

        admission_view = "La veille à " + hour_veille + "h" + min_veille;
      }

      var placement_view = "à " + plage_debut;
      var _place_after_interv_id = $V(structure.form._place_after_interv_id);
      if (_place_after_interv_id == "0") {
        placement_view = "Sans préférence pour le placement";
      }
      else if (_place_after_interv_id > 0) {
        placement_view = "à " + structure.form.select("input[name=_place_after_interv_id]:checked")[0].get("heure");
      }

      var dhe_view = DOM.div(
        {class: "rank_" + rank},
        DOM.button({class: "remove notext", type: "button", title: $T("Delete"), onclick: "deleteLineDHEMultiple(" + rank + ");"}),
        DOM.span(null, "Le " + plage_date + " " + plage_view),
        DOM.div({class: "compact"}, "Placement : " + placement_view),
        DOM.div({class: "compact"}, "Admission: " + admission_view)
        );

      area_dhe_multiple[0].insert(dhe_view);

      window.parent.statusDHE.push(structure);
    }

    area_dhe_multiple[1].innerHTML = area_dhe_multiple[0].innerHTML;

    setClose('', '', '');
  }
};