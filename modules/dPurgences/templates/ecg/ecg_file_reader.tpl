{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=ecg_rpu_displayed value=0}}
{{mb_script module=files script=files ajax=$ajax}}


<script>
    Main.add(function () {
        ViewPort.SetAvlHeight("listEcgFiles-{{$category->nom_court}}", 1.0);
        ViewPort.SetAvlHeight("listEcgFilesList", 1.0);
    });
    openFile = function (objectClass, objectId, elClass, elId) {
            new Url('files', 'preview_files')
                .addParam('objectClass', objectClass)
                .addParam('objectId', objectId)
                .addParam('elementClass', elClass)
                .addParam('elementId', elId)
                .addParam('popup', "1")
                .addParam('view_light', "1")
                .addParam('no_buttons', '1')
                .addParam('inline_header', '1')
                .addParam('embed', 'ecgFileReader-{{$tab_id}}')
                .requestUpdate('ecgFileReader-{{$tab_id}}');
        };
</script>

<table class="main tbl me-no-hover me-no-align" id="listEcgFiles-{{$category->nom_court}}">
    <tr>
        <th class="me-border-right me-border-right-width-2">{{tr}}List-of-file{{/tr}}</th>
        <th class="me-text-align-center"></th>
    </tr>
    <tr>
        <td class="quarterPane me-valign-top me-border-right me-border-right-width-2 overflow-auto">
            <table id="listEcgFilesList" class="list-ecg me-block me">
                {{foreach from=$ecg_files name=ecg_files item=_file}}
                {{if !$ecg_rpu_displayed && $ecg_files|@first}}
                    <script>
                        {{* On affiche le premier ecg du rpu selectionné si il existe *}}
                        openFile('{{$_file->object_class}}', '{{$_file->object_id}}', '{{$_file->_class}}', '{{$_file->_id}}');
                    </script>
                    {{assign var=ecg_rpu_displayed value=1}}
                {{/if}}
                    <tr id="ecg-{{$_file->_id}}">
                        <td>
                            <a href="#" onclick="openFile('{{$_file->object_class}}', '{{$_file->object_id}}', '{{$_file->_class}}', '{{$_file->_id}}');"
                               class="me-padding-5"
                               style="font-size: 1.2em;line-height: 1.2em;">
                <span id="title-{{$_file->_id}}" onmouseover="ObjectTooltip.createEx(this, '{{$_file->_guid}}');">
                {{$_file->file_name}}
                </span>
                                <small>({{$_file->_file_size}})</small>
                            </a>
                        </td>
                    </tr>
                    {{foreachelse}}
                    <tr>
                        <td class="empty">
                            {{tr}}CFile.none{{/tr}}
                        </td>
                    </tr>
                {{/foreach}}
            </table>
        </td>
        <td class="greedyPane me-h100">
            <div id="ecgFileReader-{{$tab_id}}"></div>
        </td>
    </tr>
</table>
