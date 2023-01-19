{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">
  Glisser-déposer les boutons dans la zone de formulaire. <br />
  Veuillez double-cliquer sur les boutons pour modifier leur comportement ou leur affichage.
</div>

<div style="padding: 1em;" id="draggable-action-button-container">
  {{assign var=counter value=0}}
  {{foreach from=","|explode:"copy|left,copy|up,copy|right,copy|down,empty|cancel,open|new" item=_icon}}
    {{assign var=_action value="|"|explode:$_icon}}

    <div class="draggable action-button-wrapper"
         data-type="action-button"
         data-action="{{$_action.0}}"
         data-icon="{{$_action.1}}"
         data-ex_class_id="{{$ex_class->_id}}"
         data-reset_id="draggable-action-button-container"
         data-reset_pos="{{$counter}}"
         data-movable="true"
    >
      <div class="clonable" style="height: 100%;">
        {{mb_include module=forms template=inc_action_button_draggable_pixel action=$_action.0 icon=$_action.1 ex_class=$ex_class}}
      </div>
    </div>
    {{assign var=counter value=$counter+1}}
  {{/foreach}}
</div>