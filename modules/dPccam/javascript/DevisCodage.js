/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

DevisCodage = {
    list: function (object_class, object_id) {
        var url = new Url('ccam', 'listDevis');
        url.addParam('object_class', object_class);
        url.addParam('object_id', object_id);
        url.requestUpdate('view-devis');
    },

    edit: function (devis_id, callback) {
        var url = new Url('ccam', 'editDevis');
        url.addParam('devis_id', devis_id);
        url.addParam('action', 'open');
        url.modal({
            height:  -20,
            width:   -50,
            onClose: callback
        });
    },

    create: function (object, callback) {
        var url = new Url();
        url.addParam('codable_class', object.codable_class);
        url.addParam('codable_id', object.codable_id);
        url.addParam('event_type', object.event_type);
        url.addParam('patient_id', object.patient_id);
        url.addParam('praticien_id', object.praticien_id);
        url.addParam('libelle', object.libelle);
        url.addParam('codes_ccam', object.codes_ccam);
        url.addParam('date', object.date);
        url.addParam('creation_date', object.creation_date);
        url.addParam('devis_codage_id', '');
        url.addParam('@class', 'CDevisCodage');
        url.addParam('devis_codage_id', '');
        url.requestUpdate('systemMsg', {method: 'post', onComplete: callback});
    },

    remove: function (devis_id, callback) {
        var url = new Url();
        url.addParam('devis_codage_id', devis_id);
        url.addParam('@class', 'CDevisCodage');
        url.addParam('del', 1);
        url.requestUpdate('systemMsg', {method: 'post', onComplete: callback});
    },

    viewDevis: function (object_class, object_id) {
        var url = new Url('ccam', 'codageDevis');
        url.addParam('object_class', object_class);
        url.addParam('object_id', object_id);
        url.requestUpdate('view-devis');
    },

    refresh: function (devis_id) {
        var url = new Url('ccam', 'editDevis');
        url.addParam('devis_id', devis_id);
        url.addParam('action', 'refresh');
        url.requestUpdate('modalDevisContainer');
    },

    syncField: function (field, devis_id) {
        var form = getForm('editDevis-' + devis_id);

        if (form[field.name]) {
            $V(form[field.name], $V(field));
        }
    },

    print: function (devis_id) {
        var url = new Url('ccam', 'printDevis');
        url.addParam('devis_id', devis_id);
        url.pop(1000, 600);
    }
};
