/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

window.children = {};

Ajax.__uniqueID = 1;
Ajax.Responders.register({
    onCreate: function (e) {
        Url.activeRequests[e.method]++;
        var add;
        if (MbPerformance.profiling) {
            var uniqueID = Ajax.__uniqueID++;
            e.transport.__uniqueID = uniqueID;

            add = (e.url.indexOf("?") > -1) ? "&" : "?";
            e.url += add + "__uniqueID=|" + uniqueID + "|";
        }
        add = (e.url.indexOf("?") > -1) ? "&" : "?";
        e.url += add + "__requestID=" + Url.requestId;
    },
    onLoading: function (e) {
        e.__start = performance.now();
    },
    onLoaded: function (e) {
        MbPerformance.timeStart("eval");
    },
    onComplete: function (e) {
        MbPerformance.timeEnd("eval", e.transport.__uniqueID);

        Url.activeRequests[e.method]--;

        // Get server timings
        if (MbPerformance.profiling) {
            var transport = e.transport;
            var timer = transport.getResponseHeader("X-Mb-Timing");
            if (timer) {
                var now = performance.now(),
                    req = transport.getResponseHeader("X-Mb-Req").split("|"),
                    uid = transport.getResponseHeader("X-Mb-RequestUID"),
                    reqInfo = transport.getResponseHeader("X-Mb-RequestInfo"),
                    timing = MbPerformance.parseServerTiming(reqInfo),
                    page = {
                        m: req[0],
                        a: req[1],
                        id: transport.__uniqueID,
                        guid: uid
                    };

                var serverTiming = timer.evalJSON();

                if (timing) {
                    serverTiming.handlerStart = timing.start;
                    serverTiming.handlerEnd = timing.duration + timing.start;
                }

                MbPerformance.logScriptEvent.delay(1, "ajax", page, serverTiming, e.__start, now - e.__start);
            }
        }
    },
    onException: function (e) {
        Url.activeRequests[e.method]--;
    }
});

/**
 * Url Class
 * Lazy poping and ajaxing
 */
