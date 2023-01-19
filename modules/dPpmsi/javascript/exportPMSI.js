/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ExportPMSI = {
    exportPlannedOperations: function () {
        var form = getForm("changeDate");
        new Url('pmsi', 'exportCsvPlannedOperations')
            .addParam('date_min', form.date_min)
            .addParam('date_max', form.date_max)
            .addParam('types[]', $V(form.types))
            .pop(500, 500);
    },
};
