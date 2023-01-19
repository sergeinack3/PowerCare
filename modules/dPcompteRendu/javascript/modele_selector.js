/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/* Modele selector
   Allows to choose a modele from a praticien or a function
*/

ModeleSelector = Class.create({
    sForm: null,
    sView: null,
    sModele_id: null,
    sObject_id: null,
    sFastEdit: null,

    options: {
        width: 850,
        height: 650
    },

    initialize: function (sForm, sView, sModele_id, sObject_id, sFastEdit, oDefaultOptions) {
        Object.extend(this.options, oDefaultOptions);

        this.sForm = sForm;
        this.sView = sView;
        this.sModele_id = sModele_id;
        this.sObject_id = sObject_id;
        this.sFastEdit = sFastEdit;
    },

    pop: function (object_id, object_class, praticien_id) {
        var url = new Url("compteRendu", "modele_selector");
        url.addParam("object_id", object_id);
        url.addParam("object_class", object_class);
        url.addParam("praticien_id", praticien_id);
        url.popup(this.options.width, this.options.height, "Sélecteur de modèle");
    },

    set: function (modele_id, object_id, fast_edit) {
        var oForm = getForm(this.sForm);
        $V(oForm[this.sModele_id], modele_id);
        $V(oForm[this.sFastEdit], fast_edit);
        $V(oForm[this.sObject_id], object_id, true);
    }
});

modeleSelector = window.modeleSelector || [];
