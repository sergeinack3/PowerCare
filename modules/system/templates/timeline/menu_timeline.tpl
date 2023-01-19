{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=system_timeline ajax=$ajax}}

{{mb_default var=nb_late_event value=0}}
{{mb_default var=nb_reminders  value=0}}
{{mb_default var=filterExists  value=true}}
{{mb_default var=isSubFiltered value=true}}
{{mb_default var=nb_menu       value=0}}

{{*
Dont forget to embrace this file with '<div class="timeline_menu timeline_menu_design">' in the parent tpl
Also, don't forget to implement TimelineImplement.refreshResume({string|string[]|null} canonical_menu_name); which will load, refresh, filter menus
If the timeline becomes to specific to a module, just copy/paste this tpl in a new one in the module

Ox\Mediboard\System\CTimeline $timeline       - the built timeline
string[]                      $filtered_menus - menus to display
*}}

{{assign var=badges value=$timeline->getBadges()}}
{{foreach from=$timeline->getMenuItems() item=evenement}}
  {{if $evenement->isVisible()}}
    {{assign var=code_evenement value=$evenement->getCanonicalName()}}
    {{assign var=category_class value=$evenement->getCategoryColorValue()}}
    {{assign var=event_menu value=$code_evenement|cat:'_menu'}}

    <div class="menu-title-container menu-title-container-{{$category_class}}
            {{if $code_evenement == "evenements"}}
              {{if $nb_late_event > 0}}me-menu-title-container-badge_red{{/if}}
              {{if $nb_late_event == 0 && $nb_reminders > 0}}me-menu-title-container-badge_orange{{/if}}
            {{/if}}"
         onmouseover="SystemTimeline.showMenuActions('{{$code_evenement}}-0-actions');"
         onmouseout="SystemTimeline.hideMenuActions('{{$code_evenement}}-0-actions');"
         style="border: 0;width :
                 {{if $nb_menu > 9}}9%{{else}}10%{{/if}}; min-width: 76px;">
      {{assign var=show_category value="['$code_evenement'"}}
      {{foreach from=$evenement->getChildren() item=_event_child name=sub_categories}}
        {{assign var=canonical_name value=$_event_child->getCanonicalName()}}
        {{assign var=show_category value=$show_category|smarty:nodefaults|cat:", '$canonical_name'"}}
      {{/foreach}}
      {{assign var=show_category value=$show_category|smarty:nodefaults|cat:']'}}

      <!-- Menu title -->
      {{assign var=select_category value=0}}
      {{if ($filtered_menus|@count === 0 && $evenement->getSelected()) || ($filtered_menus|@count > 0 && !$evenement->getSelected())}}
        {{assign var=select_category value=1}}
      {{/if}}
      <span class="menu-title"
            {{if $evenement->isClickable()}}
               onclick="TimelineImplement.refreshResume({{if $select_category}}{{$show_category}}{{/if}});"
            {{/if}}>
        <i class="{{$evenement->getLogo()}} {{if !$evenement->getSelected()}}opacity-50{{/if}} menu-title-icon"></i>

          {{if $code_evenement == "evenements"}}
              {{if isset($badges.alerts|smarty:nodefaults) && $badges.alerts > 0}}
                  <span class="indicateur {{if $badges.alerts > 9}}indicateur_2_num{{/if}}
                                {{if !$filterExists && !$isSubFiltered}}opacity-50{{/if}}"
                        style="width: 10px; display: inline-block; margin-right: 0;"
                        title="{{tr var1=$badges.alerts}}oxCabinet-TAMM event badge alerts{{if $badges.alerts>1}}|pl{{/if}}{{/tr}}">
              {{$badges.alerts}}
            </span>
              {{/if}}
              {{if isset($badges.reminders|smarty:nodefaults) && $badges.reminders > 0}}
                  <span class="indicateur {{if $badges.reminders > 9}}indicateur_2_num{{/if}}
                                {{if !$filterExists && !$isSubFiltered}}opacity-50{{/if}}"
                        title="{{tr var1=$badges.reminders}}oxCabinet-TAMM event badge reminders{{if $badges.reminders>1}}|pl{{/if}}{{/tr}}"
                        style="width: 10px; display: inline-block; background: #ff9502;">
                    {{$badges.reminders}}
                  </span>
              {{/if}}
          {{else}}
            {{if isset($badges.$event_menu|smarty:nodefaults) && $badges.$event_menu > 0}}
              {{assign var=traduction_badge value="oxCabinet-TAMM event badge $code_evenement"}}
              <span class="indicateur {{if $badges.$event_menu > 9}}indicateur_2_num{{/if}}
                          {{if !$filterExists && !$isSubFiltered}}opacity-50{{/if}}"
                    title="{{tr var1=$badges.$event_menu}}{{$traduction_badge}}{{if $badges.$event_menu>1}}|pl{{/if}}{{/tr}}">
                {{$badges.$event_menu}}
              </span>
            {{/if}}
          {{/if}}

        <div class="timeline_menu_design_icon {{$code_evenement}}
             {{if !$evenement->getSelected()}}opacity-50{{/if}}">
          <span class="text">{{$evenement->getName()}}</span>
        </div>
      </span>

      {{* Sub-menus *}}
      {{if in_array("inc_$code_evenement.tpl", $actions_files) || $evenement->countChildren() > 0}}
        <div id="{{$code_evenement}}-0-actions" class="tooltip timeline-event-actions" style="display:none;">
            {{if in_array("inc_$code_evenement.tpl", $actions_files)}}
                <div class="timeline_menu_item"
                     onclick="TimelineImplement.refreshResume({{if $filtered_menus|@count === 0 || !$evenement->getSelected()}}['{{$code_evenement}}']{{/if}})">
                    <i class="{{$evenement->getLogo()}} {{if !$evenement->getSelected()}}opacity-50{{else}}selected-sub-menu{{/if}}"></i>
                    <span class="text timeline_menu_item_text">{{$evenement->getName()}}</span>

                    {{if isset($badges.$code_evenement|smarty:nodefaults) && $badges.$code_evenement > 0}}
                        {{assign var=traduction_badge value="$m event badge $code_evenement"}}
                        <span class="timeline-indicateur {{if $badges.$code_evenement > 9}}indicateur_2_num{{/if}} {{if !$evenement->getSelected()}}opacity-50{{/if}}"
                              title="{{tr var1=$badges.$code_evenement}}{{$traduction_badge}}{{if $badges.$code_evenement>1}}|pl{{/if}}{{/tr}}">
                      {{$badges.$code_evenement}}
                    </span>
                    {{/if}}
                </div>

                {{mb_include module=$m template="timeline/actions/inc_$code_evenement"}}
            {{/if}}

          {{foreach from=$evenement->getChildren() item=_child name=children}}
            {{if $_child->isVisible()}}
              {{assign var=child_code value=$_child->getCanonicalName()}}

              <div class="timeline_menu_item"
                   onclick="TimelineImplement.refreshResume({{if $filtered_menus|@count === 0 || !$_child->getSelected()}}['{{$child_code}}']{{/if}})">
                <i class="{{$_child->getLogo()}} {{if !$_child->getSelected()}}opacity-50{{else}}selected-sub-menu{{/if}}"></i>
                <span class="text timeline_menu_item_text">{{$_child->getName()}}</span>

                {{if isset($badges.$child_code|smarty:nodefaults) && $badges.$child_code > 0}}
                  {{assign var=traduction_badge value="$m event badge $child_code"}}
                  <span class="timeline-indicateur {{if $badges.$child_code > 9}}indicateur_2_num{{/if}} {{if !$_child->getSelected()}}opacity-50{{/if}}"
                        title="{{tr var1=$badges.$child_code}}{{$traduction_badge}}{{if $badges.$child_code>1}}|pl{{/if}}{{/tr}}">
                      {{$badges.$child_code}}
                    </span>
                {{/if}}
              </div>

              {{if in_array("inc_$child_code.tpl", $actions_files)}}
                {{mb_include module=$m template="timeline/actions/inc_$child_code"}}
              {{/if}}
            {{/if}}
          {{/foreach}}
        </div>
      {{/if}}
    </div>
  {{/if}}
{{/foreach}}
