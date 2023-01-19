/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Modele = {
  refresh: function(compte_rendu_id, enable_slave) {
    var url = new Url("compteRendu", "ajax_list_modeles");
    url.addFormData(getForm("filterModeles"));
    if (!Object.isUndefined(compte_rendu_id)) {
      url.addParam("compte_rendu_id", compte_rendu_id);
    }
    if (!Object.isUndefined(enable_slave)) {
      url.addParam("enable_slave", enable_slave);
    }
    url.requestUpdate("modeles_area");
  },

  refreshLine: function(compte_rendu_id) {
    if (!compte_rendu_id) {
      return;
    }

    new Url("compteRendu", "ajax_line_modele")
      .addParam("compte_rendu_id", compte_rendu_id)
      .requestUpdate("line_modele_" + compte_rendu_id);
  },

  delLine: function(compte_rendu_id) {
    var line = $("line_modele_" + compte_rendu_id);
    if (line) {
      // On décrémente le compteur
      var small = $("tabs-owner").down("a[href=#" + line.up("div").id + "]").down("small");
      var count = parseInt(small.innerHTML.replace(/\(/, "").replace(/\)/, "")) - 1;
      small.update("(" + count + ")");
      if (count == 0) {
        small.up("a").removeClassName("active");
        small.up("a").addClassName("empty");
      }
      line.remove();
    }
  },

  edit: function(compte_rendu_id) {
    new Url("compteRendu", "addedit_modeles")
      .addParam("compte_rendu_id", compte_rendu_id)
      .modal({
        width:  "95%",
        height: "90%",
        onClose: function() {
          if (compte_rendu_id) {
            Modele.refreshLine(compte_rendu_id);
          }
          else{
            Modele.refresh(undefined, 0);
          }

          var window_iframe = this.modalObject.container.down('iframe').contentWindow;

          if (window.Preferences.pdf_and_thumbs == 1 && window_iframe.Thumb.contentChanged == true) {
            window_iframe.emptyPDF();
          }

          Modele.saveReadTime(compte_rendu_id, window_iframe.ReadWriteTimer.getTime());
        },
        closeOnEscape: false,
        waitingText: true
      });
  },

  remove: function(compte_rendu_id, nom) {
    var form = getForm("deleteModele");
    $V(form.compte_rendu_id, compte_rendu_id);
    confirmDeletion(form, {
      typeName: "le modèle",
      objName:  nom,
      ajax:     1
    },
    function() {
      if ($("systemMsg").select("div.info").length) {
        Modele.delLine(compte_rendu_id)
      }
    });
  },

  removeSelection: function(table) {
    var compte_rendu_ids = $(table).select("input[class='export_modele']:checked").pluck("value");

    if (!compte_rendu_ids.length) {
      return;
    }

    var form = getForm("deleteModele");
    $V(form.compte_rendu_ids, compte_rendu_ids.join("-"));
    confirmDeletion(form, {
      typeName: "les modèles sélectionnés",
      ajax:     1
    },
    function() {
      getForm("filterModeles").onsubmit();
    });
  },

  preview: function(id) {
    var url = new Url("compteRendu", "print_cr");
    url.addParam("compte_rendu_id", id);
    url.popup(800, 800);
  },

  preview_layout: function() {
    var header_size = parseInt($V(getForm("editFrm").elements.height));
    if (!isNaN(header_size)) {
      $("header_footer_content").style["height"] = ((header_size / 728.5) * 80).round() + "px";
    }
    $("body_content").style["height"] =  "80px";
  },

  generate_auto_height: function() {
    var content = window.CKEDITOR.instances.htmlarea ? CKEDITOR.instances.htmlarea.getData() : $V(form.source);
    var container = new Element("div", {style: "width: 17cm; padding: 0; margin: 0; position: absolute; left: -1500px; bottom: 200px;"}).insert(content);
    $$("body")[0].insert(container);
    // Calcul approximatif de la hauteur
    $V(getForm("editFrm").height, (container.getHeight()).round());
  },

  showUtilisation: function(compte_rendu_id) {
    new Url('compteRendu', 'ajax_show_utilisation')
      .addParam("compte_rendu_id", compte_rendu_id)
      .requestModal('80%', '80%');
  },

  copy: function(form, user_id, droit) {
    form = form || getForm("editFrm");

    if (droit && !confirm($T("CCompteRendu-already-access"))) {
      return;
    }
    $V(form.compte_rendu_id, "");
    $V(form.nom, "Copie de " + $V(form.nom));
    $V(form.user_id, user_id);
    $V(form.factory, 'CWkHtmlToPDFConverter');
    form.onsubmit();
  },

  filter: function(input) {
    var table = input.up("table");

    var term = $V(input);

    if (!term) {
      table.select("tr.line").invoke("show");
      return;
    }

    table.select("tr.line").invoke("hide");
    table.select(".CCompteRendu-view").each(function(e) {
      if (e.innerHTML.like(term)) {
        e.up("tr").show();
      }
    });
  },

  exportXML: function(owner, object_class, modeles_ids) {
    new Url("compteRendu", "ajax_export_modeles", "raw")
      .addParam("owner", owner)
      .addParam("object_class", object_class)
      .pop(400, 300, "export_csv", null, null, {
      modeles_ids:  modeles_ids.join("-"),
      owner:        owner,
      object_class: object_class
    })
  },

  importXML: function(owner_guid) {
    new Url("compteRendu", "viewImport")
      .addParam("owner_guid", owner_guid)
      .pop(500, 400, "Import de modèles");
  },

  /**
   * Exports selected models to a csv file
   */
  exportCSV: function () {
    var ids = [];
    Array.from($$('.export_modele:checked')).forEach(function (element) {
      ids.push(element.value);
    });

    if (ids.length > 0) {
      new Url('compteRendu', 'ajax_export_csv', 'raw')
        .addParam('model_ids[]', ids, true)
        .pop(500, 500, $T('Export-CSV'));
    }
  },
  /**
   * Set category field null or not null
   */
  categoryNullOrNotNull: function (element) {
    var form = element.form;
    var file_category = form.file_category_id;
    var category_label = file_category.next('label');

    if ($V(form.type) === 'body') {
      file_category.addClassName('notNull');
      category_label.addClassName($V(file_category) ? 'notNullOK' : 'notNull');
    }
    else {
        file_category.removeClassName('notNull');
        file_category.removeClassName('notNullOK');
        category_label.removeClassName('notNull');
        category_label.removeClassName('notNullOK');
    }
  },

  /**
   * Sauvegarde du temps de lecture sur un modèle
   *
   * @param compte_rendu_id
   * @param read_time
   */
  saveReadTime: (compte_rendu_id, read_time) => {
    new Url()
      .addParam('@class', 'CCompteRendu')
      .addParam('compte_rendu_id', compte_rendu_id)
      .addParam('_add_duree_lecture', read_time)
      .requestJSON(Prototype.emptyFunction, {method: 'POST'});
  },
};
