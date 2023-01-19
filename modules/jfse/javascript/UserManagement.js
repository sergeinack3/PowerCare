/**
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

UserManagement = {
    /**
     * Initialize the control tabs of the index view of the users management
     */
    initializeIndexView: () => {
        Control.Tabs.create('tabs-user-management-index', true, {
            afterChange: (container) => {
                if (container.id == 'users-container') {
                    UserManagement.listUsersIndex();
                } else if (container.id == 'establishments-container') {
                    UserManagement.listEstablishmentsIndex();
                }
            }
        });
    },

    /**
     * Displays the index view of the list of users
     */
    listUsersIndex: () => {
        Jfse.displayView('user_management/users/list', 'users-container', {refresh: 0});
    },

    /**
     * Refresh the list of users
     */
    refreshListUsers: () => {
        UserManagement.filterListUsers(getForm('filterUsers'));
    },

    /**
     * Displays the list of users from the given start
     *
     * @param start
     */
    changePageListUsers: (start) => {
        UserManagement.filterListUsers(getForm('filterUsers'), start);
    },

    /**
     * Displays the list of users with the filters from the given form and from the given start
     *
     * @param form
     * @param start
     * @returns {boolean}
     */
    filterListUsers: (form, start) => {
        if (start === undefined) {
            start = 0
        }

        let parameters = {
            refresh: 1,
            start:   start
        };

        if ($V(form.elements['last_name']) != '') {
            parameters.last_name = $V(form.elements['last_name']);
        }
        if ($V(form.elements['first_name']) != '') {
            parameters.first_name = $V(form.elements['first_name']);
        }
        if ($V(form.elements['national_identifier']) != '') {
            parameters.national_identifier = $V(form.elements['national_identifier']);
        }

        Jfse.displayView('user_management/users/list', 'users-list-container', parameters);
        return false;
    },

    /**
     * Display the data of the user with given id
     *
     * @param user_id
     */
    viewUser: (user_id) => {
        Jfse.displayViewModal(
            'user_management/user/view',
            600,
            800,
            {user_id: user_id},
            {title: $T('CJfseUserView')}
        );
    },

    /**
     * Unlink the user with given id from the CMediusers
     *
     * @param user_id
     */
    unlinkUserToMediuser: async(user_id) => {
        const response = await Jfse.requestJson('user_management/user/unlink', {user_id: user_id}, {});

        if (response.success) {
            let form = getForm('link-CJfseUser-' + user_id + '-CMediuser');
            $V(form.elements['mediuser_id'], '');
            $V(form.elements['mediuser_view'], '');
            $('link-CJfseUser-' + user_id + '-button').show();
            $('unlink-CJfseUser-' + user_id + '-button').hide();
            Jfse.notifySuccessMessage('CJfseUserView-msg-user_unlinked');
        } else {
            Jfse.notifyErrorMessage('CJfseUserView-error-user_unlinked');
        }
    },

    /**
     * Link the User with the given id to a CMediusers
     *
     * @param user_id
     */
    linkUserToMediuser: async(user_id) => {
        let form = getForm('link-CJfseUser-' + user_id + '-CMediuser');
        if ($V(form.elements['mediuser_id'])) {
            const response = await Jfse.requestJson('user_management/user/link', {
                user_id:     user_id,
                mediuser_id: $V(form.elements['mediuser_id'])
            });

            if (response.success) {
                $('link-CJfseUser-' + user_id + '-button').hide();
                $('unlink-CJfseUser-' + user_id + '-button').show();

                Jfse.notifySuccessMessage('CJfseUserView-msg-user_linked');
            } else {
                Jfse.notifyErrorMessage('CJfseUserView-error-user_linked');
            }
        } else {
            Modal.alert($T('CJfseUserView-error-no_mediuser_selected'));
        }
    },

    /**
     * Enable the modification of the value field for the given parameter
     *
     * @param user_id
     * @param parameter_id
     */
    enableUserParameter: (user_id, parameter_id) => {
        getForm('edit-CJfseUser-' + user_id + '-UserParameter-' + parameter_id).elements['value'].enable();
        $('unlock-CJfseUser-' + user_id + '-UserParameter-' + parameter_id).hide();
        $('lock-CJfseUser-' + user_id + '-UserParameter-' + parameter_id).show();
    },

    /**
     * Disable the modification of the value field for the given parameter
     *
     * @param user_id
     * @param parameter_id
     */
    disableUserParameter: (user_id, parameter_id) => {
        getForm('edit-CJfseUser-' + user_id + '-UserParameter-' + parameter_id).elements['value'].disable();
        $('unlock-CJfseUser-' + user_id + '-UserParameter-' + parameter_id).show();
        $('lock-CJfseUser-' + user_id + '-UserParameter-' + parameter_id).hide();
    },

    /**
     * Reads the CPS and creates the User
     *
     * @param cps_code
     */
    createUser: () => {
        Jfse.displayViewModal('user_management/user/create', 400, 400, {}, {
            title:   $T('CJfseUserView-title-create'),
            onClose: UserManagement.refreshListUsers.bind(UserManagement)
        });
    },

    confirmUserDeletion: function (user_id) {
        Modal.confirm($T('CJfseUserView-msg-confirm_deletion'), {
            onOK: this.deleteUser.bind(this, user_id)
        });
    },

    /**
     * Deletes the User with the given id
     *
     * @param user_id
     */
    deleteUser: async(user_id) => {
        const response = await Jfse.requestJson('user_management/user/delete', {user_id: user_id}, {});

        if (response.success) {
            Control.Modal.close();
            UserManagement.refreshListUsers();
            Jfse.notifySuccessMessage('CJfseUserView-msg-delete');
        } else {
            Jfse.notifyErrorMessage('CJfseUserView-error-delete');
        }
    },

    /**
     * Updates the value of the parameter with the given id for the given user
     *
     * @param user_id
     * @param parameter_id
     */
    editUserParameter: async(user_id, parameter_id) => {
        let value = $V(getForm('edit-CJfseUser-' + user_id + '-UserParameter-' + parameter_id).elements['value']);
        const response = await Jfse.requestJson('user_management/user/parameter/edit', {
            user_id:      user_id,
            parameter_id: parameter_id,
            value:        value
        }, {});

        if (response.success) {
            Jfse.notifySuccessMessage('CJfseUserParameter-msg-modify');
        } else {
            Jfse.notifyErrorMessage('CJfseUserParameter-error-modify');
        }
    },

    /**
     * Deletes the parameter with the given id for the given user
     *
     * @param user_id
     * @param parameter_id
     */
    deleteUserParameter: async(user_id, parameter_id) => {
        const response = await Jfse.requestJson('user_management/user/parameter/delete', {
            user_id:      user_id,
            parameter_id: parameter_id
        }, {});

        if (response.success) {
            $('CJfseUser-' + user_id + '-UserParameter-' + parameter_id + '-row').remove();
            Jfse.notifySuccessMessage('CJfseUserParameter-msg-deleted');
        } else {
            Jfse.notifyErrorMessage('CJfseUserParameter-error-delete');
        }
    },

    /**
     * Displays the index view of the list of users
     */
    listEstablishmentsIndex: () => {
        Jfse.displayView('user_management/establishments/list', 'establishments-container', {refresh: 0});
    },

    /**
     * Displays the list of users with the filters from the given form and from the given start
     *
     * @param start
     *
     * @returns {boolean}
     */
    listEstablishments: (start) => {
        if (start === undefined) {
            start = 0
        }

        let parameters = {
            refresh: 1,
            start: start
        };

        Jfse.displayView('user_management/establishments/list', 'establishments-list-container', parameters);
    },

    editEstablishment: (id) => {
        let title = 'CJfseEstablishmentView-title-modification';
        if (!id) {
            title = 'CJfseEstablishmentView-title-create'
        }
        Jfse.displayViewModal('user_management/establishment/edit', 600, 600, {id: id}, {
            title: $T(title),
            onClose: UserManagement.listEstablishments.bind(UserManagement)
        });
    },

    storeEstablishment: async(form) => {
        const response = await Jfse.requestJson('user_management/establishment/store', {form: form}, {});

        if (response.success) {
            let message = 'CJfseEstablishmentView-msg-created';
            if ($V(form.elements['id'])) {
                message = 'CJfseEstablishmentView-msg-modified';
            }

            Jfse.notifySuccessMessage(message);
            Control.Modal.close();
        } else if (response.messages) {
            Jfse.notifyMessages(response.messages)
        }
    },

    deleteEstablishment: async(id) => {
        const response = await Jfse.requestJson('user_management/establishment/delete', {id: id}, {});

        if (response.success) {
            Jfse.notifySuccessMessage('CJfseEstablishmentView-msg-deleted');
            Control.Modal.close();
        } else if (response.messages) {
            Jfse.notifyMessages(response.messages);
        }
    },

    /**
     * Unlink the Establishment with given id from the CFunctions or CGroups
     *
     * @param user_id
     */
    unlinkEstablishmentToObject: async(establishment_id) => {
        const response = await Jfse.requestJson('user_management/establishment/unlink', {establishment_id: establishment_id}, {});

        if (response.success) {
            let form = getForm('edit-CJfseEstablishment-' + establishment_id);
            $V(form.elements['_object_id'], '');
            $V(form.elements['_object_class'], '');
            $V(form.elements['function_view'], '');
            $V(form.elements['group_view'], '');
            $('link-CJfseEstablishment-' + establishment_id + '-button').show();
            $('unlink-CJfseEstablishment-' + establishment_id + '-button').hide();
            $$('div.CJfseEstablishment-group_container').each((element) => element.show());
            $$('div.CJfseEstablishment-function_container').each((element) => element.show());
            form.elements['group_view'].enable();
            form.elements['function_view'].enable();

            Jfse.notifySuccessMessage('CJfseEstablishmentView-msg-user_unlinked');
        } else {
            Jfse.notifyErrorMessage('CJfseEstablishmentView-error-user_unlinked');
        }
    },

    /**
     * Link the Establishment with the given id to a CFunctions or a CGroups
     *
     * @param user_id
     */
    linkEstablishmentToObject: async(establishment_id) => {
        let form = getForm('edit-CJfseEstablishment-' + establishment_id);
        if ($V(form.elements['_object_id']) && $V(form.elements['_object_class'])) {
            const object_class = $V(form.elements['_object_class']);
            const response = await Jfse.requestJson('user_management/establishment/link', {
                establishment_id:     establishment_id,
                object_id: $V(form.elements['_object_id']),
                object_class: $V(form.elements['_object_class'])
            });

            if (response.success) {
                if (object_class == 'CFunctions') {
                    $$('div.CJfseEstablishment-group_container').each((element) => element.hide());
                    form.elements['function_view'].disable();
                } else if (object_class == 'CGroups') {
                    $$('div.CJfseEstablishment-function_container').each((element) => element.hide());
                    form.elements['group_view'].disable();
                }
                $('link-CJfseEstablishment-' + establishment_id + '-button').hide();
                $('unlink-CJfseEstablishment-' + establishment_id + '-button').show();

                Jfse.notifySuccessMessage('CJfseEstablishmentView-msg-user_linked');
            } else {
                Jfse.notifyErrorMessage('CJfseEstablishmentView-error-user_linked');
            }
        } else {
            Modal.alert($T('CJfseEstablishmentView-error-no_mediuser_selected'));
        }
    },

    linkUserToEstablisment: async(user_id) => {
        let form = getForm('link-CJFseUser-' + user_id + '-CJfseEstablishment');
        if ($V(form.elements['establishment_id'])) {
            const establishment_id = $V(form.elements['establishment_id']);
            const response = await Jfse.requestJson('user_management/user/linkEstablishment', {
                user_id:          user_id,
                establishment_id: establishment_id,
            });

            if (response.success) {
                form.elements['establishment_view'].disable();
                $('link-CJfseUser-' + user_id + '-Establishment-button').hide();
                $('unlink-CJfseUser-' + user_id + '-Establishment-button').show();

                Jfse.notifySuccessMessage('CJfseEstablishmentView-msg-user_linked');
            } else {
                Jfse.notifyErrorMessage('CJfseEstablishmentView-error-user_linked');
            }
        } else {
            Modal.alert($T('CJfseUserView-error-no_establishment_selected'));
        }
    },

    unlinkUserToEstablisment: async(user_id) => {
        const response = await Jfse.requestJson('user_management/user/unlinkEstablishment', {user_id: user_id}, {});

        if (response.success) {
            let form = getForm('link-CJFseUser-' + user_id + '-CJfseEstablishment');
            $V(form.elements['establishment_id'], '');
            $V(form.elements['establishment_view'], '');
            $('link-CJfseUser-' + user_id + '-Establishment-button').show();
            $('unlink-CJfseUser-' + user_id + '-Establishment-button').hide();
            form.elements['establishment_view'].enable();

            Jfse.notifySuccessMessage('CJfseEstablishmentView-msg-user_unlinked');
        } else {
            Jfse.notifyErrorMessage('CJfseEstablishmentView-error-user_unlinked');
        }
    },

    displayModalLinkUserToEstablishment: (establishment_id) => {
        Modal.open($('CJfseEstablishmentView-link-user-container'), {
            title: $T('CJfseEstablishmentView-action-link_user'),
            onClose: UserManagement.listEstablishmentUsers.bind(UserManagement, establishment_id)
        })
    },

    listEstablishmentUsers: (establishment_id) => {
        Jfse.displayView('user_management/establishment/users/list', 'linked-users-container', {establishment_id: establishment_id});
    },

    linkEstablishmentToUser: async(user_id, establishment_id) => {
        const response = await Jfse.requestJson('user_management/user/linkEstablishment', {
            user_id:          user_id,
            establishment_id: establishment_id,
        });

        if (response.success) {
            Jfse.notifySuccessMessage('CJfseEstablishmentView-msg-user_linked');
            Control.Modal.Close();
        } else {
            $V($('CJfseEstablishment-link-user_user_view'), '');
            Jfse.notifyErrorMessage('CJfseEstablishmentView-error-user_linked');
        }
    },

    unlinkEstablishmentToUser: async(user_id, establishment_id) => {
        const response = await Jfse.requestJson('user_management/user/unlinkEstablishment', {user_id: user_id});

        if (response.success) {
            Jfse.notifySuccessMessage('CJfseEstablishmentView-msg-user_unlinked');
            UserManagement.listEstablishmentUsers(establishment_id);
        } else {
            Jfse.notifyErrorMessage('CJfseEstablishmentView-error-user_unlinked');
            $V($('CJfseEstablishment-link-user_user_view'), '');
        }
    },

    listEmployeeCards: (establishment_id) => {
        Jfse.displayView('user_management/employee_cards/list', 'linked-employee_cards-container', {establishment_id: establishment_id});
    },

    editEmployeeCard: (establishment_id) => {
        Jfse.displayViewModal('user_management/employee_card/edit', 400, 300, {establishment_id: establishment_id}, {
            title: $T('CEmployeeCard-title-create'),
            onClose: UserManagement.listEmployeeCards.bind(UserManagement, establishment_id)
        });
    },

    storeEmployeeCard: async(form) => {
        const response = await Jfse.requestJson('user_management/employee_card/store', {
            form: form,
        });

        if (response.success) {
            Jfse.notifySuccessMessage('CEmployeeCard-msg-created');
            Control.Modal.close();
        } else {
            Jfse.notifyErrorMessage('CEmployeeCard-error-created');
        }
    },

    deleteEmployeeCard: async(employee_id, establishment_id) => {
        const response = await Jfse.requestJson('user_management/employee_card/delete', {
            id: employee_id,
        });

        if (response.success) {
            Jfse.notifySuccessMessage('CEmployeeCard-msg-deleted');
            UserManagement.listEmployeeCards(establishment_id);
        } else {
            Jfse.notifyErrorMessage('CEmployeeCard-error-deleted');
        }
    },

    /**
     * Initialize the control tabs and the CMediusers autocomplete in the User view
     *
     * @param user_id
     */
    initializeUserView: (user_id) => {
        Control.Tabs.create('tabs-user-CJfseUserView-' + user_id, false, {
            afterChange: (container) => {
                if (container.id == 'parameters-container') {
                    ViewPort.SetAvlHeight('parameters-container', 1.0);
                }
            }
        });
        let form = getForm('link-CJfseUser-' + user_id + '-CMediuser');
        new Url('mediusers', 'ajax_users_autocomplete')
            .addParam('edit', '1')
            .addParam('prof_sante', '1')
            .addParam('input_field', 'mediuser_view')
            .autoComplete(form.elements['mediuser_view'], null, {
                minChars:           0,
                method:             'get',
                select:             'view',
                dropdown:           true,
                afterUpdateElement: ((form, field, selected) => {
                    $V(form.elements['mediuser_view'], selected.down('.view').innerHTML);
                    $V(form.elements['mediuser_id'], selected.getAttribute('id').split('-')[2]);
                }).bind(this, form)
            });
        let establishment_form = getForm('link-CJFseUser-' + user_id + '-CJfseEstablishment');
        Jfse.displayAutocomplete('user_management/establishments/autocomplete', establishment_form.elements['establishment_view'], {input_field: 'establishment_view'}, null,{
            minChars: 0,
            dropdown: true,
            afterUpdateElement: ((form, field, selected) => {
                $V(form.elements['establishment_view'], selected.down('.view').innerHTML);
                $V(form.elements['establishment_id'], selected.get('id'));
            }).bind(this, establishment_form)
        });
    },

    /**
     * Initialize the CMediusers autocomplete in the Establishment view
     *
     * @param user_id
     */
    initializeEstablishmentView: (establishment_id) => {
        let form_name = 'edit-CJfseEstablishment';
        if (establishment_id) {
            form_name = form_name + '-' + establishment_id;

            Control.Tabs.create('tabs-CJfseEstablishmentView-' + establishment_id, false);
        }

        let form = getForm(form_name);
        new Url('mediusers', 'ajax_functions_autocomplete')
            .addParam('edit', '1')
            .addParam('type', 'cabinet')
            .addParam('input_field', 'function_view')
            .autoComplete(form.elements['function_view'], null, {
                minChars:           0,
                method:             'get',
                select:             'view',
                dropdown:           true,
                afterUpdateElement: ((form, field, selected) => {
                    $V(form.elements['function_view'], selected.down('.view').innerHTML);
                    $V(form.elements['_object_id'], selected.getAttribute('id').split('-')[2]);
                    $V(form.elements['_object_class'], 'CFunctions');
                    $V(form.elements['group_view'], '');
                    if (
                        $V(form.elements['id']) == '' && ($V(form.elements['name']) == ''
                        || $V(form.elements['name']) == $V(form.elements['group_view']))
                    ) {
                        $V(form.elements['name'], selected.down('.view').innerHTML);
                    }
                }).bind(this, form)
            });
        new Url('etablissement', 'ajax_groups_autocomplete')
            .addParam('edit', 1)
            .addParam('input_field', 'group_view')
            .autoComplete(form.elements['group_view'], null, {
                minChars:           0,
                method:             'get',
                select:             'view',
                dropdown:           true,
                afterUpdateElement: ((form, field, selected) => {
                    $V(form.elements['group_view'], selected.down('.view').innerHTML);
                    $V(form.elements['_object_id'], selected.getAttribute('id').split('-')[2]);
                    $V(form.elements['_object_class'], 'CGroups');
                    $V(form.elements['function_view'], '');
                    if ($V(form.elements['id']) == '' && ($V(form.elements['name']) == '' || $V(form.elements['name']) == $V(form.elements['group_view']))) {
                        $V(form.elements['name'], selected.down('.view').innerHTML);
                    }
                }).bind(this, form)
            });
    }
};
