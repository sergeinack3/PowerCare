{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  reloadCallback = function(id, object, close_modal) {
    if (Object.isUndefined(close_modal)) {
      close_modal = true;
    }
    if (window.reloadAfterUploadFile) {
      window.reloadAfterUploadFile(object.file_category_id);
    }

    if (window.File && window.File.refresh) {
      window.File.refresh(object.object_id, object.object_class, undefined);
    }

    if (window.Patient && Patient.reloadListFileEditPatient) {
      Patient.reloadListFileEditPatient('load', object.file_category_id);
    }

    if (window.reloadAfterUpload) {
      window.reloadAfterUpload();
    }

    if (window.Transport && window.Transport.refreshFile && object.object_class == 'CPatient') {
      window.Transport.refreshFile(object.object_id, object.object_class);
    }

    $("systemMsg").update('{{$messages|smarty:nodefaults}}').show();

    if (close_modal) {
      Control.Modal.close();
    }
  }
</script>
