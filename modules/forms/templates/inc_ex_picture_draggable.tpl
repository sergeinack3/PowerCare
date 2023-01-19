{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$_picture->disabled && ($_picture->_ref_file && $_picture->_ref_file->_id || $_picture->drawable)}}
  <div class="resizable form-picture {{if $_picture->drawable}} drawable {{/if}}" tabIndex="0" data-picture_id="{{$_picture->_id}}"
       data-angle="{{$_picture->coord_angle}}"
       style="text-align: center;
         -ms-transform: rotate({{$_picture->coord_angle}}deg);
         -webkit-transform: rotate({{$_picture->coord_angle}}deg);
         -moz-transform: rotate({{$_picture->coord_angle}}deg);
         transform: rotate({{$_picture->coord_angle}}deg);
         left:{{$_picture->coord_left}}px;
         top:{{$_picture->coord_top}}px;
         width:{{$_picture->coord_width}}px;
         height:{{$_picture->coord_height}}px;">
    {{mb_include module=forms template=inc_resizable_handles show_rotate=$_picture->drawable|ternary:false:true}}
    <div class="overlayed"
         data-picture_id="{{$_picture->_id}}"
         ondblclick="ExPicture.edit({{$_picture->_id}}); Event.stop(event);"
         onclick="ExClass.focusResizable(event, this)"
         unselectable="on"
         onselectstart="return false;"
      >
      <div class="area" style="position: relative; width: 100%; height: 100%; {{if !$_picture->_ref_file || !$_picture->_ref_file->_id}} background: white; {{/if}}">
        <div style="position:absolute; color: green; font-size: 16px;">
          {{if $_picture->triggered_ex_class_id}}
            <i class="fa fa-bolt" style="float: left; margin: 2px;"></i>
          {{/if}}
          {{if $_picture->movable}}
            <i class="fa fa-arrows" style="float: left; margin: 2px;"></i>
          {{/if}}
          {{if $_picture->drawable}}
            <i class="fas fa-pencil-alt" style="float: left; margin: 2px;"></i>
          {{/if}}
        </div>

        {{if $_picture->_ref_file && $_picture->_ref_file->_id}}
          {{thumbnail document=$_picture->_ref_file profile=large style="width: 100%; height: 100%;"}}
        {{/if}}

        {{if $_picture->show_label}}
          {{$_picture->name}}
        {{/if}}
        <div class="overlay"></div>
      </div>
    </div>
  </div>
{{/if}}


