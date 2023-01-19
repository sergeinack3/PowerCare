/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

IdInterpreter = window.IdInterpreter || {
  formToComplete: null,
  formPatient: null,
  currentPatient: null,
  patientId: null,
  internalFile: false,

  /**
   * Open the IdInterpreter popup
   * @param form
   * @param form_patient
   */
  open : function(form, form_patient) {
    IdInterpreter.formPatient = form_patient;
    IdInterpreter.formToComplete = form;

    if ($V(form._copy_file_id)) {
      IdInterpreter.selectPatientFile($V(form._copy_file_id), IdInterpreter.subOpen);
    }
    else {
      IdInterpreter.subOpen();
    }
  },

  subOpen: () => {
    IdInterpreter.fillFromImage();
    IdInterpreter.submitImage();
  },

  fillFromImage: () => {
    new Url('files', 'idInterpreter')
      .addParam('patient_id', $V(IdInterpreter.formPatient.patient_id))
      .requestModal();
  },

  /**
   * Image submitting traitment
   */
  submitImage: function() {
    var options = {
      useFormData: true,
      method: 'post',
      params: {
        ajax: 1,
        m: 'files',
        dosql: 'do_id_interpreter'
      },
    };
    options.postBody = serializeForm(IdInterpreter.formToComplete, options);
    options.contentType = 'multipart/form-data';

    new Url()
      .requestJSON(function(patient) {
      var tmpInput = null;
      // No patient var, or an error value : error occured on the server side
      if (!patient) {
        patient = {
          error : "an_error_occured"
        }
      }
      if (patient.error) {
        SystemMessage.notify(DOM.div({
          className: 'error'
        }, $T('CIdInterpreter.' + patient.error)));
        if (!patient.continue) {
          Control.Modal.refresh();
          return false;
        }
      }
      IdInterpreter.toggleLoading($('idinterpreter-result'));
      var form = getForm('idinterpreter-result');

      // Fill the corresponding inputs
      for (var patientAttribute in patient) {
        if (tmpInput = form['patient_' + patientAttribute]) {
          if (patient[patientAttribute]) {
            tmpInput.checked = true;
            $V(form[patientAttribute], patient[patientAttribute]);
          }
        }
      }

      // Show the cropped picture
      if (patient.image) {
        var img = $('idinterpreter-image');
        img.src = "data:" + patient.image_mime + ";base64," + patient.image;
      }
      else {
        form["patient_image"].disabled = "disabled";
      }

      if (patient.image_cropped) {
        $('idinterpreter-show-container').update(DOM.img({
          style: "max-height: 100%; max-width: 400px",
          id: "idinterpreter-show-file",
          src: "data:" + patient.image_mime + ";base64," + patient.image_cropped
        }));
      }

      Control.Modal.position.defer();
    }, options);
  },

  /**
   * Toggle the Loading block, and possibly an other block
   *
   * @param otherElement Other block to toggle
   */
  toggleLoading: function(otherElement) {
    $('idinterpreter-loading').toggle();
    if (otherElement) {
      otherElement.toggle();
    }
  },

  /**
   * Submitting fileds traitment (basically, fill the inputs of the initial form)
   *
   * @param form The form submitted
   */
  submitFields: function(form) {
    var fileContainer = getForm('idinterpreter-update-files').down('div');

    fileContainer.select('input').invoke('remove');
    // No initial form

    if (!IdInterpreter.formToComplete) {
      Control.Modal.close();
      return false;
    }

    // Get the checked fields
    form.select('input[type="checkbox"]:checked').each(function(checkbox) {
      if (IdInterpreter.formToComplete['_source_' + checkbox.value]) {
        $V(IdInterpreter.formToComplete['_source_' + checkbox.value], form[checkbox.value].value);
      }
    });

    // Images traitment
    if (form.patient_image.checked && fileContainer) {
      fileContainer.insert(DOM.input({
        type: "hidden",
        name: "formfile[]",
        value: "identite.jpg",
        "data-blob": "blob"
      })
        .store("blob", IdInterpreter.dataURItoBlob($('idinterpreter-image').src)));
    }

    IdInterpreter.formPatient.insert(DOM.input({type: 'hidden', name: '_handle_files', value: '0'}));

    onSubmitFormAjax(fileContainer.up('form'));

    Control.Modal.close();
    return false;
  },

  /**
   * Util function : Convert URI to Blob data
   *
   * @param dataURI
   *
   * @returns {Blob}
   */
  dataURItoBlob: function(dataURI) {
    // convert base64 to raw binary data held in a string
    var byteString = atob(dataURI.split(',')[1]);

    // write the bytes of the string to an ArrayBuffer
    var ab = new ArrayBuffer(byteString.length);
    var ia = new Uint8Array(ab);
    for (var i = 0; i < byteString.length; i++) {
      ia[i] = byteString.charCodeAt(i);
    }

    // write the ArrayBuffer to a blob, and you're done
    return new Blob([ab]);
  },

  /**
   * Prepare a patient file for the IdInterpreter file form
   *   Used as callback in the Patient Files page
   *
   * @param file_id
   * @param callback
   */
  selectPatientFile: function(file_id, callback) {
    var canvas = new DOM.canvas();
    var img = new Image();

    img.src = '?m=files&raw=thumbnail&document_id=' + file_id + '&document_class=CFile&thumb=0&download_raw=0';
    img.onload = function() {
      canvas.width = img.width;
      canvas.height = img.height;
      canvas.getContext('2d').drawImage(img, 0, 0, img.width, img.height);

      canvas.toBlob(function(blob) {
        IdInterpreter.formToComplete.insert(DOM.input({
            type: "text",
            "data-blob": "blob",
            name: "formfile[]",
            value: "Image",
            style: 'display: none;'
          })
            .store('blob', blob)
        );

        IdInterpreter.internalFile = true;
        callback();
      });
    };
  },
};
