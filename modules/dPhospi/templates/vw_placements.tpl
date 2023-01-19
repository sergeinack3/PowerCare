{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=hospi script=affectation}}
{{mb_script module=hospi script=affectation_uf}}
{{mb_script module=hospi script=info_group}}
{{mb_script module=hospi script=prestation}}

{{if "dPImeds"|module_active}}
  {{mb_script module="dPImeds" script="Imeds_results_watcher"}}
{{/if}}

<script>
  Affectation.from_tempo = true;
  Position.includeScrollOffsets = true;
  Placement = {
    tabs:               null,
    updater:            null,
    frequency:          null,
    scrollAffectations: 0,
    scrollNonPlaces:    0,
    loadTableau:        function (services_ids) {
      new Url('hospi', 'vw_affectations').requestUpdate('tableau');
    },
    loadTemporel:       function () {
      new Url('hospi', 'vw_mouvements').requestUpdate('temporel');
    },
    loadTopologique:    function () {
      new Url('hospi', 'vw_placement_patients').requestUpdate('topologique');
    },
    loadRegulation:     function () {
      new Url('hospi', 'regulationView').requestUpdate('regulation');
    },
    showLegend:         function () {
      Modal.open("legend_" + this.tabs.activeLink.key);
    },
    selectServices:     function (view, services_ids_suggest) {
      var url = new Url("hospi", "ajax_select_services");
      
      if (Object.isUndefined(view)) {
        view = this.tabs.activeLink.key;
      }
      
      if (!Object.isUndefined(services_ids_suggest)) {
        url.addParam("services_ids_suggest", services_ids_suggest);
      }
      
      url.addParam("view", view);
      url.requestModal(null, null);
    },
    loadActiveView:     function () {
      switch (this.tabs.activeLink.key) {
        case 'tableau':
          this.loadTableau();
          break;
        case 'temporel':
          this.loadTemporel();
          break;
        case 'topologique':
          this.loadTopologique();
          break;
        case 'regulation':
          this.loadRegulation();
      }
    },
    init:               function (frequency) {
      this.frequency = frequency || this.frequency;
      
      var url = new Url("hospi", "vw_mouvements");
      Placement.updater = url.periodicalUpdate('temporel', {
        frequency: this.frequency,
        onCreate:  function () {
          /* On vide la mémoire avant de rafraîchir */
          Droppables.reset();
          Draggables.drags.each(function (elt) {
            Draggables.unregister(elt);
          });
          {{if "reservation"|module_active}}
          var tableau_vue_temporel = $("tableau_vue_temporel");
          if (tableau_vue_temporel) {
            tableau_vue_temporel.select(".mouvement_lit").invoke("stopObserving", "dblclick");
          }
          {{/if}}
          
          var view_affectations = $("view_affectations");
          var list_affectations = $("list_affectations");
          if (!view_affectations || !list_affectations) {
            return;
          }
          Placement.scrollAffectations = view_affectations.scrollTop;
          Placement.scrollNonPlaces = list_affectations.scrollTop;
        }
      });
    },
    
    start: function (delay, frequency) {
      this.stop();
      this.init.delay(delay, frequency);
    },
    
    stop: function () {
      if (this.updater) {
        this.updater.stop();
      }
    },
    
    resume: function () {
      if (this.updater) {
        this.updater.resume();
      }
    }
  };
  
  filter = function (input, table) {
    var alerte = $("alerte_" + table);
    table = $(table);

    var term = $V(input);
    
    if (!term) {
      table.select("tr.line").invoke("show");
      alerte.hide();
      return;
    }

    table.select("tr.line").invoke("hide");
    alerte.show();
    
    table.select(".CPatient-view").each(function (e) {
      if (e.innerHTML.like(term)) {
        e.up("tr.line").show();
      }
    });
  };
  
  loadNonPlaces = function (after_refresh) {
    Draggables.drags.each(function (elt) {
      Draggables.unregister(elt);
    });
    after_refresh = Object.isFunction(after_refresh) ? after_refresh : Prototype.emptyFunction;
    var url = new Url("hospi", "ajax_vw_non_places");
    url.requestUpdate("list_affectations", {
      onComplete: after_refresh
    });
  };

  changeLit = function (affectation_id, link_affectation, datetime) {
    var url = new Url('hospi', 'ajax_suggest_lit');
    url.addParam('affectation_id', affectation_id);
    url.addParam("datetime", datetime);
    if (link_affectation) {
      url.addParam("_link_affectation", link_affectation);
    }
    
    url.requestModal(700, 400);
  };

  moveAffectation = function (affectation_id, lit_id, sejour_id, lit_id_origine, force) {
    new Url("hospi", "ajax_move_affectation")
      .addNotNullParam("affectation_id", affectation_id)
      .addParam("lit_id", lit_id)
      .addNotNullParam("sejour_id", sejour_id)
      .addNotNullParam("lit_id_origine", lit_id_origine)
      .addNotNullParam("force", force)
      .requestUpdate("systemMsg");
  };

  forceMoveAffectation = function (result, affectation_id, lit_id, sejour_id, lit_id_origine) {
    result = JSON.parse(result);

    if ((!result.patient_mineur || confirm($T("warning-patient_mineur_majeur"))) &&
      (!result.sexe_opposes || confirm($T("warning-sexe_opposes")))) {
      moveAffectation(affectation_id, lit_id, sejour_id, lit_id_origine, 1);
    }
    else {
      window.div_lit = null;

      if (window.lit_selected) {
        $("lit_move_" + window.lit_selected).checked = false;
        window.lit_selected = null;
      }

      if (window.sejour_selected) {
        $("sejour_move_" + window.sejour_selected).checked = false;
        window.sejour_selected = null;
      }

      if (window.affectation_selected) {
        $("aff_move_" + window.affectation_selected).checked = false;
        window.affectation_selected = null;
      }

      callbackMoveAffectation(affectation_id, lit_id, sejour_id, lit_id_origine, 0);
    }
  };

  callbackMoveAffectation = function (affectation_id, lit_id, sejour_id, lit_id_origine, del_line, open_modal_etab) {
    if (!window.refreshMouvements) {
      return;
    }

    var after_mouv = Prototype.emptyFunction;

    if (window.div_lit) {
      div_lit.remove();
    }

    if (Object.isUndefined(del_line)) {
      del_line = 1;
    }

    // Pas d'affectation_id ou pas de lit_id_origine (affectation dans un couloir),
    // on supprime l'affectation ciblée dans la liste des affectations (placement d'un patient)
    if (del_line && (!affectation_id || !lit_id_origine)) {
      after_mouv = delLine.curry(affectation_id ? affectation_id : sejour_id);
    }

    if (lit_id_origine) {
      refreshMouvements(after_mouv, lit_id_origine);
    }

    if (lit_id) {
      refreshMouvements(after_mouv, lit_id);
    }

    if (window.lit_selected && window.affectation_selected) {
      var affectation = $("affectation_temporel_" + window.affectation_selected);
      var list_affectation = affectation.up('div');
      if (list_affectation.id == "view_affectations") {
        var ids = affectation.get("affectations_enfant");
        if (ids && ids.length > 0) {
          ids.split("-").each(function (id) {
            $("affectation_temporel_" + id).remove();
          });
        }
        affectation.remove();
      }
      window.affectation_selected = null;
      window.lit_selected = null;
    }

    if (window.sejour_selected && window.lit_selected) {
      window.sejour_selected = null;
      window.lit_selected = null;
    }

    if (open_modal_etab) {
      openModalEtab(affectation_id);
    }
  };

  delLine = function (object_id) {
    var line = $("wrapper_line_" + object_id);
    if (line) {
      var div = line.down("div");
      var ids = div.get("affectations_enfant");
      if (ids && ids.length > 0) {
        ids.split("-").each(function (id) {
          $("wrapper_line_" + id).up("tr.line").remove();
        });
      }
      line.up("tr.line").remove();
    }
  };

  // Choix d'action lors du déplacement d'une affectation
  selectAction = function (affectation_id, lit_id) {
    Placement.stop();
    new Url("hospi", "ajax_select_action_affectation")
      .addParam("affectation_id", affectation_id)
      .addParam("lit_id", lit_id)
      .requestModal(500, null, {showReload: false, onClose: Placement.resume});
  };

  chooseSejour = function (sejour_id) {
    window.sejour_selected = sejour_id;
    moveByRadio();
  };
  
  chooseLit = function (lit_id) {
    window.lit_selected = lit_id;
    moveByRadio();
  };
  
  chooseAffectation = function (affectation_id) {
    window.affectation_selected = affectation_id;
    moveByRadio();
  };
  
  // Placement par les boutons radio
  moveByRadio = function () {
    var open_choix_action = parseInt({{"dPhospi vue_temporelle open_fenetre_action"|gconf|@json}});

    if (window.lit_selected && (window.sejour_selected || window.affectation_selected)) {
      var affectation_id = window.affectation_selected;
      var lit_id = window.lit_selected;
      var sejour_id = window.sejour_selected;
      var affectation = $("affectation_temporel_" + affectation_id);
      var curr_lit_id = null;

      if (affectation) {
        curr_lit_id = affectation.get("lit_id");
      }

      if (open_choix_action && curr_lit_id) {
        selectAction(affectation_id, lit_id);
      }
      else {
        moveAffectation(affectation_id, lit_id, sejour_id);
      }
    }
  };
  
  syncBars = function (pre) {
    other = pre.id == 'view_affectations' ? $("list_affectations") : $("view_affectations");
    other.scrollLeft = pre.scrollLeft;
  };
  
  makeDragVisible = function (container, element) {
    if (!container || !element) {
      return false;
    }

    var i = $(container).getStyle('width');
    i = i.replace('px', '');
    i = Math.round(i - 20) + 'px';
    element.setStyle({
      'width':    i,
      'z-index':  2000,
      'position': 'absolute',
      'cursor':   'move'
    });
  };
  
  togglePlayPause = function (button) {
    button.toggleClassName("play");
    button.toggleClassName("pause");
    if (button.hasClassName("play")) {
      Placement.stop();
    }
    else {
      Placement.resume();
    }
  };
  
  savePrefAndReload = function (prestation_id) {
    var oForm = getForm("editPrefPresta");
    $V(oForm.elements["pref[prestation_id_hospi]"], prestation_id);
    return onSubmitFormAjax(oForm, {
      onComplete: function () {
        refreshMouvements(loadNonPlaces);
      }
    });
  };
  
  createAffectation = function (sejour_id, lit_id) {
    var url = new Url("planningOp", "ajax_create_affectation");
    url.addParam("sejour_id", sejour_id);
    url.addParam("lit_id", lit_id);
    url.requestUpdate("systemMsg", function () {
      refreshMouvements(null, lit_id);
    });
  };
  
  createIntervention = function () {
    Placement.stop();
    Control.Modal.close();
    var url = new Url("planningOp", "vw_edit_urgence");
    url.addParam("date_urgence", window.save_date);
    url.addParam("hour_urgence", window.save_hour);
    url.addParam("min_urgence", "00");
    url.addParam("dialog", 1);
    url.addParam("operation_id", 0);
    url.modal({
      width:   "95%",
      height:  "95%",
      onClose: function () {
        Placement.resume();
        if (window.sejour_id_for_affectation) {
          createAffectation(window.sejour_id_for_affectation, window.save_lit_guid.split("-")[1]);
        }
      }
    });
  };
  
  createSejour = function () {
    Placement.stop();
    Control.Modal.close();
    var url = new Url("planningOp", "vw_edit_sejour");
    url.addParam("date_reservation", window.save_date);
    url.addParam("sejour_id", 0);
    url.addParam("dialog", 1);
    url.modal({
      width:   "95%",
      height:  "95%",
      onClose: function () {
        Placement.resume();
        if (window.sejour_id_for_affectation) {
          createAffectation(window.sejour_id_for_affectation, window.save_lit_guid.split("-")[1]);
        }
      }
    });
  };
  
  chooseIntervSejour = function () {
    window.sejour_id_for_affectation = null;
    Modal.open("choose_interv_sejour");
  };

  changeAffService = function (object_id, object_class, sejour_id, lit_id) {
    var form = getForm("changeServiceForm");
    switch (object_class) {
      case "CSejour":
        $V(form.m, "planningOp");
        $V(form.dosql, "do_sejour_aed");
        $V(form.affectation_id, "");
        $V(form.sejour_id, object_id);
        break;
      case "CAffectation":
        $V(form.m, "hospi");
        $V(form.dosql, "do_affectation_aed");
        $V(form.affectation_id, object_id);
        $V(form.sejour_id, sejour_id);
    }
    var url = new Url("hospi", "ajax_select_service");
    url.addParam("action", "changeService");
    url.addParam("lit_id", lit_id);
    url.requestModal(null, null, {maxHeight: "90%"});
  };

  displayOccupationRate = function () {
    new Url("hospi", "ajax_occupation_rate").requestModal();
  };

  openModalEtab = function (affectation_id) {
    new Url('hospi', 'ajax_select_etab_externe')
      .addParam('affectation_id', affectation_id)
      .requestModal('700px', '400px', {showClose: false, showReload: false});
  };

  Main.add(function () {
    Placement.tabs = Control.Tabs.create('placements_tabs', true);
    if (Placement.tabs.activeLink.key == "temporel") {
      Placement.start(0, 120);
    }
    else {
      Placement.loadActiveView();
    }
  });
