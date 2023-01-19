MediboardExt = {
    base_url: "",
    currentDate: null,
    referenceDate: null,
    dropdownListenerFlag: false,

    /**
     * Controle des configurations front requises : Cookies images scriptjs etc...
     */
    jsControl: {
        /**
         * Au moins une erreur a été controlée
         */
        hasError: function () {
            return this.hasFrontError() || $("jscontrol").select(".control-browser-error.displayed").length > 0;
        },
        /**
         * Au moins une erreur front a été controlée
         */
        hasFrontError: function () {
            return $("jscontrol").select(".control-front-error").length > 0;
        },
        /**
         * Ajout d'une erreur
         * @param key - Clef de l'erreur
         * @param label - Label de l'erreur
         */
        addError: function (key, label) {
            $("jscontrol").append(DOM.div({class: "control-error control-front-error", id: key}, label));
            return this;
        },
        /**
         * Retrait d'une erreur
         * @param key - Clef de l'erreur
         */
        removeError: function (key) {
            var errorSpan = $("jscontrol").down("#" + key);
            if (errorSpan) {
                errorSpan.remove();
            }
            return this;
        },
        /**
         * Lancement global des controles
         */
        control: function (serverSideError) {
            return this.controlScripts()
                .controlCookies()
                .controlServerSideError(serverSideError)
                .controlImages(
                    function () {
                        this.controlCautionAppearance();
                    }.bind(this)
                );
        },
        /**
         * Controle de la disponibilité "Script javascript" du navigateur
         */
        controlScripts: function () {
            return this.removeError("controlScript")
                .addError("controlImage", $T("Image-loading-error"));
        },
        /**
         * Controle de la disponibilité des cookies du navigateur
         */
        controlCookies: function () {
            if (navigator.cookieEnabled) {
                return this;
            }
            return this.addError("controlCookie", $T("Cookie-loading-error"));
        },
        /**
         * Controle de la disponibilité du chargement des images du navigateur
         */
        controlImages: function (callback) {
            var logo = $("mediboard-logo");
            if (logo.complete && logo.naturalHeight !== 0) {
                return this.controlImagesOk(callback);
            }
            logo.on("load", function () {
                this.controlImagesOk(callback);
            }.bind(this));
            return this;
        },
        /**
         * Traitement après controle OK du chargement d'image
         */
        controlImagesOk: function (callback) {
            this.removeError("controlImage");
            callback();
            return this;
        },
        /**
         * Controle d'erreur côté serveur
         * @param serverData - Erreurs injectées
         */
        controlServerSideError: function (serverData) {
            if (serverData) {
                $$(".control-browser-error").invoke("addClassName", "displayed");
            }
            return this;
        },
        /**
         * Modifie l'apparence de l'erreur en fonction des erreurs enregistrées
         */
        controlCautionAppearance: function () {
            if (!this.hasError()) {
                this.hide();
                return this;
            }
            if (!this.hasFrontError()) {
                this.hideFront();
                return this;
            }
            return this;
        },
        /**
         * Cache le conteneur de message d'erreurs
         */
        hide: function () {
            $$(".me-caution-wrap").invoke("addClassName", "no-error");
            return this;
        },
        /**
         * Cache le conteneur de message d'erreurs front
         */
        hideFront: function () {
            $$(".control-error-title").invoke("remove");
            return this;
        },
    },
    /**
     * Récupération de la base url pour prendre en charges les routes legacy ET gui
     */
    getBaseUrl: function () {
        if (!this.base_url) {
            this.initBaseUrl();
        }
        return this.base_url;
    },
    /**
     * Initialisation de la base url pour prendre en charges les routes legacy ET gui
     */
    initBaseUrl: function (base) {
        base = base ? base : document.location.href;
        if (base.indexOf("/index.php") !== -1) {
            base = base.substr(0, base.indexOf("/index.php"));
        }
        if (base.indexOf("?") !== -1) {
            base = base.substr(0, base.indexOf("?"));
        }
        if (base.indexOf("/gui/") !== -1) {
            base = base.substr(0, base.indexOf("/gui/"));
        }
        if (base === "") {
            this.base_url = ".";
        } else {
            while (base[base.length - 1] === "/") {
                base = base.substr(0, base.length - 1);
            }
            this.base_url = base;
        }

        this.base_url += '/';

        return this.base_url;
    },

    toggleTogglingElements: function () {
        $$('.toggled').invoke('removeClassName', 'toggled');
        $$('.toggling').invoke('addClassName', 'toggled');
        $$('.toggling').invoke('removeClassName', 'toggling');
        return this;
    },

    addTogglableElement: function (element) {
        if (!this.dropdownListenerFlag) {
          window.addEventListener(
            'click',
            function () {
              this.toggleTogglingElements.defer()
            }.bind(this)
          );
          this.dropdownListenerFlag = true
        }
        var dropdownContent = $(element).next('.me-dropdown-content');
        element.on(
            'click',
            function () {
                this[this.hasClassName('toggled') ? 'removeClassName' : 'addClassName']('toggling');
                // Attente de l'affichage du dropdown dans la page
                setTimeout(function () {
                    MediboardExt.replaceDropdown(dropdownContent);
                }, 120);
            }
        );
        return this;
    },

    /**
     * Replace le dropdown dans la fenêtre si ce dernier dépasse en hauteur
     * @param dropdown
     */
    replaceDropdown: function (dropdown) {
        var dropdownContent_rect = dropdown.getBoundingClientRect();
        var yOverflow = (dropdownContent_rect.top + dropdownContent_rect.height) - window.innerHeight;
        if (yOverflow > 0) {
            dropdown.setStyle({transform: 'translateY(-' + parseInt(yOverflow + 10) + 'px)'});
        }
    },
    /**
     * Détection de l'OS client
     * @returns {boolean}
     */
    isOnMac: function () {
        return navigator.platform.match('Mac') !== null
    },
    /**
     * Traitement automatique à chaque rendu
     */
    onRendering: function () {
        this.addTextareaListeners();
        return this;
    },
    /**
     * Ajout d'un raccourcis "Soumission de formulaire" depuis les textareas (mac uniquement)
     */
    addTextareaListeners: function () {
        if (!this.isOnMac()) {
            return this
        }
        var textareas = $$("textarea:not('.me-textarea-listener')");
        for (var i = 0; i < textareas.length; i++) {
            var textarea = textareas[i];
            if (!textarea.up("form")) {
                continue;
            }
            textarea.on(
                "keydown",
                function (event) {
                    var textarea = event.target;
                    if ([224, 91, 93].indexOf(event.keyCode) > -1) {
                        textarea.addClassName("me-textarea-listener-mac-cmd");
                    }
                    if (event.keyCode !== 13 || !textarea.hasClassName("me-textarea-listener-mac-cmd")) {
                        return this;
                    }
                    textarea.removeClassName("me-textarea-listener-mac-cmd");
                    event.preventDefault();
                    event.stopPropagation();
                    var form = textarea.up("form");
                    if (form.onsubmit) {
                        form.onsubmit(form);
                    } else {
                        form.submit(form);
                    }
                }
            );
            textarea.on(
                "keyup",
                function (event) {
                    if ([224, 91, 93].indexOf(event.keyCode) > -1) {
                        event.target.removeClassName("me-textarea-listener-mac-cmd")
                    }
                }
            );
        }
        textareas.invoke("addClassName", "me-textarea-listener");
        return this;
    },

    /**
     * Ajoute ou met  jour un badge  un onglet
     *
     * @param mod_name Nom du module
     * @param tab_name Nom de l'onglet
     * @param count Valeur du badge
     * @param color Couleur du badge
     */
    setBadge: function (mod_name, tab_name, count, color) {
      // 1sec timeout for wait appbar to be mounted
      setTimeout(function () {
        const event = new CustomEvent('badge', { detail: {
            module_name: mod_name,
            tab_name: tab_name,
            counter: count,
            color: color } })
        window.dispatchEvent(event)
      }, 1000);
    },


    /**
     * Initialisation de la date
     */
    initDate: function (dateString) {
        // Server date
        this.currentDate = new Date(dateString);
        // Local date (only use to compute date spend on page)
        this.referenceDate = new Date();
        return this;
    },
    /**
     * Affichage de la date courante dans l'appbar
     */
    showDateHeader: function () {
      const container = $("nav-date");
      if (!container || !this.currentDate) {
        return this;
      }
      container.update(this.displayDate());
      return this;
    },

    /**
     * Récupération de la date formatée
     */
    displayDate: function () {
        if (!this.currentDate) {
            return ""
        }

        const days = [
            'Sunday|short',
            'Monday|short',
            'Tuesday|short',
            'Wednesday|short',
            'Thursday|short',
            'Friday|short',
            'Saturday|short',
        ];
        const months = [
            'OxDate-ShortMonth-Janvier',
            'OxDate-ShortMonth-Fevrier',
            'OxDate-ShortMonth-Mars',
            'OxDate-ShortMonth-Avril',
            'OxDate-ShortMonth-Mai',
            'OxDate-ShortMonth-Juin',
            'OxDate-ShortMonth-Juillet',
            'OxDate-ShortMonth-Aout',
            'OxDate-ShortMonth-Septembre',
            'OxDate-ShortMonth-Octobre',
            'OxDate-ShortMonth-Novembre',
            'OxDate-ShortMonth-Decembre',
        ];

        return $T(days[this.currentDate.getDay()])
            + " "
            + this.currentDate.getDate()
            + " "
            + $T(months[this.currentDate.getMonth()])
            + " "
            + this.currentDate.getFullYear();
    },
    /**
     * Mise à jour de la date courante stockée et affichée
     */
    updateDate: function () {
      if (!this.currentDate || !this.referenceDate) {
        return this;
      }
      // Time diff between now and last date update
      const diff = new Date() - this.referenceDate;

      // Add time spend to the current date
      this.currentDate = new Date(this.currentDate.getTime() + diff);
      // Update reference date to now
      this.referenceDate = new Date();

      return this.showDateHeader();
    },

    showCGU: function () {
      new Url("system", "showCGU")
        .modal({width: "95%", height: "95%"})
    },

    TammMenu: {
      editInfosPerso: function () {
        new Url("oxCabinet", "edit_info_perso")
          .modal({width: "90%", height: "90%"});
        try {
          this.hideMenu();
        }
        catch (e) {
          // Hide menu is usefull only with old appbar
        }

      },
      showAbonnement: function () {
        new Url("oxCabinet", "show_abonnement")
          .modal({width: "90%", height: "90%"});
        try {
          this.hideMenu();
        }
        catch (e) {
          // Hide menu is usefull only with old appbar
        }
      },
      showHistory: function () {
        new Url("oxCabinet", "vw_history")
          .requestModal('95%', '95%');
        try {
          this.hideMenu();
        }
        catch (e) {
          // Hide menu is usefull only with old appbar
        }
      },
      showSecretary: function () {
        new Url("oxCabinet", "vw_list_secretaries")
          .requestModal('60%', '60%');
        try {
          this.hideMenu();
        }
        catch (e) {
          // Hide menu is usefull only with old appbar
        }
      },
      editProtocoles: function (protocole_id) {
        new Url("prescription", "vw_protocoles")
          .addNotNullParam("protocole_id", protocole_id)
          .requestModal("100%", "100%", {showReload: true});
        try {
          this.hideMenu();
        }
        catch (e) {
          // Hide menu is usefull only with old appbar
        }
      },
      editCataloguePrescription: function () {
        new Url("prescription", "vw_edit_category")
          .modal({width: "95%", height: "95%"});
        try {
          this.hideMenu();
        }
        catch (e) {
          // Hide menu is usefull only with old appbar
        }
      },
      editStocks: function () {
        new Url("oxCabinet", "vw_stocks")
          .requestModal('500px', '300px', {showReload: true});
      },
      editCorrespondantsTAMM: function () {
        new Url("patients", "vw_correspondants")
          .addParam("all_correspondants", 1)
          .requestModal('90%', '90%');
        try {
          this.hideMenu();
        }
        catch (e) {
          // Hide menu is usefull only with old appbar
        }
      },
      editRessources: function () {
        new Url("oxCabinet", "vw_ressources")
          .addParam('tamm_mod', '1')
          .modal({width: "1200", height: "700"});
        try {
          this.hideMenu();
        }
        catch (e) {
          // Hide menu is usefull only with old appbar
        }
      },
      showNotifications: function () {
        new Url("notifications", "vw_notifications_user")
          .requestModal(1200, 700);
        try {
          this.hideMenu();
        }
        catch (e) {
          // Hide menu is usefull only with old appbar
        }
      },
      editProtocoleStructureTAMM: function () {
        new Url("patients", "vw_programmes")
          .requestModal('80%', '80%');
        try {
          this.hideMenu();
        }
        catch (e) {
          // Hide menu is usefull only with old appbar
        }
      },
      showListPatients: function () {
        new Url("patients", "vw_list_patients")
          .requestModal('95%', '95%');
        try {
          this.hideMenu();
        }
        catch (e) {
          // Hide menu is usefull only with old appbar
        }
      },
      showListVerrouDossier: function () {
        new Url("oxCabinet", "vw_list_verrou_dossiers")
          .requestModal('80%', '80%');
        try {
          this.hideMenu();
        }
        catch (e) {
          // Hide menu is usefull only with old appbar
        }
      },
      /**
       * Display "gestion des identités" popup from Patient module
       */
      showGestionIdentite: function () {
        new Url("patients", "vw_patient_state")
          .requestModal('80%', '80%');
        try {
          this.hideMenu();
        }
        catch (e) {
          // Hide menu is usefull only with old appbar
        }
      },
      /**
       * Affiche l'onglet de recherche et de consultation des e-prescriptions
       */
      showSearchConsultEPrescription: function () {
        new Url("ePrescription", "searchEPrescription")
          .requestModal('80%', '80%');
        try {
          this.hideMenu();
        }
        catch (e) {
          // Hide menu is usefull only with old appbar
        }
      },
      /**
       * Affiche l'onglet de l'envoi des traçabilités en e-prescroption
       */
      showTracePosteEPrescription:    function () {
        new Url("ePrescription", "sendTracesPoste")
          .requestModal('80%', '80%');
        try {
          this.hideMenu();
        }
        catch (e) {
          // Hide menu is usefull only with old appbar
        }
      },

      /**
       * Affiche le tableau de bord OxLaboClient
       */
      showDashboardOxLabo: function () {
        new Url("oxLaboClient", "dashboard")
          .requestModal('80%', '80%');
        try {
          this.hideMenu();
        }
        catch (e) {
          // Hide menu is usefull only with old appbar
        }
      }
    },

    /*********************
     * Special field
     ********************/
    MeFormField: {
        /**
         * Crée un lien entre le fonctionnement du nouvel input et de l'ancien
         *
         * @param element_id
         * @param {string} var_true  La valeur de l'input "oui"
         * @param {string} var_false La valeur de l'input "non"
         *
         * @returns {MeFormField}
         */
        prepareFormBool: function (element_id, var_true, var_false) {
            var element = $(element_id);
            var new_input = $(element_id + '_input');
            var old_content = $(element_id + '_old_input');
            var old_input = old_content.down('input');

            var input_type = old_input.type;

            $V(new_input, this.getInputChildState(old_content, input_type, var_true, var_false));

            new_input.disabled = old_input.disabled;

            new_input.on(
                'click',
                function () {
                    this.setInputChildState(old_content, input_type, var_true, var_false, $V(new_input));
                }.bind(this)
            );

            var label = element.down('label');
            if (typeof (label) !== 'undefined') {
                label.on(
                    'click',
                    function () {
                        new_input.click();
                    }
                );
            }

            this.updateNewInputState(old_content, input_type, var_true, var_false, new_input);

            return this;
        },
        /**
         * Récupère l'état de l'input enfant en fonction de son type
         *
         * @param {Element} container  L'élément parent de l'input
         * @param {string}  input_type Le type de l'input (radio ou checkbox)
         * @param {string}  var_true   La valeur de l'input "oui"
         * @param {string}  var_false  La valeur de l'input "non"
         *
         * @returns {bool}
         */
        getInputChildState: function (container, input_type, var_true, var_false) {
            switch (input_type) {
                case 'radio':
                    var input_oui = container.down('input[value="' + var_true + '"]');
                    var input_non = container.down('input[value="' + var_false + '"]');

                    if (input_oui && input_non) {
                        if ($V(input_oui)) {
                            return true;
                        }
                        if ($V(input_non)) {
                            return false;
                        }
                        return null;
                    }
                    break;
                case 'checkbox':
                    var checkbox = container.down('input');

                    if (checkbox) {
                        return $V(checkbox);
                    }
                    break;
                default:
                    return null;
            }
        },
        /**
         * Déclenche le click sur le bon input
         *
         * @param {Element} container   L'élément parent de l'input
         * @param {string}  input_type  Le type de l'input (radio ou checkbox)
         * @param {string}  var_true    La valeur de l'input "oui"
         * @param {string}  var_false   La valeur de l'input "non"
         * @param {bool}    input_state L'état du nouvel input
         *
         * @returns {null}
         */
        setInputChildState: function (container, input_type, var_true, var_false, input_state) {
            switch (input_type) {
                case 'radio':
                    var input_oui = container.down('input[value="' + var_true + '"]');
                    var input_non = container.down('input[value="' + var_false + '"]');

                    if (input_oui && input_non) {
                        if (input_state) {
                            input_oui.click();
                            break;
                        }
                        input_non.click();
                    }
                    break;
                case 'checkbox':
                    var checkbox = container.down('input');
                    if (input_state !== $V(checkbox)) {
                        checkbox.click();
                    }
                    break;
                default:
                    return null;
            }
        },
        /**
         * Met à jour l'état du nouvel input lors des changements de l'ancien input
         *
         * @param {Element} container  L'élément parent de l'input
         * @param {string}  input_type Le type de l'input (radio ou checkbox)
         * @param {string}  var_true   La valeur de l'input "oui"
         * @param {string}  var_false  La valeur de l'input "non"
         * @param {Element} new_input  Le nouvel input
         *
         * @returns {null}
         */
        updateNewInputState: function (container, input_type, var_true, var_false, new_input) {
            switch (input_type) {
                case 'radio':
                    var input_oui = container.down('input[value="' + var_true + '"]');
                    var input_non = container.down('input[value="' + var_false + '"]');
                    var default_onchange = input_oui.onchange ? input_oui.onchange : Prototype.emptyFunction;
                    input_oui.onchange = function () {
                        default_onchange.bind(input_oui)();
                        var new_state = $V(input_oui);
                        if (new_state) {
                            $V(new_input, 1);
                        }
                    };
                    var default_onchange = input_non.onchange ? input_non.onchange : Prototype.emptyFunction;
                    input_non.onchange = function () {
                        default_onchange.bind(input_non)();
                        var new_state = $V(input_non);
                        if (new_state) {
                            $V(new_input, 0);
                        }
                    };
                    break;
                case 'checkbox':
                    var checkbox = container.down('input[type="hidden"]');
                    if (!checkbox) {
                        return null;
                    }
                    var default_onchange = checkbox.onchange ? checkbox.onchange : Prototype.emptyFunction;
                    checkbox.onchange = function () {
                        default_onchange.bind(checkbox)();
                        $V(new_input, parseInt($V(checkbox)));
                    };
                    break;
                default:
                    return null;
            }
        },

        /**
         * Nettoie une chaine de caractère en supprimant tous les caractères considérés comme null
         *
         * @param value - la chaine de caractère
         * @param chars - Tableau contenant les caractères null
         */
        clearValue: function (value, chars) {
            chars.each(
                function (char) {
                    var regex = new RegExp(char, 'g');
                    if (value) {
                        value = value.replace(regex, '');
                    }
                }
            );
            return value;
        },
        /**
         * Ajout d'un élément 'dirtyable' : à la modification, passe à la classe 'dirty'
         *
         * @param element
         * @param null_chars {array}
         *
         * @returns {MeFormField}
         */
        prepareFormField: function (element, null_chars) {
            var inputs = element.select('input, textarea, select');
            var input = null;
            var i = 0;
            while (input === null && inputs.length > 0) {
                if (inputs[i].getStyle('display') !== 'none') {
                    input = inputs[i];
                }
                i++;
                if (i >= inputs.length && input === null) {
                    input = false;
                }
            }
            if (!input) {
                return this;
            }
            var label = element.select('>label');
            label = label.length > 0 ? label[0] : false;
            var event_callback = function () {
                var value = this.clearValue($V(input), null_chars);
                element[(value === '') ? 'removeClassName' : 'addClassName']('dirty');
            }.bind(this);

            if (label) {
                label.on(
                    'click',
                    function () {
                        input.focus();
                        // Spécial V1 : éviter l'ourverture-fermeture des calendriers (le preventDefault ne fonctionne pas)
                        setTimeout(
                            function () {
                                input.click();
                            },
                            50
                        );
                    }
                );
            }

            if ($V(input) !== 'undefined' && $V(input) !== '') {
                element.addClassName('dirty');
            }

            if (input.tagName === "SELECT") {
                return this;
            }

            input.on('blur', event_callback);
            var default_onchange = input.onchange ? input.onchange : Prototype.emptyFunction;
            input.onchange = function () {
                default_onchange.bind(input)();
                event_callback();
            };

            return this;
        },
        prepareMbPassword: function (fieldId) {
            const mbPwdInput = $(fieldId);
            const mbPwdIconOn = $(fieldId + '-on');
            const mbPwdIconOff = $(fieldId + '-off');
            if (!mbPwdInput || !mbPwdIconOn || !mbPwdIconOff) {
                return;
            }
            const parent = mbPwdInput.parentElement;
            if (!parent.hasClassName('me-form-group') && !parent.parentElement.hasClassName('me-form-group-layout')) {
                const container = DOM.div(
                    {
                        className: 'me-form-group'
                    }
                );
                mbPwdInput.after(container);
                container.append(mbPwdInput);
                container.append(mbPwdIconOn);
                container.append(mbPwdIconOff);
            }
            mbPwdIconOn.on(
                'click',
                function () {
                    mbPwdIconOn.removeClassName('displayed');
                    mbPwdIconOff.addClassName('displayed');
                    mbPwdInput.type = 'password';
                }
            );
            mbPwdIconOff.on(
                'click',
                function () {
                    mbPwdIconOff.removeClassName('displayed');
                    mbPwdIconOn.addClassName('displayed');
                    mbPwdInput.type = 'text';
                }
            );
        }
    }
};
