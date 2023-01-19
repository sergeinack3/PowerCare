{{*
 * @package Mediboard\MonitorServer
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=limit value=100}}

{{mb_script module=dPfiles script=files_explorer}}

<script>
  Main.add(function () {
    var form = getForm('files_explorer');
    Calendar.regField(form.elements.from_date);
    Calendar.regField(form.elements.to_date);

    $(form.object_class).makeAutocomplete({
      width: "200px"
    });

    FilesExplorer.makeUserAutocomplete(form, form.elements._user_autocomplete);
    FilesExplorer.makeFunctionAutocomplete(form, form.elements._function_autocomplete);
    FilesExplorer.makeCategoryAutocomplete(form, form.elements._category_autocomplete);

  });
</script>

<form name="files_explorer" method="get"
      onsubmit="return onSubmitFormAjax(this, null, 'files_search_results');">
    <input type="hidden" name="m" value="dPfiles" />
    <input type="hidden" name="a" value="ajax_search_files" />
    <input type="hidden" name="start" value="0" />
    <input type="hidden" name="limit" value="{{$limit}}" />
    <input type="hidden" name="_order" value="{{$_order}}" />
    <input type="hidden" name="_way" value="{{$_way}}" />
    <input type="hidden" name="user_id" value="" />
    <input type="hidden" name="function_id" value="" />
    <input type="hidden" name="category_id" value="" />
    <input type="hidden" name="csv" value="0" />

    <table class="main form">
        <tr>
            <th class="narrow">{{tr}}common-Date{{/tr}}</th>
            <td class="narrow">
                <input type="hidden" name="from_date" class="dateTime" value="{{$from_date}}" onchange="$V(form.elements.start, '0');" />
                &raquo;
                <input type="hidden" name="to_date" class="dateTime" value="{{$to_date}}" onchange="$V(form.elements.start, '0');" />
            </td>
            <th class="narrow">{{tr}}CFile.mimetype{{/tr}}</th>
            <td class="narrow">
                <input type="text" name="mimetype" value="{{$mimetype}}"/>
            </td>
            <th class="narrow">{{mb_title object=$file field=annule}}</th>
            <td class="narrow">
                {{mb_field object=$file field=annule typeEnum=select emptyLabel="CFile-annule.all" canNull=true}}
            </td>
        </tr>
        <tr>
            <th class="narrow">{{tr}}CFile-_file_size{{/tr}} (kB)</th>
            <td class="narrow">
                <input type="text" name="min_size" value="{{$min_size}}"/>
                &raquo;
                <input type="text" name="max_size" value="{{$max_size}}"/>
            </td>
            <th class="narrow">{{tr}}CFile-file_name{{/tr}}</th>
            <td class="narrow" colspan="3">
                <input type="text" name="file_name" value="{{$file_name}}"/>
            </td>
        </tr>
        <tr>
            <th>{{tr}}CFile-author_id{{/tr}}</th>
            <td>
                <input type="text" name="_user_autocomplete" class="autocomplete" value="" />
                <button type="button" class="erase notext" onclick="FilesExplorer.clearField(this.form._user_autocomplete, this.form.user_id);"></button>
            </td>
            <th>{{tr}}CFunctions{{/tr}}</th>
            <td colspan="3">
                <input type="text" name="_function_autocomplete" class="autocomplete" value="" />
                <button type="button" class="erase notext" onclick="FilesExplorer.clearField(this.form._function_autocomplete, this.form.function_id);"></button>
            </td>
        </tr>
        <tr>
            <th>{{mb_label object=$file field=object_class}}</th>
            <td>
                <select name="object_class" class="str" style="width: 200px;">
                    <option value="">&mdash; Toutes les classes</option>
                    {{foreach from=$classes item=curr_class}}
                        <option value="{{$curr_class}}" {{if $curr_class == $file->object_class}}selected{{/if}}>
                            {{tr}}{{$curr_class}}{{/tr}} - {{$curr_class}}
                        </option>
                    {{/foreach}}
                </select>
                <button type="button" class="erase notext" onclick="FilesExplorer.clearField(this.form.object_class, this.form.object_id);"></button>
            </td>
            <th class="narrow">{{tr}}CFile.file_hash{{/tr}}</th>
            <td class="narrow">
                <input type="text" name="file_hash" value="{{$file_hash}}"/>
            </td>
            <th>{{tr}}CFilesCategory{{/tr}}</th>
            <td>
                <input type="text" name="_category_autocomplete" class="autocomplete" value="" />
                <button type="button" class="erase notext" onclick="FilesExplorer.clearField(this.form._category_autocomplete, this.form.category_id);"></button>
            </td>
        </tr>
        <tr>
            <td class="me-text-align-right">
              <div class="small-info">
                {{tr}}CFilesExplorer-Msg-Stats-count{{/tr}} : <span id="stats-file-count"></span>
                <br/>
                {{tr}}CFilesExplorer-Msg-Stats-min_time{{/tr}} : <span id="stats-file-min-time"></span>
                <br/>
                {{tr}}CFilesExplorer-Msg-Stats-max_time{{/tr}} : <span id="stats-file-max-time"></span>
                <br/>
                {{tr}}CFilesExplorer-Msg-Stats-mean_time{{/tr}} : <span id="stats-file-mean-time"></span>
                <br/>
                {{tr}}CFilesExplorer-Msg-Stats-std_deviation{{/tr}} : <span id="stats-file-std-deviation"></span>
                <br/>
              </div>
            </td>
            <td class="button" colspan="3">
                <button type="button" class="search me-primary" onclick="this.form.onsubmit();">
                    {{tr}}common-action-Search{{/tr}}
                </button>
            </td>
            <td colspan="2">
              <fieldset>
                <legend>{{tr}}CFilesExplorer-Action-Export options{{/tr}}</legend>

                <label>
                  <input type="checkbox" name="only_missing" value="1"/>
                  {{tr}}CFilesExplorer-Option-Only missing{{/tr}}
                </label>

                <br/>

                <label>
                  <input type="radio" name="_mode" value="default" checked/>
                  {{tr}}CFilesExplorer.mode.default{{/tr}}
                </label>

                <label>
                  <input type="radio" name="_mode" value="fast"/>
                  {{tr}}CFilesExplorer.mode.fast{{/tr}}
                </label>

                <label>
                  <input type="radio" name="_mode" value="min"/>
                  {{tr}}CFilesExplorer.mode.min{{/tr}}
                </label>

                <br/>

                <a class="button fas fa-external-link-alt" onclick="return FilesExplorer.exportAsCSV();">
                  {{tr}}CFilesExplorer-Action-Export{{/tr}}
                </a>
              </fieldset>
            </td>
        </tr>
    </table>
</form>

<div id="files_search_results" class="me-padding-0 me-align-auto"></div>
