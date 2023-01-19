{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $prescription}}
  {{assign var=create_evt_user_can_edit value="ssr general create_evt_user_can_edit"|gconf}}
  {{foreach from=$prescription->_ref_prescription_lines_element_by_cat item=_lines_by_chap}}
    {{foreach from=$_lines_by_chap item=_lines_by_cat}}
      {{foreach from=$_lines_by_cat.element item=_line name=foreach_category}}
        {{assign var=element value=$_line->_ref_element_prescription}}
        {{if $smarty.foreach.foreach_category.first}}
          {{assign var=category value=$element->_ref_category_prescription}}
          {{assign var=category_id value=$category->_id}}
          <div class="techniciens" id="techniciens{{$num}}-{{$category->_guid}}" style="display: none;">
            {{if array_key_exists($category_id, $executants)}}
              {{assign var=list_executants value=$executants.$category_id}}
              {{if $num == "" && (array_key_exists($user->_id, $list_executants) || $create_evt_user_can_edit) && !$can->admin}}
                {{if $create_evt_user_can_edit}}
                  {{foreach from=$list_executants item=current_user}}
                    <button title="{{$current_user->_view}}" id="technicien{{$num}}-{{$category_id}}-{{$current_user->_id}}"
                            class="none ressource" type="button"
                            onclick="selectTechnicien('{{$current_user->_id}}', '{{$num}}', this)">
                      {{$current_user->_user_last_name}}
                    </button>
                  {{/foreach}}
                {{else}}
                  {{assign var=current_user_id value=$user->_id}}
                  {{assign var=current_user value=$list_executants.$current_user_id}}
                  <button title="{{$current_user->_view}}" id="technicien{{$num}}-{{$category_id}}-{{$user->_id}}"
                          class="none ressource" type="button"
                          onclick="selectTechnicien('{{$current_user->_id}}', '{{$num}}', this)">
                    {{$current_user->_user_last_name}}
                  </button>
                {{/if}}
              {{/if}}

              {{if $can->admin || $num != ""}}
                <select class="_technicien_id" onchange="selectTechnicien(this.value, '{{$num}}');">
                  <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                  {{mb_include module=mediusers template=inc_options_mediuser list=$list_executants}}
                </select>
              {{/if}}
            {{else}}
              <div class="small-warning">
                {{tr}}ssr-no_reeduc_for_cat_elt{{/tr}}
              </div>
            {{/if}}
          </div>
        {{/if}}
      {{/foreach}}
    {{/foreach}}
  {{/foreach}}
  {{if $none_list}}
    <div class="techniciens" id="techniciens{{$num}}-" style="display: none;">
      <select class="_technicien_id" onchange="selectTechnicien(this.value, '{{$num}}');">
        <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
        {{mb_include module=mediusers template=inc_options_mediuser list=$none_list selected=$user->_id}}
      </select>
    </div>
  {{/if}}
{{else}}
  {{if $can->edit}}
    <select class="_technicien_id" onchange="selectTechnicien(this.value, '{{$num}}');">
      <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
      {{mb_include module=mediusers template=inc_options_mediuser list=$executants selected=$user->_id}}
    </select>
  {{/if}}
{{/if}}