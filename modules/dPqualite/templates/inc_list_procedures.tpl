{{*
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="pagination">
  {{foreach from=$pages item=page key=number}}
    <a class="page {{if $first == $page}}active{{/if}}" href="#1"
       onclick="$V(document.forms['filter-procedures'].first, {{$page}})">{{$number+1}}</a>
  {{/foreach}}
</div>

<table class="tbl">
  <tr>
    {{if $can->edit}}
      <th></th>
    {{/if}}
    <th class="narrow">{{tr}}CDocGed-_reference_doc{{/tr}}</th>
    <th>V.</th>
    <th>{{tr}}CDocGed-titre{{/tr}}</th>
    <th>{{tr}}Date{{/tr}}</th>
  </tr>
  {{foreach from=$procedures item=currProc}}
    <tr>
      {{if $can->edit}}
        <td>
          {{if $can->admin}}
            <form name="ProcEditFrm" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
              <input type="hidden" name="dosql" value="do_docged_aed" />
              <input type="hidden" name="m" value="{{$m}}" />
              <input type="hidden" name="del" value="0" />
              <input type="hidden" name="_validation" value="1" />
              <input type="hidden" name="ged[doc_ged_id]" value="{{$currProc->doc_ged_id}}" />
              <input type="hidden" name="ged[user_id]" value="{{$app->user_id}}" />
              <input type="hidden" name="ged[group_id]" value="{{$currProc->group_id}}" />
              <input type="hidden" name="ged[annule]" value="1" />
              <input type="hidden" name="ged[etat]" value="{{$currProc->etat}}" />
              <button class="cancel notext" style="float: left;" type="submit" title="{{tr}}button-CDocGed-cancel{{/tr}}">
                {{tr}}button-CDocGed-cancel{{/tr}}
              </button>
            </form>
          {{/if}}
          {{if $currProc->etat=='Ox\Mediboard\Qualite\CDocGed'|const:'TERMINE'}}
            <button type="button" class="edit notext" style="float: left;"
                    onclick="document.location = '?m={{$m}}&tab=vw_procencours&doc_ged_id={{$currProc->doc_ged_id}}'"
                    title="{{tr}}button-CDocGed-edit{{/tr}}">
              {{tr}}button-CDocGed-edit{{/tr}}
            </button>
          {{/if}}
        </td>
      {{/if}}
      <td>
        <a href="#1"
           onclick="highlightRow(this); ZoomAjax('{{$currProc->_class}}','{{$currProc->_id}}','CFile','{{$currProc->_lastactif->file_id}}', 0);">
          {{$currProc->_reference_doc}}
        </a>
      </td>
      <td>
        <a href="#1"
           onclick="highlightRow(this); ZoomAjax('{{$currProc->_class}}','{{$currProc->_id}}','CFile','{{$currProc->_lastactif->file_id}}', 0);">
          {{$currProc->version}}
        </a>
      </td>
      <td class="text">
        <a href="#1"
           onclick="highlightRow(this); ZoomAjax('{{$currProc->_class}}','{{$currProc->_id}}','CFile','{{$currProc->_lastactif->file_id}}', 0);">
          {{$currProc->titre}}
        </a>
      </td>
      <td>
        {{$currProc->_lastactif->date|date_format:$conf.date}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="10" class="empty">{{tr}}CDocGed.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>