</script>

<!-- Formulaire de sauvegarde de l'axe de prestation en préférence utilisateur (vue temporelle)-->
<form name="editPrefPresta" method="post">
  <input type="hidden" name="m" value="admin" />
  <input type="hidden" name="dosql" value="do_preference_aed" />
  <input type="hidden" name="user_id" value="{{$app->user_id}}" />
  <input type="hidden" name="pref[prestation_id_hospi]" />
</form>

<!-- Formulaire de changement de service d'une affectation dans un couloir (pas de lit_id) -->
<form name="changeServiceForm" method="post">
  <input type="hidden" name="m" />
  <input type="hidden" name="dosql" />
  <input type="hidden" name="affectation_id" />
  <input type="hidden" name="sejour_id" />
  <input type="hidden" name="service_id" />
  <input type="hidden" name="lit_id" />
</form>

<!-- Légendes -->
<div id="legend_temporel" style="display: none;">
  {{mb_include module=hospi template=inc_legend_mouvement}}
</div>

<div id="legend_tableau" style="display: none;">
  {{mb_include module=hospi template=legende}}
</div>

{{if "dPhospi vue_topologique use_vue_topologique"|gconf}}
  <div id="legend_topologique" style="display: none;">
    {{mb_include module=hospi template=legende_topologique}}
  </div>
{{/if}}

