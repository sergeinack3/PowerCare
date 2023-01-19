{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=pdf_and_thumbs value=$app->user_prefs.pdf_and_thumbs}}

<ul style="text-align: left;">
  {{foreach from=$packs item=_pack}}
    {{if $_pack->_owner == "user"}}
      {{assign var=owner_icon value="user"}}
      {{if $_pack->user_id == $app->user_id}}
        {{assign var=owner_icon value="user-glow"}}
      {{/if}}
    {{elseif $_pack->_owner == "func"}}
      {{assign var=owner_icon value="user-function"}}
      {{if $_pack->function_id == $app->_ref_user->function_id}}
        {{assign var=owner_icon value="user-function-glow"}}
      {{/if}}
    {{else}}
      {{assign var=owner_icon value="group"}}
    {{/if}}
      
    <li data-modeles_ids="{{'|'|implode:$_pack->_modeles_ids}}">
      <img style="float: right; clear: both; margin: -1px;" 
        src="images/icons/{{$owner_icon}}.png" />
        {{if $_pack->fast_edit_pdf && $_pack->fast_edit && $pdf_and_thumbs}}
          <img style="float: right;" src="modules/dPcompteRendu/fcke_plugins/mbprintPDF/images/mbprintPDF.png"/>
        {{elseif $_pack->fast_edit}}
          <img style="float: right;" src="modules/dPcompteRendu/fcke_plugins/mbprinting/images/mbprinting.png"/>
        {{/if}}
        {{if $_pack->fast_edit}}
          <img style="float: right;" src="images/icons/lightning.png"/>
        {{/if}}
      
      <div class="{{if $_pack->fast_edit}}fast_edit{{/if}} {{if !$_pack->merge_docs}}merge_docs{{/if}}">{{$_pack->nom|emphasize:$keywords}}</div>
      
      <div style="display: none;" class="id">{{$_pack->_id}}</div>
      <div style="display: none;" class="pack_is_eligible">{{$_pack->is_eligible_selection_document}}</div>
    </li>
  {{/foreach}}
</ul>
