{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul class="list">
  {{if $result->result_id}}
    {{foreach from=$result->_transcodings item=transcoding}}
      <li class="transcoding" data-transcoding_id="{{$transcoding->transcoding_id}}" data-code="{{$transcoding->code_cim_1}}"
          data-libelle="{{$transcoding->libelle_cim_1|ucfirst}}"
          {{if $transcoding->_transcoding_criteria && $transcoding->_transcoding_criteria|@count}}
            data-conditions="1"
            {{assign var=criteria value=""}}
            {{foreach from=$transcoding->_transcoding_criteria item=criterion name=transcoding_criteria}}
              {{assign var=criteria value=$criteria|cat:$criterion->criterion_id}}
              {{if !$smarty.foreach.transcoding_criteria.last}}
                {{assign var=criteria value=$criteria|cat:'|'}}
              {{/if}}
            {{/foreach}}
            data-criteria="{{$criteria}}"
          {{else}}
            data-conditions="0"
          {{/if}}
          {{if $result->cim10_code == $transcoding->code_cim_1}}
            data-default="1"
          {{/if}}>
        {{$transcoding->code_cim_1}} &mdash; {{$transcoding->libelle_cim_1|ucfirst}}
      </li>
    {{foreachelse}}
      <li style="font-style: italic; color: #aaa">
        {{tr}}CDRCConsultationResult-_transcodings.none{{/tr}}
      </li>
    {{/foreach}}
  {{else}}
    <li style="font-style: italic; color: #aaa">
      {{tr}}CDRCConsultationResult-msg-no_selected_result{{/tr}}
    </li>
  {{/if}}
</ul>
