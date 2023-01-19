/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

DocumentItem = {
  onComplete : null,

  viewRecipientsForSharing: function(docItem_guid, onComplete) {
    DocumentItem.onComplete = onComplete;

    new Url('files', 'ajax_vw_share_document')
      .addParam('docItem_guid', docItem_guid)
      .requestModal('1000px', null, { onClose : onComplete });
  },

  viewSharingWithSIH: function (docItem_guid) {
    new Url('oxCabinet', 'shareDocumentSIH')
      .addParam('docItem_guid', docItem_guid)
      .requestModal('1000px', null, {
        onClose: () => {
          if (window.TdBTamm && TdBTamm.patient_id) {
            var form = getForm("filtreTdb");
            if (form) {
              TdBTamm.refreshTimeline(TdBTamm.patient_id);
            }
          }
        }
      });
  },

  shareDocumentDetails: function(form) {
    new Url("files", "ajax_vw_share_document_details")
      .addFormData(form)
      .requestUpdate("share-"+$V(form.elements.docItem_guid));

    return false;
  },

  refreshNavMenu : function (step, total, success, docItem_guid) {
    if (step == total) {
      Control.Modal.close();
      return false;
    }

    var steps = $("nav_timeline_share_docs").select("div.timeline_share_doc_label");
    var li    = $("nav_timeline_share_docs").children[step];

    var module_name             = li.dataset.module_name;
    var receiver_guid           = li.dataset.receiver_guid;
    var document_reference_guid = li.dataset.document_reference_guid;

    for (var i=0; i<steps.length; i++) {
      steps[i].removeClassName("actual_passed");
      steps[i].removeClassName("actual");

      if (!steps[i].up().dataset.document_reference_guid) {
        steps[i].removeClassName("passed");
      }
    }

    if (!document_reference_guid) {
      steps[step].addClassName("actual_passed");
    }

    new Url("files", "ajax_vw_send_docItem")
      .addParam('docItem_guid' , docItem_guid)
      .addParam('module_name'  , module_name)
      .addParam('receiver_guid', receiver_guid)
      .addParam('step'         , step)
      .addParam('total'        , total)
      .requestUpdate("send_docItem");

    return false;
  },

  shareDocument: function(form) {
    new Url("files", "controllers/do_share_document")
      .addFormData(form)
      .requestUpdate("systemMsg", Control.Modal.close);

    return false;
  },

  toggleShare: function (status) {
    $('shareDocumentDetails').select(".input_receiver").each(
      function (elt) {
        elt.checked = status ? "checked" : "";
      }
    );
  },

  toggleSelectFile: function (element) {
    if (element.hasClassName('file-selected')) {
      element.removeClassName('file-selected');
    } else {
      element = $(element);
      element.addClassName('file-selected');
    }

    var nb_selected = $$('div.file-selected').length;

    if ($("nb_element")) {
      var node = $("nb_element");
      var icon = $("element_icon");
      $("show_file_selected").removeChild(node);
      $("show_file_selected").removeChild(icon);
    }
    if (nb_selected > 0) {
      $("show_file_selected").insert(
        DOM.p({id: 'nb_element', style: 'display:inline-block'}, nb_selected + " élément(s) séléctionné(s)")
      );
      $("show_file_selected").insert(
        DOM.a({
            id: 'element_icon',
            title: 'Voir les documents sélectionnés',
            style: 'display:inline-block; font-size: 13pt; ' +
                  'font-weight: normal; margin-left: 10px; cursor: pointer;' +
                  'margin-right: 5px;', 'onclick': 'getFileSelected();'
          },
          DOM.i({class: 'fas fa-list ', title: 'Voir les documents sélectionnés'}))
      );
    }
  },

    addTypeDocDMP : function (document_guid) {
        new Url("files", "add_type_document")
            .addParam('document_guid', document_guid)
            .requestModal("40%", "40%");

        return false;
    },

    generateCDA : function (document_guid) {
        new Url("files", "confirm_generate_cda")
            .addParam('document_guid', document_guid)
            .requestModal("40%", "40%");

        return false;
    },

    confirmGenerateCDA : function (document_guid) {
        Control.Modal.close();

        new Url('files', 'generate_cda')
            .addParam('document_guid', document_guid)
            .requestModal('40%', '40%', {onClose : function() { DocumentItem.refreshListFile()} });

      return false;
    },

    refreshListFile : function() {
        // sSelClass et sSelKey sont mis en session pour pouvoir les récupérer dans les fichiers
        var url = new Url('files', 'httpreq_vw_listfiles');
        url.addParam('accordDossier', 0);
        url.requestUpdate('listView');
    },

    showDeliveries : function (element) {
        $(element).next().toggle();
        element.toggleClassName('fa-caret-square-right');
        element.toggleClassName('fa-caret-square-down');
    }
};
