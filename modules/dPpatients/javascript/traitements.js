/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Traitement = {
  prescription_sejour_id: null,
  remove:                 function (oForm, onComplete) {
    var oOptions = {
      typeName: 'ce traitement',
      ajax:     1,
      target:   'systemMsg'
    };

    var oOptionsAjax = {
      onComplete: function () {
        if (onComplete) {
          onComplete();
        }
        if (window.refreshWidget) {
          TdBTamm.refreshDistinctsWidget('tp', 'tp_dossier', 'list_tp');
        }
      }
    };

    confirmDeletion(oForm, oOptions, oOptionsAjax);
  },
  cancel:                 function (oForm, onComplete) {
    $V(oForm.annule, 1);
    onSubmitFormAjax(oForm, {
      onComplete: function () {
        if (onComplete) {
          onComplete();
        }
        if (window.refreshWidget) {
          TdBTamm.refreshDistinctsWidget('tp', 'tp_dossier', 'list_tp');
        }
      }
    });
    $V(oForm.annule, '');
  },
  restore:                function (oForm, onComplete) {
    $V(oForm.annule, '0');
    onSubmitFormAjax(oForm, {
      onComplete: function () {
        if (onComplete) {
          onComplete();
        }
        if (window.refreshWidget) {
          TdBTamm.refreshDistinctsWidget('tp', 'tp_dossier', 'list_tp');
        }
      }
    });
    $V(oForm.annule, '');
  },
  toggleCancelled:        function (list) {
    $(list).select('.cancelled').invoke('toggle');
  },

  stopAll: function (form, callback) {
    if (!confirm($T('CPrescription-Ask stop all TP'))) {
      return;
    }

    onSubmitFormAjax(form, callback ? callback : null);
  }
};