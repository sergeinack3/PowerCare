{{*
 * @package Mediboard\Lpp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    importDB = function (get_version) {
        var url = new Url('lpp', 'do_import_lpp', 'dosql');
        if (get_version) {
            url.addParam('get_version', 1);
            url.requestUpdate('db-version', {
                method: 'post', getParameters: {m: 'lpp', dosql: 'do_import_lpp'}
            });
        } else {
            url.requestUpdate('db-import', {
                method: 'post', getParameters: {m: 'lpp', dosql: 'do_import_lpp'}
            });
        }
    };

    importChapters = function () {
        new Url('lpp', 'do_import_lpp_chapters', 'dosql')
            .requestUpdate('chapters-import', {
                method: 'post', getParameters: {m: 'lpp', dosql: 'do_import_lpp_chapters'}
            });
    }
</script>

<div id="bdd">

    {{mb_include module=system template=configure_dsn dsn=lpp}}

    <h2>Import de la base de données LPP</h2>

    <table class="tbl" style="table-layout: fixed;">
        <tr>
            <th>{{tr}}Action{{/tr}}</th>
            <th>{{tr}}Status{{/tr}}</th>
        </tr>

        <tr>
            <td>
                <button class="tick" onclick="importDB(true)">Vérifier la dernière version disponible</button>
            </td>
            <td id="db-version"></td>
        </tr>

        <tr>
            <td>
                <button class="tick" onclick="importDB()">Importer la base de données LPP</button>
            </td>
            <td id="db-import"></td>
        </tr>
        <tr>
            <td>
                <button class="tick" onclick="importChapters();">Importer les chapitres de la LPP</button>
            </td>
            <td id="chapters-import"></td>
        </tr>
    </table>
</div>
