{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main layout">
  {{foreach from=$list item=item name=anesth}}
    {{if $smarty.foreach.anesth.first}}
      <tr>
        <td>
          <span class="type_item circled">
            {{if $item->_ref_consultation->teleconsultation}}
                {{tr}}CConsultAnesth-teleconsultation{{/tr}}
            {{else}}
                {{tr}}CConsultAnesth{{/tr}}
            {{/if}}
          </span>
        </td>
      </tr>
    {{/if}}
    <tr>
      <td style="width: 50%;">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
          {{$item}}
        </span>
        <br>
        {{$item->_ref_consultation->_datetime}}
        <br>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$item->_ref_consultation->_ref_chir}}
      </td>
      <td>
        {{if $item->_ref_consultation->categorie_id}}
          <span class="timeline_description">
          {{mb_include module=cabinet template=inc_icone_categorie_consult consultation=$item->_ref_consultation categorie=$item->_ref_consultation->_ref_categorie display_name=true}}
        </span>
        {{/if}}
        {{if $item->_ref_consultation->rques}}
          <span class="timeline_description">{{$item->_ref_consultation->rques}}</span>
        {{/if}}
        {{if $item->_ref_consultation->motif}}
          <span class="timeline_description">{{$item->_ref_consultation->motif}}</span>
        {{/if}}
        {{if $item->_ref_consultation->histoire_maladie}}
          <span class="timeline_description">{{$item->_ref_consultation->histoire_maladie}}</span>
        {{/if}}
        {{if $item->_ref_consultation->examen}}
          <span class="timeline_description">{{$item->_ref_consultation->examen}}</span>
        {{/if}}
        {{if $item->_ref_consultation->conclusion}}
          <span class="timeline_description">{{$item->_ref_consultation->conclusion}}</span>
        {{/if}}
        {{if $item->_ref_consultation->_nb_files_docs}}
          <span class="timeline_description">{{$item->_ref_consultation->_nb_files_docs}} document(s)</span>
        {{/if}}
      </td>
    </tr>
    {{if !$smarty.foreach.anesth.last}}
      <tr>
        <td colspan="2"><hr class="item_separator"/></td>
      </tr>
    {{/if}}
  {{/foreach}}
</table>
