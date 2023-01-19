/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

GestePerop = {
  ProtocoleGesteItems: {},
  /**
   * Change a page with the pagination
   *
   * @param page
   */
  changePage: function (page) {
    var form = getForm('filterGestePerop');
    $V(form.page, page);

    GestePerop.loadGestesPerop(form);
  },
  /**
   * refresh the active tab
   */
  refreshActiveTab: function () {
    $('tab_geste_perop').down('a.active').onmouseup();
  },
  /**
   * refresh the main active tab
   */
  refreshMainActiveTab: function () {
    $('control_grouped_tabs').down('span.active').click();
  },
  /**
   * Erase an input
   *
   * @param elt
   * @param elt_view
   */
  eraseInput: function (elt, elt_view) {
    $V(elt, '');
    $V(elt_view, '');
  },
  /**
   * Edit a perop category
   *
   * @param categorie_id
   */
  editEventPeropCategorie: function (categorie_id) {
    new Url("dPsalleOp", "ajax_edit_evenement_perop_categorie")
      .addParam("categorie_id", categorie_id)
      .requestModal(800, 450, {
        onClose: function () {
          GestePerop.loadEventPeropCategories();
        }
      });
  },
  /**
   * Edit a perop chapter
   *
   * @param chapitre_id
   */
  editEventPeropChapitre: function (chapitre_id) {
    new Url("dPsalleOp", "ajax_edit_evenement_perop_chapitre")
      .addParam("chapitre_id", chapitre_id)
      .requestModal(800, 450, {
        onClose: function () {
          GestePerop.loadEventPeropChapitres();
        }
      });
  },
  /**
   * Edit a perop gesture
   *
   * @param geste_perop_id
   */
  editGestePerop: function (geste_perop_id) {
    new Url("dPsalleOp", "ajax_edit_geste_perop")
      .addParam("geste_perop_id", geste_perop_id)
      .requestModal("60%", "100%", {onClose: GestePerop.loadGestesPerop.curry(getForm("filterGestePerop"))});
  },
  /**
   * Edit a perop precision
   *
   * @param precision_id
   * @param geste_perop_id
   */
  editPrecision: function (precision_id, geste_perop_id) {
    new Url("dPsalleOp", "ajax_edit_precision")
      .addParam("precision_id", precision_id)
      .addNotNullParam("geste_perop_id", geste_perop_id)
      .requestModal(800, 450, {onClose: GestePerop.loadlistPrecisions.curry(geste_perop_id)});
  },
  /**
   * Edit a perop precision value
   *
   * @param precision_valeur_id
   * @param precision_id
   */
  editPrecisionValeur: function (precision_valeur_id, precision_id) {
    new Url("dPsalleOp", "ajax_edit_precision_valeur")
      .addParam("precision_valeur_id", precision_valeur_id)
      .addParam("precision_id", precision_id)
      .requestModal(350, 250, {onClose: GestePerop.loadlistPrecisionValeurs.curry(precision_id)});
  },
  /**
   * Load the perop gesture list
   */
  loadlistGestesPerop: function () {
    new Url("dPsalleOp", "ajax_vw_list_gestes_perop")
      .requestUpdate("gestes_perop");
  },
  /**
   * Load the perop categories
   */
  loadEventPeropCategories: function () {
    new Url("dPsalleOp", "ajax_vw_evenement_perop_categories")
      .requestUpdate("event_perop_category");
  },
  /**
   * Load the perop chapters
   */
  loadEventPeropChapitres: function () {
    new Url("dPsalleOp", "ajax_vw_evenement_perop_chapitres")
      .requestUpdate("event_perop_chapter");
  },
  /**
   * Load the gesture perop details
   *
   * @param geste_perop_id
   */
  loadlistPrecisions: function (geste_perop_id) {
    new Url("dPsalleOp", "ajax_vw_list_precisions")
      .addParam("geste_perop_id", geste_perop_id)
      .requestUpdate('precisions');
  },
  /**
   * Load the values of precision
   *
   * @param precision_id
   */
  loadlistPrecisionValeurs: function (precision_id) {
    new Url("dPsalleOp", "ajax_vw_list_precision_valeurs")
      .addParam("precision_id", precision_id)
      .requestUpdate("precision_valeurs");
  },
  /**
   * Load the perop gesture list
   *
   * @param form
   */
  loadGestesPerop: function (form) {
    new Url("dPsalleOp", "httpreq_list_gestes_perop")
      .addFormData(form)
      .requestUpdate("list_gestes_perop");
  },
  /**
   * check the context
   *
   * @param form
   */
  checkContext: function (form) {
   if ($V(form.user_id) || $V(form.function_id) || $V(form.group_id)) {
     return true;
   }

   alert($T('CGestePerop-msg-Please enter a context'));

   return false;
  },
  /**
   * Get the user list
   *
   * @param form
   */
  userAutocomplete: function (form) {
    new Url("mediusers", "ajax_users_autocomplete")
      .addParam("edit", "1")
      .addParam("input_field", "user_id_view")
      .autoComplete(form.user_id_view, null, {
        minChars:           0,
        method:             "get",
        select:             "view",
        dropdown:           true,
        afterUpdateElement: function (field, selected) {
          var id = selected.getAttribute("id").split("-")[2];
          $V(form.user_id, id);
        }
      });
  },
  /**
   * Get the function list
   *
   * @param form
   */
  functionAutocomplete: function (form) {
    new Url("mediusers", "ajax_functions_autocomplete")
      .addParam("edit", "1")
      .addParam("input_field", "function_id_view")
      .addParam("view_field", "text")
      .autoComplete(form.function_id_view, null, {
        minChars:           0,
        method:             "get",
        select:             "view",
        dropdown:           true,
        afterUpdateElement: function (field, selected) {
          var id = selected.getAttribute("id").split("-")[2];
          $V(form.function_id, id);
        }
      });
  },
  /**
   * Get the group list
   *
   * @param form
   */
  groupAutocomplete: function (form) {
    new Url("etablissement", "ajax_groups_autocomplete")
      .addParam("edit", "1")
      .addParam("input_field", "group_id_view")
      .addParam("view_field", "text")
      .autoComplete(form.group_id_view, null, {
        minChars:           0,
        method:             "get",
        select:             "view",
        dropdown:           true,
        afterUpdateElement: function (field, selected) {
          var id = selected.getAttribute("id").split("-")[2];
          $V(form.group_id, id);
        }
      });
  },
  /**
   * Get the perop gesture list
   *
   * @param form_main
   * @param form_second
   * @param context
   * @param object_guid
   */
  gestePeropAutocomplete: function (form_main, form_second, context, object_guid) {
    new Url("dPsalleOp", "ajax_gestes_perop_autocomplete")
      .addParam("edit", "1")
      .addParam("input_field", "geste_perop_id_view")
      .addParam("view_field", "libelle")
      .addParam("object_guid", object_guid)
      .autoComplete(form_main.geste_perop_id_view, null, {
        minChars:           0,
        method:             "get",
        select:             "view",
        dropdown:           true,
        afterUpdateElement: function (field, selected) {
          var guid = selected.get("guid").split("-");
          var elt_class = guid[0];
          var elt_id = guid[1];

          if (form_second) {
            if (context == 'protocole') {
              $V(form_second.object_id, elt_id);
              $V(form_second.object_class, elt_class);
            }
            else {
              $V(form_second.geste_perop_id, elt_id);
            }

            form_second.onsubmit();
          }
          else {
            $V(form_main.geste_perop_id, elt_id);
            $V(form_main.geste_perop_id_view, selected.innerHTML.trim());
          }
        }
      });
  },
  /**
   * Get the perop categories list
   *
   * @param form_main
   * @param form_second
   * @param object_guid
   */
  categoriesPeropAutocomplete: function (form_main, form_second, object_guid) {
    new Url("dPsalleOp", "ajax_categories_perop_autocomplete")
      .addParam("edit", "1")
      .addParam("input_field", "categorie_perop_id_view")
      .addParam("object_guid", object_guid)
      .autoComplete(form_main.categorie_perop_id_view, null, {
        minChars:           0,
        method:             "get",
        select:             "view",
        dropdown:           true,
        afterUpdateElement: function (field, selected) {
          var elt_id = selected.get("id");

          $V(form_second.anesth_perop_categorie_id, elt_id);

          form_second.onsubmit();
        }
      });
  },
  /**
   * Get the precision value list
   *
   * @param form_main
   * @param form_second
   * @param object_guid
   */
  precisionValueAutocomplete: function (form_main, form_second, object_guid) {
    new Url("dPsalleOp", "ajax_precision_valeurs_autocomplete")
      .addParam("edit", "1")
      .addParam("input_field", "precision_valeur_id_view")
      .addParam("object_guid", object_guid)
      .autoComplete(form_main.precision_valeur_id_view, null, {
        minChars:           0,
        method:             "get",
        select:             "view",
        dropdown:           true,
        afterUpdateElement: function (field, selected) {
          var elt_id = selected.get("id");

          if (form_second) {
            $V(form_second.precision_valeur_id, elt_id);

            form_second.onsubmit();
          }
        }
      });
  },
  /**
   * Load the Perop gesture protocol list
   */
  loadListProtocolesGestesPerop: function () {
    new Url("dPsalleOp", "ajax_vw_list_protocoles_gestes_perop")
      .requestUpdate("protocoles_gestes_perop");
  },
  /**
   * Load the Perop gesture protocols
   */
  loadProtocolesGestesPerop: function (form) {
    new Url("dPsalleOp", "ajax_protocoles_gestes_perop")
      .addFormData(form)
      .requestUpdate("list_protocoles_gestes_perop");
  },
  /**
   * Change page
   */
  changePageProtocole: function (page) {
    var form = getForm('filterProtocolesGestePerop');
    $V(form.page, page);

    GestePerop.loadProtocolesGestesPerop(form);
  },
  /**
   * Edit the Perop gesture protocol
   */
  editProtocoleGestePerop: function (protocole_geste_perop_id) {
    new Url("dPsalleOp", "ajax_edit_protocole_geste_perop")
      .addParam("protocole_geste_perop_id", protocole_geste_perop_id)
      .requestModal("50%", "100%", {
        onClose: function () {
          GestePerop.loadProtocolesGestesPerop(getForm('filterProtocolesGestePerop'));
        }
      });
  },
  /**
   * Refresh item Perop gesture protocol
   *
   * @param protocole_geste_perop_id
   */
  refreshListProtocoleItems: function (protocole_geste_perop_id) {
    new Url("dPsalleOp", "ajax_vw_protocole_geste_perop_items")
      .addParam("protocole_geste_perop_id", protocole_geste_perop_id)
      .requestUpdate("list_items_" + protocole_geste_perop_id);
  },
  /**
   * Add perop gesture protocol items from category
   *
   * @param form
   */
  AddProtocoleItemGestePeropFromCategory: function (form) {
    var gestes = $V(form.select('input.geste_selected:checked'));

    new Url('salleOp', 'do_geste_perop_multi_from_category', 'dosql')
      .addParam('protocole_geste_perop_id', $V(form.protocole_geste_perop_id))
      .addParam('_geste_perop_ids', Object.toJSON(gestes))
      .requestUpdate('systemMsg', {
        method: 'post', onComplete: function () {
          Control.Modal.close();
        }
      });
  },
  /**
   * Select all checkbox lines
   *
   * @param protocole_id
   * @param element
   */
  selectAllLines: function (protocole_id, element) {
    var element_main = $('legend-protocole-' + protocole_id);
    var color = 'black';
    var value_element = $V(element);

    if (value_element) {
      color = 'green';
    }
    element_main.setAttribute('style', 'color:' + color + ';');

    element_main.up("fieldset").select('input[type=checkbox]').each(function (e) {
      if (element.checked) {
        e.checked = true;
        $$('.select_' + e.value).invoke('enable');

        if (e.name.indexOf('_view_' + e.value) > -1) {
          GestePerop.bindElementGeste(e);
        }
      }
      else {
        e.checked = false;
        $$('.select_' + e.value).invoke('disable');

        if ($('li_geste_' + e.value)) {
          $('li_geste_' + e.value).remove();
        }

        if ($$('ul#show_tags_gestes > li').length == 1) {
          $('tag_geste_none').show();
        }
      }
    });
  },
  /**
   * Select main checkbox line
   *
   * @param protocole_id
   * @param element
   * @param geste_id
   * @param geste_libelle
   * @param item_precision
   * @param item_precision_valeur
   */
  selectOneLine: function (protocole_id, element, geste_id, geste_libelle, item_precision, item_precision_valeur) {
    var legend_input = $('legend-protocole-' + protocole_id);
    var color = 'black';
    var value_element = 0;

    if ($$('.geste_perop_item:checked').length > 0) {
      color = 'green';
      value_element = 1;
    }

    legend_input.setAttribute('style', 'color:' + color + ';');

    $V(legend_input.down('input'), value_element);

    if (element.checked) {
      $$('.select_' + element.value).invoke('enable');

      GestePerop.bindElementGeste(element);

      if (item_precision) {
        GestePerop.bindElementPrecision($('precision_' + geste_id).down('option:selected'), geste_id, geste_libelle);
      }

      if (item_precision && item_precision_valeur) {
        var precision_id = $('precision_' + geste_id).down('option:selected').value;
        var precision_libelle = $('precision_' + geste_id).down('option:selected').innerText;

        GestePerop.bindElementValeur($('valeur_' + geste_id).down('option:selected'), geste_id, geste_libelle, precision_id, precision_libelle);
      }
    }
    else {
      $$('.select_' + element.value).invoke('disable');

      if ($('li_geste_' + element.value)) {
        $('li_geste_' + element.value).remove();
      }

      if ($$('ul#show_tags_gestes > li').length < 2) {
        $('tag_geste_none').show();
      }
    }
  },
  /**
   * Check select protocol
   *
   * @param form
   * @param protocole_id
   * @param limit_datetime
   */
  checkSelectProtocoleGestes: function (form, protocole_id, limit_datetime) {
    GestePerop.ProtocoleGesteItems = {};
    var gestes = $V(getForm('bindingGestes').elements['geste[]']);
    var continue_action = true;

    $('table-protocole-' + protocole_id).select('input[type=checkbox]:checked').each(function (e) {
      if (e.name.indexOf('_view_' + e.value) > -1) {
        var value_geste = e.value;
        var date = $('selectProtocoleGeste_' + value_geste + '__datetime').value;

        if (limit_datetime && (date < limit_datetime)) {
          var datetimes_format = limit_datetime.split(' ');
          var dates = datetimes_format[0].split('-');
          var times = datetimes_format[1].split(':');

          var datetime_format = dates[2] + '/' + dates[1] + '/' + dates[0] + ' ' + times[0] + 'h' + times[1];
          alert($T('CGestePerop-msg-You can no longer record perop gesture on a date and time earlier than %s', datetime_format));
          continue_action = false;
        }

        GestePerop.ProtocoleGesteItems[value_geste] = date;
      }
    });

    if (!continue_action) {
      return false;
    }

    $V(form._geste_perop_dates, Object.toJSON(GestePerop.ProtocoleGesteItems));
    $V(form._geste_perop_ids, Object.toJSON(gestes));

    onSubmitFormAjax(form, Control.Modal.close);
  },
  /**
   * Confirm to dissociate perop element
   *
   * @param form
   */
  confirmDissociateElement: function (form, context) {
    var message = $T('CAnesthPeropCategorie-msg-Are you sure you want to separate the gesture from this category');

    if (context == 'categorie') {
      message = $T('CAnesthPeropChapitre-msg-Are you sure you want to separate the category from this chapter');
    }
    if (context == 'valeur') {
      message = $T('CPrecisionValeur-msg-Are you sure you want to separate the value from this precision');
    }

    if (confirm(message)) {
      onSubmitFormAjax(form, Control.Modal.refresh);
    }
  },
  /**
   * Show the gestures list
   *
   * @param categorie_id
   * @param protocole_geste_perop_id
   */
  showListGestes: function (categorie_id, protocole_geste_perop_id, show_only) {
    if (!categorie_id) {
      return;
    }

    new Url("dPsalleOp", "ajax_vw_list_gestes_associated")
      .addParam("categorie_id", categorie_id)
      .addParam("protocole_geste_perop_id", protocole_geste_perop_id)
      .addParam("show_only", show_only)
      .requestModal("60%", null, {onClose: GestePerop.refreshListProtocoleItems.curry(protocole_geste_perop_id)});
  },
  /**
   * Show chapter's gestures menu
   *
   * @param element
   * @param chapitre_id
   * @param chapitre_ids
   * @param clickable
   */
  showMenuChapitres: function (element, chapitre_id, chapitre_ids, clickable) {
    if (element && chapitre_id) {
      $$('.chapitres-container').invoke("removeClassName", "selected");
      element.addClassName("selected");
    }

    new Url("dPsalleOp", "ajax_vw_menu_geste_chapitres")
      .addParam("chapitre_ids", chapitre_ids ? chapitre_ids.join('|') : "")
      .addParam("clickable", clickable)
      .requestUpdate("list_chapitres");

    if (clickable) {
      GestePerop.showMenuCategories(null, 0, null, clickable);
    }
  },
  /**
   * Show categorie's gestures menu
   *
   * @param element
   * @param chapitre_id
   * @param categorie_ids
   * @param clickable
   * @param see_all_gestes
   */
  showMenuCategories: function (element, chapitre_id, categorie_ids, clickable, see_all_gestes) {
    if (element) {
      $$('.chapitres-container').invoke("removeClassName", "selected");
      element.addClassName("selected");
    }

    new Url("dPsalleOp", "ajax_vw_menu_geste_categories")
      .addParam("chapitre_id", chapitre_id)
      .addParam("categorie_ids", categorie_ids ? categorie_ids.join('|') : "")
      .addParam("clickable", clickable)
      .addParam("see_all_gestes", see_all_gestes)
      .requestUpdate("list_categories");

    if (clickable) {
      GestePerop.showMenuGestes(null, 0, null, clickable, see_all_gestes);
      GestePerop.showMenuPrecisions(null, 0, null, clickable);
      GestePerop.showMenuValeurs(null, 0, clickable);
    }
  },
  /**
   * Show the gestures perop menu
   *
   * @param element
   * @param categorie_id
   * @param geste_ids
   * @param clickable
   * @param see_all_gestes
   */
  showMenuGestes: function (element, categorie_id, geste_ids, clickable, see_all_gestes) {
    if (element) {
      $$('.categories-container').invoke("removeClassName", "selected");
      element.addClassName("selected");
    }

    new Url("dPsalleOp", "ajax_vw_menu_gestes_perop")
      .addParam("categorie_id", categorie_id)
      .addParam("geste_ids", geste_ids ? geste_ids.join('|') : "")
      .addParam("clickable", clickable)
      .addParam("see_all_gestes", see_all_gestes)
      .requestUpdate("list_gestes_perop");

    if (clickable) {
      GestePerop.showMenuPrecisions(null, 0, null, clickable);
    }
  },
  /**
   * Show precision's gestures menu
   *
   * @param element
   * @param geste_perop_id
   * @param precision_ids
   * @param clickable
   */
  showMenuPrecisions: function (element, geste_perop_id, precision_ids, clickable) {
    if (element && geste_perop_id) {
      $$('.gestes-container').invoke("removeClassName", "selected");
      element.up('div').addClassName("selected");
    }

    new Url("dPsalleOp", "ajax_vw_menu_geste_precisions")
      .addParam("geste_perop_id", geste_perop_id)
      .addParam("precision_ids", precision_ids ? precision_ids.join('|') : "")
      .addParam("clickable", clickable)
      .requestUpdate("list_precisions");

    if (clickable) {
      GestePerop.showMenuValeurs(null, 0, clickable);
    }
  },
  /**
   * Show precision values' gestures menu
   *
   * @param element
   * @param precision_id
   * @param clickable
   */
  showMenuValeurs: function (element, precision_id, clickable) {
    if (element && precision_id) {
      $$('.precisions-container').invoke("removeClassName", "selected");
      element.up('div').addClassName("selected");
    }

    new Url("dPsalleOp", "ajax_vw_menu_geste_valeurs")
      .addParam("precision_id", precision_id)
      .addParam("clickable", clickable)
      .requestUpdate("list_valeurs");
  },
  /**
   * Choose precisions for the gestures perop
   *
   * @param form
   * @param incident
   */
  choosePrecisionsGeste: function (form, incident) {
    var gestes_ids = $V(form.select("input.gestes:checked")).join("|");

    new Url("dPsalleOp", "ajax_vw_choose_precisions_gestes")
      .addParam("geste_ids", gestes_ids)
      .requestModal("40%", "40%", {onClose: function () {
          $V(form._geste_perop_ids, gestes_ids);
          $V(form.incident, incident);

          form.onsubmit();
        }});
  },
  /**
   * Show CIM10 for the gesture perop
   *
   * @param grossesse_id
   * @param callback
   */
  showCIMs10: function (grossesse_id, callback) {
    new Url("cim10", "find_codes_antecedent")
    .addParam("mater", grossesse_id)
    .addParam("callback", callback)
    .requestUpdate('do_antecedent');
  },
  /**
   * Submit perop form
   *
   * @param form
   * @param limit_date_min
   * @returns {Boolean|boolean}
   */
  submitPerOp: function(form, limit_date_min){
    if (limit_date_min) {
      if ($V(form.datetime) < limit_date_min) {
        alert($T('CAnesthPerop-msg-You can no longer record events on a date and time earlier than %s', Date.fromDATETIME(limit_date_min).toLocaleDateTime()));
        return false;
      }
    }

    if ($V(form.antecedent) && form.libelle.value != "") {
      GestePerop.saveAnt(form);
    }
    return onSubmitFormAjax(form, {
      onComplete: function(){
        Control.Modal.close();
      }
    });
  },
  /**
   * Save gestures perop like antecedent
   *
   * @param formAnt
   * @returns {Boolean}
   */
  saveAnt: function (formAnt) {
    var form = getForm('addAntecedentIncident');

    let rques = $V(formAnt.codecim) ? (formAnt.libelle.value + ' ' + $V(formAnt.codecim)) : formAnt.libelle.value;

    let precision = (
      formAnt.geste_perop_precision_id ?
        formAnt.geste_perop_precision_id.options[formAnt.geste_perop_precision_id.selectedIndex].getText() : ''
    ).trim();

    let valeur = (
      formAnt.precision_valeur_id ?
      formAnt.precision_valeur_id.options[formAnt.precision_valeur_id.selectedIndex].getText() : ''
    ).trim();

    if (precision) {
      rques += "\n" + $T('CGestePeropPrecision') + ' : ' + precision;
    }

    if (valeur) {
      rques += ' / ' + valeur;
    }

    $V(form.rques, rques);

    return onSubmitFormAjax(form, {
      onComplete: function () {
        $V(formAnt.codecim, '');
        $V(formAnt.antecedent, 0);
      }
    });
  },
  /**
   * Show antecedents
   *
   * @param form
   * @param antecedent_code_cim
   */
  incidentAntecedent: function (form, antecedent_code_cim) {
    if (!antecedent_code_cim) {
      if ($V(form.antecedent)) {
        $('do_antecedent').show();
      } else {
        $('do_antecedent').hide();
        $V(form.antecedent, 0);
        $V(form.antecedent_code_cim, '');
      }
    }
    else {
      $('do_antecedent').show();
      form.antecedent.checked = true;
      setTimeout(function () {$$('input[name=codecim][value="'+ antecedent_code_cim +'"]')[0].checked = true;}, 700);
    }
  },
  /**
   * Initialize the view size (choice of gestures perop)
   */
  initializeView: function () {
    var elements = $('list_elements_gestes').select('fieldset');
    var top = 15;

    if (elements) {
      var dimensions = document.viewport.getDimensions();
      elements.each(function(element){
        element.setStyle(
          {
            height: dimensions.height - element.cumulativeOffset().top  - top + 'px',
          }
        );
      });
    }
  },
  /**
   * Search into menu chapters
   *
   * @param chapitres
   * @param form
   * @param msg
   */
  searchMenuChapitres: function (chapitres, form, msg) {
    if (chapitres.length) {
      GestePerop.showMenuChapitres(null, null, chapitres, 1);

      $('counter_chapitre').innerHTML = chapitres.length;
    }
    else if (!chapitres.length && msg) {
      alert('Aucun chapitre trouvé pour les mots clés : "' + $V(form.keywords) + '"');
    }
  },
  /**
   * Search into menu categories
   *
   * @param categories
   * @param form
   * @param msg
   */
  searchMenuCategories: function (categories, form, msg) {
    if (categories.length) {
      GestePerop.showMenuCategories(null, null, categories);

      $('counter_categorie').innerHTML = categories.length;
    }
    else if (!categories.length && msg) {
      alert('Aucune catégorie trouvée pour les mots clés : "' + $V(form.keywords) + '"');
    }
  },
  /**
   * Search into menu gestures
   *
   * @param gestes
   * @param form
   * @param msg
   */
  searchMenuGestes: function (gestes, form, msg) {
    if (gestes.length) {
      GestePerop.showMenuGestes(null, null, gestes);

      $('counter_geste').innerHTML = gestes.length;
    }
    else if (!gestes.length && msg) {
      alert('Aucun geste trouvé pour les mots clés : "' + $V(form.keywords) + '"');
    }
  },
  /**
   * Search into menu precision
   *
   * @param precisions
   * @param form
   * @param msg
   */
  searchMenuPrecisions: function (precisions, form, msg) {
    if (precisions.length) {
      GestePerop.showMenuPrecisions(null, null, precisions);

      $('counter_precision').innerHTML = precisions.length;
    }
    else if (!precisions.length && msg) {
      alert('Aucune précision trouvée pour les mots clés : "' + $V(form.keywords) + '"');
    }
  },
  /**
   * Search into menu gesture perop
   *
   * @param form
   */
  searchIntoMenu: function (form) {
    if (!$V(form.keywords)) {
      return $("systemMsg").update(DOM.div({
        className: "fas fa-exclamation-triangle",
        style: "font-size: 1.5em;"},
        " " + $T('CGestePerop-msg-Please enter keywords for your search'))
      ).show();
    }

    new Url("dPsalleOp", "ajax_search_menu_structure")
      .addFormData(form)
      .requestJSON(function (results) {
        var chapitres  = Object.keys(results['chapitres']);
        var categories = Object.keys(results['categories']);
        var gestes     = Object.keys(results['gestes']);
        var precisions = Object.keys(results['precisions']);

        //$$('.chapitres-container').invoke("removeClassName", "selected");

        switch ($V(form.context)) {
          case 'chapitre':
            GestePerop.searchMenuChapitres(chapitres, form, 1);
            break;
          case 'categorie':
            GestePerop.searchMenuChapitres(chapitres, form, 0);
            GestePerop.searchMenuCategories(categories, form, 1);
            break;
          case 'geste':
            GestePerop.searchMenuChapitres(chapitres, form, 0);
            GestePerop.searchMenuCategories(categories, form, 0);
            GestePerop.searchMenuGestes(gestes, form, 1);
            break;
          default:
            GestePerop.searchMenuChapitres(chapitres, form, 0);
            GestePerop.searchMenuCategories(categories, form, 0);
            GestePerop.searchMenuGestes(gestes, form, 0);
            GestePerop.searchMenuPrecisions(precisions, form, 1);
            break;
        }
      });
  },
  /**
   * Bind the gesture element to an object
   *
   * @param element
   */
  bindElementGeste: function (element) {
    var element_id = element.value;
    var element_libelle = $('geste_element_' + element_id).innerHTML;

    if (element.type === 'checkbox' && !element.checked) {
      if ($('li_geste_' + element_id)) {
        $('li_geste_' + element_id).remove();
      }

      if ($$('ul#show_tags_gestes > li').length <= 1) {
        $('tag_geste_none').show();
        $('button_validate_geste').disabled = true;
      }

      return;
    }

    var form = getForm('bindingGestes');
    var li = DOM.li({
        id: 'li_geste_' + element_id,
        className: 'tag geste_selected',
        style: 'cursor: default;'
      },
      DOM.span({}, element_libelle),
      DOM.i({
        className: 'fas fa-times',
        type:      'button',
        style: 'margin-left: 10px; cursor: pointer;',
        title: 'Supprimer',
        onclick:   "$(this).up('li').remove(); GestePerop.deleteElementTag(" + element_id + ");"
      }),
      DOM.input({
        type:  'hidden',
        id:    form.name + "__geste_id[" + element_id + "]",
        name:  'geste[]',
        value: element_id
      })
    );

    $("show_tags_gestes").insert(li);

    if ($$('ul#show_tags_gestes > li').length > 1) {
      $('tag_geste_none').hide();

      if ($('button_validate_geste')) {
        $('button_validate_geste').disabled = false;
      }
    }
  },
  /**
   * Bind the precision element to an object
   *
   * @param element
   * @param geste_id
   * @param geste_libelle
   */
  bindElementPrecision: function (element, geste_id, geste_libelle) {
    let element_id = element.value;

    let li_geste_id = 'li_geste_' + geste_id;

    if ($(li_geste_id)) {
      $(li_geste_id).remove();
    }

    if (!element_id) {
      return GestePerop.bindElementGeste(element.up('tr').down('input:checkbox'));
    }

    var element_libelle = $('precision_element_' + element_id).innerHTML;

    if (element.type === 'checkbox' && !element.checked) {
      if ($$('ul#show_tags_gestes > li').length <= 1) {
        $('tag_geste_none').show();
        $('button_validate_geste').disabled = true;
      }

      return;
    }

    var form = getForm('bindingGestes');

    var li = DOM.li({
        id: li_geste_id,
        className: 'tag geste_selected',
        style: 'cursor: default;'
      },
      DOM.span({}, geste_libelle + ' <i class="fas fa-long-arrow-alt-right"></i> ' + element_libelle),
      DOM.i({
        className: 'fas fa-times',
        type:      'button',
        style: 'margin-left: 10px; cursor: pointer;',
        title: 'Supprimer',
        onclick:   "$(this).up('li').remove(); GestePerop.deleteElementTag(" + element_id + ");"
      }),
      DOM.input({
        type:  'hidden',
        id:    form.name + "__geste_id[" + geste_id + "]",
        name:  'geste[]',
        value: geste_id + '|' + element_id
      })
    );

    $("show_tags_gestes").insert(li);

    if ($$('ul#show_tags_gestes > li').length > 1) {
      $('tag_geste_none').hide();

      if ($('button_validate_geste')) {
        $('button_validate_geste').disabled = false;
      }
    }
  },
  /**
   * Bind the precision value element to an object
   *
   * @param element
   * @param geste_id
   * @param geste_libelle
   * @param precision_id
   * @param precision_libelle
   */
  bindElementValeur: function (element, geste_id, geste_libelle, precision_id, precision_libelle) {
    let li_geste_id = 'li_geste_' + geste_id;
    let li_geste_id_elt = $(li_geste_id);

    var element_id = element.value;

    if (li_geste_id_elt) {
      li_geste_id_elt.remove();
    }

    if (!element_id) {
      return GestePerop.bindElementPrecision($('precision_' + geste_id).down('option:selected'), geste_id, geste_libelle);
    }

    var element_libelle = $('valeur_' + element_id).innerHTML;

    if (element.type === 'checkbox' && !element.checked) {
      if ($$('ul#show_tags_gestes > li').length <= 1) {
        $('tag_geste_none').show();
        $('button_validate_geste').disabled = true;
      }

      return;
    }

    var form = getForm('bindingGestes');



    var li = DOM.li({
        id: li_geste_id,
        className: 'tag geste_selected',
        style: 'cursor: default;'
      },
      DOM.span({}, geste_libelle + ' <i class="fas fa-long-arrow-alt-right"></i> ' + precision_libelle + ' <i class="fas fa-long-arrow-alt-right"></i> ' + element_libelle),
      DOM.i({
        className: 'fas fa-times',
        type:      'button',
        style: 'margin-left: 10px; cursor: pointer;',
        title: 'Supprimer',
        onclick:   "$(this).up('li').remove(); GestePerop.deleteElementTag(" + element_id + ");"
      }),
      DOM.input({
        type:  'hidden',
        id:    form.name + "__geste_id[" + geste_id + "]",
        name:  'geste[]',
        value: geste_id + '|' + precision_id + '|' + element_id
      })
    );

    $("show_tags_gestes").insert(li);

    if ($$('ul#show_tags_gestes > li').length > 1) {
      $('tag_geste_none').hide();
      $('button_validate_geste').disabled = false;
    }
  },
  /**
   * Remove element of tag
   *
   * @param element_id
   */
  deleteElementTag: function (element_id) {
    if ($$('input[name="geste[' + element_id + ']"')) {
      $$('input[name="geste[' + element_id + ']"')[0].checked = false;
    }

    if ($$('ul#show_tags_gestes > li').length < 2) {
      $('tag_geste_none').show();
      $('button_validate_geste').disabled = true;
    }
  },
  /**
   * Save gestures to an object
   *
   * @param form
   * @param limit_datetime
   */
  saveBindingAllGestes: function (form, limit_datetime) {
    if (limit_datetime && ($V(form.datetime) < limit_datetime)) {
      var datetimes_format = limit_datetime.split(' ');
      var dates = datetimes_format[0].split('-');
      var times = datetimes_format[1].split(':');

      var datetime_format = dates[2] + '/' + dates[1] + '/' + dates[0] + ' ' + times[0] + 'h' + times[1];
      alert($T('CGestePerop-msg-You can no longer record perop gesture on a date and time earlier than %s', datetime_format));
      return false;
    }

   var gestes = $V(form.elements['geste[]']);
    
   new Url('salleOp', 'do_geste_perop_multi_aed', 'dosql')
      .addParam('context_menu', 1)
      .addParam('operation_id', $V(form.operation_id))
      .addParam('datetime', $V(form.datetime))
      .addParam('gestes[]', gestes, true)
      .requestUpdate('systemMsg', {
        method: 'post', onComplete: function () {
          Control.Modal.close();
        }
      });
  },
  /**
   * Show the precision values' list
   *
   * @param element
   * @param geste_id
   * @param geste_libelle
   * @param protocole_geste_perop_item_id
   * @param protocole_settings
   * @param checked_item
   */
  showListValeurs: function (element, geste_id, geste_libelle, protocole_geste_perop_item_id, protocole_settings, checked_item) {
    new Url("dPsalleOp", "ajax_vw_select_geste_valeurs")
      .addParam("precision_id", element.down('option:selected').value)
      .addParam("geste_id", geste_id)
      .addParam("protocole_geste_perop_item_id", protocole_geste_perop_item_id)
      .addParam("protocole_settings", protocole_settings)
      .addParam("checked_item", checked_item)
      .requestUpdate('select_list_valeurs_' + geste_id, {onComplete: function () {
        if ((!protocole_geste_perop_item_id || !protocole_settings) && checked_item == 1) {
          GestePerop.bindElementPrecision(element.down('option:selected'), geste_id, geste_libelle);
        }
      }});
  },
  /**
   * Export perop gestures
   */
  export: function () {
    var form = getForm("filter_gestes_export");

    if (($V(form.current_group) == 1) || $V(form.function_id) || $V(form.user_id)) {
      new Url("dPsalleOp", "ajax_vw_export_gestes", "raw")
        .addParam('current_group', $V(form.current_group))
        .addParam('function_id', $V(form.function_id))
        .addParam('user_id', $V(form.user_id))
        .pop();
    }
    else {
      SystemMessage.notify("<div class='error'>"+$T('CGestePerop-msg-Please choose a filter to export the different gestures')+"</div>");
    }
  },
  /**
   * Import perop gestures
   */
  import: function () {
    var form = getForm("filter_gestes_import");

    new Url("dPsalleOp", "do_import_gestes_perop", "dosql")
      .addParam('current_group', $V(form.current_group))
      .addParam('function_id', $V(form.function_id))
      .addParam('user_id', $V(form.user_id))
      .requestUpdate(SystemMessage.id, {method: 'post',onComplete: function () {
          Control.Modal.close();
        }});
  },
  /**
   * Change the protocol item context
   */
  changeProtocoleItem: function () {
    new Url("dPsalleOp", "ajax_vw_tools_change_protocole_items")
      .requestUpdate("change_item");
  },
  /**
   * Select all checkbox line items
   *
   * @param protocole_id
   * @param element
   */
  selectAllLineItems: function (element) {
    var form = getForm('chooseGesteFromCategory');
    form.select('input.geste_selected').each(function (input) {
      input.checked = element.checked ? true : false;
    })
  },
  /**
   * Add an antecedent to a gesture perop
   *
   * @param input
   */
  addAntecedent: function (input) {
    var form = input.form;
    $V(form.antecedent_code_cim, input.value);
  },
  /**
   * Edit the precision in the protocole item settings
   *
   * @param {int}     element
   * @param {int}     protocole_geste_perop_item_id
   * @param {int}     geste_id
   * @param {string}  geste_libelle
   */
  protocoleItemPrecisionSettings: function (element, protocole_geste_perop_item_id, geste_id, geste_libelle) {
    var form = element.form;

    if (!$V(element)) {
      $V(form.precision_valeur_id, '');
    }

    GestePerop.showListValeurs(element, geste_id, geste_libelle, protocole_geste_perop_item_id, 1);
  },
  /**
   * Show the precisions select
   *
   * @param geste_perop_id
   */
  showGestePrecisions: function (geste_perop_id) {
    new Url("salleOp", "vwGestePrecisions")
      .addParam("geste_perop_id", geste_perop_id)
      .requestUpdate("list_precisions_gesture");
  },
  /**
   * Show the precision values select
   *
   * @param geste_perop_precision_id
   */
  showPrecisionValues: function (geste_perop_precision_id) {
    new Url("salleOp", "vwPrecisionValeurs")
      .addParam("geste_perop_precision_id", geste_perop_precision_id)
      .requestUpdate("list_precision_valeurs");
  }
};
