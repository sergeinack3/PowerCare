/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

File = (!Object.isUndefined(window.File) && !Object.isUndefined(File.use_mozaic)) ? window.File : {
    use_mozaic: 0,
    ext_cabinet_id: null,

    popup: function (object_class, object_id, element_class, element_id, sfn) {
        var url = new Url;
        url.ViewFilePopup(object_class, object_id, element_class, element_id, sfn);
    },

    upload: function (object_class, object_id, file_category_id, callback) {
        var url = new Url("files", "upload_file");
        url.addParam("object_class", object_class);
        url.addParam("object_id", object_id);
        url.addParam("object_guid", object_class + "-" + object_id);
        url.addParam("file_category_id", file_category_id);
        url.addParam("ext_cabinet_id", File.ext_cabinet_id);
        url.requestModal(700, 500, callback);
    },

    cancel: function (form, object_id, object_class, category_id) {
        if (confirm($T('CFile-comfirm_cancel'))) {
            $V(form.annule, 1);

            var oAjaxOptions = File.refresh.curry(object_id, object_class, 0, category_id);

            if (window.Transport && window.Transport.refreshFile && Control.Modal.stack.length > 0) {
                oAjaxOptions = window.Transport.refreshFile.curry(object_id, object_class);

                if ($V(form.annule)) {
                    oAjaxOptions = Control.Modal.refresh;
                }
            }

            onSubmitFormAjax(form, oAjaxOptions);
        }
        return false;
    },

    restore: function (form, object_id, object_class, category_id) {
        var oAjaxOptions = File.refresh.curry(object_id, object_class, 0, category_id);

        if (window.Transport && window.Transport.refreshFile && Control.Modal.stack.length > 0) {
            oAjaxOptions = Control.Modal.refresh;
        }

        return onSubmitFormAjax(form, oAjaxOptions);
    },

    remove: function (oButton, object_id, object_class) {
        var oOptions = {
            typeName: 'le fichier',
            objName: oButton.form._view.value,
            target: 'systemMsg'
        };
        var oAjaxOptions = File.refresh.curry(object_id, object_class);

        if (window.Transport && window.Transport.refreshFile && object_class == 'CPatient' && Control.Modal.stack.length > 0) {
            oAjaxOptions = window.Transport.refreshFile.curry(object_id, object_class);
        }

        confirmDeletion(oButton.form, oOptions, oAjaxOptions);
    },

    removeAll: function (oButton, object_guid) {
        var oOptions = {
            typeName: 'tous les fichiers',
            objName: '',
            target: 'systemMsg'
        };
        object_guid = object_guid.split('-');
        var oAjaxOptions = File.refresh.curry(object_guid[1], object_guid[0]);
        confirmDeletion(oButton.form, oOptions, oAjaxOptions);
    },

    refreshList: function (object_id, object_class, only_files, show_actions, category_id, target) {
        var url = new Url("files", "httpreq_widget_files");
        url.addParam("object_id", object_id);
        url.addParam("object_class", object_class);
        url.addParam("category_id", category_id);
        url.addParam("mozaic", File.use_mozaic);
        if (!Object.isUndefined(show_actions)) {
            url.addParam("show_actions", show_actions);
        }

        if ((only_files == undefined || only_files == 1) && $("Category-files-" + object_class + "-" + category_id)) {
            url.addParam("only_files", 1);
            target = $('Category-files-' + object_class + '-' + category_id);
        }

        if (target.up(".name_readonly")) {
            url.addParam("name_readonly", 1);
        }

        url.requestUpdate(target);
    },

    refresh: function (object_id, object_class, only_files, category_id, show_actions) {
        var selector = printf("div.files-%s-%s", object_class, object_id);
        $$(selector).each(function (elt) {
            File.refreshList(object_id, object_class, only_files, show_actions, category_id, elt);
        });
    },

    register: function (object_id, object_class, container, show_actions, category_id, options) {
        var div = new DOM.div({style: 'min-width:200px; min-height:50px;'});
        div.className = printf("files-%s-%s", object_class, object_id);
        $(container).insert(div);

        options = Object.extend({}, options);

        if (options.ext_cabinet_id) {
            File.ext_cabinet_id = options.ext_cabinet_id
        }

        Main.add(function () {
            File.refresh(object_id, object_class, 0, category_id, show_actions);
        });
    },

    createMozaic: function (context_guid, category_id, callback) {
        var url = new Url("files", "ajax_img_to_document");
        url.addParam("context_guid", context_guid);
        url.addParam("category_id", category_id);
        url.requestModal("1024", "768", {
            onClose: function () {
                if (callback) {
                    callback();
                } else {
                    var parts = context_guid.split("-");
                    File.refresh(parts[1], parts[0]);
                }
            }
        });
    },

    editNom: function (guid) {
        var form = getForm("editName-" + guid);
        $("readonly_" + guid).toggle();
        $("buttons_" + guid).toggle();
        var input = form.file_name;

        if ($(input).getStyle("display") == "inline-block") {
            $(input).setStyle({display: "none"});
            $V(input, input.up().previous().innerHTML);
        } else {
            $(input).setStyle({display: "inline-block"});
            // Focus et sélection de la sous-chaîne jusqu'au dernier point
            input.focus();
            input.caret(0, $V(input).lastIndexOf("."));
        }
    },

    toggleClass: function (element) {
        if (element.hasClassName("edit")) {
            element.removeClassName("edit");
            element.addClassName("undo");
        } else {
            element.removeClassName("undo");
            element.addClassName("edit");
        }
    },

    reloadFile: function (object_id, object_class, id) {
        var url = new Url("files", "ajax_reload_line_file");
        url.addParam("id", id);
        url.addParam("dialog", 1);
        url.addParam("object_id", object_id);
        url.addParam("object_class", object_class);
        var elt = $("list_" + object_class + object_id);
        if (elt.up().up().hasClassName("name_readonly")) {
            url.addParam("name_readonly", 1);
        }

        url.requestUpdate("td_CFile-" + id);
    },

    /**
     * Check file name
     *
     * @param file_name
     * @returns {boolean}
     */
    checkFileName: function (file_name) {
        if (file_name.match(/[<>/\\]/g)) {
            alert($T("CFile-error-File name cannot contain ban characters"));
            return false;
        }

        return true;
    },

    switchFile: function (id, form, event) {
        if (!event) {
            event = window.event;
        }
        if (Event.key(event) != 9) {
            return true;
        }

        // On annule le comportement par défaut
        if (event.stopPropagation) {
            event.stopPropagation();
        }

        if (event.preventDefault) {
            event.preventDefault();
        }

        event.returnValue = false;

        if (File.checkFileName($V(form.file_name))) {
            form.onsubmit();
            var current_tr = $("tr_CFile-" + id);

            // S'il y a un fichier suivant, alors on simule le onclick sur le bouton de modification
            if (next_tr = current_tr.next()) {
                var button = next_tr.down(".edit");
                // Si le bouton d'édition n'existe pas, alors on focus sur l'input pour le changement de nom
                if (button == undefined) {
                    var input = next_tr.select("input[type='text']")[0];
                    input.focus();
                    input.caret(0, $V(input).lastIndexOf("."));
                } else {
                    button.onclick();
                }
            }
        }

        return false;
    },

    renameFile: function (file_id) {
        new Url("files", "ajax_rename_file")
            .addParam("file_id", file_id)
            .requestModal("20%", null);
    },

    showCancelled: function (button, table) {
        table.select("tr.file_cancelled").invoke("toggle");
    },

    openProtocoleDocItems: function (button, type, contextClass, contextId) {
        new Url("files", "ajax_docitems_context")
            .addParam("context_class", contextClass)
            .addParam("context_id", contextId)
            .addParam("type", type)
            .requestModal(800, 400, {
                onClose: function () {
                    new Url("files", "ajax_count_docitems_context")
                        .addParam("context_class", contextClass)
                        .addParam("context_id", contextId)
                        .addParam("type", type)
                        .requestJSON(function (count) {
                            button.down("span").update("(" + count + ")");
                        });
                }
            });
    },
    readFile: function(docitem_id, docitem_class, object_id, object_class, uid_unread) {
        new Url('files', 'readFile')
        .addParam('docitem_id', docitem_id)
        .addParam('docitem_class', docitem_class)
        .addParam('object_id', object_id)
        .addParam('object_class', object_class)
        .addParam('uid_unread', uid_unread)
        .requestUpdate('tooltip_file_' + object_id + '_' + uid_unread);
    },
    controleTab: function (element_to_show) {
      let tab = ['tab-group', 'tab-func', 'tab-user'];
      $(element_to_show).show();
      tab.forEach(function (element_to_hide) {
        if (element_to_hide !== element_to_show) {
          $(element_to_hide).hide()
        }
      })
    },
    searchByFactory: function(doc_class, factory){
      new Url("files","incVwStats")
        .addParam("factory", factory)
        .addParam("doc_class", doc_class)
        .requestUpdate("results_stats");
    }
};