var Url = Class.create({
    /**
     * Url constructor
     *
     * @param {String=} sModule Module name
     * @param {String=} sAction Action name
     * @param {String=} sMode   Mode: "action", "tab", "dosql" or "raw"
     */
    initialize: function (sModule, sAction, sMode) {
        sMode = sMode || "action";

        this.oParams = {};
        this.oWindow = null;
        this.sFragment = null;
        this.oPrefixed = {};
        this.currentAjax = null;
        this.resourcePath = null;

        if (sModule && sAction) {
            switch (sMode) {
                case 'action' :
                    this.setModuleAction(sModule, sAction);
                    break;
                case 'tab'    :
                    this.setModuleTab(sModule, sAction);
                    break;
                case 'dosql'  :
                    this.setModuleDosql(sModule, sAction);
                    break;
                case 'raw'    :
                    this.setModuleRaw(sModule, sAction);
                    break;
                default:
                    console.error('Url type incorrect : ' + sMode);
            }
        } else if (sModule) {
            this.resourcePath = sModule;
        }
    },

    /**
     * Set module and action
     *
     * @param {String} sModule Module name
     * @param {String} sAction Action name
     *
     * @return {Url}
     */
    setModuleAction: function (sModule, sAction) {
        return this.addParam("m", sModule)
            .addParam("a", sAction);
    },

    /**
     * Set module and tabulation
     *
     * @param {String} sModule Module name
     * @param {String} sTab    Tabulation name
     *
     * @return {Url}
     */
    setModuleTab: function (sModule, sTab) {
        return this.addParam("m", sModule)
            .addParam("tab", sTab);
    },

    /**
     * Set module and dosql
     *
     * @param {String} sModule Module name
     * @param {String} sDosql  Dosql name
     *
     * @return {Url}
     */
    setModuleDosql: function (sModule, sDosql) {
        return this.addParam("m", sModule)
            .addParam("dosql", sDosql);
    },

    /**
     * Set the module name and "raw" flag
     *
     * @param {String} sModule Module name
     * @param {String} sRaw    Raw action name
     *
     * @return {Url}
     */
    setModuleRaw: function (sModule, sRaw) {
        return this.addParam("m", sModule)
            .addParam("raw", sRaw);
    },

    /**
     * Set the URL fragment (part after the #), useful for popups
     *
     * @param {String} sFragment Fragment
     *
     * @return {Url}
     */
    setFragment: function (sFragment) {
        this.sFragment = sFragment;
        return this;
    },

    /**
     * Add a parameter value to the URL request
     *
     * @param {String}   sName        Parameter name
     * @param {*}        sValue       Parameter value
     * @param {Boolean=} bAcceptArray Accept array values
     *
     * @return {Url}
     */
    addParam: function (sName, sValue, bAcceptArray) {
        if (bAcceptArray && Object.isArray(sValue)) {
            $A(sValue).each(function (elt, i) {
                this.oParams[sName.replace(/\[([^\[]*)\]$/, "[" + i + "]")] = elt;
            }, this);
            return this;
        }
        this.oParams[sName] = sValue;
        return this;
    },

    /**
     * Add a parameter value to the URL request only if its value evaluates to true
     *
     * @param {String}   sName        Parameter name
     * @param {*}        sValue       Parameter value
     * @param {Boolean=} bAcceptArray Accept array values
     *
     * @return {Url}
     */
    addNotNullParam: function (sName, sValue, bAcceptArray) {
        if (sValue) {
            return this.addParam(sName, sValue, bAcceptArray);
        }

        return this;
    },

    /**
     * Add an object parameter to the URL request
     *
     * @param {String} sName   Parameter name
     * @param {Object} oObject Parameter value
     *
     * @return {Url}
     */
    addObjectParam: function (sName, oObject) {
        if (typeof oObject != "object") {
            return this.addParam(sName, oObject);
        }

        // Recursive call
        $H(oObject).each(function (pair) {
            this.addObjectParam(printf("%s[%s]", sName, pair.key), pair.value);
        }, this);

        return this;
    },

    /**
     * Add form data to the parameters
     *
     * @param {HTMLFormElement} oForm The form
     *
     * @return {Url}
     */
    addFormData: function (oForm) {
        Object.extend(this.oParams, getForm(oForm).serialize(true));
        return this;
    },

    /**
     * Merge the params with the object
     *
     * @param {Object} oObject
     *
     * @return {Url}
     */
    mergeParams: function (oObject) {
        Object.extend(this.oParams, oObject);
        return this;
    },

    /**
     * Add element value to the parameters
     * Won't work with radio button, use addRadio() instead
     *
     * @param {HTMLInputElement,HTMLSelectElement,HTMLTextAreaElement} oElement   The element to add to the data
     * @param {String=}                                                sParamName The parameter name
     *
     * @return {Url}
     */
    addElement: function (oElement, sParamName) {
        if (!oElement) {
            return this;
        }

        if (!sParamName) {
            sParamName = oElement.name;
        }

        var value = oElement.value;
        if (oElement.type == 'checkbox') {
            value = $V(oElement) ? 1 : 0;
        }

        return this.addParam(sParamName, value);
    },

    /**
     * Add element not null value to the parameters
     * Won't work with radio button, use addRadio() instead
     *
     * @param {HTMLInputElement,HTMLSelectElement,HTMLTextAreaElement} oElement The element to add to the data
     * @param {String}      sParamName The parameter name
     *
     * @return {Url}
     */
    addNotNullElement: function (oElement, sParamName) {
        if (!oElement) {
            return this;
        }

        if (!sParamName) {
            sParamName = oElement.name;
        }

        var value = oElement.value;
        if (oElement.type == 'checkbox') {
            value = $V(oElement) ? 1 : 0;
        }

        return this.addNotNullParam(sParamName, value);
    },

    /**
     * Add radio button value to the parameters
     *
     * @param {NodeList} oButtons   The buttons
     * @param {String}   sParamName The parameter name
     *
     * @return {Url}
     */
    addRadio: function (oButtons, sParamName) {
        if (!oButtons) {
            return this;
        }

        if (!sParamName) {
            sParamName = oButtons[0].name;
        }

        var value = $V(oButtons);

        return this.addParam(sParamName, value);
    },


    /**
     * Build an URL string
     *
     * @param {Boolean=} questionMark Add the question mark or the ampersand at the beginning
     *
     * @return {String} The URL string
     */
    make: function (questionMark) {
        var params = $H(this.oParams);
        var sUrl = "";

        if (params.size()) {
            sUrl = (questionMark ? "&" : "?") + params.toQueryString();
        }
        if (this.sFragment) {
            sUrl += "#" + this.sFragment;
        }

        return sUrl;
    },

    /**
     * Build an absolute URL string
     *
     * @param {Boolean=} questionMark Add the question mark or the ampersand at the beginning
     *
     * @return {String} The URL string
     */
    makeAbsolute: function (questionMark) {
        var sUrl = window.location.href;
        sUrl = sUrl.substring(0, sUrl.lastIndexOf('/') + 1);
        sUrl += this.make(questionMark);
        return sUrl;
    },

    makeAbsoluteApi: function (path, questionMark) {
      let sUrl = window.location.href;
      return sUrl.substring(0, sUrl.lastIndexOf('/')) + path + this.make(questionMark);
    },

    /**
     * @return {Url}
     */
    open: function (sBaseUrl) {
        var uri = decodeURI(this.make(!!sBaseUrl));
        uri = (sBaseUrl ? sBaseUrl : "") + uri;

        (this.oWindow || window).open(uri);

        return this;
    },

    /**
     * @param {String=} sBaseUrl The base URL
     *
     * @return {Url}
     */
    redirect: function (sBaseUrl) {
        var uri = decodeURI(this.make(!!sBaseUrl));

        (this.oWindow || window).location.href = (sBaseUrl ? sBaseUrl : "") + uri;

        return this;
    },

    /**
     * @return {void}
     */
    redirectOpener: function () {
        if (window.opener && !window.opener.closed) {
            try {
                window.opener.location.assign(this.make());
            } catch (e) {
                // To prevent cross origin errors
                this.redirect();
            }
        } else {
            this.redirect();
        }
    },

    /**
     * @return {Object}
     */
    getPopupFeatures: function () {
        return Object.clone(Url.popupFeatures);
    },

    /**
     * Open a popup window
     *
     * @param {Number,String}      iWidth
     * @param {Number,String}      iHeight
     * @param {String=}            sWindowName
     * @param {String=}            sBaseUrl
     * @param {String=}            sPrefix
     * @param {Object=}            oPostParameters
     * @param {HTMLIFrameElement=} iFrame
     *
     * @return {Url}
     */
    pop: function (iWidth, iHeight, sWindowName, sBaseUrl, sPrefix, oPostParameters, iFrame) {
        var features = this.getPopupFeatures();

        features = Object.extend(features, {
            width: iWidth,
            height: iHeight
        });

        if (features.height == "100%" || features.width == "100%") {
            if (features.width == "100%") {
                //features.fullscreen = true; // REALLY invasive under IE
                //features.type = "fullWindow";
                features.width = screen.availWidth || screen.width;
                features.left = 0;
            }

            if (features.height == "100%") {
                features.height = screen.availHeight || screen.height;
                features.top = 0;
            }
        }

        sWindowName = sWindowName || "";
        sBaseUrl = sBaseUrl || "";

        var questionMark = true;
        if (!sBaseUrl) {
            if (!this.oParams.raw && !this.oParams.dialog) {
                this.addParam("dialog", 1);
            }
            questionMark = false;
        }

        // the Iframe argument is used when exporting data (export_csv_array for ex.)
        if (!iFrame) {
            var sFeatures = Url.buildPopupFeatures(features);

            // Prefixed window collection
            if (sPrefix && this.oPrefixed[sPrefix]) {
                this.oPrefixed[sPrefix] = this.oPrefixed[sPrefix].reject(function (oWindow) {
                    return oWindow.closed;
                });
            }

            // Forbidden characters for IE
            if (Prototype.Browser.IE) {
                sWindowName = sWindowName.replace(/[^a-z0-9_]/gi, "_");
            }

            var wasClosedBefore = !window.children[sWindowName] || window.children[sWindowName].closed;

            try {
                this.oWindow = window.open(oPostParameters ? "" : (sBaseUrl + this.make(questionMark)), sWindowName, sFeatures);
            } catch (e) {
                // window.open failed :(
            }

            if (!this.oWindow) {
                return this.showPopupBlockerAlert(sWindowName);
            }

            window.children[sWindowName] = this.oWindow;

            if (wasClosedBefore && this.oWindow.history && this.oWindow.history.length == 0) {
                // bug in Chrome 18: invisible popup
                if (bowser.name != "Chrome") {
                    this.oWindow.moveTo(features.left, features.top);
                    this.oWindow.resizeTo(features.width, features.height);
                }
            }
        }

        if (oPostParameters) {
            var form = DOM.form({
                method: "post",
                action: sBaseUrl + this.make(questionMark),
                target: (iFrame ? iFrame.getAttribute("name") : sWindowName)
            });

            $(document.documentElement).insert(form);

            Form.fromObject(form, oPostParameters, true);
            form.submit();
            form.remove();
        }

        // Prefixed window collection
        if (sPrefix) {
            if (!this.oPrefixed[sPrefix]) {
                this.oPrefixed[sPrefix] = [];
            }
            this.oPrefixed[sPrefix].push(this.oWindow);
        }

        return this;
    },

    /**
     * Open a modal window
     *
     * @param {Object=} options
     *
     * @return {Url}
     */
    modal: function (options) {
        var closeButton = DOM.button({type: "button", className: "close notext me-primary"});

        options = Object.extend({
            className: 'modal popup',
            width: 900,
            height: 600,
            iframe: true,
            title: "",
            baseUrl: "",
            closeOnClick: closeButton,
            closeOnEscape: true,
            onClose: null,
            draggable: App.config.modal_windows_draggable,
            onComplete: null,
            canCloseIf: null
        }, options);

        var questionMark = false;
        if (!options.baseUrl) {
            if (!this.oParams.raw) {
                this.addParam("dialog", 1);
            }

            // Flag telling Mediboard to decode UTF-8, because data comes from JS
            this.addParam("is_utf8", 1);

            // Dummy timestamp to allow iframe inside iframes recursion, with the same URL
            this.addParam("__ts", Date.now());
        } else if (options.baseUrl.indexOf("?") > -1) {
            questionMark = true;
        }

        var titleElement = DOM.div({className: "title"},
            (App.config.instance_role === "qualif") ? DOM.div({className: "me-modal-ribbon"}, "Qualif") : null,
            DOM.span({className: "left"},
                options.title || "&nbsp;"
            ),
            DOM.span({className: "right"})
        );

        if (options.draggable) {
            options.draggable = titleElement;
        }

        var style = Modal.prepareDimensions({
            width: options.width,
            height: options.height
        });

        // Do not pass dimensions to Control.Modal.open
        delete options.height;
        delete options.width;

        if (options.maxHeight) {
            style.maxHeight = String.getCSSLength(options.maxHeight);
        }
        var href = options.baseUrl + this.make(questionMark);
        delete this.oParams.__ts;

        this.modalObject = Control.Modal.open(new Element("a", {href: 'about:blank'}), options);

        var modalContainer = this.modalObject.container;
        modalContainer.insert({top: titleElement});

        // Wrap iframe with div.content
        var iframe = modalContainer.down("iframe");
        var content = DOM.div({className: "content"}, iframe);
        modalContainer.insert(content);
        modalContainer.addClassName("modal-iframe");
        iframe.src = href;
        if (options.onComplete instanceof Function) {
            iframe.onload = options.onComplete.bind(iframe.contentWindow)
        }

        /*var href = options.baseUrl + this.make(questionMark);
        var content = DOM.div({className: "content"});
        var modalContainer = DOM.div({href: href, className: "modal-iframe"}, titleElement, content);
        //modalContainer.identify();

        $(document.body).insert(modalContainer);

        options.insertRemoteContentAt = content;
        this.modalObject = Control.Modal.open(modalContainer, options);*/

        style.paddingTop = titleElement.getHeight() + "px";
        modalContainer.setStyle(style);

        this.modalObject.position();

        if (options.closeOnClick) {
            titleElement.down(".right").insert(closeButton);
        }

        // iframe.onload not thrown under IE
        if (Prototype.Browser.IE) {
            var that = this.modalObject;
            var iframe = that.container.down("iframe");

            iframe.onload = null;
            iframe.onreadystatechange = function () {
                if (iframe.readyState !== "complete") {
                    return;
                }

                that.notify('onRemoteContentLoaded');
                if (that.options.indicator) {
                    that.hideIndicator();
                }

                iframe.onreadystatechange = null;
            }
        }

        var m = this.oParams.m,
            a = this.oParams.a;

        // Observe remote content loading
        this.modalObject.observe("onRemoteContentLoaded", (function () {
            var iframeWindow = this.container.down("iframe").contentWindow;

            if (!options.title) {
                titleElement.down("span").update(Localize.first('mod-' + m + '-tab-' + a, 'mod-dP' + m + '-tab-' + a));
            }

            if (!options.closeOnEscape) {
                iframeWindow.document.stopObserving('keydown', iframeWindow.closeWindowByEscape);
            }

            this.position();

        }).bind(this.modalObject));

        if (modalContainer.getDimensions().height > window.innerHeight - 112) {
            modalContainer.addClassName("me-full-height");

            modalContainer.down(".title>.right").append(
                DOM.div(
                    {
                        className: "me-date me-date-modal"
                    },
                    MediboardExt.updateDate().displayDate()
                )
            );
        }
        if (options.canCloseIf instanceof Function) {
            this.modalObject.observe("beforeClose", function () {
                if (!options.canCloseIf()) {
                    throw $break;
                }
            });
        }
        // Observe modal closing
        if (options.onClose) {
            this.modalObject.observe("afterClose", options.onClose.bindAsEventListener(this));
        }

        // Remove container, 5 seconds later
        this.modalObject.observe("afterClose", (function () {
            (function (element) {
                element.remove();
            }).delay(5, this);
        }).bind(modalContainer));

        return this;
    },

    /**
     * Opens a popup window
     *
     * @param {Number=} iWidth
     * @param {Number=} iHeight
     * @param {String=} sWindowName
     * @param {String=} sBaseUrl
     *
     * @return {Url}
     */
    popDirect: function (iWidth, iHeight, sWindowName, sBaseUrl) {
        iWidth = iWidth || 800;
        iHeight = iHeight || 600;
        sWindowName = sWindowName || "";
        sBaseUrl = sBaseUrl || "";

        var sFeatures = Url.buildPopupFeatures({height: iHeight, width: iWidth});

        // Forbidden characters for IE
        if (Prototype.Browser.IE) {
            sWindowName = sWindowName.replace(/[^a-z0-9_]/gi, "_");
        }
        var questionMark = sBaseUrl.indexOf("?") != -1;
        this.oWindow = window.open(sBaseUrl + this.make(questionMark), sWindowName, sFeatures);
        window.children[sWindowName] = this.oWindow;

        if (!this.oWindow) {
            this.showPopupBlockerAlert(sWindowName);
        }

        return this;
    },

    /**
     * Opens a popup window
     *
     * @param {Number}  iWidth          Popup width
     * @param {Number}  iHeight         Popup height
     * @param {String=} sWindowName     Popup internal name
     * @param {String=} sPrefix         Popup name prefix
     * @param {Object=} oPostParameters Popup POST parameters
     * @param {String=} sBaseUrl
     *
     * @return {Url}
     */
    popup: function (iWidth, iHeight, sWindowName, sPrefix, oPostParameters, sBaseUrl) {
        this.pop(iWidth, iHeight, sWindowName, sBaseUrl, sPrefix, oPostParameters);

        // Prefixed window collection
        if (sPrefix) {
            (this.oPrefixed[sPrefix] || []).each(function (oWindow) {
                oWindow.blur(); // Chrome issue
                oWindow.focus();
            });
        }

        if (this.oWindow) {
            this.oWindow.blur(); // Chrome issue
            this.oWindow.focus();
        } else {
            this.showPopupBlockerAlert(sWindowName);
        }

        return this;
    },

    /**
     * Show an alert telling the popup could not be opened
     *
     * @param {String} popupName The name of the popup the message is referring to
     *
     * @return {Url}
     */
    showPopupBlockerAlert: function (popupName) {
        Modal.alert($T("Popup blocker alert", popupName));
        return this;
    },

    /**
     * Initializes an autocompleter
     *
     * @param {HTMLInputElement,String} input    Input to autocomplete
     * @param {HTMLElement,String}      populate The element which will receive the response list
     * @param {Object=}                 oOptions Various options
     *
     * @return {Ajax.Autocompleter|Boolean}
     */
    autoComplete: function (input, populate, oOptions) {
        var saveInput = input;
        input = $(input);

        if (!input) {
            try {
                console.warn((saveInput || "$(input)") + " doesn't exist [Url.autoComplete]");
            } catch (e) {
            }

            return false;
        }

        if ($(input.form).isReadonly()) {
            input.removeClassName("autocomplete");
            return false;
        }

        var autocompleteDelays = {
            "short": 0.5,
            "medium": 1.0,
            "long": 1.5
        };

        oOptions = Object.extend({
            minChars: 2,
            frequency: autocompleteDelays[Preferences.autocompleteDelay],
            width: null,
            inputWidth: null,
            dropdown: false,
            valueElement: null,
            localStorage: false,

            // Allows bigger width than input
            onShow: function (element, update) {
                update.style.position = "absolute";

                var elementDimensions = element.getDimensions();

                update.show().clonePosition(element, {
                    setWidth: true,
                    setHeight: false,
                    setTop: false,
                    setLeft: false
                });

                // Default width behaviour
                var style = {
                    width: "auto",
                    whiteSpace: "nowrap",
                    minWidth: elementDimensions.width + "px",
                    maxWidth: "400px"
                };

                // Fixed width behaviour
                if (oOptions.width) {
                    style = {
                       width: oOptions.width
                    };
                }

                // Default positionning
                update.up().setStyle({ position: "relative" });
                style.top = parseInt(elementDimensions.height + 1) + "px";
                style.left = "0px";

                update.setStyle(style)
                    .setOpacity(1);

                // Responses window overflow
                var scroll = element.cumulativeScrollOffset(); // field offset
                var viewport = document.viewport.getDimensions(); // Viewport size
                var scrollOffset = update.cumulativeOffset();
                var updateHeight = update.getHeight();

                var overflowBottom = parseInt((scrollOffset.top + updateHeight) - (viewport.height + scroll.top))
                if (overflowBottom > 0) {
                  update.setStyle({ top: "unset", bottom: parseInt(0 + update.up().getBoundingClientRect().height) + "px" })
                }

                if (oOptions.onAfterShow) {
                    oOptions.onAfterShow(element, update);
                }
            },

            onHide: function (element, update) {
                update.scrollTop = 0;
                update.setStyle({ top: "0px", bottom: "unset" })
                Element.hide(update);
            }
        }, oOptions);

        input.addClassName("autocomplete");

        populate = $(populate);
        if (!populate) {
            populate = new Element("div").addClassName("autocomplete").hide();
            input.insert({after: populate});
        }

        // Autocomplete
        this.addParam("ajax", 1);

        if (oOptions.valueElement) {
            oOptions.afterUpdateElement = function (input, selected) {
                var valueElement = $(selected).down(".value");
                var value = valueElement ? valueElement.innerHTML.strip() : selected.innerHTML.stripTags().strip();
                $V(oOptions.valueElement, value);
            };

            var clearElement = function () {
                if ($V(input) == "") {
                    $V(oOptions.valueElement, "");
                }
            };

            input.observe("change", clearElement).observe("ui:change", clearElement);
        }

        var autocompleter = new Ajax.Autocompleter(input, populate, this.make(), oOptions);

        if (oOptions.localStorage) {
            autocompleter.set = (function () {
                var url = new Url(this.url.match(/m=([^&]+)&/)[1], this.url.match(/a=([^&]+)&/)[1]);

                var split = (this.url + "&" + this.options.callback().params + "&" + this.options.defaultParams).split("&");

                // Delete module and action
                split.shift();
                split.shift();

                split.each(function (_split) {
                    var explode = _split.split("=");
                    url.addParam(explode[0], explode[1]);
                });

                var key = oOptions.callback().key;

                // Verrou pour éviter un autre lancement du get
                window.AideSaisie.lock_get[key] = true;

                window.AideSaisie.gcLocalStorage();

                url.requestJSON((function (result) {
                    // Expiration dans 5 minutes si pour une quelconque raison il n'y a pas de résultat
                    if (!result) {
                        result = {
                            expire: Date.now() / 1000 + 300,
                            data: {
                                aides: {}
                            }
                        };
                    }

                    // if (window.LZString) {
                    //   result.lz = LZString.compress(Object.toJSON(result.data));
                    //   var data = result.data;
                    //   delete result.data;
                    // }

                    store.set(key, result);

                    // if (window.LZString) {
                    //   result.data = data;
                    //   delete result.lz;
                    // }

                    window.AideSaisie.cache[key] = result;

                    // On enlève le verrou sur le get
                    window.AideSaisie.lock_get[key] = false;

                    if (this.options.callbackLocalStorage) {
                        var callback = this.options.callbackLocalStorage;
                        this.options.callbackLocalStorage = null;
                        return callback();
                    }
                }).bind(this));
            }).bind(autocompleter);

            autocompleter.get = (function () {
                var key = oOptions.callback().key;

                var result = null;

                // Si l'obtention des aides est en cours,
                // on stoppe le get
                if (window.AideSaisie.lock_get[key]) {
                    return result;
                }

                if (window.AideSaisie.cache[key]) {
                    result = window.AideSaisie.cache[key];
                } else {
                    result = store.get(key);

                    // if (result && result.lz && window.LZString) {
                    //   result.data = LZString.decompress(result.lz).evalJSON();
                    //   delete result.lz;
                    // }

                    window.AideSaisie.cache[key] = result;
                }

                if (result && result.expire && result.expire > (Date.now() / 1000)) {
                    this.options.callbackLocalStorage = null;
                    return result.data;
                }

                this.set();
                return false;
            }).bind(autocompleter);

            function makeKey(aide) {
                aide.key = [aide.gid, aide.fid, aide.uid, aide.d1, aide.d2, aide.n, (!Object.isUndefined(aide.t) ? aide.t : "")].join('|');
            }

            function makeOwner(aide, owners) {
                var gid, fid, uid;

                if (gid = aide.gid || (!aide.gid && !aide.fid && !aide.uid)) {
                    aide._ov = aide._ov || owners.g[gid];
                    aide._o = "group";
                    return;
                }
                if (fid = aide.fid) {
                    aide._ov = aide._ov || owners.f[fid];
                    aide._o = "function";
                    return;
                }
                if (uid = aide.uid) {
                    aide._ov = aide._ov || owners.u[uid];
                    aide._o = "user";
                }
            }

            autocompleter.search = (function (with_text) {
                if (this.options.callbackLocalStorage) {
                    this.options.callbackLocalStorage = null;
                    return;
                }

                var list = this.get();

                if (!list) {
                    this.options.callbackLocalStorage = this.search.curry(with_text).bind(this);
                    return;
                }

                // No results
                if ("aides" in list && list.aides.length == 0) {
                    return this.updateChoices("<ul></ul>");
                }

                var dependFields = this.options.getDependFields();
                var property = this.options.getProperty();
                var results = [];
                var limit = 1000;

                // No research, display all the helpers
                if (!with_text) {
                    var count_aides = 0;
                    Object.keys(list.aides).each(function (key) {
                        var aide = list.aides[key];
                        if (aide.f != property) {
                            return;
                        }

                        if ((!dependFields.dependField1 || !aide.d1 || dependFields.dependField1 == aide.d1) &&
                            (!dependFields.dependField2 || !aide.d2 || dependFields.dependField2 == aide.d2)) {
                            makeKey(aide);
                            makeOwner(aide, list.owners);
                            results.push(aide);

                            count_aides++;

                            if (count_aides >= limit) {
                                throw $break;
                            }
                        }
                    });

                    // Tri des aides
                    results.sort(function (a, b) {
                        return a.key.localeCompare(b.key)
                    });

                    if (count_aides == limit) {
                        results.push({
                            n: "<i>Il y a plus de " + limit + " résultats, saisissez un ou plusieurs mots-clés pour affiner la recherche.</i>",
                            t: "",
                            links: []
                        });
                    }

                    return this.displaySearch(results, with_text);
                }

                var tokens = this.getToken();
                if (!tokens) {
                    return;
                }

                tokens = tokens.toLowerCase().removeDiacritics().replace(/%/g, "").split(/[\s!"\#$%&'()*+,\-\.\/:;<=>?@\[\]\\^_`{|}~]+/);

                var stop_words = $T("CAideSaisie-stop_words").split(" ");

                var occurences_by_token = {};
                var occurences = {};
                var results_temp = [];

                var keys_tokens = Object.keys(list.by_token);

                tokens.each(function (_token_search) {
                    var reg_token_search = new RegExp(_token_search);
                    keys_tokens.each(function (_token) {
                        // Exclusion des stop words
                        if (stop_words.indexOf(_token) !== -1) {
                            return;
                        }
                        // Recherche parmi les tokens
                        if (reg_token_search.test(String(_token))) {
                            list.by_token[_token].each(function (_aide) {
                                if (Object.isUndefined(occurences_by_token[_token_search])) {
                                    occurences_by_token[_token_search] = {};
                                }
                                // Sous-ensemble des aides par token
                                occurences_by_token[_token_search][_aide] = 1;
                            });
                        }
                    });
                });

                // Intersection des listes par token
                Object.keys(occurences_by_token).each(function (_key_token) {
                    Object.keys(occurences_by_token[_key_token]).each(function (_aide) {
                        if (Object.isUndefined(occurences[_aide])) {
                            occurences[_aide] = 1;
                            return;
                        }
                        occurences[_aide]++;
                    });
                });

                Object.keys(occurences).each(function (_key) {
                    var aide = list.aides[_key];

                    // Si autant d'occurences que le nombre de tokens après découpe de la chaîne de caractères cherché
                    if (occurences[_key] == tokens.length) {
                        if (property != aide.f) {
                            return;
                        }
                        // On filtre également sur les depends fields si nécessaire
                        if ((!dependFields.dependField1 || !aide.d1 || dependFields.dependField1 == aide.d1) &&
                            (!dependFields.dependField2 || !aide.d2 || dependFields.dependField2 == aide.d2)) {
                            makeKey(aide);
                            makeOwner(aide, list.owners);
                            results_temp.push(aide);
                        }
                    }
                });

                // Tri des aides
                results_temp.sort(function (a, b) {
                    return a.key.localeCompare(b.key)
                });

                return this.displaySearch(results_temp, with_text);
            }).bind(autocompleter);

            autocompleter.displaySearch = (function (results, with_text) {
                var token = this.getToken().replace(/%/g, "");

                var result_html = "<ul>";

                var results_keys = Object.keys(results);
                if (results_keys.length) {

                    results_keys.each((function (key) {
                        var result = results[key];

                        var result_links = "";
                        if (result.links) {
                            result.links.each(function (link) {
                                result_links +=
                                    '<a href="#{link}" data-link_id="#{link_id}" target="_blank" class="hypertext_links" style="display: none;">#{link_name}</a>'.interpolate({
                                        link: link.link,
                                        link_id: link.id,
                                        link_name: link.name
                                    });
                            });
                        }

                        var result_t;

                        if (!Object.isUndefined(result.t)) {
                            result_t = result.t.escapeHTML();
                        }

                        var result_n = result.n.escapeHTML();

                        result_html +=
                            '<li class="#{result_owner}" title="#{result_owner_view}">\
                               <div class="depend1" style="display: none;">#{result_depend_value_1}</div>\
                               <div class="depend2" style="display: none;">#{result_depend_value_2}</div>\
                               <strong>#{str_depend}</strong>\
                               <span>#{result_name}</span>\
                               <br />\
                               <small class="text" style="color: #666; margin-left: 1em;">#{result_text}</small>\
                               <div class="value" style="display: none; white-space: pre;">#{result_text_full}</div>\
                               #{result_links}\
                             </li>'.interpolate({
                                result_owner: result._o,
                                result_owner_view: result._ov,
                                result_depend_value_1: result.d1,
                                result_depend_value_2: result.d2,
                                str_depend: (result.d1 ? (result._vd1 + " - ") : "") +
                                    (result.d2 ? (result._vd2 + " - ") : ""),
                                result_name: with_text ? this.emphasize(result_n, token) : result_n,
                                result_text: with_text ? this.emphasize(result_t ? result_t : result_n, token) : result_t,
                                result_text_full: result_t ? result_t : result_n,
                                result_links: result_links
                            });

                    }).bind(this));
                }
                /*else {
                  ul.insert(DOM.li({}, $T("CAideSaisie.none"), DOM.small({"class": "value", "style": "display: none;"}, token)));
                }*/

                result_html += "</ul>";

                return this.updateChoices(result_html);
            }).bind(autocompleter);

            autocompleter.emphasize = (function (text, token) {
                var tokens = token.split(" ");

                var tokens_regex = [];
                tokens.each(function (_token) {
                    tokens_regex.push(RegExp.escape(_token).allowDiacriticsInRegexp());
                });

                var regex = new RegExp("(" + tokens_regex.join("|") + ")", "ig");

                return text.replace(regex, "<em>$1</em>");
            }).bind(autocompleter);

            autocompleter.getUpdatedChoices = (function () {
                this.startIndicator();
                this.search(this.getToken().length);
            }).bind(autocompleter);
        } else {
            autocompleter.getUpdatedChoices = (function () {
                this.startIndicator();

                var entry = encodeURIComponent(this.options.paramName) + '=' +
                    encodeURIComponent(this.getToken());

                this.options.parameters = this.options.callback ?
                    this.options.callback(this.element, entry) : entry;

                if (this.options.defaultParams) {
                    this.options.parameters += '&' + this.options.defaultParams;
                }

                if (this.currentAjax) {
                    this.currentAjax.abort();
                }
                this.currentAjax = new Ajax.Request(this.url, this.options);
            }).bind(autocompleter);
        }

        // Pour "eval" les scripts inserés (utile pour lancer le onDisconnected
        autocompleter.options.onComplete = function (request) {
            var content = request.responseText;
            // remove html comments
            content = content.replace(/<!--[^>]+-->/g, '');

            content.evalScripts.bind(content).defer();
            this.updateChoices(content);
        }.bind(autocompleter);

        autocompleter.startIndicator = function () {
            if (this.options.indicator) {
                Element.show(this.options.indicator);
            }
            input.addClassName("throbbing")
                .parentElement.addClassName("throbbing");
            if (this.request) {
                this.request.abort();
            }
        };
        autocompleter.stopIndicator = function () {
            if (this.options.indicator) {
                Element.hide(this.options.indicator);
            }
            input.removeClassName("throbbing")
                .parentElement.removeClassName("throbbing");
        };

        ///////// to prevent IE (and others in some cases) from closing the autocompleter when using the scrollbar of the update element
        function onUpdateFocus(event) {
            this.updateHasFocus = true;
            Event.stop(event);
        }

        function resetUpdateFocus(event) {
            if (!this.updateHasFocus) {
                return;
            }
            this.updateHasFocus = false;
            this.onBlur(event);
        }

        Event.observe(populate, 'mousedown', onUpdateFocus.bindAsEventListener(autocompleter));
        document.observe('click', resetUpdateFocus.bindAsEventListener(autocompleter));
        /////////

        // Drop down button, like <select> tags
        var container = new Element("div").addClassName("dropdown");
        input
            .observe("focus", function () {
                container.addClassName("input-focus");
            })
            .observe("blur", function () {
                container.removeClassName("input-focus");
            });

        if (input && input.wrap && Object.isFunction(input.wrap)) {
            input.wrap(container);
            if (populate) {
              container.insert(populate);
            }
        }
        if (oOptions.dropdown) {
            container.addClassName("dropdown-group");

            if (oOptions.inputWidth) {
                container.addClassName("fixed-width").setStyle({
                    width: oOptions.inputWidth
                });

                input.setStyle({
                    width: '100%'
                });
            }

            container.insert(populate);

            // The trigger button
            var trigger = new Element("div").addClassName("dropdown-trigger");
            trigger.insert(new Element("div"));

            // Hide the list
            var hideAutocomplete = function (e) {
                autocompleter.onBlur(e);
                //$$("div.autocomplete").invoke("hide");
            }.bindAsEventListener(this);

            // Show the list
            var showAutocomplete = function (e, dontClear) {
                var oldValue;

                if (!dontClear) {
                    oldValue = $V(input);
                    $V(input, '', false);
                }

                autocompleter.activate.bind(autocompleter)();
                Event.stop(e);
                document.observeOnce("mousedown", hideAutocomplete);

                if (!dontClear) {
                    $V(input, oldValue, false);
                }

                input.select();
            };

            // Bind the events
            trigger.observe("mousedown", showAutocomplete.bindAsEventListener(this));
            //input.observe("click", showAutocomplete.bindAsEventListener(this, true));
            input.observe("click", function () {
                var valueElement = oOptions.valueElement;

                if (valueElement && valueElement.value == "") {
                    input.value = "";
                } else if (valueElement && valueElement.hasClassName("ref")) {
                    try {
                        input.select();
                    } catch (e) {
                    }
                }

                input.fire("ui:change");
                autocompleter.activate.bind(autocompleter)();
            });
            populate.observe("mousedown", Event.stop);

            container.insert(trigger);
        }

        return autocompleter;
    },

    /**
     * Close the popup window
     *
     * @return {Url}
     */
    close: function () {
        if (this.oWindow) {
            this.oWindow.close();
        }
        return this;
    },

    /**
     * Open a modal window via an Ajax request
     *
     * @param {Number,String=} width
     * @param {Number,String=} height
     * @param {Object=}        options
     *
     * @return {Url}
     */
    requestModal: function (width, height, options) {

        var m = this.oParams.m,
            a = this.oParams.a;

        // onComplete callback definition shortcut
        if (options instanceof Function) {
            options = {
                onComplete: options
            };
        }

        options = Object.extend({
            title: Localize.first('mod-' + m + '-tab-' + a, 'mod-dP' + m + '-tab-' + a),
            showReload: Preferences.INFOSYSTEM == 1,
            showClose: true,
            //onClose: null,
            container: null,
            carrousel: null,
            canCloseIf: null
        }, options);

        if (width) {
            var width_large = parseInt(width) * 1.2;
            if (width.toString().indexOf('%') > 0) {
                width = Math.min(100, width_large) + '%';
            } else {
                width = width_large + ((width.toString().indexOf('px')) ? 'px' : '');
            }
        }
        var style = Modal.prepareDimensions({
            width: width,
            height: height
        });

        var classes = "";
        if (options.maxHeight) {
            classes += "modal-max-height";
            style.maxHeight = String.getCSSLength(options.maxHeight);
        }

        classes += options.showReload ? " reloadable" : "";
        classes += options.incrustable ? " incrustable" : "";

        var modalContainer = options.container;

        if (!modalContainer) {
            modalContainer = DOM.div(null, null);
            $(document.body).insert(modalContainer);
        }

        // Si l'option carrousel est disponible
        if (options.carrousel) {
            var updateCounter = function () {
            };

            /**
             * Observe les flèches directionnelles pour déclencher le onPrevious et le onNext
             * @param e
             */
            var observeArrowKey = function (e) {
                // Check si une modale est affichée par dessus
                if (modalContainer.next('.modal-wrapper')) {
                    return
                }

                var key = Event.key(e);
                var buttonPrevious = modalContainer.down('.previousModal');
                var buttonNext = modalContainer.down('.nextModal');

                switch (key) {
                    case 37: // ARROW LEFT
                    case 38: // ARROW UP
                        buttonPrevious.addClassName('activated');
                        options.carrousel.onPrevious(this, options, updateCounter);
                        buttonPrevious.removeClassName.bind(buttonPrevious).delay(0.2, 'activated');
                        break;

                    case 39: // ARROW RIGHT
                    case 40: // ARROW DOWN
                        buttonNext.addClassName('activated');
                        options.carrousel.onNext(this, options, updateCounter);
                        buttonNext.removeClassName.bind(buttonNext).delay(0.2, 'activated');
                        break;
                }
            }.bind(this);


            var _close = options.onClose;

            // Suppression observer du keyup des flèches directionnelles sur document
            options.onClose = function () {
                document.removeEventListener('keyup', observeArrowKey);
                _close();
            }.bind(this);
        }

        this.modalObject = Modal.open(modalContainer, {
            className: 'modal popup ' + classes,
            showClose: options.showClose,
            onClose: options.onClose,
            title: options.title || "&nbsp;",
            fireLoaded: false,
            align: options.align,
            incrustable: options.incrustable,
            canCloseIf: options.canCloseIf
        });

        modalContainer = this.modalObject.container.setStyle(style);
        this.modalObject.position();

        modalContainer.store("url", this);

        if (options.showReload && !options.incrustable) {
            var title = modalContainer.down(".title");

            if (title) {
                var reloadButton = DOM.button({
                    type: "button",
                    className: "change notext"
                }, $T('Reload'));

                reloadButton.observe("click", this.refreshModal.bindAsEventListener(this));

                title.down(".right").insert({top: reloadButton});
            }
        }

        if (options.carrousel) {
            var title = modalContainer.down(".title");
            modalContainer.addClassName('carrousel');

            if (title) {
                // Initialisation du compteur
                title.down('.left').insert(DOM.div({className: 'counter'}));

                updateCounter = function () {
                    this.modalObject.container.down('.title').down('.left').down('.counter').update(
                        DOM.span(
                            {},
                            options.carrousel.getCurrentPosition(options.carrousel.offset)
                            + ' / '
                            + options.carrousel.getTotal()
                        )
                    );
                }.bind(this);

                updateCounter();

                var nextButton = DOM.button({
                    type: "button",
                    className: "fas fa-chevron-right notext nextModal"
                }, $T('common-Next'));

                var previousButton = DOM.button({
                    type: "button",
                    className: "fas fa-chevron-left notext previousModal"
                }, $T('common-Previous'));

                nextButton.observe("click", function () {
                    options.carrousel.onNext(this, options, updateCounter);
                    nextButton.blur(); // Perte du focus du button pour redonner la main au keyup de document
                }.bind(this));

                previousButton.observe("click", function () {
                    options.carrousel.onPrevious(this, options, updateCounter);
                    previousButton.blur(); // Perte du focus du button pour redonner la main au keyup de document
                }.bind(this));

                title.down(".left").insert({bottom: previousButton});
                title.down(".left").insert({bottom: nextButton});

                // Ajout observer du keyup des flcèhes directionnelles sur document
                document.addEventListener('keyup', observeArrowKey);
            }
        }

        var onComplete = options.onComplete;

        // Default on complete behaviour
        options.onComplete = (function () {
            try {
                if (onComplete) {
                    onComplete();
                }
            } catch (e) {
            }

            this.container.fire("modal:loaded");
        }).bind(this.modalObject);

        var target = modalContainer.down(".content");
        /* If a container is set in the options, it will be used a target for the request update instead of the div.content */
        if (options.container) {
            target = options.container;
        }

        this.requestUpdate(target, options);

        this.modalObject.observe("afterClose", (function () {
            // Don't remove if it was a custom container
            if (!options.container) {
                modalContainer.remove();
            }

            //if (options.onClose) {
            //  options.onClose.bind(this.modalObject)();
            //}
        }).bindAsEventListener(this));

        return this;
    },

    /**
     * Refresh current modal
     *
     * @return void
     */
    refreshModal: function () {
        this.requestUpdate(this.modalObject.container.down('.content'));
    },

    /**
     * Make an Ajax request and update a DOM element with the result
     *
     * @param {HTMLElement,String} ioTarget
     * @param {Object=}            oOptions
     *
     * @return {Url}
     */
    requestUpdate: function (ioTarget, oOptions) {
        Url.requestId++;
        if (!oOptions || (!oOptions.dontQueue && oOptions.method === 'get')) {
            Url.pendingRequests[Url.requestId] = {"url": this, "ioTarget": ioTarget, "oOptions": oOptions};
        }
        this.addParam("ajax", 1);

        // onComplete callback definition shortcut
        if (oOptions instanceof Function) {
            oOptions = {
                onComplete: oOptions
            };
        }

        var element = $(ioTarget);

        //this.addParam("__dom", element.id);

        // prepare callback to launch VueJS parts
        oOptions = oOptions ? oOptions : {};
        if (!oOptions || !(oOptions.onComplete instanceof Function)) {
            oOptions.onComplete = Prototype.emptyFunction;
        }
        if (window.initVueRoots || (MediboardExt && MediboardExt.onRendering)) {
            var _onComplete = oOptions.onComplete;
            oOptions.onComplete = function () {
                _onComplete();
                if (window.initVueRoots) {
                    initVueRoots(element);
                }
                if (MediboardExt && MediboardExt.onRendering) {
                    MediboardExt.onRendering();
                }
            }
        }
        if (!element) {
            console.warn(ioTarget + " doesn't exist");
            return this;
        }

        var paramsString = $H(this.oParams).toQueryString();
        var targetId = element.identify();
        var customInsertion = oOptions && oOptions.insertion;

        //element.writeAttribute("data-ajax", "[" + this.oParams.m + " / " + this.oParams.a + "]");

        oOptions = Object.extend({
            waitingText: null,
            urlBase: "",
            method: "get",
            parameters: paramsString,
            asynchronous: true,
            evalScripts: true,
            getParameters: null,
            onComplete: Prototype.emptyFunction,
            onCreate: Prototype.emptyFunction,
            onProgress: null,
            abortPrevious: true,
            resourcePath: this.resourcePath,
            onFailure: function () {
                element.update('<div class="error">Le serveur rencontre quelques problèmes.</div>');
            }
        }, oOptions);

        if (oOptions.method != "get") {
            oOptions.abortPrevious = false;
        }

        if (Preferences.INFOSYSTEM == 1 && oOptions.method === "get") {
            var lastQuery = Url.requestTimers[targetId];

            // Same query on the same node
            if (lastQuery && (lastQuery === paramsString)) {
                console.info("Chargement en double de l'élément '" + targetId + "'");
                return this;
            }
            /*else {
             // Different query on the same node, while the previous one is not finished
             if (element.currentXHR && element.currentXHR.transport.readyState < 4) {
             element.currentXHR.transport.abort();
             console.info("XHR cancelled", element, lastQuery.toQueryParams());
             }
             }*/

            Url.requestTimers[targetId] = paramsString;
        }

        var that = this;
        oOptions.onComplete = oOptions.onComplete.wrap(function (onComplete, response) {
            // Verify HTTP status
            var status = response.status;

            if (status && !(status >= 200 && status < 300) && status != 304 && status != 403 && status != 401) {
                var p = that.oParams;
                var a = (p.dosql || p.a || p.tab || p.ajax || p.raw);
                var page = locales['mod-' + p.m + '-tab-' + a] || locales['mod-dP' + p.m + '-tab-' + a] || locales['module-' + p.m + '-court'] + ' / ' + a;

                var msg = '#{date} - Une erreur s\'est produite lors de l\'appel à <strong>#{page}</strong>. (HTTP #{status} #{statusText})'.interpolate({
                    date: (new Date()).toLocaleDateTime(),
                    page: page,
                    status: status,
                    statusText: response.statusText || ''
                });

                SystemMessage.notify('<div class="error">' + msg + '</div>', true);
            }

            // Do not trigger onComplete when user is disconnected
            if (status && status == 401) {
                return;
            }

            try {
                delete Url.requestTimers[targetId];
                prepareForms(element);
                Note.refresh();
                onComplete(response);
                //element.prepareTouchEvents();
                Element.warnDuplicates();

                // Code highlight
                if (window.Prism) {
                    element.select('pre code:not(.highlighted)').each(function (e) {
                        Prism.highlightElement(e);
                        e.addClassName("highlighted");
                    });
                }

                // For selenium Test
                element.setAttribute("data-loaded", "1");
            } catch (e) {
                console.error(e);
            }
        });

        // On progress
        if (oOptions.onProgress) {
            oOptions.onCreate = oOptions.onCreate.wrap(function (onCreate, ajax) {
                if (ajax.transport && ajax.transport.upload) {
                    ajax.transport.upload.addEventListener("progress", oOptions.onProgress);
                }

                onCreate();
            });
        }

        var getParams = oOptions.getParameters ? "?" + $H(oOptions.getParameters).toQueryString() : '';

        // Abort previous request
        /*if (oOptions.abortPrevious) {
          var currentURL = element.retrieve("currentURL");
          if (currentURL && currentURL.currentAjax) {
            if (Preferences.INFOSYSTEM == 1) {
              try {
                console.info("Ajax aborted on '#"+targetId+"'", currentURL.currentAjax.url);
              } catch (e) {}
            }

            currentURL.currentAjax.abort();
          }
        }*/

        this.checkServerConnectivity(
            oOptions,
            function () {
                // If we have a custom insertion, we should not touch the origin target
                if (!customInsertion) {
                    // Empty holder gets a div for load notifying
                    if (!/\S/.test(element.innerHTML)) {
                        element.update('<div style="height: 2em;"></div>');
                    }

                    // Animate system message
                    if (element.id == SystemMessage.id) {
                        oOptions.waitingText = $T("Loading in progress");
                        SystemMessage.doEffect();
                    }
                    // Cover div
                    else {
                        WaitingMessage.cover(element);
                    }

                    if (oOptions.waitingText) {
                        element.update('<div class="loading">' + oOptions.waitingText + '...</div>');
                    }
                }

                var ajaxUrl = oOptions.urlBase + "index.php" + getParams;
                if (oOptions.resourcePath) {
                    ajaxUrl = MediboardExt.getBaseUrl() + oOptions.resourcePath + getParams;
                }
                // For selenium Test
                element.setAttribute("data-loaded", "0");

                this.currentAjax = new Ajax.Updater(element, ajaxUrl, oOptions);
                element.store("currentURL", this);

                return this;
            }.bind(this),
            function () {
                Url.requestTimers[targetId] = null;

                var unavailableText = $T("common-error-Application seems unavailable, your data have not been saved");
                SystemMessage.notify('<div class="error">' + unavailableText + '</div>', true);
            }
        );
    },

    checkServerConnectivity: function (oOptions, ifOnline, ifOffline) {
        if (!App.config.check_server_connectivity || oOptions.method.toLowerCase() !== 'post') {
            ifOnline && ifOnline.constructor === Function && ifOnline();

            return;
        }

        // if (window.navigator.onLine === false) {
        //   ifOffline();
        //
        //   return;
        // }

        SystemMessage.notify('<div class="loading">Vérification de la connectivité en cours...</div>');

        var xhr = new XMLHttpRequest();
        xhr.open('HEAD', 'ping.php?' + (new Date()).getTime());

        // Timeout between XMLHttpRequest::open and XMLHttpRequest::send
        xhr.timeout = 3000;

        // Requesting an empty script from server should not take more than a few ms
        xhr.ontimeout = function (e) {
            SystemMessage.notify();
            console.error('timeout', e, xhr);

            ifOffline && ifOffline.constructor === Function && ifOffline();
            return;
        };

        xhr.onreadystatechange = function (event) {
            // XMLHttpRequest.DONE === 4
            if (this.readyState === XMLHttpRequest.DONE) {
                if (this.status === 200) {
                    SystemMessage.notify();
                    ifOnline && ifOnline.constructor === Function && ifOnline();
                } else {
                    SystemMessage.notify();
                    ifOffline && ifOffline.constructor === Function && ifOffline();
                }
            }
        };

        xhr.send();
    },

    /**
     * Make an Ajax request and display the result in an Iframe created inside the target element.
     * The request is made with the GET HTTP method because we use the src attribute of the iframe element
     *
     *
     * @param target
     * @param options
     * @returns {Url}
     */
    requestIframe: function (target, options) {
        var container = $(target);

        if (!container) {
            console.warn(target + " doesn't exist");
            return this;
        }

        /* onComplete callback definition shortcut */
        if (options instanceof Function) {
            options = {
                onComplete: options
            };
        }

        options = Object.extend({
            baseUrl: "",
            method: "get",
            asynchronous: true,
            evalScripts: true,
            abortPrevious: true,
            onComplete: Prototype.emptyFunction,
            onFailure: function () {
                container.update('<div class="error">Le serveur rencontre quelques problèmes.</div>');
            }
        }, options);

        var questionMark = false;
        if (!options.baseUrl) {
            this.addParam('dialog', 1);
            // Flag telling Mediboard to decode UTF-8, because data comes from JS
            this.addParam("is_utf8", 1);

            // Dummy timestamp to allow iframe inside iframes recursion, with the same URL
            this.addParam("__ts", Date.now());
        } else if (options.baseUrl.indexOf("?") > -1) {
            questionMark = true;
        }

        var href = options.baseUrl + this.make(questionMark);

        var frame = container.down('iframe');
        var new_frame = false;
        if (!frame) {
            var height = window.getInnerDimensions().height - container.cumulativeOffset().top - 60;
            frame = DOM.iframe({src: 'about:blank', width: '100%', height: '100%'});
            container.setStyle({height: height + 'px'});
            new_frame = true;
        }

        frame.addEventListener('load', function () {
            var waitingMessage = frame.up().down('.cover-container');
            if (waitingMessage) {
                waitingMessage.remove();
            }
            options.onComplete();
        });

        frame.src = href;

        if (new_frame) {
            container.insert(frame);
        }

        /* Adding the loading indicator */
        WaitingMessage.cover(container);
    },

    /**
     * Make an Ajax request and process the JSON response by passing it to the fCallback argument
     *
     * @param {Function} fCallback The callback to call
     * @param {Object=}  oOptions  Various options
     *
     * @return {Url}
     */
    requestJSON: function (fCallback, oOptions) {
        this.addParam("suppressHeaders", 1);
        this.addParam("ajax", "");

        oOptions = Object.extend({
            urlBase: "",
            method: "get",
            parameters: $H(this.oParams).toQueryString(),
            asynchronous: true,
            evalScripts: true,
            evalJSON: 'force',
            getParameters: null
        }, oOptions);

        oOptions.onSuccess = function (transport) {
            try {
                fCallback(transport.responseJSON);
            } catch (e) {
                console.error(e);
            }
        };

        var getParams = oOptions.getParameters ? "?" + $H(oOptions.getParameters).toQueryString() : '';
        new Ajax.Request(oOptions.urlBase + getParams, oOptions);

        return this;
    },

    /**
     * Make an Ajax request and process the HTML response by passing it to the fCallback argument
     *
     * @param {Function} fCallback The callback to call
     * @param {Object=}  oOptions  Various options
     *
     * @return {Url}
     */
    requestHTML: function (fCallback, oOptions) {
        this.addParam("ajax", "1");

        oOptions = Object.extend({
            urlBase: "",
            method: "get",
            parameters: $H(this.oParams).toQueryString(),
            asynchronous: true,
            evalScripts: true,
            getParameters: null
        }, oOptions);

        oOptions.onSuccess = function (transport) {
            try {
                fCallback(transport.responseText);
            } catch (e) {
                console.error(e);
            }
        };

        var getParams = oOptions.getParameters ? "?" + $H(oOptions.getParameters).toQueryString() : '';
        new Ajax.Request(oOptions.urlBase + getParams, oOptions);

        return this;
    },

    /**
     * Make an Ajax request and update a DOM element with the result (offline version)
     *
     * @param {HTMLElement} ioTarget The element to update
     * @param {Object=}     oOptions Various options
     *
     * @return {Url}
     */
    requestUpdateOffline: function (ioTarget, oOptions) {
        if (typeof netscape != 'undefined' && typeof netscape.security != 'undefined') {
            netscape.security.PrivilegeManager.enablePrivilege('UniversalBrowserRead');
        }

        this.addParam("_syncroOffline", 1);
        if (config.date_synchro) {
            this.addParam("_synchroDatetime", config.date_synchro);
        }

        oOptions = Object.extend({
            urlBase: config.urlMediboard
        }, oOptions);

        this.requestUpdate(ioTarget, oOptions);

        return this;
    },

    /**
     * Make a repetitive Ajax request and update a DOM element with the result
     *
     * @param {HTMLElement,String} ioTarget The element to update
     * @param {Object=}            oOptions Various options
     *
     * @return {Ajax.PeriodicalUpdater,null}
     */
    periodicalUpdate: function (ioTarget, oOptions) {
        this.addParam("ajax", 1);

        var element = $(ioTarget);
        if (!element) {
            console.warn(ioTarget + " doesn't exist");
            return null;
        }

        // Empty holder gets a div for load notifying
        if (!/\S/.test(element.innerHTML)) {
            element.update('<div style="height: 2em"></div>');
        }

        oOptions = Object.extend({
            onCreate: WaitingMessage.cover.curry(element),
            method: "get",
            parameters: $H(this.oParams).toQueryString(),
            asynchronous: true,
            evalScripts: true,
            onComplete: Prototype.emptyFunction,
        }, oOptions);

        var getParams = oOptions.getParameters ? "?" + $H(oOptions.getParameters).toQueryString() : '';

        var url = (oOptions.urlBase ? oOptions.urlBase : "") + "index.php";
        if (this.resourcePath) {
            url = MediboardExt.getBaseUrl() + this.resourcePath;
        }
        url += getParams;

        var updater = new Ajax.PeriodicalUpdater(element, url, oOptions);

        updater.options.onComplete = updater.options.onComplete.wrap(function (onComplete) {
            prepareForms(element);
            Note.refresh();
            onComplete();
            //element.prepareTouchEvents();
            Element.warnDuplicates();
        });

        return updater;
    },

    ViewFilePopup: function (objectClass, objectId, elementClass, elementId, sfn, view_light) {
        var popupName = "Fichier";
        popupName += "-" + elementClass + "-" + elementId;

        /*
         var event = Function.getEvent();
         if (event) {
         Event.stop(event);
         if (event.shiftKey)
         popupName += "-"+objectClass+"-"+objectId;
         }*/

        this.setModuleAction("files", "preview_files");
        this.addParam("popup", 1);
        this.addParam("objectClass", objectClass);
        this.addParam("objectId", objectId);
        this.addParam("elementClass", elementClass);
        this.addParam("elementId", elementId);
        this.addNotNullParam("sfn", sfn);
        this.addParam("view_light", view_light);
        this.popup(900, 800, popupName);
    }
});

Url.activeRequests = {
    post: 0,
    get: 0
};

Url.popupFeatures = {
    left: 50,
    top: 50,
    height: 600,
    width: 800,
    scrollbars: true,
    resizable: true,
    menubar: true
};

Url.requestTimers = {
    // "target id" : "last query",
};

/**
 * Build popup features as a string
 *
 * @param {Object} features
 *
 * @return {String}
 */
Url.buildPopupFeatures = function (features) {
    var a = [], value;
    $H(features).each(function (f) {
        value = (f.value === true ? 'yes' : (f.value === false ? 'no' : f.value));
        a.push(f.key + '=' + value);
    });

    return a.join(',');
};

/**
 * General purpose ping
 *
 * @param {Object} options
 *
 * @return void
 */
Url.ping = function (options) {
    var url = new Url("system", "ajax_ping");

    if (Object.isFunction(options)) {
        options = {
            onComplete: options
        };
    }

    if (options.onComplete) {
        AjaxResponse.onComplete = options.onComplete;
    }

    if (options.onCompleteDisconnected) {
        AjaxResponse.onCompleteDisconnected = options.onCompleteDisconnected;
    }

    delete options.onComplete;
    delete options.onCompleteDisconnected;

    url.requestUpdate("systemMsg", options);
};

/**
 * Checks connectivity, based on timeout and HTTP 5xx error
 *
 * @param {Number}   period   Period in seconds
 * @param {Number}   timeout  Timeout in milliseconds
 * @param {Function} callback Callback
 */
Url.connectivityCheck = function (period, timeout, callback) {
    period = period || 10;
    timeout = timeout || 1000;

    setInterval(function () {
        var xhr = new XMLHttpRequest();
        xhr.onload = function (e) {
            if (xhr.readyState === 4) {
                if (xhr.status >= 500) {
                    callback("error", e, xhr);
                } else {
                    callback("ok", e, xhr);
                }
            }
        };

        xhr.onerror = function (e) {
            callback("error", e, xhr);
        };

        xhr.open('GET', 'status.php?' + (new Date()).getTime(), true);
        xhr.timeout = timeout;
        xhr.ontimeout = function (e) {
            callback("timeout", e, xhr);
        };

        xhr.send(null);
    }, period * 1000);
};

/**
 * Parses the URL to extract its components
 * Based on the work of Steven Levithan <http://blog.stevenlevithan.com/archives/parseuri>
 *
 * @param {String=} url The URL to parse
 *
 * @return {Object} The URL components
 */
Url.parse = function (url) {
    url = url || location.href;

    var keys = ["source", "scheme", "authority", "userInfo", "user", "pass", "host", "port", "relative", "path", "directory", "file", "query", "fragment"],
        regex = /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
        m = regex.exec(url),
        c = {},
        i = keys.length;

    while (i--) {
        c[keys[i]] = m[i] || "";
    }

    return c;
};

/**
 * Make an Ajax request with data from a form, to update an element
 *
 * @param {HTMLFormElement}    form    The form to take the data from
 * @param {HTMLElement,String} element The element to update
 * @param {Object=}            options Options
 *
 * @return {Boolean,Url}
 */
Url.update = function (form, element, options) {
    var method = form.getAttribute("method");
    var getParameters;

    if (method == "post") {
        getParameters = form.getAttribute("action").toQueryParams();
    }

    options = Object.extend({
        openModal: false,
        modalWidth: "90%",
        modalHeight: "90%",

        method: method,
        getParameters: getParameters
    }, options);

    var url = new Url();
    url.addFormData(form);

    if (options.openModal) {
        url.requestModal(options.modalWidth, options.modalHeight, options);

        return url;
    }

    url.requestUpdate(element, options);

    return false;
};

/**
 * Get the current page's query params
 *
 * @return {Object}
 */
Url.hashParams = function () {
    return window.location.hash.substr(1).toQueryParams();
};

/**
 * Go to an URL, based on query params
 *
 * @param {Object=} params Query params
 * @param {String=} hash   Hash (aka fragement)
 *
 * @return {Boolean}
 */
Url.go = function (params, hash) {
    var href = (params ? "?" + Object.toQueryString(params) : "") + (hash ? "#" + hash : "");
    location.assign(href);
    return false;
};

// list of pending
Url.pendingRequests = {};

Url.queueRequests = false;

Url.requestId = 0;

Progress = {
    init: function (id, max) {
        var progress = window.parent.$(id);

        if (!progress) {
            return;
        }

        progress.max = max;
    },
    adv: function (id) {
        var progress = window.parent.$(id);

        if (!progress) {
            return;
        }

        progress.value = progress.value + 1;
    },

    /**
     *
     * @param {Url} url
     */
    launchQuery: function (url) {
        url.pop(0, 0, "", null, null, {foo: "bar"}, Element.getTempIframe());
    }
};
