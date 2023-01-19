/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

FilesCategory = {
  modal_cat    : null,
  object_guids : [],

  loadList : function() {
    var url = new Url("files", "ajax_list_categories");
    url.addFormData(getForm('listFilter'));
    url.requestUpdate('list_file_category');
  },

  openInfoReadFilesGuid : function(object_guid) {
    var parts = object_guid.split("-");
    var  object_class = parts[0];
    var  object_id    = parts[1];

    var url = new Url('files', "ajax_modal_object_files_category");
    url.addParam('object_guid', object_guid);
    url.requestModal("700", "500");
    url.modalObject.observe('afterClose',
      FilesCategory.iconInfoReadFilesGuid.curry(object_class, [object_id]));
    FilesCategory.modal_cat = url;
  },

  reloadModal : function() {
    if (FilesCategory.modal_cat) {
      FilesCategory.modal_cat.refreshModal();
    }
  },

  addObjectGuid : function(object_guid) {
    FilesCategory.object_guids.push(object_guid);
  },

  showUnreadFiles : function() {
    var tab = {};

    FilesCategory.object_guids.each(function (object_guid) {
      var parts = object_guid.split("-");
      var  object_class = parts[0];
      var  object_id    = parts[1];

      if (!tab[object_class]) {
        tab[object_class] = [];
      }

      tab[object_class].push(object_id);
    });

    $H(tab).each(function(pair) {
      FilesCategory.iconInfoReadFilesGuid(pair.key, pair.value);
    });
  },

  iconInfoReadFilesGuid : function(object_class, object_ids) {
    new Url('files', "ajax_check_object_files_category")
      .addParam('object_class', object_class)
      .addParam('object_ids'  , object_ids.join("-"))
      .requestJSON(function(obj) {
        $H(obj).each(function(pair) {
          var element = $(pair.key+"_check_category");
          element.setVisible(pair.value > 0);
          element.down("span").update(pair.value);
        });
      });
  },

  edit : function(category_id, element) {
    if (element) {
      element.up('tr').addUniqueClassName('selected');
    }

    new Url("files", "ajax_edit_category")
      .addParam("category_id", category_id)
      .requestModal(800, 600)
      .modalObject.observe("afterClose", function() {
        FilesCategory.loadList();
      });
  },

  callback : function(id) {
    Control.Modal.close();
    FilesCategory.loadList();
    FilesCategory.edit(id);
  },

  checkMergeSelected : function(oinput) {
    var selected = $$("#list_file_categories input:checked");

    if (selected.length > 2) {
      //$(selected)[0].checked = false;
      $(oinput).checked = false;    // unckeck the last
    }
  },

  mergeSelected : function() {
    var selected = $$("#list_file_categories input:checked");
    if (selected.length < 2) {
      return;
    }

    var objects_id = [];
    selected.each(function(element) {
      objects_id.push($(element).get("id"));
    });

    var elements = objects_id.join('-');

    var url = new Url("system", "object_merger");
    url.addParam('objects_class', 'CFilesCategory');
    url.addParam('objects_id', elements);
    url.addParam('mode', 'fast');
    url.popup(800, 600, "merge_patients");
  },

  changePage: function(page) {
    $V(getForm('listFilter').page, page);
  },

  printEtiquettes: function(file_category_id) {
    new Url("hospi", "print_etiquettes", "raw")
      .addParam("object_class", "CFilesCategory")
      .addParam("object_id", file_category_id)
      .popup(800, 600);
  },

  /**
   * Check if the doc type for DMP, SISRA exist
   *
   * @param form
   * @param file_category_id
   */
  checkTypeDocDmpSisra: function (form, file_category_id) {
    new Url("files", "ajax_select_type_doc")
      .addParam("file_category_id", file_category_id)
      .requestJSON(
        function (type_docs) {
          $V(form.type_doc_dmp, type_docs['DMP']);
          $V(form.type_doc_sisra, type_docs['SISRA']);
        }
      );
  },

  viewRelatedReceivers: function(files_category_id) {
    new Url("files", "ajax_vw_related_receivers")
      .addParam("files_category_id", files_category_id)
      .requestModal(600, 400)
      .modalObject.observe(
        "afterClose",
        function() {
          FilesCategory.loadList();
        }
      );
  },

  refreshRelatedReceivers: function(files_category_id) {
    new Url("files", "ajax_vw_related_receivers")
      .addParam("files_category_id", files_category_id)
      .addParam("refresh", 1)
      .requestUpdate('list_receivers-'+files_category_id);
  },

  editRelatedReceiver: function(related_receiver_id, files_category_id) {
    new Url("files", "ajax_edit_related_receiver_to_category")
      .addParam("related_receiver_id", related_receiver_id)
      .addParam("files_category_id"  , files_category_id)
      .requestModal(500, 300)
      .modalObject.observe(
        "afterClose",
        function() {
          FilesCategory.refreshRelatedReceivers(files_category_id);
        }
      );
  },

  refreshRelatedReceiver: function(related_receiver_id) {
    new Url("files", "ajax_vw_related_receiver")
      .addParam("related_receiver_id", related_receiver_id)
      .requestUpdate('line_'+related_receiver_id);
  },

  /**
   * Submit form for upload file
   * => Include security check for file name
   *
   * @param form
   * @returns {Boolean|boolean}
   */
  onSubmit: function (form) {
      let formFile = form.select("input[type=text][name=formfile[]]");

      for (let index = 0; index < formFile.length; index++) {
          if ($V(formFile[index]).match(/[<>/\\]/g)) {
              alert($T("CFile-error-File name cannot contain ban characters"));
              return false;
          }

          $V(formFile[index], $V(formFile[index]).replaceAll(/[<>/\\]/g, "-"));
      }

      return onSubmitFormAjax(form);
  }
};

onMergeComplete = function() {
  FilesCategory.loadList();
};
