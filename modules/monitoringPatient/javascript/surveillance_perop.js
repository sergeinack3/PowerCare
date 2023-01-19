/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Contient les méthodes de saisie des données au bloc, et d'affichage haut niveau des différents éléments d'interface.
 */
SurveillancePerop = {
  previousPoint: null,
  lastDay:       null,
  div_element:   {},
  mode_grille_perop: 'rapide',
  modal_perop: null,
  modal_adm_perop: null,
  updating_qte_pousse_seringue: false,
  forms_pousse_seringue: [],
  show_timings_perop: null,

  /**
   * Open a modal for entering an administration
   *
   * @param operation_id              operation ID
   * @param container                 Parent element of the timeline
   * @param line_guid                 Line GUID
   * @param planification_systeme_id  Planification system ID
   * @param type                      Type graph
   * @param datetime                  Datetime
   * @param administration_guid       Administration guid
   */
  editPeropAdministration: function (operation_id, container, line_guid, planification_systeme_id, type, datetime, administration_guid) {
    var element_main = $$('div[data-graphguid=supervision-timeline]')[0];
    let prescription_id = $V(getForm('applyProtocoleSurvPerop').prescription_id);

    SurveillancePerop.modal_adm_perop = new Url('salleOp', 'ajax_vw_surveillance_perop_administration')
      .addParam('operation_id', operation_id)
      .addParam('line_guid', line_guid)
      .addParam('planif_id_selected', planification_systeme_id)
      .addParam('type', type)
      .addParam('datetime', datetime)
      .addParam('administration_guid', administration_guid)
      .requestModal("95%", "90%", {onClose: (container && prescription_id) ? (function (container) {
            SurveillancePerop.modal_adm_perop = null;
            container.retrieve("timeline").updateChildrenSelected(null, element_main);
          }).curry(container) : window.reloadSurveillance[type]});
  },

  /**
   * Formateur de ticks de l'axe X des graphiques, pour ne pas repéter la date
   *
   * @param val
   * @param axis
   *
   * @returns {String}
   */
  xTickFormatter: function (val, axis) {
    var date = new Date(val);
    var day = date.getDate();
    var formatted;

    if (val < axis.min || val > axis.max) {
      return;
    }

    if (!SurveillancePerop.lastDay || SurveillancePerop.lastDay != day) {
      formatted = printf(
        "%02d:%02d<br /> <small>%02d/%02d</small>",
        date.getHours(),
        date.getMinutes(),
        date.getDate(),
        date.getMonth() + 1
      );
    }
    else {
      formatted = printf(
        "%02d:%02d",
        date.getHours(),
        date.getMinutes()
      );
    }

    SurveillancePerop.lastDay = day;

    return formatted;
  },

  /**
   * Fonction appelée au survol des points sur les graphiques, pour afficher une tooltip
   *
   * @param {Event}  event Evènement jQuery
   * @param {Object} pos   Position
   * @param {Object} item  Item du graphique
   */
  plothover: function (event, pos, item) {
    if (item) {
      var key = item.dataIndex + "-" + item.seriesIndex;

      if (SurveillancePerop.previousPoint != key) {
        SurveillancePerop.previousPoint = key;
        jQuery("#flot-tooltip").remove();

        var yaxis = item.series.yaxis.n;
        event.target.select(".flot-y" + yaxis + "-axis, .flot-y" + yaxis + "-axis .flot-tick-label").invoke("addClassName", "axis-onhover");

        var contents = SupervisionGraph.formatTrack(item);

        $$("body")[0].insert(DOM.div({className: "tooltip", id: "flot-tooltip"}, contents).setStyle({
          position: 'absolute',
          top:      item.pageY + 5 + "px",
          left:     item.pageX + 5 + "px"
        }));
      }
    }
    else {
      $$(".axis-onhover").invoke("removeClassName", "axis-onhover");

      jQuery("#flot-tooltip").remove();
      SurveillancePerop.previousPoint = null;
    }
  },

  /**
   * Affiche une modale de modification d'une perfusion
   *
   * @param form
   * @param prescription_line_mix_id
   * @param container
   */
  viewTimingPerf: function (form, prescription_line_mix_id, container) {
    container = !container ? $$('.surveillance-timeline-container')[0] : container;
    var element_main = $$('div[data-graphguid=supervision-timeline]')[0];

    var url = new Url;
    url.setModuleAction("planSoins", "edit_perf_dossier_soin");
    url.addParam("prescription_line_mix_id", form ? $V(form.prescription_line_mix_id) : prescription_line_mix_id);
    url.addParam("mode_refresh", "timing");
    url.requestModal(600, 600, {
      onClose: (function (container) {
        container.retrieve("timeline").updateChildrenSelected(null, element_main);
      }).curry(container)
    });
  },

  /**
   * Affiche une module de saisie de pose de perfusion
   *
   * @param form
   */
  submitPosePerf: function (form) {
    var container = form.up('.surveillance-timeline-container');

    // Perfusion déà posée : création d'une nouvelle variation
    if ($V(form.date_pose)) {
      var form_variation = getForm('modifDebit-' + $V(form.prescription_line_mix_id));
      return form_variation.onsubmit();
    }

    $V(form.date_pose, 'current');
    $V(form.time_pose, 'current');

    onSubmitFormAjax(form, {
      onComplete: (function (container) {
        container.retrieve("timeline").updateChildren();
      }).curry(container)
    });
  },

  /**
   * Effectue le retrait d'une perfusion
   *
   * @param form
   */
  submitRetraitPerf: function (form) {
    if (!confirm('Etes-vous sur de vouloir retirer définitivement la perfusion ?')) {
      return;
    }

    var container = form.up('.surveillance-timeline-container');

    $V(form.date_retrait, 'current');
    $V(form.time_retrait, 'current');

    onSubmitFormAjax(form, {
      onComplete: (function (container) {
        container.retrieve("timeline").updateChildren();
      }).curry(container)
    });
  },

  /**
   * A partir d'un éveènement de timeline, permet de récupérer la cible (un item de timeline)
   * A cause du bubbling, cet item est soit un élement parent, soit l'élement lui-même
   *
   * @param event
   */
  getTargetItem: function (event) {
    var element = Event.element(event);
    var item = element["timeline-item"];

    if (!item) {
      element = element.up(".vis-item");
      if (!element) {
        return;
      }

      item = element["timeline-item"];
      if (!item) {
        return;
      }
    }

    return item.data;
  },

  /**
   * Récupère le groupe VisJS (la partie gauche des timeline) correspondant à un évènement
   *
   * @param event
   * @returns {{chapitre: string, object_class: string, guid: string}, null}
   */
  getTargetGroup: function (event) {
    var element = Event.element(event);
    var group = element["timeline-group"];

    if (!group) {
      element = element.up(".vis-group");
      if (!element) {
        return;
      }

      group = element["timeline-group"];
      if (!group) {
        return;
      }
    }

    var parts = SurveillancePerop.decomposeGroupName(group.groupId);

    if (!parts) {
      return null;
    }

    parts.element = element;

    return parts;
  },

  /**
   * A partir de la clé d'un groupe VisJS, récupère l'object class, id et guid de l'object métier
   *
   * @param groupName
   * @returns {{chapitre: string, object_class: string, guid: string}, null}
   */
  decomposeGroupName: function (groupName) {
    var parts = /^CPrescription\._chapitres\.(\w+)-(.+)-(\d+)$/.exec(groupName);

    if (parts) {
      return {
        chapitre:     parts[1],
        object_class: parts[2],
        guid:         parts[2] + "-" + parts[3]
      };
    }

    return null;
  },

  /**
   * Récupère le moment sur lequel la souris est positionnée, sur la timeline VisJS
   *
   * @param {Timeline} timeline La timeline
   * @param {Event}    event    Evènement
   * @param {Element}  element  L'élement de la timeline
   * @returns {{date: Date, x: number, width: *}}
   */
  getDate: function (timeline, event, element) {
    var pos = element.cumulativeOffset();
    var width = element.getDimensions().width;
    var x = (event.clientX - pos.left);

    var start = timeline.start;
    var end = timeline.end;
    var time = start + (end - start) * x / width;

    return {
      date:  new Date(time),
      x:     x,
      width: width
    };
  },

  /**
   * Affiche une module de modificaton d'un évènement per-op (CAnesthPerop)
   *
   * @param guid
   * @param operation_id
   * @param datetime
   * @param container
   * @param element_main
   * @returns {boolean}
   */
  editEvenementPerop: function (guid, operation_id, datetime, container, element_main, type) {
    container = !container ? $$('.surveillance-timeline-container')[0] : container;
    element_main = !element_main ? $$('div[data-graphguid=supervision-timeline-geste]')[0] : element_main;

    var url = new Url("dPsalleOp", "ajax_edit_evenement_perop");
    url.addParam("evenement_guid", guid);
    url.addParam("operation_id", operation_id);
    url.addParam("datetime", datetime);
    url.addParam("type", type);
    url.requestModal(800, 500, {
      onClose: container ? (function (container) {
        container.retrieve("timeline").updateChildrenSelected(null, element_main);
      }).curry(container) : window.reloadSurveillance.perop
    });

    return false;
  },

  /**
   * Affiche la modale de saisie rapide des gestes per-op (CGestePerop)
   *
   * @param operation_id
   * @param datetime
   * @param container
   */
  quickGestesPerop: function (operation_id, datetime, container, element_main) {
    var url = new Url("dPsalleOp", "ajax_quick_gestes_perop");
    url.addParam("operation_id", operation_id);
    url.addParam("datetime", datetime);
    url.requestModal(800, 500, {
      onClose: (function (container) {
        container.retrieve("timeline").updateChildrenSelected(null, element_main);
      }).curry(container)
    });

    return false;
  },

  /**
   * Affiche la modale de saisie rapide d'évènement per-op (CAnesthPerop), basée sur les aides à la saisie
   *
   * @param operation_id
   * @param container
   */
  quickEvenementPerop: function (operation_id, container, type) {
    var element_main = $$('div[data-graphguid=supervision-timeline-geste]')[0];

    var url = new Url("dPsalleOp", "ajax_quick_evenement_perop");
    url.addParam("operation_id", operation_id);
    url.addParam("type", type);
    url.requestModal(700, 400, {
      onClose: (function (container) {
        container.retrieve("timeline").updateChildrenSelected(null, element_main);
      }).curry(container)
    });

    return false;
  },

  /**
   * Affiche la modale de saisie rapide d'évènement per-op (CAnesthPerop), basée sur les aides à la saisie
   *
   * @param operation_id
   * @param element
   * @param category_id
   * @param geste_perop_id
   * @param date
   */
  saveEvenementPerop: function (operation_id, element, category_id, geste_perop_id, date) {
    var div_element = this.div_element;
    var container = div_element.up('.surveillance-timeline-container');
    var element_main = div_element.up("div.timeline-item");

    var url = new Url("dPsalleOp", "do_geste_perop_aed", "dosql");
    url.addParam("operation_id", operation_id);
    url.addParam("category_id", category_id);
    url.addParam("geste_perop_id", geste_perop_id);
    url.addParam("datetime", date);
    url.requestUpdate("systemMsg", {
      onComplete:
        function () {
          container.retrieve("timeline").updateChildrenSelected(null, element_main);
        }, method: "post"
    });

    return false;
  },

  /**
   * Affiche le graphique de surveillance prête à imprimer, avec des infos de la feuille de bloc
   *
   * @param operation_id
   */
  printSurveillance: function (operation_id) {
    var url = new Url("dPsalleOp", "vw_partogramme");
    url.addParam("operation_id", operation_id);
    url.pop(750, 700, "Impression surveillance");
  },

  /**
   * Affiche la modale de saisie des administrations per-op (affichage en grille)
   *
   * @param operation_id
   * @param mode
   * @param datetime
   * @param code_cis
   * @param line_guid
   * @param quantite
   * @param container
   * @param element_main
   */
  modeGrillePerop: function (operation_id, mode, datetime, code_cis, line_guid, quantite, container, element_main) {
    container = !container ? $$('.surveillance-timeline-container')[0] : container;
    element_main = !element_main ? $$('div[data-graphguid=supervision-timeline]')[0] : element_main;
    let form_protocole = getForm('applyProtocoleSurvPerop');
    let prescription_id = form_protocole ? $V(form_protocole.prescription_id) : null;

    new Url("prescription", "vw_mode_grille_perop")
      .addParam("operation_id", operation_id)
      .addParam("quantite", quantite)
      .addNotNullParam("mode", mode)
      .addNotNullParam("datetime", datetime)
      .addNotNullParam("code_cis", code_cis)
      .addNotNullParam("line_guid", line_guid)
      .addNotNullParam("type", window.SurveillanceTimeline ? SurveillanceTimeline.current.type : null)
      .requestModal("100%", "100%", {
        onClose: (element_main && prescription_id) ? (function (container) {
          container.retrieve("timeline").updateChildrenSelected(null, element_main);
        }).curry(container) : window.reloadSurveillance[container.get('type')]
      });
  },

  /**
   * Affiche le volet demandé dans le mode grille perop
   *
   * @param operation_id
   * @param mode
   * @param datetime
   * @param code_cis
   * @param quantite
   * @param atc_level
   * @param letter
   * @param page
   */
  tabModeGrillePerop: function (operation_id, mode, datetime, code_cis, quantite, atc_level, letter, page, show_all_products) {
    new Url("prescription", "modeGrillePerop")
      .addParam("operation_id", operation_id)
      .addParam("quantite", quantite)
      .addParam("mode", mode)
      .addParam("atc_level", atc_level)
      .addParam("letter", letter)
      .addParam("page", page)
      .addParam("show_all_products", show_all_products)
      .addNotNullParam("datetime", datetime)
      .addNotNullParam("code_cis", code_cis)
      .addNotNullParam("type", window.SurveillanceTimeline ? SurveillanceTimeline.current.type : null)
      .requestUpdate(mode);
  },

  /**
   * Show the Elements tab
   *
   * @param operation_id
   */
  tabModeGrillePeropElements: function (operation_id) {
    new Url("prescription", "ajax_mode_grille_perop_elements")
      .addParam("operation_id", operation_id)
      .addNotNullParam("type", window.SurveillanceTimeline ? SurveillanceTimeline.current.type : null)
      .requestUpdate('prescription_elements');
  },

  /**
   * Effectue l'enregistrement d'une administration per-op,
   * notamment utilisée quand on glisse les prises prévues de la colonne de gauche vers la timeline
   *
   * @param datetime
   * @param orig_dateTime
   * @param quantite
   * @param unite_prise
   * @param line_guid
   * @param type
   * @param replanif
   */
  storeAdministrationPerop: function (datetime, orig_dateTime, quantite, unite_prise, line_guid, type, replanif) {
    var parts = line_guid.split(/-/);
    var url = new Url("dPprescription", "do_replanif_administration_perop_aed", "dosql");
    url.addParam("dateTime", datetime);
    url.addParam("orig_dateTime", orig_dateTime);
    url.addParam("quantite", quantite);
    url.addParam("object_class", parts[0]);
    url.addParam("object_id", parts[1]);
    url.addParam("type", type);
    url.addParam("replanif", (Object.isUndefined(replanif) || replanif) ? 1 : 0);
    url.requestUpdate(SystemMessage.id, {method: "post"});
  },

  /**
   * Effectue l'enregistrement d'une administration per-op,
   * notamment utilisée quand on glisse les prises prévues de la colonne de gauche vers la timeline
   *
   * @param datetime
   * @param quantite
   * @param prise_id
   * @param line_guid
   * @param type
   */
  storeAdministration: function (datetime, quantite, prise_id, user_id, line_guid, type) {
    if ($('admin_'+ line_guid)) {
      $('admin_'+ line_guid).disabled = true;
    }

    var parts = line_guid.split(/-/);
    var url = new Url('planSoins', 'do_administration_aed', "dosql");
    url.addParam('object_id', parts[1]);
    url.addParam('object_class', parts[0]);
    url.addParam('prise_id', prise_id);
    url.addParam('administrateur_id', user_id);
    url.addParam('dateTime', datetime);
    url.addParam('quantite', quantite);
    url.requestUpdate(SystemMessage.id, {method: "post", onComplete: (function () {
        SurveillancePerop.refreshContainer(null, type);
      })});
  },

  /**
   * Fonction callback du formulaire de la création d'une prescription si elle n'existe pas déjà
   *
   * @param prescription_id
   */
  updateFormLineSurvPerop: function (prescription_id) {
    var oFormSignaturePerop = getForm("signaturePrescription");
    if (oFormSignaturePerop) {
      $V(oFormSignaturePerop.prescription_id, prescription_id);
    }
    var oFormProt = getForm("applyProtocoleSurvPerop");
    $V(oFormProt.prescription_id, prescription_id, !!$V(oFormProt.pack_protocole_id));
  },

  /**
   * Affiche la modale d'application de protocole per-op
   *
   * @param container
   */
  submitProtocoleSurvPerop: function (container) {
    SurveillancePerop.modal_perop = Modal.open('modal_apply_protocole_surv_perop', {width: 1200, height: 800, title: "Application d'un protocole"});
    SurveillancePerop.modal_perop.observe("afterClose", function () {
      window.reloadSurveillance[container.down('.supervision').get('type')]();
    });

    var url = new Url("prescription", "applyProtocole");
    url.addFormData(getForm("applyProtocoleSurvPerop"));
    url.requestUpdate("modal_apply_protocole_surv_perop");
  },

  /**
   * Affiche une modale de saisie d'une observation de surveillance (CObservationResult + CObservationResultSet)
   *
   * @param object_guid
   * @param pack_id CSupervisionGraphPack
   * @param container
   * @param datetime
   * @param element_main
   * @param type
   */
  createObservationResultSet: function (datetime, object_guid, pack_id, container, element_main, type) {
    container = !container ? $$('.timeline-container-' + type)[0] : container;
    let parts = object_guid.split("-");
    let object_id    = parts[1];
    let callback = (type == 'partogramme') ? SurveillancePerop.showPartogramme.curry(object_id, 0, 0) : window.reloadSurveillance[type];

    type = (type == 'partogramme') ? 'perop' : type;

    var url = new Url("monitoringPatient", "ajax_edit_observation_result_set");
    url.addParam("object_guid", object_guid);
    url.addParam("pack_id", pack_id);
    url.addParam("type", type);

    if (datetime) {
      url.addParam("datetime", datetime.toDATETIME(true));
    }

    url.requestModal(600, 600, {
      onClose: element_main ? callback : (function (container) {
        container.retrieve("timeline").updateChildrenSelected(null, element_main);
      }).curry(container)
    });
  },

  /**
   * Affiche une modale de modification d'une observation de surveillance (CObservationResultSet)
   *
   * @param result_set_id CObservationResultSet
   * @param pack_id CSupervisionGraphPack
   * @param result_id CObservationResultSet
   * @param container
   * @param element_main
   * @param type
   * @param operation_id
   */
  editObservationResultSet: function (result_set_id, pack_id, result_id, container, element_main, type, operation_id) {
    container = !container ? $$('.timeline-container-' + type)[0] : container;

    let callback = (type == 'partogramme') ? SurveillancePerop.showPartogramme.curry(operation_id, 0, 0) : window.reloadSurveillance[(type == 'partogramme') ? 'perop' : type];

    var url = new Url("monitoringPatient", "ajax_edit_observation_result_set");
    url.addParam("result_set_id", result_set_id);
    url.addParam("pack_id", pack_id);
    url.addParam("result_id", result_id);
    url.requestModal(600, 600, {
      onClose: element_main ? callback : (function (container) {
        container.retrieve("timeline").updateChildrenSelected(null, element_main);
      }).curry(container)
    });
  },

  /**
   * Affiche les données d'un CSupervisionTable en vue tableau dans une modale
   *
   * @param operation_id
   * @param type
   * @param table_id
   */
  displaySurveillanceTable: function (operation_id, type, table_id) {
    var url = new Url('salleOp', 'ajax_surveillance_table_perop');
    url.addParam('operation_id', operation_id);
    url.addParam('type', type);
    url.addParam('table_id', table_id);
    url.requestModal();
  },

  /**
   * Affiche un menu contextuel au double clic gauche sur les gestes Perop
   *
   * @param operation_id
   * @param datetime
   * @param close_modal
   * @param see_all_gestes
   * @param type
   */
  getGestePeropContextMenu: function (operation_id, datetime, close_modal, see_all_gestes, type) {
    if (close_modal) {
      Control.Modal.close();
    }

    new Url("dPsalleOp", "ajax_vw_menu_structure_gestes_perop")
      .addParam("operation_id", operation_id)
      .addParam("datetime", datetime)
      .addParam("see_all_gestes", see_all_gestes)
      .addParam("type", type)
      .requestModal("100%", "100%", {
        onClose: window.reloadSurveillance[type]
      });
  },

  /**
   * Edit an administration
   *
   * @param line_id
   * @param datetime
   * @param line_class
   * @param quantite
   * @param unit
   * @param container
   * @param element_main
   */
  addAdministration: function (line_id, datetime, line_class, quantite, unit, container, element_main) {
    new Url("planSoins", "addAdministration")
      .addParam("line_id", line_id)
      .addParam("object_class", line_class)
      .addParam("key_tab", unit)
      .addParam("dateTime", datetime)
      .addParam("mode_dossier", "administration")
      .addParam("quantite", quantite)
      .requestModal("100%", "100%", {
        onClose: container ? (function (container) {
          container.retrieve("timeline").updateChildrenSelected(null, element_main);
        }).curry(container) : window.reloadSurveillance.perop
      });
  },

  /**
   * Afficher les protocoles perop comme le mode grille
   *
   * @param operation_id
   * @param prescription_id
   * @param container
   * @param type
   */
  showProtocolesPerop: function (operation_id, prescription_id, container, type) {
    var praticien_id = $V(getForm('applyProtocoleSurvPerop').praticien_id);
    container = !container ? $$('.timeline-container-' + type)[0] : container;
    var element_main = $$('div[data-graphguid=supervision-timeline]')[0];

    new Url("prescription", "ajax_vw_protocoles_perop")
      .addParam("praticien_id", praticien_id)
      .addParam("operation_id", operation_id)
      .addParam("prescription_id", prescription_id)
      .addParam("container", container)
      .requestModal("100%", "90%", {
        onClose: window.reloadSurveillance[type]
      });
  },

  /**
   * Show Perop gesture protocols
   *
   * @param container
   * @param operation_id
   * @param type
   */
  showProtocolesGestesPerop: function (container, operation_id, type) {
    var url = new Url("dPsalleOp", "ajax_select_protocoles_gestes_perop");
    url.addParam("container", container);
    url.addParam("operation_id", operation_id);
    url.addParam("type", type);
    url.requestModal("40%", "60%", {
      onClose: window.reloadSurveillance[type]
    });
  },
  /**
   * Open the modal with perop gesture protocol items
   *
   * @param container
   * @param operation_id
   */
  openProtocoleItems: function (protocole_geste_perop_id, operation_id, type) {
    var url = new Url("dPsalleOp", "ajax_list_protocole_geste_perop_items");
    url.addParam("protocole_geste_perop_id", protocole_geste_perop_id);
    url.addParam("operation_id", operation_id);
    url.addParam("type", type);
    url.requestModal("60%", "100%", {onClose: Control.Modal.close});
  },

  /**
   * Show date and hour to an element
   *
   * @param event
   * @param element
   * @param date
   * @param timeline
   */
  getDateTimeElement: function (event, element, date, timeline) {
    let div_elt = SurveillancePerop.show_timings_perop;

    if (!div_elt) {
      div_elt = SurveillancePerop.show_timings_perop = DOM.div({id: "show_timings_perop", className: "tooltip tooltip-perop"});
    }

    element.insert(div_elt);

    div_elt.update('Date : ' + date);
    div_elt.show();

    div_elt.setStyle({
      top:      event.layerY - 5 + "px",
      left:     event.layerX - 20 + "px",
      paddingLeft: "20px",
      position: "absolute"
    });

    let element_width = div_elt.getWidth();
    let timeline_size = timeline.props.top.width;
    let pos_cursor = event.layerX;
    let pos_container = event.layerX + element_width + 3;

    if (pos_container >= timeline_size) {
      let pos_left = pos_cursor - element_width - 25;

      div_elt.setStyle({left: pos_left + "px"});
    }
  },

  /**
   * Function to sort alphabetically an array of objects by some specific key.
   *
   * @param {String} property Key of the object to sort.
   */
  dynamicSort: function (property) {
    var sortOrder = 1;

    if(property[0] === "-") {
      sortOrder = -1;
      property = property.substr(1);
    }

    return function (a,b) {
      if(sortOrder == -1){
        return b[property].localeCompare(a[property]);
      }else{
        return a[property].localeCompare(b[property]);
      }
    }
  },
  /**
   * Change letter pagination
   */
  changeLetter: function (letter) {
    var form = getForm('mode_grille_perop');
    var formMode = getForm('peropModeGrille' + this.mode_grille_perop);
    $V(form.letter, letter);
    $V(form.show_all_products, $V(formMode.show_all_products) ? 1 : 0);
    SurveillancePerop.tabModeGrillePerop($V(form.operation_id), $V(form.container_id), $V(form.datetime), $V(form.code_cis), $V(form.quantite), 2, letter, 0, $V(form.show_all_products));
  },
  /**
   * Change letter pagination
   */
  changePage: function (page) {
    var form = getForm('mode_grille_perop');
    var mode = $V(form.container_id);
    SurveillancePerop.tabModeGrillePerop($V(form.operation_id), mode, $V(form.datetime), $V(form.code_cis), $V(form.quantite), 2, $V(form.letter), page, getForm('peropModeGrille' + mode).show_all_products.checked ? 1 : 0);
  },
  /**
   * Load the perop graphique
   *
   * @param operation_id
   * @param type
   */
  loadPeropGraphique: function (operation_id, type) {
    new Url("dPsalleOp", "ajax_print_supervision_tabs")
      .addParam("operation_id", operation_id)
      .addParam("type", type)
      .requestUpdate('graph_' + type);
  },
  /**
   * Show the legend
   */
  showLegend: function () {
    new Url("prescription", "vw_legend_perop")
      .requestModal("30%", null);
  },
  /**
   * Show charts perop with tabs
   */
  showChartwithTabs: function (operation_id, completed_view) {
    if (completed_view == 1) {
      document.location.reload();
    }
    else {
      var url = new Url("dPsalleOp", "vw_partogramme");
      url.addParam("operation_id", operation_id);
      url.addParam("completed_view", completed_view);
      url.requestUpdate("container_supervision");
    }
  },
  /**
   * Show the partogramme
   *
   * @param operation_id
   * @param show_cormack
   * @param dossier_perinatal
   */
  showPartogramme: function (operation_id, show_cormack, dossier_perinatal) {
    new Url("salleOp", "ajax_vw_surveillance_perop")
      .addParam("operation_id", operation_id)
      .addParam("show_cormack", show_cormack)
      .addParam("isDossierPerinatal", dossier_perinatal)
      .addParam('type', 'partogramme')
      .requestUpdate("surveillance_partogramme");
  },
  /**
   * Show the post partum graphic
   *
   * @param operation_id
   * @param grossesse_id
   * @param show_cormack
   * @param dossier_perinatal
   */
  showPostPartum: function (operation_id, grossesse_id, show_cormack, dossier_perinatal) {
    new Url("maternite", "dossier_mater_graphique_sspi")
      .addParam("operation_id", operation_id)
      .addParam("grossesse_id", grossesse_id)
      .addParam("show_cormack", show_cormack)
      .addParam("isDossierPerinatal", dossier_perinatal)
      .requestUpdate("surveillance_post_partum");
  },
  /**
   * Show the container of the action buttons
   *
   * @param element
   */
  showMenu: function(element) {
    var durationHide = {
      "short": 0.3,
      "medium": 0.5,
      "long": 1
    };
    var options = {
      addContainer: false,
      offsetLeft: false,
      moveContainer: false,
      duration: 0,
      durationHide: durationHide["medium"]
    };

    ObjectTooltip.createDOM(element, element.next('div'), options);
  },
  /**
   * Refresh the container
   *
   * @param container
   * @param type
   * @param graph_name
   */
  refreshContainer: function (container, type, graph_name) {
    if (window.reloadSurveillance && type) {
      window.reloadSurveillance[type]();
    }
    else {
      container = !container ? $$('.timeline-container-' + type)[0] : container;
      graph_name = !graph_name ? 'supervision-timeline' : graph_name;
      var element_main = $$('div[data-graphguid='+ graph_name +']')[0];

      container.retrieve("timeline").updateChildrenSelected(null, element_main);
    }
  },

  /**
   * Update the quantity for an administration
   *
   * @param ratio_massique
   * @param field
   */
  updateQuantite: (ratio_massique, field) => {
    if (!ratio_massique) {
      SurveillancePerop.updateQuantitesPousseSeringue(field);
      return;
    }

    var form = field.form;

    switch (field.name) {
      default:
      case 'quantite':
        var quantite_massique = ($V(field) / ratio_massique).toFixed(2);
        $V(form._quantite_massique, quantite_massique, false);
        break;

      case '_quantite_massique':
      case '_quantite_prescription':
        var quantite = ($V(field) * ratio_massique).toFixed(5);
        $V(form.quantite, quantite, false);
    }

    SurveillancePerop.updateQuantitesPousseSeringue(field);
  },

  /**
   * Synchonize quantities of active principles and solvant according to initial quantity
   *
   * @param field
   */
  updateQuantitesPousseSeringue: (field) => {
    if (this.updating_qte_pousse_seringue) {
      return;
    }

    this.updating_qte_pousse_seringue= true;

    var line_mix_id = field.form.dataset.lineId;
    var uid = field.form.dataset.uid;
    var solvant_qte_view = $('solvant-perf-' + line_mix_id + '-' + uid);

    var quantite_principe_actif = parseFloat($V(field));
    var qte_initiale_principe_actif = parseFloat(field.dataset.qteInitiale);

    quantite_principe_actif = isNaN(quantite_principe_actif) ? 0 : quantite_principe_actif;
    qte_initiale_principe_actif = isNaN(qte_initiale_principe_actif) ? 0 : qte_initiale_principe_actif;

    // Possible solvent
    if (solvant_qte_view) {
      var solvant_form = solvant_qte_view.up('form');
      var qte_initiale_solvant = parseFloat(solvant_qte_view.dataset.qteMl);

      qte_initiale_solvant = isNaN(qte_initiale_solvant) ? 0 : qte_initiale_solvant;
      var ratio_solvant = qte_initiale_solvant / qte_initiale_principe_actif;
      var qte_solvant = Math.round(100 * quantite_principe_actif * ratio_solvant) / 100;

      $V(solvant_form.quantite, qte_solvant, false);
      solvant_qte_view.update(qte_solvant);
    }

    $$('form.adm-principe-actif-' + line_mix_id + '-' + uid).each((_form) => {
      var input = _form._quantite_massique ? _form._quantite_massique : _form.quantite;

      if (_form._quantite_prescription) {
        input = _form._quantite_prescription;
      }

      if (input === field) {
        return;
      }

      var qte_initiale_other_principe_actif = parseFloat(input.dataset.qteInitiale);
      qte_initiale_other_principe_actif = isNaN(qte_initiale_other_principe_actif) ? 0 : qte_initiale_other_principe_actif;

      var ratio = qte_initiale_other_principe_actif / qte_initiale_principe_actif;

      $V(input, Math.round(100 * quantite_principe_actif * ratio) / 100);
    });

    this.updating_qte_pousse_seringue = false;
  },

  /**
   * Prepare all the administrations forms to be submitted
   *
   * @param line_id
   * @param uid
   * @param callback
   * @returns {*}
   */
  submitAdmPousseSeringue: (line_id, uid, callback) => {
    SurveillancePerop.forms_pousse_seringue = $$('form.pousse-seringue-' + line_id + '-' + uid);
    return SurveillancePerop.submitFormsPousseSeringue(callback);
  },

  /**
   * Submit the administrations forms
   * 
   * @param callback
   */
  submitFormsPousseSeringue: (callback) => {
    var form = SurveillancePerop.forms_pousse_seringue.shift();
    
    if (!form) {
      callback();
      return false;
    }

    $V(form.administration_id, '');
    $V(form.planification, '0');

    return onSubmitFormAjax(form, SurveillancePerop.submitFormsPousseSeringue.curry(callback));
  }
};
