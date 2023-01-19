/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

FileMbHost = window.FileMbHost || {
  extensions:       null,
  extensions_thumb: null,
  paths:            null,
  timeoutID:        null,
  file_category_id: null,
  default_cat_id:   null,
  default_cat_view: null,
  object_guid:      null,

  periodicalUpdateCount: function () {
    MbHost.call('fs/file/count',
      {"extensions": FileMbHost.extensions},
      function (result) {
        var buttons = $$(".mbhostbutton");
        buttons.each(function (button) {
          if (result) {
            button.disabled = "";
            clearInterval(FileMbHost.timeoutID);
            FileMbHost.timeoutID = null;
          }
          button.style.border = result ? "2px solid #0a0" : "1px solid #888";
        });
      },
      function (error) {
        clearInterval(FileMbHost.timeoutID);
        FileMbHost.timeoutID = null;
      }
    );
  },

  initTimer: function () {
    if (FileMbHost.timeoutID) {
      return;
    }

    FileMbHost.timeoutID = setInterval(FileMbHost.periodicalUpdateCount, 3000);
  },

  modalUpload: function (object_guid) {
    MbHost.call('fs/file/list', {
        'extensions':       FileMbHost.extensions,
        'extensions_thumb': FileMbHost.extensions_thumb,
        'maxsize':          10240000
      },
      function (result) {
        var ts = Date.now();
        var form = DOM.form({
          onsubmit:  'return false;',
          name:      'mbhost-upload-form',
          className: "mbhost-uploader"
        });

        var modal = FileMbHost.getModalElement();
        
        if (result.error) {
          var msg = DOM.div(
            {
              className: "small-error"
            }, 
            "Une erreur s'est produite, il est possible que le répértoire soit mal paramétré (Erreur: " + result.msg + ")",
            "<br />",
            DOM.button({className: "fa fa-cogs"}, "Accéder au paramétrage").observe("click", function() { MbHost.call("system/system/config"); })
          );
          
          modal.update(msg);
        }
        else {
          var i = 0;
          var keys = [];
          for (var key in result) {
            if (result.hasOwnProperty(key) && key != "install_informations") {
              keys.push(key);
            }
          }
          keys.sort(function(a, b) {
            return a.localeCompare(b, undefined, {sensitive: "base", numeric: true});
          });
          var result_hash = $H(result);
          keys.each(function (key) {
            var pair = result_hash.get(key);
            if (!pair.file_name && pair.content == '') {
              return;
            }

            var id = 'thumb-' + ts + '-' + i;

            var thumb = DOM.div({className: 'thumb_mbhost'},
              DOM.input({
                name:      id,
                type:      'checkbox',
                className: 'upload',
                checked:   'checked',
                value:     pair.file_name,
                "data-filename" : pair.file_name
              }),
              DOM.label({'for': id},
                DOM.input({
                  name: "name_" + id,
                  type: "text",
                  value: key
                }).observe("change", function() { this.up('div').down('input').value = $V(this); })
                  .observe("click",  function() { this.caret(0, $V(this).lastIndexOf("."))}),
                pair.content ?
                  DOM.img({
                    src:   'data:image/png;base64,' + pair.content,
                    style: 'max-width: 160px; max-height: 160px;'
                  })
                  : null
              )
            );

            form.insert(thumb);
            i++;
          });

          var counter = DOM.div({id: 'count-' + ts, className: "file_count"}, $T("common-msg-%d file(s) selected", i));
          // form.insert(counter);

          var selector = DOM.div({style: "text-align: center"},
            counter,
            DOM.label({style: "display: inline-block"},
              DOM.input({type: "checkbox", id: "_select_all"+object_guid, checked: true}),
              "Tout sélectionner"
            ).observe("click", FileMbHost.selectAll.curry(counter))
          );

          form.insert(selector);

          var catKeywords, catId;
          var buttons = DOM.div({style: "text-align: center; margin-top: 5px;"},
            catId = DOM.input({
              type: "hidden",
              name: "file_category_id",
              value: modal.dataset.defaultCatId
            }),
            DOM.label({title: $T("CFile-file_category_id-desc")}, $T("CFile-file_category_id"),
              ' ',
              catKeywords = DOM.input({
                size: 40,
                type: "text",
                name: "file_category_keywords",
                value: modal.dataset.defaultCatView
              })
            ),
            DOM.label({},
              DOM.input({type: "checkbox", id: "_del_file_"+object_guid, checked: true}),
              "Supprimer après envoi"
            ),
            DOM.br(),
            DOM.button({type: "button", className: "submit singleclick"}, $T("Send")).observe("click", FileMbHost.sendFiles.curry(object_guid)),
            DOM.button({type: "button", className: "close"}, $T("Close")).observe("click", Control.Modal.close.curry())
          );

          form.insert(buttons);

          modal.update(form);

          var url = new Url("files", "ajax_category_autocomplete");
          url.addParam("object_class", object_guid.split(/-/)[0]);
          FileMbHost.autocompleteCat =
            url.autoComplete(catKeywords, '', {
              minChars: 2,
              dropdown: true,
              width: "312px",
              select: "view",
              valueElement: catId
            });

          form.on("click", '.thumb_mbhost', function (e) {
            var count = getForm('mbhost-upload-form').select('input.upload:checked').length;
            counter.update($T("common-msg-%d file(s) selected", count));
          });
        }

        Modal.open(
          modal.up('div'), {
            onClose: FileMbHost.periodicalUpdateCount,
            title:   $T("CFile-televersement"),
            showClose: true
          }
        );
      },
      function (error) {
      });
  },

  selectAll: function (counter) {
    var form = getForm('mbhost-upload-form');
    var checked = this.firstElementChild.checked;
    form.select('input.upload').each(
      function (element) {
        element.checked = checked;
      });
    var count = form.select('input.upload:checked').length;
    counter.update($T("common-msg-%d file(s) selected", count));
  },

  sendFiles: function (object_guid) {
    var form = getForm('mbhost-upload-form');
    FileMbHost.paths = form.select('input.upload:checked');
    FileMbHost.file_category_id = $V(form.file_category_id);

    if (FileMbHost.paths.length == 0) {
      alert($T("CFile-no_selected"));
      return;
    }

    FileMbHost.sendFile(object_guid);
  },

  sendFile: function (object_guid) {
    var input = FileMbHost.paths.shift();
    var original_filename = input.get("filename");
    var filename = input.value;
    var del_file = $('_del_file_' + object_guid).checked ? 1 : 0;

    MbHost.call("fs/file/getfile",
      {'path': original_filename},
      function (content) {
        if (del_file) {
          MbHost.call("fs/file/delfile", {'path': original_filename});
        }

        var form = getForm("sendFile" + object_guid);
        $V(form.file_name, filename);
        $V(form.content, content);
        $V(form.file_category_id, FileMbHost.file_category_id);

        onSubmitFormAjax(form, function () {
          if (FileMbHost.paths.length > 0) {
            FileMbHost.sendFile(object_guid);
          }
          else {
            var object_class = object_guid.split('-')[0];
            var object_id = object_guid.split('-')[1];

            // Pour le refresh dans les consultations
            if (window.File && File.refresh) {
              File.refresh(object_id, object_class);
            }

            // Pour le refresh dans le dossier patient
            if (window.reloadAfterUploadFile) {
              window.reloadAfterUploadFile();
            }

            // Por le refresh dans l'édition du dossier patient
            if (window.Patient && Patient.reloadListFileEditPatient) {
              Patient.reloadListFileEditPatient('load');
            }

            Control.Modal.close();

            if (window.File && File.refresh) {
              File.refresh()
            }

            if (window.TimelineImplement) {
              TimelineImplement.refreshResume()
            }
          }
        });
      },
      function (error) {
      });
  },

  getModalElement: () => {
    let modal_id = 'mbhost_file_' + FileMbHost.object_guid;
    let modal = $(modal_id);

    if (modal) {
      return modal;
    }

    modal = DOM.div(
      null,
      DOM.div(
        {
          id: modal_id,
          'data-default-cat-id': FileMbHost.default_cat_id ? FileMbHost.default_cat_id : '',
          'data-default-cat-view': FileMbHost.default_cat_view ? FileMbHost.default_cat_view : ''
        }
      )
    );

    modal.setStyle(
      {
        display: 'none',
        margin: '5px',
        width: '880px',
        height: '700px',
        whiteSpace: 'normal',
      }
    );

    $('main').insert(modal);

    return modal.down('div');
  }
};
