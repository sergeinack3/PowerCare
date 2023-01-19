/**
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Convention = {
    /**
     * Display convention form
     */
    editConvention: () => {
        Jfse.displayViewModal(
            'convention/editConvention',
            500,
            1000,
            {},
            {title: $T('CConvention-title-edit')}
        );
    },

    /**
     * Submit convention form data
     *
     * @param form
     */
    storeConvention: (form) => {
        Jfse.displayView('convention/updateConvention',
            'systemMsg',
            {form}
        );
    },

    /**
     * List conventions
     */
    listConventions: () => {
        Jfse.displayViewModal(
            'convention/listConventions',
            500,
            1000,
            {},
            {title: $T('CConvention-title-list')}
        );
    },

    /**
     * Delete convention
     * @param id
     */
    deleteConvention: (id) => {
        Jfse.displayView('convention/deleteConvention',
            'systemMsg',
            {id: id}
        );
    },

    /**
     * List convention types
     */
    listConventionTypes: () => {
        Jfse.displayViewModal(
            'convention/listTypesConvention',
            500,
            1000,
            {},
            {title: $T('CConventionType-title-list')}
        );
    },

    /**
     * Open import modal
     */
    importModal: () => {
        Jfse.displayViewModal(
            'convention/importModal',
            1000,
            1000,
            {},
            {title: $T('CConvention-title-import')}
        );
    },

    /**
     * Submit bin file
     * @param form
     */
    importBinFile: (form) => {
        Jfse.displayView('convention/importBinFile',
            'systemMsg',
            {form}
        );
    },

    /**
     * Submit zip file
     * @param form
     */
    importZipFile: (form) => {
        Jfse.displayView('convention/importZipFile',
            'systemMsg',
            {form}
        );
    },

    /**
     * Submit csv file
     * @param form
     */
    uploadCsvFile: (form) => {
        Jfse.displayView('convention/uploadCsvFile',
            'systemMsg',
            {useFormData: true, form}
        );
    },

    /**
     * Display the conventions ready to install in a file
     * @param file_name
     * @param jfse_id
     */
    getListeConventionsToInstall: (file_name, jfse_id) => {
        Jfse.displayViewModal(
            'convention/getListeConventionsToInstall',
            1000,
            1000,
            {file_name: file_name, jfse_id: jfse_id},
            {title: $T('CConvention-title-elements to install')}
        )
    },

    /**
     * Install conventions from a csv file
     * @param form
     */
    updateConventionsViaCsv: (form) => {
        Jfse.displayView('convention/updateConventionsViaCsv',
            'systemMsg',
            {form}
        )
    },

    /**
     * Delete a csv file
     * @param form
     */
    deleteFichierConventions: (form) => {
        Jfse.displayView('convention/deleteFichierConventions',
            'systemMsg',
            {form}
        )
    },

    /**
     * Copy conventions and grouping to a practicionner ou a group
     * @param form
     */
    importConventionsRegroupementsByPS: (form) => {
        Jfse.displayView('convention/importConventionsRegroupementsByPS',
            'results',
            {form}
        )
    },

    /**
     * Display grouping form
     */
    editGrouping: () => {
        Jfse.displayViewModal(
            'convention/editGrouping',
            500,
            1000,
            {},
            {title: $T('CGrouping-title-edit')}
        );
    },

    /**
     * Submit grouping form data
     * @param form
     */
    storeGrouping: (form) => {
        Jfse.displayView('convention/updateGrouping',
            'systemMsg',
            {form}
        );
    },

    /**
     * Display groupings
     */
    listGroupings: () => {
        Jfse.displayViewModal(
            'convention/listGroupings',
            1000,
            1000,
            {},
            {title: $T('CGrouping-title-list')}
        );
    },

    /**
     * Delete grouping
     * @param id
     */
    deleteGrouping: (id) => {
        Jfse.displayView('convention/deleteGrouping',
            'systemMsg',
            {id: id}
        );
    },
    
    /**
     * Display correspondence form
     */
    editCorrespondence: () => {
        Jfse.displayViewModal(
            'convention/editCorrespondence',
            500,
            1000,
            {},
            {title: $T('CCorrespondence-title-edit')}
        );
    },

    /**
     * Submit correspondence form
     * @param form
     */
    storeCorrespondence: (form) => {
        Jfse.displayView('convention/updateCorrespondence',
            'systemMsg',
            {form}
        );
    },

    /**
     * List correspondences
     */
    listCorrespondences: () => {
        Jfse.displayViewModal(
            'convention/listCorrespondences',
            1000,
            1000,
            {},
            {title: $T('CCorrespondence-title-list')}
        );
    },

    /**
     * Delete correspondence
     * @param id
     */
    deleteCorrespondence: (id) => {
        Jfse.displayView('convention/deleteCorrespondence',
            'systemMsg',
            {id: id}
        );
    }
};
