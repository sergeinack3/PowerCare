/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ECG = {
    /**
     * Get list of ecg documents (.pdf) from a category
     *
     * @param category_id
     * @param container
     * @param sejour_id
     */
    getListEcgPdfFromCategory: function (category_id, container, sejour_id) {
        let tab_id = container.replace(/^ecgTab-/, '')

        new Url('urgences', 'getListEcgPdfFromCategory')
            .addParam('category_id', category_id)
            .addParam('sejour_id', sejour_id)
            .addParam('tab_id', tab_id)
            .requestUpdate(container);
    },

    /**
     * Display pdf document in iframe and set next and previous button attributes
     *
     * @param document_id
     * @param container_suffix
     */
    displayEcgPdf: function (document_id, container_suffix) {
        var rows = $$('.list-ecg tr.selected');
        rows.each((row) => {
            row.removeClassName('selected')
        });

        var document_row = $("ecg-" + document_id),
            ecg_container = $("listEcgFiles-" + container_suffix),
            prev_tr = document_row.previous('tr'),
            next_tr = document_row.next('tr'),
            prev_btn = ecg_container.down('button.previous'),
            next_btn = ecg_container.down('button.next'),
            title_preview = $("titlePreview-" + container_suffix),
            title = document_row.down('span#title-' + document_id).getText().strip();

        document_row.addClassName('selected');
        title_preview.update($T('Preview') + ' - ' + title);

        if (prev_tr) {
            var prev_id = (prev_tr.identify()).split('-')[1];
            prev_btn.writeAttribute('onclick', 'ECG.displayEcgPdf(' + prev_id + ', "' + container_suffix + '")');
            prev_btn.removeAttribute('disabled');
        } else {
            prev_btn.writeAttribute('disabled', 'disabled');
        }
        if (next_tr) {
            var next_id = (next_tr.identify()).split('-')[1];
            next_btn.writeAttribute('onclick', 'ECG.displayEcgPdf(' + next_id + ', "' + container_suffix + '")');
            next_btn.removeAttribute('disabled');
        } else {
            next_btn.writeAttribute('disabled', 'disabled');
        }

        // On affiche le pdf dans l'iframe
        $("ecgFileReader-" + container_suffix).src = "?m=urgences&raw=displayEcgPdf&document_id=" + document_id;
    }
};
