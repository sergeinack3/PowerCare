{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="resizable field-input {{if $_field->_no_size}} no-size {{/if}}" tabIndex="0" data-field_id="{{$_field->_id}}"
     unselectable="on"
     style="left:{{$_field->coord_left}}px; top:{{$_field->coord_top}}px; width:{{$_field->coord_width}}px; height:{{$_field->coord_height}}px;">
{{mb_include module=forms template=inc_resizable_handles}}

{{mb_include module=forms template=inc_ex_field_draggable _field=$_field _type="field"}}
</div>