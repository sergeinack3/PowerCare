/**
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CarePath = {
    save: async function (form) {
        if (form.indicator.value === 'O' || form.indicator.value === 'M') {
            if (!form.last_name.value || !form.first_name.value) {
                Jfse.displayErrorMessage($T('CCarePathDoctor-MissingNames'), this.getElementMessage());
                return;
            }
        } else if (form.indicator.value === 'J' && !form.install_date.value) {
            Jfse.displayErrorMessage($T('CCarePath-MissingInstallDate'), this.getElementMessage());
            return;
        } else if (form.indicator.value === 'B' && !form.poor_md_zone_install_date.value) {
            Jfse.displayErrorMessage($T('CCarePath-MissingInstallDatePoorMDZone'), this.getElementMessage());
            return;
        }

        this.hideElementMessage();

        const response = await Jfse.requestJson('carePath/store', {form}, {});

        if (response.success) {
            Jfse.hideMessageElement(this.getElementMessage());
            Jfse.notifySuccessMessage(response.message);
            Invoicing.reloadMessages($V(form.elements['invoice_id']));
        } else if (response.error) {
            Jfse.displayErrorMessage(response.error, this.getElementMessage());
        } else if (response.messages) {
            Jfse.displayMessages(response.messages, this.getElementMessage());
        }
    },

    onChangeDeclaration: function (form) {
        if (
            (form.indicator.value === 'M' && form.last_name.value && form.first_name.value)
            || (form.indicator.value === 'J' && form.install_date.value)
            || (form.indicator.value === 'B' && form.poor_md_zone_install_date.value)
            || ['M', 'J', 'B'].indexOf(form.indicator.value) === -1
        ) {
            this.save(form);
        }
    },

    changeIndicator: function (input, initialization) {
        const indicator = input.value;
        $$('table.care_path tr').invoke('hide');
        $$('table.care_path tr.all, table.care_path tr.indicator-' + indicator.toLowerCase()).invoke('show');

        switch (indicator.toLowerCase()) {
            /* Urgence */
            case 'u':
            /* Médecin traitant */
            case 't':
            /* Nouveau médecin traitant */
            case 'n':
            /* Médecin traitant de substitution */
            case 'r':
            /* Accès direct spécifique */
            case 'd':
            /* Hors résidence habituelle */
            case 'h':
            /* Hors accès direct spécifique */
            case 's1':
            /* Non respect du parcours */
            case 's2':
                this.onSelectSimpleCarePath(input.form, initialization);
                break;
            /* Orienté par le médecin traitant */
            case 'o':
                this.onSelectReferringPhysician(input.form, initialization);
                break;
            /* Orienté par un autre médecin */
            case 'm':
                this.onSelectIndicatorCorrespondingPhysician(input.form, initialization);
                break;
            /* Généraliste récemment installé */
            case 'j':
                this.onSelectRecentlyInstalledPhysician(input.form, initialization);
                break;
            /* Médecin installé en zone sous médicalisée */
            case 'b':
                this.onSelectPoorMedicalizedZone(input.form, initialization);
                break;
        }
    },

    /**
     * Set the form for the care paths that require no other inputs, like "Urgence", "Médecin traitant",
     * "Nouveau médecin traitant", "Médecin traitant de substitution".
     *
     * On the initialization of the view, the form won't be saved
     *
     * @param form
     * @param initialization
     */
    onSelectSimpleCarePath: function (form, initialization) {
        Jfse.setInputNullable(form.elements['first_name']);
        Jfse.setInputNullable(form.elements['last_name']);
        Jfse.setInputNullable(form.elements['corresponding_physician']);
        Jfse.setInputNullable(form.elements['install_date']);
        Jfse.setInputNullable(form.elements['poor_md_zone_install_date']);

        if (!initialization) {
            this.save(form);
        }
    },

    onSelectRecentlyInstalledPhysician: function (form, initialization) {
        if (!initialization) {
            $V(form.elements['install_date'], '');
            $V(form.elements['install_date_da'], '');
        }

        Jfse.setInputNullable(form.elements['first_name']);
        Jfse.setInputNullable(form.elements['last_name']);
        Jfse.setInputNullable(form.elements['corresponding_physician']);
        Jfse.setInputNullable(form.elements['poor_md_zone_install_date']);

        Jfse.setInputNotNull(form.elements['install_date']);
    },

    onChangeInstallDate: function (input) {
        if ($V(input) !== '') {
            this.save(input.form);
        }
    },

    onSelectPoorMedicalizedZone: function (form, initialization) {
        if (!initialization) {
            $V(form.elements['poor_md_zone_install_date'], '');
            $V(form.elements['poor_md_zone_install_date_da'], '');
        }

        Jfse.setInputNullable(form.elements['first_name']);
        Jfse.setInputNullable(form.elements['last_name']);
        Jfse.setInputNullable(form.elements['corresponding_physician']);
        Jfse.setInputNullable(form.elements['install_date']);

        Jfse.setInputNotNull(form.elements['poor_md_zone_install_date']);
    },

    /**
     * Set the form for the care path "Orienté par le médecin traitant".
     * On initialization of the view, the last_name and first_name fields won't be valued, and the form won't be saved
     *
     * @param form
     * @param initialization
     */
    onSelectReferringPhysician: function (form, initialization) {
        /* Sets the fields Not Null, and set the other fields to nullable */
        Jfse.setInputNotNull(form.elements['first_name']);
        Jfse.setInputNotNull(form.elements['last_name']);

        Jfse.setInputNullable(form.elements['corresponding_physician']);
        Jfse.setInputNullable(form.elements['install_date']);
        Jfse.setInputNullable(form.elements['poor_md_zone_install_date']);

        if (!initialization) {
            $V(form.elements['last_name'], $V(form.elements['referring_physician_last_name']));
            $V(form.elements['first_name'], $V(form.elements['referring_physician_first_name']));
            this.onChangeReferringPhysician(form);
        }
    },

    onSelectIndicatorCorrespondingPhysician: function (form, initialization) {
        if (!initialization) {
            $V(form.elements['corresponding_physician'], '');
            $V(form.elements['last_name'], '');
            $V(form.elements['first_name'], '');
            $('row-doctor').hide();
        } else if ($V(form.elements['corresponding_physician']) === 'other') {
            $('row-doctor').show();
        } else {
            $('row-doctor').hide();
        }

        Jfse.setInputNullable(form.elements['install_date']);
        Jfse.setInputNullable(form.elements['poor_md_zone_install_date']);
        Jfse.setInputNotNull(form.elements['first_name']);
        Jfse.setInputNotNull(form.elements['last_name']);
        Jfse.setInputNotNull(form.elements['corresponding_physician']);
    },

    onChangeReferringPhysician: function (form) {
        if ($V(form.elements['last_name']) !== '' && $V(form.elements['first_name']) !== '') {
            this.save(form);
        }
    },

    onSelectCorrespondingPhysician: function (select) {
        let last_name = '';
        let first_name = '';

        if ($V(select) !== 'other' && $V(select) !== '') {
            let option = select.down('[value="' + $V(select) + '"]');
            last_name = option.get('last_name');
            first_name = option.get('first_name');
        }

        $V(select.form.elements['last_name'], last_name);
        $V(select.form.elements['first_name'], first_name);
        if ($V(select) === 'other') {
            $('row-doctor').show();
        } else {
            $('row-doctor').hide();
            if ($V(select) !== '') {
                this.onChangeReferringPhysician(select.form);
            }
        }
    },

    getElementMessage: function () {
        return $('care_path_message_container');
    },

    hideElementMessage: function () {
        Jfse.hideMessageElement(this.getElementMessage());
    }
};
