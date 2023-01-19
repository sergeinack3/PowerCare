/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

(function () {
  var FileUpload = Class.create({
    initialize: function (input, options) {
      options = Object.extend({
        maxSize: 1024 * 1024 * 4
      }, options);

      function appendFile(file, container) {
        var preview = DOM.div({className: "inline-upload-file"});
        var thumbnail = DOM.div({className: "inline-upload-thumbnail"});
        var info = DOM.div({className: "inline-upload-info"});

        preview.insert(thumbnail).insert(info);

        // In drag and drop, files properties of the input is empty
        // Check if file object is a File
        // (lastModified property only in drag and drop and file selector, not in copy paste)
        if (!input.files.length && file.lastModified) {
          let container = new DataTransfer();
          container.items.add(file);
          input.files = container.files;
        }

        var inputHidden = DOM.input({
          type: "text",
          "data-blob": "blob",
          name: "formfile[]",
          value: file.name
        });

        inputHidden.store("blob", file);

        info.insert(inputHidden);

        info.insert(DOM.div({}, Number.toDecaBinary(file.size)));

        if (/image\/.*/.exec(file.type)) {
          var imgThumb = DOM.img({}).hide();

          var img = DOM.div({
            title: "Cliquer pour fermer",
            className: "magnified"
          }).hide();

          var fileReader = new FileReader();
          fileReader.onloadend = function (e) {
            var uri = e.target.result;

            imgThumb.src = uri;
            imgThumb.show().observe("click", function () {
              img.show();
            });
            img.observe("click", function () {
              img.hide();
            });

            var tmpImg = new Image();
            tmpImg.src = uri;
            tmpImg.onload = function () {
              info.insert(DOM.div({}, tmpImg.width + " x " + tmpImg.height));
            };

            img.style.backgroundImage = "url(" + uri + ")";

            thumbnail.insert(img).insert(imgThumb);
          };

          fileReader.readAsDataURL(file);
        }
        else {
          var ext = file.name.split('.').pop().toLowerCase();
          var icons = {
            zip: "far fa-file-archive",
            tar: "far fa-file-archive",
            '7z': "far fa-file-archive",
            tgz: "far fa-file-archive",
            'tar.gz': "far fa-file-archive",
            doc: "far fa-file-word",
            docx: "far fa-file-word",
            pdf: "far fa-file-pdf",
            mov: "far fa-file-video",
            mpg: "far fa-file-video",
            mpeg: "far fa-file-video",
            avi: "far fa-file-video",
            wmv: "far fa-file-video",
            mp4: "far fa-file-video",
            ppt: "far fa-file-powerpoint",
            pptx: "far fa-file-powerpoint",
            mp3: "far fa-file-audio",
            wav: "far fa-file-audio",
            ogg: "far fa-file-audio",
            xls: "far fa-file-excel",
            xlsx: "far fa-file-excel",
            csv: "far fa-file-excel",
            txt: "far fa-file-alt",
            rtf: "far fa-file-alt",
            odt: "far fa-file-alt"
          };

          var icon = icons[ext] || "far fa-file-alt";

          thumbnail.insert(DOM.i({className: icon}));
        }

        if (file.size > options.maxSize) {
          info.insert(DOM.div({
              className: "warning"
            },
            $T("common-msg-File is too large, max file size is %s", Number.toDecaBinary(options.maxSize))
          ));
        }

        info.insert(DOM.button({
          className: "inline-upload-trash far fa-trash-alt notext", type: "button"
        }).observe("click", function (e) {
          Event.element(e).up(".inline-upload-file").remove();
        }));

        container.insert(preview);
      }

      var inline = input.up('.inline-upload');
      var container = inline.down(".inline-upload-files");

      // File input, drag / drop
      input.observe("change", (function (e) {
        var container = inline.down(".inline-upload-files");

        $A(this.files).each(function (file) {
          appendFile(file, container);
        }, this);
      }).bindAsEventListener(input));

      if (!window._uploadInitialized) {
        // Block the "dragover" event
        document.addEventListener('dragover', function (e) {
          e.stopPropagation();
          e.preventDefault();

          $$(".inline-upload-input").invoke("addClassName", "inline-upload-drag");
        }, false);

        // Block the "dragover" event
        document.addEventListener('dragleave', function (e) {
          e.stopPropagation();
          e.preventDefault();

          $$(".inline-upload-input").invoke("removeClassName", "inline-upload-drag");
        }, false);

        // Handle the "drop" event
        document.addEventListener('drop', function (e) {
          e.stopPropagation();
          e.preventDefault();

          var elt = Event.element(e);
          if (elt.hasClassName("inline-upload-input") || elt.up(".inline-upload-input")) {
            var container = elt.up(".inline-upload").down(".inline-upload-files");

            $A(e.dataTransfer.files).each(function (file) {
              appendFile(file, container);
            });
          }

          $$(".inline-upload-input").invoke("removeClassName", "inline-upload-drag");
        }, false);

        window._uploadInitialized = true;
      }

      var pastearea = jQuery(inline.down('.inline-upload-pastearea'));
      pastearea.pastableNonInputable();
      pastearea.on('pasteImage', function (evt, data) {
        var file = data.blob;

        if (file.type) {
          file.name = "Image." + (file.type.split(/\//)[1]);
        }
        else {
          file.name = "Image.png";
        }

        appendFile(file, container);
      });
    }
  });

  define(function () {
    return FileUpload;
  });
})();