<div id="legend_regulation" style="display: none;">
  {{mb_include module=hospi template=legende_regulation}}
</div>

<ul class="control_tabs me-small me-margin-top--4" id="placements_tabs">
  <li onmousedown="Placement.loadTableau();">
    <a href="#tableau">{{tr}}mod-dPhospi-tab-vw_placements-tableau{{/tr}}</a>
  </li>
  <li onmousedown="Placement.start(0, 120);">
    <a href="#temporel">{{tr}}mod-dPhospi-tab-vw_placements-temporel{{/tr}}</a>
  </li>
  {{if "dPhospi vue_topologique use_vue_topologique"|gconf}}
    <li onmousedown="Placement.loadTopologique();">
      <a href="#topologique">{{tr}}mod-dPhospi-tab-vw_placements-topologique{{/tr}}</a>
    </li>
  {{/if}}
  <li onmousedown="Placement.loadRegulation();">
    <a href="#regulation">{{tr}}mod-dPhospi-tab-vw_placements-regulation{{/tr}}</a>
  </li>
  <li>
    <button type="button" onclick="Placement.selectServices();" class="search">Services</button>
  </li>
  <li>
    <button type="button" onclick="displayOccupationRate();" class="stats">{{tr}}dPhospi-action-display-occupation_rate{{/tr}}</button>
  </li>
  <li style="float: right">
    {{if $app->user_prefs.show_group_information}}
      <button type="button" onclick="InfoGroup.openInfoGroup();"
              class="search">{{tr}}CInfoGroup-action-Etablishment information{{/tr}}</button>
    {{/if}}
    <button type="button" onclick="Placement.showLegend();" class="search">Légende</button>
  </li>
</ul>

<div id="tableau" style="display: none;" class="me-padding-left-4 me-padding-right-4"></div>
<div id="temporel" style="display: none;"></div>

{{if "dPhospi vue_topologique use_vue_topologique"|gconf}}
  <div id="topologique" style="display: none;"></div>
{{/if}}

<div id="regulation" style="display: none;" class="me-padding-top-4 me-padding-bottom-8"></div>
