/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Document = (window.Document && window.Document.create) ? window.Document : {
  popupSize: {
    width: 1150,
    height: 950
  },
  iframe: {},
  modeles_ids: null,
  object_class: null,
  object_id: null,
  unique_id: null,
  ext_cabinet_id: null,

  documentCreateCallback: null,
  documentClose: null,

  modal: null,

  /**
   * @param ... A DECRIRE
   */
  create: function(modele_id, object_id, target_id, target_class, switch_mode, onclose, uniqueIdToRefresh) {
    if (!modele_id) return;

    var url = new Url("compteRendu", "edit");
    url.addParam("modele_id", modele_id);
    url.addParam("object_id", object_id);
    url.addParam("unique_id", uniqueIdToRefresh);
    url.addParam("ext_cabinet_id", Document.ext_cabinet_id);
    if (target_id) {
      url.addParam("target_id", target_id);
    }

    if (target_class) {
      url.addParam("target_class", target_class);
    }

    if (switch_mode) {
      url.addParam("switch_mode", switch_mode);
    }

    if (Document.documentCreateCallback) {
      Document.documentCreateCallback(url);
    }

    var multiple_docs = Preferences.multiple_docs == "0" ? "Document" : null;
    let popup = url.popup(Document.popupSize.width, Document.popupSize.height, multiple_docs);

    Document.checkClose(popup);

    Document.documentClose = onclose;
  },

  createPack: function(pack_id, object_id, target_id, target_class, switch_mode) {
    if (!pack_id) return;

    var url = new Url("compteRendu", "edit");
    url.addParam("pack_id", pack_id);
    url.addParam("object_id", object_id);
    url.addParam("ext_cabinet_id", Document.ext_cabinet_id);
    url.addParam("verify", 0);

    if (target_id){
      url.addParam("target_id", target_id);
    }

    if(target_class){
      url.addParam("target_class", target_class);
    }

    if (switch_mode) {
      url.addParam("switch_mode", switch_mode);
    }

    var multiple_docs = Preferences.multiple_docs == "0" ? "Document" : null;
    url.popup(Document.popupSize.width, Document.popupSize.height, multiple_docs);
  },

  createUnmergePack: function(pack_id, object_id, object_class) {
    var liste_cr = [];
    $$("input.cr").each(function (input) {
      if(input.checked){
        liste_cr.push(input.name);
      }
    });
    Control.Modal.close();
    new Url('compteRendu', 'do_pack_multi_aed', 'dosql')
      .addParam('pack_id', pack_id)
      .addParam('object_id', object_id)
      .addParam('object_class', object_class)
      .addParam('_ext_cabinet_id', Document.ext_cabinet_id)
      .addParam('callback', 'Document.afterUnmerge')
      .addParam("liste_cr[]", liste_cr, true)
      .requestUpdate('systemMsg', {method: 'POST'});
  },

  fastMode: function(object_class, modele_id, object_id, unique_id, just_save) {
    if (!modele_id) return;

    var url = new Url("compteRendu", "editFast");
    url.addParam("modele_id"   , modele_id);
    url.addParam("object_id"   , object_id);
    url.addParam("target_id"   , object_id);
    url.addParam("target_class", object_class);
    url.addParam("unique_id"   , unique_id);
    url.addParam("ext_cabinet_id", Document.ext_cabinet_id);
    if (just_save) {
      url.addParam("force_fast_edit", 1);
    }
    url.addParam("just_save"   , just_save ? 1 : 0);
    url.requestModal(750, 400, {onClose: function() {
      // En mode non fusion et édition rapide de pack
      // A la fermeture de la modale, lancement du modèle suivant
      if (Document.modeles_ids && Document.modeles_ids.length) {
        Document.fastMode(Document.object_class, Document.modeles_ids.shift(), Document.object_id, Document.unique_id, true);
      }
    }});
  },

  fastModePack: function(pack_id, object_id, object_class, unique_id, modeles_ids) {
    if (!pack_id) return;

    // Mode normal
    if (!modeles_ids) {
      var url = new Url("compteRendu", "editFast");
      url.addParam("pack_id", pack_id);
      url.addParam("object_id", object_id);
      url.addParam("unique_id", unique_id);
      url.addParam("ext_cabinet_id", Document.ext_cabinet_id);
      url.requestModal(750, 400);
      return;
    }

    // Mode un doc par modèle du pack (lancement du premier modèle)
    Document.modeles_ids  = modeles_ids.split("|");
    Document.object_class = object_class;
    Document.object_id    = object_id;
    Document.unique_id    = unique_id;

    Document.fastMode(object_class, Document.modeles_ids.shift(), object_id, unique_id, true);
  },

  edit: function(compte_rendu_id, force, onclose, unique_id) {
    let window_name = "Document";
    if (Preferences.multiple_docs != "0" || force) {
      window_name = "cr_" + compte_rendu_id;
    }
    let popup = new Url("compteRendu", "edit")
      .addParam("compte_rendu_id", compte_rendu_id)
      .addParam('unique_id', unique_id)
      .popup(Document.popupSize.width, Document.popupSize.height, window_name);

    Document.checkClose(popup);

    Document.documentClose = onclose;
  },

  del: function(form, doc_view, unique_id) {
    var oConfirmOptions = {
      typeName: "le document",
      objName: doc_view,
      target: "systemMsg"
    };

    var oAjaxOptions = Document.refreshList.curry($V(form.file_category_id), $V(form.object_class), $V(form.object_id), null, unique_id);

    confirmDeletion(form, oConfirmOptions, oAjaxOptions);
  },

  cancel: function(form) {
    if (confirm($T("CFile-comfirm_cancel"))) {
      $V(form.annule, 1);
      onSubmitFormAjax(form, Document.refreshList.curry($V(form.file_category_id), $V(form.object_class), $V(form.object_id), 0));
    }
    return false;
  },

  restore: function(form) {
    return onSubmitFormAjax(form, Document.refreshList.curry($V(form.file_category_id), $V(form.object_class), $V(form.object_id), 0));
  },

  refreshList: function(category_id, object_class, object_id, only_docs, uniqueIdToRefresh) {
    var selector = printf("div.documents-%s-%s", object_class, object_id);
    $$(selector).each(function(elt) {
      Document.refresh(elt, null, only_docs, category_id, uniqueIdToRefresh);
    });
    if (window.loadAllDocs) {
      loadAllDocs();
    }
    if (window.DocumentV2) {
      selector = printf("div.documentsV2-%s-%s", object_class, object_id);
      $$(selector).each(function(elt) {
        DocumentV2.refresh(elt);
      });
    }
  },

  /**
   * Mode normal|collapse Defaults to normal
   */
  register: function(object_id, object_class, praticien_id, container, mode, options) {
    if (!object_id || !object_class) return;

    options = Object.extend({
      mode: "normal",
      categories: "hide"
    }, options);

    if (options.ext_cabinet_id) {
      Document.ext_cabinet_id = options.ext_cabinet_id;
    }

    mode = mode || "normal";

    var element = $(container);

    if (!element) {
      console.warn(container+" doesn't exist");
      return;
    }

    var div = new Element("div", {style: "min-width:260px;"+((mode != "hide") ? "min-height:50px;" : "")});
    div.className = printf("documents-%s-%s praticien-%s mode-%s", object_class, object_id, praticien_id, mode);
    $(element).insert(div);
    Document.refresh(div, null, 0);
  },

  refresh: function(container, oOptions, only_docs, category_id, uniqueIdToRefresh) {
    var matches = container.className.match(/documents-(\w+)-(\d+) praticien-(\d*) mode-(\w+)/);

    if (!matches) {
      console.warn(printf("'%s' is not a valid document container", container.className));
      return;
    }

    oOptions = Object.extend({
      object_class: matches[1],
      object_id   : matches[2],
      praticien_id: matches[3],
      mode        : matches[4]
    }, oOptions);

    var url = new Url("compteRendu", "httpreq_widget_documents");
    url.addParam("object_class", oOptions.object_class);
    url.addParam("object_id"   , oOptions.object_id);
    url.addParam("praticien_id", oOptions.praticien_id);
    url.addParam("mode"        , oOptions.mode);
    url.addParam("unique_id"   , uniqueIdToRefresh);

    // When two doc widget with the same args in the same page, the ajax request is down ONCE !!!
    url.addParam("_dummyarg_"  , container.identify());

    if ((only_docs == undefined || only_docs == 1) && container.down("table tbody.docs_container_" + category_id)) {
      url.addParam("only_docs", 1);
      url.addParam("category_id", category_id);
      url.requestUpdate('Category-documents-' + (uniqueIdToRefresh ? (uniqueIdToRefresh + '-') : '') + oOptions.object_class + '-' + category_id);
      return;
    }

    url.requestUpdate(container);
  },

  print: function(document_id) {
    var oIframe = Element.getTempIframe();
    var url = new Url("compteRendu", "ajax_get_document_source");
    url.addParam("dialog"         , 1);
    url.addParam("suppressHeaders", 1);
    url.addParam("update_date_print", 1);
    url.addParam("compte_rendu_id", document_id);
    var sUrl = url.make();

    if (Prototype.Browser.IE) {
      oIframe.onload = null;
      oIframe.onreadystatechange = function(){
        if (oIframe.readyState !== "complete") {
          return;
        }
        oIframe.contentWindow.document.execCommand("print", false, null);
        oIframe.onreadystatechange = null;
      }
    }
    else {
      oIframe.onload = function() {
        if (document.documentMode) {
          window.frames[oIframe.name].window.document.execCommand("print", false, null);
        }
        else {
          window.frames[oIframe.name].print();
        }
      };
    }
    oIframe.src = sUrl;
  },

  printPDF: function(document_id, signature_mandatory, valide) {
    if (signature_mandatory && !valide &&
        !confirm($T("CCompteRendu.ask_force_print"))) {
      return;
    }
    var url = new Url("compteRendu", "ajax_pdf");
    url.addParam("suppressHeaders", 1);

    if (this.iframe[document_id]) {
      this.iframe[document_id].remove();
    }

    this.iframe[document_id] = Element.getTempIframe();
    url.pop(0, 0, "Download PDF", null, null, {
      compte_rendu_id: document_id,
      stream: 1,
      update_date_print: 1}, this.iframe[document_id]);
  },

  printPDFs: (docitems_ids) => {
    let url = new Url('compteRendu', 'print_docs', 'raw');

    Object.keys(docitems_ids).each((docitem_id) => {
      url.addParam('nbDoc[' + docitem_id + ']', 1)
    });

    url.open();
  },

  printSelDocs: function(object_id, object_class, callback) {
    new Url("compteRendu", "print_select_docs")
      .addParam("object_id"   , object_id)
      .addParam("object_class", object_class)
      .requestModal(null, null, {
      onClose: function () {
        callback();
      }
    });
  },

  afterUnmerge: function(compte_rendu_id, obj) {
    Document.refreshList(obj.file_category_id, obj.object_class, obj.object_id);
    Document.edit(compte_rendu_id);
  },

  removeAll: function(oButton, object_guid) {
    var oOptions = {
      typeName: 'tous les documents',
      objName: '',
      target: 'systemMsg'
    };

    object_guid = object_guid.split('-');
    var oAjaxOptions = Document.refreshList.curry(null, object_guid[0], object_guid[1]);
    confirmDeletion(oButton.form, oOptions, oAjaxOptions);
  },

  showCancelled: function(button) {
    button.up("div").select("tr.doc_cancelled").invoke("toggle");
  },

  createDocAutocomplete: function(object_class, object_id, unique_id, input, selected) {
    $V(input, '');
    var id = selected.down(".id").innerHTML;
    var call_object_class = null;

    if (id == 0) {
      call_object_class = object_class;
    }

    if (selected.select(".fast_edit").length) {
      Document.fastMode(call_object_class, id, object_id, unique_id);
    } else {
      Document.create(id, object_id, null, call_object_class, null, null, unique_id);
    }
  },

  createPackAutocomplete: function(object_class, object_id, unique_id, input, selected) {
    $V(input, '');
    if (selected.select(".fast_edit").length) {
      Document.fastModePack(selected.down(".id").innerHTML, object_id, object_class, unique_id, selected.select(".merge_docs").length ? selected.get("modeles_ids") : null);
    }
    else if (selected.select(".merge_docs").length){
      if (selected.down(".pack_is_eligible").innerHTML == 1){
        Document.chooseModeleInPack(selected.down(".id").innerHTML, object_id, object_class);
      }
      else{
        Document.createUnmergePack(selected.down(".id").innerHTML, object_id, object_class);
      }

    }
    else {
      Document.createPack(selected.down(".id").innerHTML, object_id);
    }
  },

  sendDocuments: function(object_class, object_id) {
    new Url('compteRendu', 'ajax_vw_send_documents')
      .addParam('object_class', object_class)
      .addParam('object_id', object_id)
      .requestModal('80%', '80%');
  },

  /**
   * Choisir les modèles d'un pack qui seront générés
   *
   * @param pack_id
   * @param object_id
   * @param object_class
   */
  chooseModeleInPack: function(pack_id, object_id, object_class) {
    if (!pack_id) return;
    new Url("compteRendu", "ajax_choose_compte_rendu")
      .addParam("pack_id", pack_id)
      .addParam("object_id", object_id)
      .addParam('object_class', object_class)
      .addParam('_ext_cabinet_id', Document.ext_cabinet_id)
      .addParam('callback', 'Document.afterUnmerge')
      .requestModal('80%', '80%')
  },

  checkClose: (popup) => {
    let timer = setInterval(function() {
      if (popup.oWindow.closed) {
        clearInterval(timer);
        let compte_rendu_id = popup.oWindow.ReadWriteTimer.compte_rendu_id;
        let modele_id       = popup.oWindow.ReadWriteTimer.modele_id;
        let doc_id_time     = compte_rendu_id ? compte_rendu_id : modele_id;

        if (doc_id_time) {
          Document.saveReadTime(doc_id_time, popup.oWindow.ReadWriteTimer.getTime());
        }

        Document.clearOpener(compte_rendu_id);

        if (window.Board) {
          if (window.App.tab === "viewDay") {
            let form = document.getElementById("editPrefShowAllDocs");
            Board.updateDocuments(form);
          } else if (window.App.tab === "tdbSecretaire") {
            let form = document.getElementById("selectPraticien");
            if (form.praticiens.value === '[]') {
              Board.reloadDocuments([], form);
            } else {
              Board.reloadDocuments(Array.from(form.praticiens.value.slice(1, -1).split(',')), form);
            }
          }
        }
      }
    }, 1000);
  },

  saveReadTime: (compte_rendu_id, read_time) => {
    new Url()
      .addParam('@class', 'CCompteRendu')
      .addParam('compte_rendu_id', compte_rendu_id)
      .addParam('_add_duree_lecture', read_time)
      .requestJSON(Prototype.emptyFunction, {method: 'POST'});
  },

  saveAndSetFast: (modele_id) => {
    new Url()
      .addParam('@class', 'CCompteRendu')
      .addParam('compte_rendu_id', modele_id)
      .addParam('fast_edit', 1)
      .addParam('fast_edit_pdf', 1)
      .requestUpdate('systemMsg', {method: 'POST', onComplete: Control.Modal.close});
  },

  deleteDoc: (compte_rendu_id, file_category_id, object_class, object_id, unique_id) => {
    if (!confirm($T('CCompteRendu-Confirm deletion'))) {
      return;
    }

    new Url()
      .addParam('@class', 'CCompteRendu')
      .addParam('compte_rendu_id', compte_rendu_id)
      .addParam('del', 1)
      .requestUpdate(
        'systemMsg',
        {
          method: 'POST',
          onComplete: () => {
            Document.refreshList(file_category_id, object_class, object_id, unique_id);
            Control.Modal.close();
          }
        }
      )
  },

  showAllStatut: function (compte_rendu_id) {
    new Url("compteRendu", "showAllStatut")
      .addParam("compte_rendu_id", compte_rendu_id)
      .requestModal("50%", "70%")
  },

  clearOpener: (compte_rendu_id) => {
      if (!compte_rendu_id) {
          return;
      }

      new Url('compteRendu', 'clearOpener')
          .addParam('compte_rendu_id', compte_rendu_id)
          .requestJSON();
  }
};

