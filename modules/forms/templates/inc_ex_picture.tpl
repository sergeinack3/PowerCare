{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=default_color value='#0D23FF'}}
{{mb_default var=default_width value=2}}

{{if $_picture->movable || $_picture->drawable || $_picture->triggered_ex_class_id}}
  {{unique_id var=picture_uid}}

  {{assign var=picture_coords value=$_picture}}
  {{if !$readonly}}
    {{if $_picture->_reported_ex_object_picture}}
      {{assign var=picture_coords value=$_picture->_reported_ex_object_picture}}
    {{/if}}
    <script>
      Main.add(function () {
        require(['lib/fabricjs/fabric.require'], function (fabric) {
          const pic_width = parseInt({{$picture_coords->coord_width|@json}});
          const pic_height = parseInt({{$picture_coords->coord_height|@json}});

          window.currentPictures[{{$_picture->_id}}] = {
            coord_top:    {{$picture_coords->coord_top|@json}},
            coord_left:   {{$picture_coords->coord_left|@json}},
            coord_width:  pic_width,
            coord_height: pic_height,
            coord_angle:  {{$picture_coords->coord_angle|@json}},
            comment:      {{$_picture->_comment|@json}},
            triggered_ex_class_id:  {{$_picture->_triggered_ex_class_id|@json}},
            triggered_ex_object_id: {{$_picture->_triggered_ex_object_id|@json}},
            default_color: '{{$default_color}}',
            default_width: {{$default_width}}
          };

          {{if $_picture->drawable}}
          var fab = new fabric.Canvas('canvas-{{$picture_uid}}', {isDrawingMode: true});

          var img = $("img-{{$picture_uid}}");
          if (img) {
            const options = {};

            if (pic_width && pic_height) {
              options.width = pic_width;
              options.height = pic_height;
            }

            fab.setBackgroundImage(new fabric.Image(img, options));
            fab.freeDrawingBrush.color = window.currentPictures[{{$_picture->_id}}].default_color;
            fab.freeDrawingBrush.width = parseInt(window.currentPictures[{{$_picture->_id}}].default_width) || 1;
            fab.renderAll();
          }

          window.currentPictures[{{$_picture->_id}}].drawing = fab;
          {{/if}}
        });
      });
    </script>
  {{/if}}
  <div class="{{if !$readonly}} {{if $_picture->movable}} resizable {{elseif $_picture->drawable}}drawable{{/if}} {{/if}} form-picture"
       id="picture-{{$_picture->_guid}}"
    {{if !$readonly && !$_picture->drawable}} tabIndex="0" {{/if}}
       data-picture_id="{{$_picture->_id}}"
       data-triggered_ex_class_id="{{$_picture->_triggered_ex_class_id}}"
       data-triggered_ex_object_id="{{$_picture->_triggered_ex_object_id}}"
       data-angle="{{$picture_coords->coord_angle}}"
       style="text-align: center;
         -ms-transform: rotate({{$picture_coords->coord_angle}}deg);
         -webkit-transform: rotate({{$picture_coords->coord_angle}}deg);
         -moz-transform: rotate({{$picture_coords->coord_angle}}deg);
         transform: rotate({{$picture_coords->coord_angle}}deg);
         left:{{$picture_coords->coord_left}}px;
         top:{{$picture_coords->coord_top}}px;
         width:{{$picture_coords->coord_width}}px;
         height:{{$picture_coords->coord_height}}px;
       {{if $_picture->movable}} z-index: 100; {{/if}}
         ">

    {{if !$readonly && $_picture->movable}}
      {{mb_include module=forms template=inc_resizable_handles show_rotate=$_picture->drawable|ternary:false:true}}
    {{/if}}

    <div class="overlayed {{if !$readonly && $_picture->drawable}}ex-picture-canvas{{/if}}"
         data-picture_id="{{$_picture->_id}}"
         onclick="ExClass.focusResizable(event, this)"
         unselectable="on"
         onselectstart="return false;"
    >
      {{if $_picture->drawable && !$readonly}}
        <div class="ex-picture-tools">
          <div style="position: absolute; top: -1px; right: -2px;" onclick="this.up('div.ex-picture-tools').toggleClassName('ex-picture-tools-fixed'); event.stopPropagation();">
            <label title="{{tr}}common-action-Lock{{/tr}}">
              <input type="checkbox" name="picture-lock-{{$_picture->_id}}"/>
            </label>
          </div>

          <fieldset style="position: relative; white-space: nowrap;">
            <legend>
              <label>
                <input type="radio" id="drawing-picture-{{$_picture->_id}}" name="mode-picture-{{$_picture->_id}}" value="drawing" checked onchange="ExObject.togglePictureSelection('{{$_picture->_id}}', this);" onclick="event.stopPropagation();" />

                {{tr}}common-Drawing{{/tr}}
              </label>
            </legend>

            <div id="ex-picture-tools-drawing-{{$_picture->_id}}">
              <div style="padding-top: 2px;">
                {{*<label title="{{tr}}common-Mode{{/tr}}">*}}
                  {{*<select id="drawing-mode-{{$_picture->_id}}" style="width: auto;"*}}
                          {{*onchange="ExObject.setPictureDrawingMode('{{$_picture->_id}}', this.value);">*}}
                    {{*<option value="Pencil" selected>{{tr}}common-Pencil{{/tr}}</option>*}}
                    {{*<option value="Circle">{{tr}}common-Circle{{/tr}}</option>*}}
                    {{*<option value="Spray">{{tr}}common-Spray{{/tr}}</option>*}}
                  {{*</select>*}}
                {{*</label>*}}

                <label title="{{tr}}common-Pencil{{/tr}}">
                  <input type="radio" name="drawing-mode-{{$_picture->_id}}" value="Pencil" checked
                  onchange="ExObject.setPictureDrawingMode('{{$_picture->_id}}', this.value);" onclick="event.stopPropagation();" />
                  <i class="fas fa-pen fa-lg"></i>
                </label>

                <label title="{{tr}}common-Circle{{/tr}}">
                  <input type="radio" name="drawing-mode-{{$_picture->_id}}" value="Circle"
                  onchange="ExObject.setPictureDrawingMode('{{$_picture->_id}}', this.value);" onclick="event.stopPropagation();" />
                  <i class="fas fa-circle fa-lg"></i>
                </label>

                <label title="{{tr}}common-Spray{{/tr}}">
                  <input type="radio" name="drawing-mode-{{$_picture->_id}}" value="Spray"
                  onchange="ExObject.setPictureDrawingMode('{{$_picture->_id}}', this.value);" onclick="event.stopPropagation();" />
                  <i class="fas fa-spray-can fa-lg"></i>
                </label>
              </div>

              <div style="padding-top: 5px;">
                <label title="{{tr}}common-Color{{/tr}}">
                  {{*<input type="color" value="{{$default_color}}" onchange="ExObject.setPictureDrawingColor('{{$_picture->_id}}', this.value);" />*}}

                  <script>
                    Main.add(function () {
                      var e = $('picture-color-picker-{{$_picture->_id}}');

                      e.colorPicker({
                        change: function (color) {
                          $V(this, color ? '#' + color.toHex() : '', true);
                        }.bind(e)
                      });
                    });
                  </script>

                  <input type="hidden" id="picture-color-picker-{{$_picture->_id}}" value="{{$default_color}}"
                         onchange="ExObject.setPictureDrawingColor('{{$_picture->_id}}', this.value);" />
                </label>

                <label title="{{tr}}common-Thickness{{/tr}}">
                  <script>
                    Main.add(function () {
                      $('picture-width-{{$_picture->_id}}').addSpinner({min: 1, max: 30});
                    });
                  </script>

                  <input type="text" id="picture-width-{{$_picture->_id}}" size="3" value="{{$default_width}}" onchange="ExObject.setPictureDrawingWidth('{{$_picture->_id}}', this.value);" />
                </label>
              </div>
            </div>
          </fieldset>

          <fieldset style="position: relative; white-space: nowrap;">
            <legend>
              <label>
                <input type="radio" name="mode-picture-{{$_picture->_id}}" value="selection" onchange="ExObject.togglePictureSelection('{{$_picture->_id}}', this);" onclick="event.stopPropagation();" />

                {{tr}}common-Selection{{/tr}}
              </label>
            </legend>

            <div id="ex-picture-tools-selection-{{$_picture->_id}}">
              <div>
                <button disabled type="button" class="fas fa-copy fa-lg notext" onclick="ExObject.copyPictureSelection('{{$_picture->_id}}');">
                  {{tr}}common-action-Copy{{/tr}}
                </button>

                <button disabled type="button" class="fas fa-paste fa-lg notext" onclick="ExObject.pastePictureSelection('{{$_picture->_id}}');">
                  {{tr}}common-action-Paste{{/tr}}
                </button>

                <button disabled type="button" class="fas fa-eraser fa-lg notext" onclick="ExObject.removePictureSelection('{{$_picture->_id}}');">
                  {{tr}}common-action-Delete selection{{/tr}}
                </button>

                <button disabled type="button" class="fas fa-trash fa-lg notext" onclick="ExObject.clearPictureCanvas('{{$_picture->_id}}');"
                style="color: firebrick !important;">
                  {{tr}}common-action-Delete all{{/tr}}
                </button>
              </div>
            </div>
          </fieldset>
        </div>
      {{/if}}

      <div style="position: relative; width: 100%; height: 100%;"
           {{if $_picture->description}}title="{{$_picture->description}}"{{/if}}>
        {{if $_picture->drawable}}
          {{if $_picture->_reported_ex_object_picture
          && $picture_coords->_id
          && $picture_coords->_ref_drawing
          && $picture_coords->_ref_drawing->_id
          }}
            {{assign var=_background value=$picture_coords->_ref_drawing}}
          {{elseif $_picture->_ref_ex_object_picture
          && $_picture->_ref_ex_object_picture->_id
          && $_picture->_ref_ex_object_picture->_ref_drawing
          && $_picture->_ref_ex_object_picture->_ref_drawing->_id
          }}
            {{assign var=_background value=$_picture->_ref_ex_object_picture->_ref_drawing}}
          {{else}}
            {{assign var=_background value=$_picture->_ref_file}}
          {{/if}}

          {{if $_background && $_background->_id}}
            {{assign var=style value="width: 100%; height: 100%;"}}
            {{if !$readonly}}
              {{assign var=style value="visibility: hidden; width: 100%; height: 100%;"}}
            {{/if}}
            {{thumbnail document=$_background profile=large id="img-$picture_uid" style="$style"}}
          {{else}}
            <div style="width: 100%; height: 100%; top: 0; left: 0; background: white;"></div>
          {{/if}}

          {{if !$readonly}}
            <div style="width: 100%; height: 100%; top: 0; left: 0; position: absolute; background: white;">
              <canvas class="area" id="canvas-{{$picture_uid}}" width="{{$picture_coords->coord_width}}"
                      height="{{$picture_coords->coord_height}}"></canvas>
            </div>
          {{/if}}

          {{if $_picture->show_label}}
            {{$_picture->name}}
          {{/if}}
        {{else}}
          {{if $_picture->_ref_ex_object_picture && $_picture->_ref_ex_object_picture->_id && $_picture->_ref_ex_object_picture->_ref_drawing && $_picture->_ref_ex_object_picture->_ref_drawing->_id}}
            {{assign var=_background value=$_picture->_ref_ex_object_picture->_ref_drawing}}
          {{else}}
            {{assign var=_background value=$_picture->_ref_file}}
          {{/if}}

          {{if $_background && $_background->_id}}
            {{thumbnail document=$_background profile=large id="img-$picture_uid" style="width: 100%; height: 100%;"}}
          {{else}}
            <div style="width: 100%; height: 100%; top: 0; left: 0; background: white;"></div>
          {{/if}}

          {{if $_picture->show_label}}
            {{$_picture->name}}
          {{/if}}
          <div class="overlay"></div>
        {{/if}}

        {{if !$readonly && $_picture->triggered_ex_class_id}}
          <div style="position: absolute; top: 0;">
            <a href="#1"
               onclick="ExObject.triggerExClassFromPicture(this.up('.form-picture'),'{{$ex_object->_ref_ex_class->name|smarty:nodefaults|JSAttribute}}'); Event.stop(event);">
              <i class="fa fa-bolt form-picture-trigger" type="button" title="Ouvrir le sous-formulaire"></i>
            </a>

            {{*
            <button class="comment notext" type="button" style="float: left;"
                    onclick="ExObject.showExObjectPictureComment(this.up('.form-picture'), 'ex-picture-{{$picture_uid}}'); Event.stop(event);">
              {{tr}}CExObjectPicture-comment{{/tr}}
            </button>
            *}}
          </div>
        {{/if}}
      </div>
    </div>
  </div>
  <div id="ex-picture-{{$picture_uid}}" style="display: none; text-align: center;">
    <textarea name="_ex_picture_comment_{{$picture_uid}}" rows="5">{{$_picture->_comment}}</textarea>
    <button type="button" class="tick"
            onclick="window.currentPictures[{{$_picture->_id}}].comment = this.form['_ex_picture_comment_{{$picture_uid}}'].value; Control.Modal.close()">
      {{tr}}OK{{/tr}}
    </button>
    <button type="button" class="cancel" onclick="Control.Modal.close()">
      {{tr}}Cancel{{/tr}}
    </button>
  </div>
{{else}}
  <div id="picture-{{$_picture->_guid}}"
       class="resizable form-picture"
       style="text-align: center;
       {{if $_picture->description}} pointer-events: auto; {{else}} pointer-events: none; {{/if}}
         -ms-transform: rotate({{$_picture->coord_angle}}deg);
         -webkit-transform: rotate({{$_picture->coord_angle}}deg);
         -moz-transform: rotate({{$_picture->coord_angle}}deg);
         transform: rotate({{$_picture->coord_angle}}deg);
         left:{{$_picture->coord_left}}px;
         top:{{$_picture->coord_top}}px;
         width:{{$_picture->coord_width}}px;
         height:{{$_picture->coord_height}}px;">
    {{if $_picture->description}}
      {{thumbnail document=$_picture->_ref_file profile=large style="width: 100%; height: 100%;" title="`$_picture->description`"}}
    {{else}}
      {{thumbnail document=$_picture->_ref_file profile=large style="width: 100%; height: 100%;"}}
    {{/if}}

    {{if $_picture->show_label}}
      {{$_picture->name}}
    {{/if}}
  </div>
{{/if}}
