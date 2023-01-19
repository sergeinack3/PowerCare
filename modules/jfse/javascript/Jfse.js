/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Jfse = {
    /**
     * Makes an AJAX request to the given route and display the response inside the given target
     *
     * @param {String}             route      The route to call
     * @param {HTMLElement,String} target     The element (or its id) that will display the response
     * @param {Object}             parameters The optional parameters of the request
     * @param {Object}             options    The options of the request
     *
     * @returns {Promise<bool>}
     */
    displayView: async function (route, target, parameters, options = null) {
        return new Promise(async(resolve, reject) => {
            try {
                let url = await this.initializeRequest(route, parameters);
                url.requestUpdate(target, this.getRequestOptions(options, resolve));
            } catch (e) {
                console.error(e);
                reject(false);
            }
        });
    },

    /**
     * Makes an AJAX call to the given route and displays the response in a modal
     *
     * @param {String}        route      The route to call
     * @param {Number,String} width      The width of the modal
     * @param {Number,String} height     The height of the modal
     * @param {Object}        parameters The optional parameters of the request
     * @param {Object}        options    The options of the modal and request
     *
     * @returns {Promise<bool>}
     */
    displayViewModal: async function (route, width, height, parameters, options) {
        return new Promise(async(resolve, reject) => {
            try {
                options = Object.extend({showReload: false}, options);

                let url = await this.initializeRequest(route, parameters);
                url.requestModal(width, height, this.getRequestOptions(options, resolve));
            } catch (e) {
                console.error(e);
                reject(false);
            }
        });
    },

    /**
     * Makes an AJAX request to the given route using an autocomplete behaviour
     *
     * @param {String} route            The route to call
     * @param {String|HTMLInputElement} search_input     The input to use as an autocomplete
     * @param {String} populate_element Div id to populate with found data
     * @param {Object} options          Options of the request
     *
     * @returns {Promise<bool>}
     */
    displayAutocomplete: async function (route, search_input, parameters, populate_element, options) {
        return new Promise(async(resolve, reject) => {
            try {
                if (!parameters) {
                    parameters = {};
                }
                let url = await this.initializeRequest(route, parameters);
                url.autoComplete(search_input, populate_element, this.getRequestOptions(options, resolve));
            } catch (e) {
                console.error(e);
                reject(false);
            }
        });
    },

    /**
     * Makes an AJAX request to the given route and display the response inside the given target
     *
     * @param {String}   route      The route to call
     * @param {Function} callback   The element (or its id) that will display the response
     * @param {Object}   parameters The optional parameters of the request
     * @param {Object}   options    The options of the request
     *
     * @returns {Promise<bool>}
     */
    requestJson: async function (route, parameters, options) {
        return new Promise(async (resolve, reject) => {
            parameters = Object.extend(parameters, {json: 1});

            try {
                let url = await this.initializeRequest(route, parameters);
                url.requestJSON((response) => {
                        if (response.error) {
                            this.displayErrorMessageModal(response.error);
                            reject(response.error);
                        }

                        resolve(response);
                    },
                    this.getRequestOptions(options)
                );
            } catch (e) {
                console.error(e);
                reject(false);
            }
        });
    },

    /**
     * Initialize the Url object, add the given parameters and the reader id
     *
     * @param {String} route      The route to call
     * @param {Object} parameters The optional parameters
     *
     * @returns {Promise<Url>}
     */
    initializeRequest: async function (route, parameters, raw) {
        let url;
        if (raw) {
            url = new Url('jfse', 'jfseIndex', 'raw');
        } else {
            url = new Url('jfse', 'jfseIndex');
        }

        parameters = Object.extend(parameters, {route: route});

        try {
            parameters.resident_uid = await this.getResidentUid();
            $H(parameters).each(pair => {
                if (pair.value instanceof HTMLFormElement) {
                    url.addFormData(pair.value);
                } else {
                    url.addParam(pair.key, pair.value, true);
                }
            });

            return url;
        } catch (error) {
            this.displayErrorMessageModal(error);
        }
    },

    /**
     * Add the method, and the get parameters to the given options
     *
     * @param {Object} options
     * @param {function} resolve
     *
     * @returns {Object}
     */
    getRequestOptions: function (options, resolve = null) {
        let object = Object.extend({
            method:        'post',
            getParameters: {m: 'jfse', a: 'jfseIndex'},

        }, options);

        if (resolve !== null) {
            object = Object.extend({
                onComplete: () => resolve(true)
            }, object);
        }

        return object;
    },

    /**
     * Get the Jfse reader id from a cookie, or directly gets it from the resident
     *
     * @returns {Promise<string>}
     */
    getResidentUid: function () {
        return new Promise((resolve, reject) => {
            var cookie = new CookieJar({expires: 300});
            if (!cookie.get('Jfse-resident-uid')) {
                let xdr = new XDR('http://localhost:8888/lecteur/id', 'GET', {
                    timeout: 5000,
                    onload:  (xhr) => {
                        let response = JSON.parse(xhr.responseText);
                        if (response.id) {
                            cookie.put('Jfse-resident-uid', response.id);
                            resolve(response.id);
                        }
                    },
                    onerror: () => {
                        reject('Jfse-error-resident_unreachable');
                    },
                    headers: {'Accept': '*/*'}
                });
                xdr.send();
            } else {
                resolve(cookie.get('Jfse-resident-uid'));
            }
        });
    },

    /**
     * Displays the given error message in a modal
     *
     * @param {String} message
     */
    displayErrorMessageModal: function (message, callback) {
        this._callDisplayMessageModal(message, 'error', callback);
    },

    /**
     * Displays the given success message in a modal
     *
     * @param {String} message
     */
    displaySuccessMessageModal: function (message, callback) {
        this._callDisplayMessageModal(message, 'success', callback);
    },

    /**
     * Display message depending on the type
     *
     * @param {string} message
     * @param {string} type
     */
    _callDisplayMessageModal: function (message, type, callback) {
        let options = {
            method:        'post',
            getParameters: {m: 'jfse', a: 'displayMessage'}
        };

        if (callback) {
            options.onClose = callback;
        }

        new Url('jfse', 'displayMessage')
            .addParam('message', message)
            .addParam('type', type)
            .requestModal(300, 200, options);
    },

    /**
     * Display the messages depending on the type
     *
     * @param {array} messages
     */
    displayMessagesModal: function (messages, callback) {
        let options = {
            method:        'post',
            getParameters: {m: 'jfse', a: 'displayMessages'}
        };

        if (callback) {
            options.onClose = callback;
        }

        new Url('jfse', 'displayMessages')
            .addParam('messages', Object.toJSON(messages), true)
            .requestModal(null, null, options);
    },

    displaySuccessMessage: function (message, target) {
        this.displayMessage(message, 'info', target);
    },

    displayErrorMessage: function (message, target) {
        this.displayMessage(message, 'error', target);
    },

    /**
     * Displays the given messages in the specified element
     *
     *
     * @param messages
     * @param target
     */
    displayMessages: function (messages, target) {
        if (Array.isArray(messages)) {
            messages.each((function (message, index) {
                this.displayMessage(message.text, message.type, target, index !== 0);
            }).bind(this));
        }
    },

    /**
     * Displays the given message in the given DOM element.
     * If append is false, the element's content will be removed before adding the message
     *
     * @param message
     * @param type
     * @param target
     */
    displayMessage: function (message, type, target, append) {
        target = $(target);

        if (append === undefined) {
            append = false;
        }

        if (['info', 'success', 'error'].indexOf(type) === -1) {
            type = 'info';
        }

        if (target) {
            if (!append) {
                target.innerHTML = '';
            }

            target.insert(DOM.div({class: 'small-' + type}, $T(message)));
            target.show();
        }
    },

    hideMessageElement: function (element) {
        element.innerHTML = '';
        element.hide();
    },

    notifySuccessMessage: function (message) {
        this.notifyMessage(message, 'info');
    },

    notifyErrorMessage: function (message) {
        this.notifyMessage(message, 'error');
    },


    /**
     * Display the given messages in the system messages
     *
     * @param message
     */
    notifyMessages: function (messages) {
        if (Array.isArray(messages)) {
            messages.each((function (message, index) {
                this.notifyMessage(message.text, message.type, index != 0);
            }).bind(this));
        }
    },

    /**
     * Display the given message in the system messages
     *
     * @param message
     * @param type
     */
    notifyMessage: function (message, type, append) {
        if (['info', 'success', 'error'].indexOf(type) === -1) {
            type = 'info';
        }

        if (append === undefined) {
            append = false;
        }

        SystemMessage.notify(DOM.div({class: type}, $T(message)), append);
    },

    displayContentModal: (content, title, onClose) => {
        const container = DOM.div(null, null);
        $(document.body).insert(container);
        container.update(content);
        let options = {
            className:  'modal popup',
            showClose: true
        };

        if (title) {
            options.title = $T(title);
        }

        if (!onClose) {
            onClose = Prototype.emptyFunction;
        }

        /* The onClose if redefined to always remove the modal container from the dom */
        options.onClose = ((container, onClose) => {
            const modal = container.up().up();
            if (modal) {
                modal.remove();
            } else {
                container.remove();
            }
            onClose();
        }).curry(container, onClose);

        const modal = Modal.open(container, options);
        modal.position();
    },

    /**
     * Display a modal for the user to set the CPS code
     *
     * @return Promise<string>
     */
    askCpsCode: function () {
        return new Promise(((resolve, reject) => {
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
                    resolve($V(input));
                    $V(input, '');
                    input.stopObserving();
                    $('code-cps-container').remove();
                } else {
                    Modal.alert($('CCpsCard-msg-invalid_code'));
                }
            });

            cancel_button.observe('click', event => {
                Control.Modal.close();
                reject('');
                $("field_code_cps").stopObserving();
                $('code-cps-container').remove();
            });
        }));
    },

    /**
     *  Build and return jfse window in iframe
     *
     * @param {String} route
     * @param {Object} parameters
     * @returns {Promise<*>}
     */
    displayJfseIframe: async(url, container) => {
        let iframe = Element.getTempIframe(null);
        iframe.src = url;
        iframe.setStyle({width: '100%', height: '100%', top: null});

        if (container) {
            $(container).update(iframe);

            ViewPort.SetAvlHeight(iframe, 1.0);
            ViewPort.SetAvlWidth(iframe, 1.0)
        } else {
            document.body.insert(iframe);

            Modal.open(iframe, {showClose: true, width: '-30', height: '-30'});

            iframe.setStyle({width: '100%', height: '100%'});
        }

        return iframe;
    },

    pop: async(route, parameters, width, height, window_name) => {
        return new Promise(async(resolve, reject) => {
            try {
                let url = await Jfse.initializeRequest(route, parameters, true);
                url.popup(width, height, window_name, window_name, url.oParams);
            } catch (e) {
                console.error(e);
                reject(false);
            }
        });
    },

    /**
     * Diplay jfse window in an modal
     *
     * @param {String} route
     * @param {Object} parameters
     */
    displayGuiModal: async(route, parameters = {}) => {
        const response = await Jfse.requestJson(route, parameters, {});

        if (!response.url) {
            return;
        }

        Jfse.displayJfseIframe(response.url);
    },

    /**
     * Display jfse window in a element
     *
     * @param {String} route
     * @param {String} container_id
     * @param {Object} parameters
     *
     */
    displayGui: async(route, container_id, parameters = {}) => {
        const response = await Jfse.requestJson(route, parameters, {});

        if (!response.url) {
            return;
        }

        Jfse.displayJfseIframe(response.url, container_id);
    },



    /**
     * Set the given field to a not null field
     *
     * @param {HTMLInputElement} input The field
     */
    setInputNotNull: function (input) {
        input.addClassName('notNull');
        const label = this.getInputLabel(input);
        if (label) {
            label.addClassName('notNull');
        }
        input.observe('change', this.notNullOK).observe('keyup', this.notNullOK).observe('ui:change', this.notNullOK);
        input.dispatchEvent(new Event('keyup'));
    },

    /**
     * Set the given field to a null field
     *
     * @param {HTMLInputElement} field The field
     */
    setInputNullable: function (input) {
        input.removeClassName('notNull');
        const label = this.getInputLabel(input);
        if (label) {
            label.removeClassName('notNull').removeClassName('notNullOK');
        }
        input.observe('change', Prototype.emptyFunction).observe('keyup', Prototype.emptyFunction).observe('ui:change', Prototype.emptyFunction);
    },

    /* Specific notNullOK method because the base one doesn't handle the me_form_field */
    notNullOK: function (event) {
        let element = event.element ? event.element() : event,
              label = Jfse.getInputLabel(element);

        if (label) {
            label.className = ($V(element.form[element.name]) ? "notNullOK" : "notNull");
        }
    },

    getInputLabel: function (input) {
        let label = input.up('div').down('label');

        if (input.type == 'radio' || (!label && input.type == 'select-one')) {
            label = input.up('div').next('label');
        }

        return label;
    }
};
