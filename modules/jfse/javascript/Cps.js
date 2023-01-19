/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Cps = {
    /**
     * Make an AJAX call to read the CPS, and display the results in a modal
     */
    read: () => {
        Jfse.displayViewModal('cps/read', 400, 150, {}, {
            title: $T('CCpsCard-title-read')
        });
    },

    /**
     * Make an AJAX call to read the CPS, and display the full data of the CPS in a modal
     */
    displayData: () => {
        Jfse.displayViewModal('cps/read', 500, 700, {display_data: 1}, {title: $T('CCpsCard-title-read')});
    },

    /**
     * Display a modal for the user to set the CPS code, and call the given method when it's done
     *
     * @param {Function} callback
     * @deprecated
     */
    getCpsCode: (callback) => {
        let code_input = DOM.input({
            type:      'password',
            name:      'code_cps',
            id:        'field_code_cps',
            pattern:   '[0-9]{4}',
            size:      '4',
            maxlength: '4'
        });
        let read_button = DOM.button({type: 'button', id: 'button-read_cps', class: 'tick'}, $T('Validate'));
        let cancel_button = DOM.button({type: 'button', class: 'cancel'}, $T('Cancel'));
        let div = DOM.div(
            {id: 'code-cps-container', style: 'display: none;'},
            DOM.table(
                {class: 'form'},
                DOM.tr(
                    {},
                    DOM.th({title: $T('CCpsCard-code-desc'), class: 'me-color-black'}, $T('CCpsCard-code')),
                    DOM.td({}, code_input)
                ),
                DOM.tr(
                    {},
                    DOM.td(
                        {class: 'button', colspan: '2'},
                        read_button,
                        cancel_button,
                    )
                )
            )
        );

        $('main').insert(div);
        Modal.open('code-cps-container', {showClose: false, title: $T('CCpsCard-action-code')});

        code_input.focus();
        code_input.observe('keydown', event => {
            if (event.which === 13 || event.keyCode === 13) {
                $('button-read_cps').click();
            }
        });

        read_button.observe('click', event => {
            if ($$('#field_code_cps:valid').length > 0) {
                let input = $('field_code_cps');
                Control.Modal.close();
                callback($V(input));
                $V(input, '');
                input.stopObserving();
                $('code-cps-container').remove();
            } else {
                Modal.alert($('CCpsCard-msg-invalid_code'));
            }
        });

        cancel_button.observe('click', event => {
            Control.Modal.close();
            $("field_code_cps").stopObserving();
            $('code-cps-container').remove();
        });
    },

    selectSituation: (situation_id, callback_route) => {
        Jfse.displayView(callback_route, 'jfse-container', {select_situation: 1, situation_id: situation_id});
    },

    /**
     * Gets the user's information and displays it in the tooltip
     *
     * @param {int} mediuser_id
     * @return {Promise<void>}
     */
    getUserInfos: async(mediuser_id) => {
        const data = await Jfse.requestJson('user_management/user/info', {user_id: mediuser_id}, {});

        Cps._fillOutToolTip(data);
    },

    deactivateSubstituteSession: async(substitute_id) => {
        const response = await Jfse.requestJson('cps/substituteSession/deactivate', {substitute_id: substitute_id}, {});

        Control.Modal.close();
        if (response.messages) {
            Jfse.displayMessagesModal(response.messages);
        } else if (response.error) {
            Jfse.displayErrorMessageModal(response.error);
        } else {
            Jfse.notifySuccessMessage('CSubstitute-msg-session_deactivated')
        }
    },

    /**
     * Fills out the tooltip
     *
     * @param {Object} data
     * @private
     */
    _fillOutToolTip: (data) => {
        $$('#cps_infos .rpps')[0].innerHTML = data.rpps;
        $$('#cps_infos .speciality')[0].innerHTML = data.speciality;
        $$('#cps_infos .contracted')[0].innerHTML = data.contracted_label;
        $$('#cps_infos .invoicing_number')[0].innerHTML = data.invoicing_number;
    }
};
