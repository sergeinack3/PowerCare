{{*
 * @package Mediboard\MonitorServer
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    $('files_search_results_container').fixedTableHeaders();
  });
</script>

{{mb_include module=system template=inc_pagination change_page='FilesExplorer.changeFilesPage' total=$total current=$start step=$limit}}

<div id="files_search_results_container">
    <table class="main tbl">
        <thead>
            <tr>
                <th>{{mb_colonne class=CFile field=doc_size order_col=$_order order_way=$_way function=FilesExplorer.orderFilesList}} (kB)</th>
                <th>{{tr}}CFile.fs_file_size{{/tr}} (kB)</th>
                <th>{{tr}}CFile.file_exists{{/tr}}</th>
                <th>{{tr}}CFile.file_size_mismatch{{/tr}}</th>
                <th>{{mb_colonne class=CFile field=file_real_filename order_col=$_order order_way=$_way function=FilesExplorer.orderFilesList}}</th>
                <th>{{mb_colonne class=CFile field=file_name order_col=$_order order_way=$_way function=FilesExplorer.orderFilesList}}</th>
                <th>{{mb_colonne class=CFile field=file_type order_col=$_order order_way=$_way function=FilesExplorer.orderFilesList}}</th>
                <th>{{mb_title class=CFile field=object_class}}</th>
                <th>{{mb_title class=CFile field=object_id}}</th>
                <th>{{tr}}CPatient{{/tr}}</th>
                <th>{{mb_title class=CFile field=file_category_id}}</th>
                <th>{{mb_colonne class=CFile field=author_id order_col=$_order order_way=$_way function=FilesExplorer.orderFilesList}}</th>
                <th>{{mb_title class=CMediusers field=function_id}}</th>
                <th>{{mb_colonne class=CFile field=file_date order_col=$_order order_way=$_way function=FilesExplorer.orderFilesList}}</th>
                <th>{{mb_title class=CFile field=annule}}</th>
                <th class="narrow"></th>
            </tr>
        </thead>
        <tbody>

        {{foreach from=$files item=_file}}

            {{assign var=file_id value=$_file->_id}}
            {{assign var=file_status value=$file_statuses.$file_id}}
            {{assign var=db_file_size value=$_file->doc_size}}
            {{assign var=fs_file_size value=$file_status.fs_file_size}}
            {{assign var=patient value=$file_status.patient}}

            <tr>
                <td align="right">
                    {{if $db_file_size != 0}}
                        {{math equation="x / y" x=$db_file_size y=1024 format="%.2f"}}
                    {{/if}}
                </td>
                <td align="right">
                    {{if $fs_file_size != 0}}
                        {{math equation="x / y" x=$fs_file_size y=1024 format="%.2f"}}
                    {{/if}}
                </td>
                <td align="center">
                    <span class="circled {{if $file_status.file_exists == false}}error{{else}}ok{{/if}}">
                      {{if $file_status.file_exists == false}}
                          {{tr}}No{{/tr}}
                      {{else}}
                          {{tr}}Yes{{/tr}}
                      {{/if}}
                    </span>
                </td>
                <td align="center">
                    <span class="circled {{if $file_status.file_size_mismatch == true}}error{{else}}ok{{/if}}">
                      {{if $file_status.file_size_mismatch == true}}
                          {{tr}}Yes{{/tr}}
                      {{else}}
                          {{tr}}No{{/tr}}
                      {{/if}}
                    </span>
                </td>
                <td>
                    {{mb_value object=$_file field=file_real_filename}}
                </td>
                <td>
                    <span class="truncate" title="{{$_file->file_name}}">
                        {{mb_value object=$_file field=file_name}}
                    </span>
                </td>
                <td>
                    {{mb_value object=$_file field=file_type}}
                </td>
                <td>
                    {{mb_value object=$_file field=object_class}}
                </td>
                <td>
                    <span class="truncate">
                        {{mb_value object=$_file field=object_id tooltip=true}}
                    </span>
                </td>
                <td>
                    {{if $patient && $patient->_id}}
                        <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
                            {{$patient->_view}}
                        </span>
                    {{/if}}
                </td>
                <td>
                    {{mb_value object=$_file field=file_category_id tooltip=true}}
                </td>
                <td>
                    {{mb_value object=$_file field=author_id tooltip=true}}
                </td>
                <td>
                    {{mb_value object=$_file->_ref_author field=function_id tooltip=true}}
                </td>
                <td align="center">
                    {{mb_value object=$_file field=file_date}}
                </td>
                <td align="center">
                    {{mb_value object=$_file field=annule}}
                </td>
                <td>
                    {{mb_include module=system template=inc_object_history object=$_file}}
                </td>
            </tr>
            {{foreachelse}}
            <tr>
                <td class="empty" colspan="16">{{tr}}CFile.none{{/tr}}</td>
            </tr>
        {{/foreach}}
        </tbody>
    </table>
</div>
