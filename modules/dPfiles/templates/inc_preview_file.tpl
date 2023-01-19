{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$fileSel || !$fileSel->_id}}
    <div class="small-info">
        {{tr}}CFile-select_document_preview{{/tr}}
    </div>
    {{mb_return}}
{{/if}}

<h4>{{$fileSel->_view}}</h4>

{{if $fileSel->_class=="CFile"}}
    {{$fileSel->file_date|date_format:$conf.datetime}}
    <br/>
{{/if}}

{{if $fileSel->_class == "CFile" && $fileSel->_nb_pages}}
    <!-- Déplacement dans les pages -->
    <button type="button" {{if $page_prev === null}}disabled{{/if}} title="Page précédente" class="left notext"
            onclick="ZoomAjax('{{$objectClass}}', '{{$objectId}}', '{{$elementClass}}', '{{$elementId}}', '{{$page_prev}}');">
    </button>
    {{if $fileSel->_nb_pages && $fileSel->_nb_pages>=2}}
        <select name="_num_page"
                onchange="ZoomAjax('{{$objectClass}}', '{{$objectId}}', '{{$elementClass}}', '{{$elementId}}', this.value);">
            {{foreach from=$arrNumPages item=currPage}}
                <option value="{{$currPage-1}}" {{if $currPage-1==$sfn}}selected{{/if}}>
                    {{$currPage}} / {{$fileSel->_nb_pages}}
                </option>
            {{/foreach}}
        </select>
    {{elseif $fileSel->_nb_pages}}
        Page {{$sfn+1}} / {{$fileSel->_nb_pages}}
    {{/if}}
    <button type="button" {{if !$page_next}}disabled{{/if}} title="Page suivante" class="right notext"
            onclick="ZoomAjax('{{$objectClass}}', '{{$objectId}}', '{{$elementClass}}', '{{$elementId}}', '{{$page_next}}');">
    </button>
{{/if}}

<hr/>

{{if $file_list}}
    <ul>
        {{foreach from=$file_list item=_file_path}}
            <li>{{$_file_path}}</li>
        {{/foreach}}
    </ul>
{{else}}
    {{if $display_as_is}}
        {{assign var=page value=$sfn+1}}
        <a class="button lookup" href="#popFile"
           onclick="popFile('{{$objectClass}}', '{{$objectId}}', '{{$elementClass}}', '{{$elementId}}',{{if $sfn}}{{$sfn}}{{else}}0{{/if}})">
            Visualiser
        </a>
        {{if $includeInfosFile}}
            {{mb_include module=files template=inc_preview_contenu_file}}
        {{else}}
            {{thumbnail document=$fileSel profile=large class=preview title="Afficher le grand aperçu" border="0"
            style="max-height: 450px; max-width: 450px" page=$page}}
        {{/if}}
    {{else}}
        <a href="#popFile"
           onclick="popFile('{{$objectClass}}', '{{$objectId}}', '{{$elementClass}}', '{{$elementId}}',{{if $sfn}}{{$sfn}}{{else}}0{{/if}})">
            {{if $includeInfosFile}}
                {{mb_include module=files template=inc_preview_contenu_file}}
            {{else}}
                {{thumbnail document=$fileSel class=preview profile=large title="Afficher le grand aperçu" border="0"
                style="max-height: 450px; max-width: 450px" page=$page}}
            {{/if}}
        </a>
    {{/if}}
{{/if}}
