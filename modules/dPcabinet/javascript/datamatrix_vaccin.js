/**
 * @package Mediboard\dPcabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

dataVacc = {
    /**
     * Modal pour lire le datamatrix vaccin
     *
     * @param search
     */
    openModalReadDatamatrix: function (search = 1) {
        new Url('dPcabinet', 'openModalReadDatamatrixVaccin')
            .addParam('search', search)
            .requestModal();
    },

    /**
     * Recherche d'un vaccin à partir d'un datamatrix
     *
     *
     * @param datamatrix
     */
    readDatamatrix: function (datamatrix) {
        new Url('dPcabinet', 'readDatamatrixVaccin')
            .addParam('datamatrix', datamatrix)
            .requestUpdate('systemMsg');
    },

    /**
     * Recherche d'un code CIP pour le remplacer avec son libelle
     *
     * @param code
     */
    searchCIP: function (code13, form) {
        new Url('dPcabinet', 'searchCIP')
            .addParam('code13', code13)
            .requestJSON(function (json) {
                $V(form['edit-injection_speciality'], json.libelle);
            });
    },

    /**
     * Remplir les champs pour la création d'un vaccin
     *
     * @param params
     */
    createVaccin: function (params) {
        Control.Modal.close();
        let form = getForm('edit-injection');
        dataVacc.searchCIP(params.cip13, form);
        $V(form['edit-injection_cip_product'], params.cip13);
        $V(form['edit-injection_batch'], params.lot);
        $V(form['edit-injection_expiration_date_da'], params.exp_display);
        $V(form['edit-injection_expiration_date'], params.exp_hidden);
    }
}
;
