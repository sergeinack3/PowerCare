{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $templateManager->editor != "ckeditor"}}
  {{mb_return}}
{{/if}}

{{mb_script path="lib/ckeditor/ckeditor.js"}}

<style type="text/css">
  #cke_htmlarea {
    border: none;
  }
  .cke_dialog_ui_vbox {
    height: 100%;
  }
  /* Ugly hack to display labels with plugin buttons */
  .cke_button__mbfields_label,
  .cke_button__mbhelpers_label,
  .cke_button__mbfreetext_label,
  .cke_button__mblists_label,
  .cke_button__mbbenchmark_label
    { display: inline !important; }

  .cke_button__mbbenchmark_icon {
    display: none!important;
  }
</style>

<script>
  window.time_before_thumbs = {{"dPcompteRendu CCompteRenduPrint time_before_thumbs"|gconf}};
  window.time_before_thumbs *= 1000;

  window.nb_lists = {{$templateManager->usedLists|@count}};
  window.nb_textes_libres = {{$templateManager->textes_libres|@count}};

  window.keystrokes_autocap = keystrokes = {32: '', 188: ',', 2228414: '.', 186: ':', 191: ':', 49: '!', 223: '!', 2228415: '?', 2228412: '?'};

  initCKEditor = function() {
    window.old_source = $("htmlarea").value;
    CKEDITOR.replace("htmlarea", {customConfig: "../../?m=compteRendu&raw=mb_fckeditor"});

    {{if $templateManager->font != ""}}
      CKEDITOR.addCss("body { font-family: {{$templateManager->font}} }");
    {{else}}
      CKEDITOR.addCss("body { font-family: {{$conf.dPcompteRendu.CCompteRendu.default_font}} }");
    {{/if}}

    {{if $templateManager->size != ""}}
      CKEDITOR.addCss("body { font-size: {{$templateManager->size}} }");
    {{else}}
      CKEDITOR.addCss("body { font-size: {{"dPcompteRendu CCompteRendu default_size"|gconf}} }");
    {{/if}}

    CKEDITOR.on("instanceReady", function(e) {
      var editor = e.editor;

      // Onbeforeunload called on IE after closing a dialog box
      if (CKEDITOR.env.ie) {
        editor.on("dialogShow", function(dialogShowEvent) {
          $(dialogShowEvent.data._.element.$).select('a[href*="void(0)"]').invoke("removeAttribute", "href");
        });
      }

      window.resizeEditor = function() {
        var greedyPane = $$(".greedyPane")[0];

        if (!greedyPane) {
          return;
        }

        var dims = document.viewport.getDimensions();
        var top_offset = greedyPane.hasClassName("message_input") ? 50 : 10;

        editor.resize("", (dims.height - greedyPane.cumulativeOffset().top - top_offset));

        var height = (dims.height - greedyPane.cumulativeOffset().top - top_offset);

        // Surcharge de l'erreur de calcul de la bibliothèque sur le nouveau thème
        height -= greedyPane.down('span').getDimensions().height + 28;

        var style_height = window.style_height || DOM.style();

        if (!window.style_height) {
          window.style_height = style_height;
          document.body.insert(style_height);
        }

        style_height.update('.cke_contents { height: ' + height + 'px !important; } ');

        if (window.Preferences.pdf_and_thumbs == 1) {
          $("thumbs").style.height = (dims.height - greedyPane.cumulativeOffset().top - top_offset) + "px";
        }
      };

      {{if !$templateManager->valueMode}}
      var plugins = ["source", "undo", "redo", "pastefromword", "mbprint"];
      window.toggleContentEditable = function(state, obj) {
        if (Object.isUndefined(obj)) {
          obj = {data: null};
        }

        if (editor.document == null || (obj.data && plugins.indexOf(obj.data.name) != -1)) return;

        if (Prototype.Browser.IE) {
          var spans = editor.document.getBody().getElementsByTag("span").$;
          for (var i in spans) {
            var span = spans[i];
            if (span && span.className && (Element.hasClassName(span, "field") || Element.hasClassName(span, "name"))) {
              if (state) {
                span.removeAttribute("contentEditable");
              }
              else {
                span.contentEditable = false;
              }
            }
          }
          return;
        }

        var spans_by_class = [];
        spans_by_class[0] = editor.document.$.getElementsByClassName("field");
        spans_by_class[1] = editor.document.$.getElementsByClassName("name");

        for (var s = 0; s < spans_by_class.length; s++) {
          var spans = spans_by_class[s];

          if (spans.length) {
            for (var i = 0; i < spans.length; i++) {
              var span = spans[i];

              if (state) {
                span.removeAttribute("contentEditable");
              }
              else {
                span.contentEditable = false;
              }
            }
          }
        }
      };
      window.toggleContentEditable(false);

      editor.on("beforeCommandExec" , window.toggleContentEditable.curry(true));
      editor.on("afterCommandExec"  , window.toggleContentEditable.curry(false));
      editor.on("beforeCombo"       , window.toggleContentEditable.curry(true));
      editor.on("afterCombo"        , window.toggleContentEditable.curry(false));
      editor.on("beforeRenderColors", window.toggleContentEditable.curry(true));
      editor.on("afterRenderColors" , window.toggleContentEditable.curry(false));
      {{/if}}

      // Redimensionnement de l'éditeur
      window.resizeEditor();

      // Redimensionnement automatique de l'éditeur en même temps que celui de la fenêtre.
      Event.observe(window, "resize", function() {
        window.resizeEditor();
      });

      {{if $templateManager->printMode}}
      editor.setReadOnly();
        let mbprintpdf  = editor.getCommand("mbprintPDF");
        let usermessage = editor.getCommand("usermessage");
        let apicrypt    = editor.getCommand("apicrypt");
        let medimail    = editor.getCommand('medimail');
        let mssanteIHeXDM = editor.getCommand('mssanteIHEXDM');
        let mssante     = editor.getCommand('mssante');
        if (mbprintpdf) {
          mbprintpdf.setState(CKEDITOR.TRISTATE_OFF);
        }
        if (usermessage) {
          usermessage.setState(CKEDITOR.TRISTATE_OFF);
        }
        if (apicrypt) {
          apicrypt.setState(CKEDITOR.TRISTATE_OFF);
        }
        if (medimail) {
          medimail.setState(CKEDITOR.TRISTATE_OFF);
        }
        if (mssanteIHeXDM) {
          mssanteIHeXDM.setState(CKEDITOR.TRISTATE_OFF);
        }
        if (mssante) {
          mssante.setState(CKEDITOR.TRISTATE_OFF);
        }

      {{else}}
      editor.document.getBody().on("keydown", autoCapHelper);
        if (Preferences.pdf_and_thumbs && window.Thumb) {
          Thumb.content = editor.getData();
          window.thumbs_timeout = setTimeout(function() {
            Thumb.refreshThumbs(1);
          }, time_before_thumbs);
        }

        if (Prototype.Browser.IE) {
          window.save_style = deleteStyle();
          editor.on("beforePreview", function() { restoreStyle(); });
          editor.on("afterPreview" , function() { window.save_style = deleteStyle(); });
          editor.on("beforeSource" , function() { editor.fire("beforePreview"); });
          editor.on("afterSource"  , function() { editor.fire("afterPreview"); });
        }

        // Don't close the window with escape
        document.stopObserving('keydown', closeWindowByEscape);

        // Surveillance de modification de l'éditeur de texte
        if (window.Thumb) {
          editor.on("key", function() {
            loadOld();
            Thumb.stopRefreshPeriodical();
            Thumb.refreshPeriodical();
          });
        }
      {{/if}}

      document.observe("keydown", function (e) {
        var key = Event.key(e);

        if (e.altKey && key == 73) {
          var iframe = $$("iframe[name=download_pdf]")[0];
          if (iframe.style.width == "0px") {
            iframe.setStyle(
              {
                width:    "100%",
                height:   "400px",
                border:   "1px solid grey",
                top:      "",
                position: "relative"
              });
          }
          else {
            iframe.setStyle(
              {
                width:    "0px",
                height:   "0px",
                border:   "",
                top:      "-1000px",
                position: "absolute"
              });
          }
        }
      });
    });
  };

  Main.add(initCKEditor);

  autoCapHelper = function(event) {
    var editor = event.sender.editor;

    var mbcap     = editor.getCommand('mbcap');
    var mbreplace = editor.getCommand('mbreplace');

    if (!mbcap) {
      return;
    }

    if (mbcap.state === CKEDITOR.TRISTATE_OFF && mbreplace.state === CKEDITOR.TRISTATE_OFF) {
      return;
    }

    var keystroke = event.data.getKeystroke();

    // Majuscule auto
    if (mbcap.state === CKEDITOR.TRISTATE_ON && keystroke >= 65 && keystroke <= 90) {
      autoCapInsert(event, keystroke);
    }

    // Remplacement d'aide à la saisie (après un espace, virgule, point, deux points, point d'exclamation, point d'interrogation)

    if (mbreplace.state === CKEDITOR.TRISTATE_ON && keystroke in window.keystrokes_autocap) {
      helperInsert(event, keystroke);
    }
  };

  autoCapInsert = function(event, keystroke) {
    var editor = CKEDITOR.instances.htmlarea;
    var range, walker, selection, native, chars, data;

    selection = editor.getSelection();
    range = selection.getRanges()[0];
    range.setStartAt(editor.document.getBody(), CKEDITOR.POSITION_AFTER_START);
    walker = new CKEDITOR.dom.walker(range);

    var node = walker.previous();

    if (!node) {
      return insertUpperCase(editor, event, keystroke);
    }

    native = selection.getNative();

    if ("focusNode" in native && native.focusNode.data) {
      chars = native.focusNode.data.substr(native.anchorOffset-2, +2);
    }

    var elt = node.$;

    if (!Object.isUndefined(elt.innerHTML)) {
      data = elt.innerHTML;
    }
    else if (!Object.isUndefined(elt.data)) {
      data = elt.data;
    }

    if (!Prototype.Browser.IE) {
      data = data.strip();
    }

    // Escape des zero width space characters
    data = data.replace(/[\u200B-\u200D\uFEFF]/g, '');

    if (!Object.isUndefined(elt.data) && !data) {
      if (elt.wholeText.replace(/[\u200B-\u200D\uFEFF]/g, '').trim().length) {
        return;
      }
    }

    var previous = elt.previousElementSibling;
    if (data == "" && elt.nodeName != "BR" && previous && previous.nodeName == "SPAN" && previous.className == "field") {
      return;
    }

    if (
    /* Commence par un retour chariot ou une ligne verticale */
      elt.nodeName === "BR" ||
        data == ""       ||
        (data && data.length == 0) ||
        (Prototype.Browser.IE && !Object.isUndefined(data) && /[\.\?!]\s/.test(data.substr(-2))) ||
        /(<br|<hr)/.test(data) ||
        (native.focusNode && native.focusNode.length == 0) ||
        /* Les 2 derniers caractères sont :
         - un point ou
         - un point d'exclamation ou
         - un point d'interrogation
         et un espace */
        (/[\.\?!]\s/.test(chars))) {
      insertUpperCase(editor, event, keystroke);
    }
  };

  helperInsert = function(event, keystroke) {
    var editor = CKEDITOR.instances.htmlarea;
    var range, selection, selected_ranges, container, chars, text, last_char, last_space;

    // Remplacement d'aide à la saisie (après un espace, virgule, point, deux points, point d'exclamation, point d'interrogation)
    selection = editor.getSelection();
    selected_ranges = selection.getRanges();
    range = selected_ranges[0];
    container = range.startContainer;
    chars = text = container.getText();
    chars = chars.strip().trim();
    last_char = window.keystrokes_autocap[keystroke];

    // Espace insécable pour IE
    if (Prototype.Browser.IE) {
      last_space = chars.lastIndexOf(" ");
    }
    else {
      last_space = chars.lastIndexOf(" ");
    }

    if (last_space != -1) {
      chars = chars.substr(last_space+1);
    }

    chars = chars.toLowerCase();

    $H(helpers[0].options).each(function(categ) {
      var helpers = categ[1];
      if (Object.isUndefined(helpers.length)) {
        $H(helpers).each(function(helper) {
          var key = helper[0];
          if (key.toLowerCase() === chars) {

            var pattern = new RegExp(key + "$", "gi");

            // On insère un espace insécable après le remplacement de l'aide
            container.setText(text.replace(pattern, helper[1] + last_char + " "));
            selection.selectElement(container);
            selected_ranges = selection.getRanges();
            selected_ranges[0].collapse(false);
            selection.selectRanges(selected_ranges);

            event.data.preventDefault();
            throw $break;
          }
        });
      }
    });
  };

  mbfile_onclick = function() {
    var current_dialog = CKEDITOR.dialog.getCurrent();
    if (current_dialog) {
      current_dialog.hide();
    }

    var form = getForm('editFrm');
    new Url('compteRendu', 'vw_select_image')
      .addParam('context_guid', $V(form.object_class) + '-' + ($V(form.object_id) ? $V(form.object_id) : 'none'))
      .addParam('user_id', $V(form.user_id))
      .addParam('function_id', $V(form.function_id))
      .addParam('group_id', $V(form.group_id))
      .requestModal('90%', '90%');
  };

  insertImage = function(file_id) {
    var editor = CKEDITOR.instances.htmlarea;

    editor.focus();

    var html = '<img src="?m=files&raw=thumbnail&profile=large&document_guid=CFile-' + file_id + '" />';
    var elt = CKEDITOR.dom.element.createFromHtml(html, editor.document);

    editor.insertElement(elt);

    // Lancement du plugin d'ajout d'image classique afin de pouvoir redimensionner l'image insérée.
    // L'image doit être sélectionnée pour que le plugin la détecte et propose de la modifier
    var selection = editor.getSelection(elt);
    selection.selectElement(elt);
    editor.selectionChange(true);
    editor.getCommand("image").exec();

    Control.Modal.close();

    return true;
  };
</script>
