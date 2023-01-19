/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

PatientState = {
    filterPatientState: function (form) {
        new Url("dPpatients", "ajax_filter_patient_state")
            .addFormData(form)
            .requestUpdate("patient_manage_container");

        return false;
    },

    /**
     * Liste paginée des patients par statut d'identité
     *
     * @param state
     * @param page
     */
    getListPatientByState: function (state, page) {
        new Url("dPpatients", "listPatientState")
            .addParam("state", state)
            .addParam("page", page)
            .requestUpdate("patient_" + state);
    },

    edit_patient: function (patient_id, state) {
        Patient.editModal(patient_id, false, null, PatientState.getListPatientByState.curry(state))
    },

    changePage: {
        prov: function (page) {
            PatientState.getListPatientByState('prov', page);
        },

        vali: function (page) {
            PatientState.getListPatientByState('vali', page);
        },

        dpot: function (page) {
            PatientState.getListPatientByState('dpot', page);
        },

        anom: function (page) {
            PatientState.getListPatientByState('anom', page);
        },

        cach: function (page) {
            PatientState.getListPatientByState('cach', page);
        }
    },

    mergePatient: function (patients_id) {
        new Url("system", "object_merger")
            .addParam("objects_class", "CPatient")
            .addParam("objects_id", patients_id)
            .popup(800, 600, "merge_patients");
    },

    // ==================
    // Statistics
    // ==================

    /**
     * View all patient statistics
     */
    viewStats: function () {
        new Url("patients", "viewStats")
            .requestUpdate("patient_stats");
    },

    /**
     * Export statistics to CSV
     */
    downloadCSV: function () {
        let form = getForm("filter_graph_bar_patient_state");

        new Url("dPpatients", "exportStatsPatientState", "raw")
            .addFormData(form)
            .popup(200, 200)
    },

    /**
     * View patient statistics
     *
     * @param form Form
     */
    statsFilter: function (form) {
        let url = new Url("patients", "viewStatsPatientState");

        if (form) {
            url.addFormData(form);
        }

        url.requestUpdate("stats_patient_state");
    },

    /**
     * Displays patient statistics
     *
     * @param form Form
     */
    showStats: function (form) {
        new Url("patients", "loadListStatsPatientState")
            .addFormData(form)
            .requestUpdate("list_stats_patient_state");
    },

    /**
     * Displays the graph's tooltip
     *
     * @param event Event
     * @param pos   Position
     * @param item  Item
     */
    hoverGraph: function (event, pos, item) {
        if (item) {
            jQuery("#flot-tooltip").remove();

            let abscisse = parseInt(pos.x1) | 0,
                content  = item.series.label + "<br /><strong>" + item.series.data[abscisse][1] + " " + item.series.unit + "</strong>";

            if (item.series.bars.show) {
                content += "<br />" + item.series.data[abscisse].day;
            }

            $$("body")[0].insert(
                DOM.div({
                    className: "tooltip",
                    id: "flot-tooltip"
                }, content).setStyle({
                    top:  pos.pageY + "px",
                    left: parseInt(pos.pageX) + "px"
                })
            );
        } else {
            jQuery("#flot-tooltip").remove();
        }
    },

    /**
     * Show Merged patients view
     *
     * @param event Event
     * @param pos   Position
     * @param item  Item
     */
    showMergedPatients: function (event, pos, item) {
        if (item) {
            let x    = parseInt(pos.x1),
                data = item.series.data[x];

            if (data.ids) {
                new Url('dPpatients', 'ajax_show_merged_patients')
                    .addParam('date', data.day)
                    .addParam('ids', data.ids)
                    .requestModal(800, 600);
            }
        }
    },

    /**
     * Draw graph
     *
     * @param circular_graph Circular graph information
     * @param bar_graph      Bar graph information
     */
    drawGraph: function (circular_graph, bar_graph) {
        let state_graph    = jQuery("#state_graph"),
            identity_graph = jQuery("#identity_graph");

        state_graph.bind('plothover', PatientState.hoverGraph);

        identity_graph.bind('plothover', PatientState.hoverGraph);
        identity_graph.bind('plotclick', PatientState.showMergedPatients);

        jQuery.plot(state_graph, circular_graph.datum, circular_graph.options);
        jQuery.plot(identity_graph, bar_graph.datum, bar_graph.options);
    },

    massiveQualify: () => {
        const form = getForm('filter_patient_state');
        new Url('patients', 'vwMassiveQualify')
          .addParam('date_min', $V(form._date_min))
          .addParam('date_max', $V(form._date_max))
          .requestModal('650px', '250px', {onClose: () => { form.onsubmit(); }});
    }
};
