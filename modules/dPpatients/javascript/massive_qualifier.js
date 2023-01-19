/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

MassiveQualifier = {
    patients:                  {},
    patients_ids:              [],
    initial_count:             0,
    current_count:             0,
    current_patient_id:        null,
    in_progress:               false,
    button_play:               null,
    button_pause:              null,
    button_stop:               null,
    td_elapsed_time:           null,
    td_average_time:           null,
    td_estimated_end_time:     null,
    td_current_patient:        null,
    td_last_patient:           null,
    div_massive_qualify_state: null,
    progressbar:               null,
    elapsed_time:              null,
    average_time:              null,
    chronos:                   [],
    chrono:                    null,
    chrono_periodical:         null,

    /**
     * Initialize the qualifier tool
     *
     * @param patients
     */
    init: (patients) => {
        MassiveQualifier.patients = JSON.parse(patients);
        MassiveQualifier.patients_ids = Object.keys(MassiveQualifier.patients);
        MassiveQualifier.initial_count = MassiveQualifier.patients_ids.length;

        const div = $('massive_qualify_area');

        MassiveQualifier.button_play = div.down('button.play');
        MassiveQualifier.button_pause = div.down('button.pause');
        MassiveQualifier.button_stop = div.down('button.media_stop');
        MassiveQualifier.td_elapsed_time = $('elapsed_time');
        MassiveQualifier.td_average_time = $('average_time');
        MassiveQualifier.td_estimated_end_time = $('estimated_end_time');
        MassiveQualifier.td_current_patient = $('current_patient');
        MassiveQualifier.td_last_patient = $('last_patient');
        MassiveQualifier.progressbar = $('qualifier_progress');
        MassiveQualifier.div_massive_qualify_state = $('massive_qualify_state')
    },

    /**
     * Launch the qualifier tool
     */
    play: () => {
        MassiveQualifier.button_play.writeAttribute('disabled', true);
        MassiveQualifier.button_pause.writeAttribute('disabled', null);
        MassiveQualifier.button_stop.writeAttribute('disabled', null);
        MassiveQualifier.in_progress = true;

        MassiveQualifier.generalTimer();
        MassiveQualifier.askInsi();
    },

    /**
     * Interrupts the qualifier tool
     */
    pause: () => {
        MassiveQualifier.button_pause.writeAttribute('disabled', true);
        MassiveQualifier.button_play.writeAttribute('disabled', null);
        MassiveQualifier.button_stop.writeAttribute('disabled', true);
        MassiveQualifier.in_progress = false;
    },

    /**
     * Stop the qualifier tool
     */
    stop: () => {
        MassiveQualifier.button_pause.writeAttribute('disabled', true);
        MassiveQualifier.button_play.writeAttribute('disabled', true);
        MassiveQualifier.button_stop.writeAttribute('disabled', true);
        MassiveQualifier.in_progress = false;
    },

    /**
     * Ask insi for the identity
     */
    askInsi: () => {
        if (!MassiveQualifier.in_progress) {
            return;
        }

        MassiveQualifier.patient_id = MassiveQualifier.patients_ids.shift();

        if (!MassiveQualifier.patient_id) {
            MassiveQualifier.stop();
            return;
        }

        MassiveQualifier.updatePatients(MassiveQualifier.patient_id);
        MassiveQualifier.startChrono();

        INSi.searchByIdentityTrait(
            'CPatient-' + MassiveQualifier.patient_id,
            Preferences.insi_use_tls_authentification === '0',
            false,
            MassiveQualifier.callbackInsi,
            MassiveQualifier.callbackError,
            1
        );
    },

    /**
     * The callback afer calling insi
     *
     * @param json
     */
    callbackInsi: (json) => {
        if (!json || json.type !== 'ok') {
            MassiveQualifier.callback();
            return;
        }

        MassiveQualifier.qualify(json.data);
    },

    /**
     * Qualify identity according to the data received by insi
     *
     * @param data
     */
    qualify: (data) => {
        new Url()
            .addParam('patient_id', MassiveQualifier.patient_id)
            .addParam('traits_insi', JSON.stringify(data))
            .requestJSON(
                MassiveQualifier.callback,
                {
                    method:        'POST',
                    getParameters: {m: 'patients', a: 'qualifyIdentity'}
                }
            );
    },

    /**
     * Callbacks executed after qualification
     * @param result_qualification Result of qualification
     */
    callback: (result_qualification = {}) => {
        MassiveQualifier.stopChrono();
        MassiveQualifier.updateProgressBar();
        MassiveQualifier.updateAverageTime();
        MassiveQualifier.updateEstimatedEndTime();
        MassiveQualifier.updatePatients(null, result_qualification);
        MassiveQualifier.updateState();
        MassiveQualifier.askInsi();
    },

    /**
     * Callbacks executed when some errors appears
     */
    callbackError: () => {
        MassiveQualifier.stop();
        MassiveQualifier.callback({qualified: false});
    },

    /**
     * Elapsed time during execution
     */
    generalTimer: () => {
        new PeriodicalExecuter((pe) => {
            if (!MassiveQualifier.in_progress) {
                pe.stop();
                return;
            }

            MassiveQualifier.elapsed_time++;

            const date = new Date(MassiveQualifier.elapsed_time * 1000);

            MassiveQualifier.td_elapsed_time.update(
                (date.getHours() - 1) + 'h ' + date.getMinutes() + 'm ' + date.getSeconds() + 's'
            );
        }, 1);
    },

    /**
     * Update patients area (current patient or last treated)
     *
     * @param patient_id
     * @param result_qualification
     */
    updatePatients: (patient_id = null, result_qualification = {}) => {
        let patient = null;

        if (patient_id) {
            patient = MassiveQualifier.patients[patient_id];
            MassiveQualifier.td_current_patient.update(patient);
        } else {
            MassiveQualifier.td_last_patient.removeClassName('ok');
            MassiveQualifier.td_last_patient.removeClassName('error');
            MassiveQualifier.td_last_patient.addClassName(result_qualification.qualified ? 'ok' : 'error');
            MassiveQualifier.td_last_patient.update(MassiveQualifier.td_current_patient.getText());
            MassiveQualifier.td_current_patient.update();
        }
    },

    /**
     * Update the progress bar in the bottom of the modal
     */
    updateProgressBar: () => {
        if (!MassiveQualifier.initial_count) {
            return;
        }

        MassiveQualifier.progressbar.value =
            parseInt(
                ((MassiveQualifier.initial_count - MassiveQualifier.patients_ids.length) / MassiveQualifier.initial_count) * 100
            );
    },

    /**
     * Update the average time spent by patient
     */
    updateAverageTime: () => {
        if (!MassiveQualifier.chronos.length) {
            return;
        }

        MassiveQualifier.average_time = (MassiveQualifier.chronos.reduce((a, b) => a + b, 0) / MassiveQualifier.chronos.length);

        const date = new Date(MassiveQualifier.average_time * 1000);

        MassiveQualifier.td_average_time.update(date.getSeconds() + '.' + date.getMilliseconds() + 's');
    },

    /**
     * Update the estmated end according to the average time spent by patient
     */
    updateEstimatedEndTime: () => {
        let date = new Date();

        date.setSeconds(MassiveQualifier.average_time * MassiveQualifier.patients_ids.length);

        MassiveQualifier.td_estimated_end_time.update(date.toLocaleTime().replace(':', 'h'));
    },

    /**
     * Start the chrono by patient
     */
    startChrono: () => {
        MassiveQualifier.chrono = 0;

        MassiveQualifier.chrono_periodical = new PeriodicalExecuter(() => {
            MassiveQualifier.chrono += 0.1;
        }, 0.1);
    },

    /**
     * Stop the chrono
     */
    stopChrono: () => {
        MassiveQualifier.chrono_periodical.stop();
        MassiveQualifier.chronos.push(MassiveQualifier.chrono);
    },

    /**
     * Update the status of the tool
     */
    updateState: () => {
        if (!MassiveQualifier.patients_ids.length) {
            MassiveQualifier.div_massive_qualify_state.removeClassName('loading');
            MassiveQualifier.div_massive_qualify_state.addClassName('success');
            MassiveQualifier.div_massive_qualify_state.update($T('MassiveQualiferService-Processing done'));
            return;
        }

        if (MassiveQualifier.in_progress) {
            MassiveQualifier.div_massive_qualify_state.addClassName('loading');
            MassiveQualifier.div_massive_qualify_state.update(
                $T(
                    'MassiveQualiferService-Status in progress',
                    (MassiveQualifier.initial_count - MassiveQualifier.patients_ids.length),
                    MassiveQualifier.initial_count
                )
            );
            return;
        }

        MassiveQualifier.div_massive_qualify_state.removeClassName('loading');

        MassiveQualifier.div_massive_qualify_state.update(
            $T('MassiveQualiferService-Patients to treat', MassiveQualifier.patients_ids.length)
        );
    }
};
