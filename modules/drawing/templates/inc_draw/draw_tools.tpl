{{*
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<fieldset id="draw_tools_1">
  <legend>
    <label>
      <input type="radio" name="toggle_type" onclick="changeMode('draw');" checked="checked"/>
      {{tr}}CDrawingCategory-Pencil drawing{{/tr}}
    </label>
  </legend>
  <p>
    <label>{{tr}}CDrawingCategory-Width of line{{/tr}}<input type="range" min="1" max="10" value="3" onchange="DrawObject.changeDrawWidth($V(this));"/></label>
  </p>
  <p>
    <label>
      {{tr}}Color{{/tr}}
      <input type="hidden" name="color" value="#000000" onchange="DrawObject.changeDrawColor(this.value)" id="color_picker_draw"/>
    </label>
    <script>
      Main.add(function () {
        [$('color_picker_draw'), $('color_text_cv')].each(function(p){
          p.colorPicker({
            prefferedFormat: "hex"
          });
        })
      });
    </script>
  </p>
</fieldset>

<fieldset id="draw_tools_0">
  <legend>
    <label>
      <input type="radio" name="toggle_type" onclick="changeMode('edit');"/>
      {{tr}}CDrawingCategory-Controls{{/tr}}
    </label>
  </legend>
  <div id="draw_object_tool">
    <button onclick="DrawObject.removeActiveObject();" class="cancel notext">{{tr}}drawobject.delete{{/tr}}</button>
    <button onclick="DrawObject.zoomInObject()" class="zoom-in notext">{{tr}}drawobject.flipy-desc{{/tr}}</button>
    <button onclick="DrawObject.zoomOutObject()" class="zoom-out notext">{{tr}}drawobject.flipy-desc{{/tr}}</button>

    <button onclick="DrawObject.flipXObject()" class="hslip notext">{{tr}}drawobject.flipx-desc{{/tr}}</button>
    <button onclick="DrawObject.flipYObject()" class="vslip notext">{{tr}}drawobject.flipy-desc{{/tr}}</button>

    {{*<button onclick="DrawObject.sendToBack()"     class="down notext">{{tr}}drawobject.flipy-desc{{/tr}}</button>*}}
    <button onclick="DrawObject.sendBackwards()" class="down notext">{{tr}}drawobject.sendBackwards-desc{{/tr}}</button>
    <button onclick="DrawObject.bringForward()" class="up notext">{{tr}}drawobject.sendForward-desc{{/tr}}</button>
    {{*<button onclick="DrawObject.bringToFront()"   class="up notext">{{tr}}drawobject.flipy-desc{{/tr}}</button>*}}

    <p><label>{{tr}}CDrawingCategory-Opacity{{/tr}}<input type="range" min="1" max="100" value="100" onchange="DrawObject.changeOpacty($V(this));"/></label></p>
  </div>
  <button onclick="DrawObject.clearCanvas();" class="cleanup me-tertiary">{{tr}}CDrawingCategory-action-Erase all{{/tr}}</button>

</fieldset>

<hr class="me-no-display"/>

<fieldset>
  <legend>{{tr}}CDrawingCategory-legend-Text{{/tr}}</legend>
  <form method="get" name="text_edit_canvas">
    <table class="form me-no-box-shadow">
      <tr>
        <th>{{tr}}CDrawingCategory-legend-Text{{/tr}}</th>
        <td>
          <textarea id="content_text_cv" name="content_text_cv"></textarea>
        </td>
      </tr>
      <tr>
        <th>{{tr}}Color{{/tr}}</th>
        <td>
          <input type="hidden" value="#000000" name="color_text_cv" id="color_text_cv"/>
        </td>
      </tr>
      <tr>
        <th>
          {{tr}}CDrawingCategory-Shadow text{{/tr}}
        </th>
        <td>
          <input type="text" value="#000000 0 0 10px" name="bgcolor_text_cv" id="bgcolor_text_cv"/>
        </td>
      </tr>
      <tr>
        <td colspan="2" class="button">
          <button type="button" class="tick" onclick="DrawObject.addEditText( $V('content_text_cv'), $V('color_text_cv'), $V('bgcolor_text_cv') );">
            {{tr}}common-action-Validate{{/tr}}
          </button>
        </td>
      </tr>
    </table>
  </form>
</fieldset>
