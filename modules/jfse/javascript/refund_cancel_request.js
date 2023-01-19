/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

RefundCancelRequest = {
    /**
     * Display the search form
     */
    displaySearchForm: () => {
        Jfse.displayViewModal(
            'refundrequestcancel/searchForm',
            '500',
            '1000',
            {},
            {title: $T('CRefundCancelRequest-title-search-form')}
        );
    },
    /**
     * Search for refund cancel requests
     *
     * @param form
     */
    search: (form) => {
        Jfse.displayViewModal(
            'refundrequestcancel/search',
            '500',
            '1000',
            {form},
            {title: $T('CRefundCancelRequest-title-search-results')}
        );
    },

    /**
     *  Display the refund cancel request creation form
     */
    edit: () => {
        Jfse.displayViewModal(
            'refundrequestcancel/edit',
            '500',
            '1000',
            {},
            {title: $T('CRefundCancelRequest-title-edit')}
        );
    },

    /**
     * Create a new Refund cancel request
     *
     * @param form
     */
    store: (form) => {
        Jfse.displayView(
            'refundrequestcancel/store',
            'systemMsg',
            {form}
        );

    },

    /**
     * Display the details of a refund cancel request
     *
     * @param invoice_id
     */
    details: (invoice_id) => {
        Jfse.displayViewModal(
            'refundrequestcancel/details',
            '500',
            '1000',
            {invoice_id: invoice_id},
            {title: $T('CRefundCancelRequestDetails-title')}
        );
    }
};
