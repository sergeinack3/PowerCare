{{*
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=drawing script=DrawObject ajax=1}}

<style>
  .cfile_to_select_list img {
    max-width: 150px;
  }

  .cfile_to_select_list {
    text-align: center;
  }

  .cfile_to_select_list li {
    display: inline-block;
    list-style: none;
    padding:0;
    margin: 5px 0;
  }

  fieldset.active{
    border-left:solid 3px green;
  }

  .basic_color {
    border:solid 1px black;
    width: 1.35em;
    height: 2em;
    display: inline-block;
    line-height: 1.5em;
  }

  .note {
    padding:5px;
    border:solid 1px grey;
    box-shadow: 0 0 3px grey;
    border-radius: 5px;
    margin-top:10px;
    text-align:left;
  }
</style>

<script>

  saveDraw = function(form, is_export) {
    if (is_export) {
      var remove_draft = confirm($T('CDrawingCategory-msg-Do you want to delete the draft ?'));
      if (remove_draft) {
        $(form.remove_draft, 1);
      }
      var sdata = DrawObject.getSvgStr();
      $V(form.export, 1);
    }
    else {
      var sdata = DrawObject.getJsonStr();
    }
    $V(form.svg_content, sdata);
    return onSubmitFormAjax(
      form,
      {
        method: 'post',
        onComplete: () => {
          if (window.refreshAfterAdd) {
            Control.Modal.close();
            window.refreshAfterAdd();
          }

          Control.Modal.close();
        }
      }
    );
  };

  changeMode = function(type) {
    DrawObject.changeMode(type);
  };

  canvas_text = function() {
    DrawObject.addEditText('modal_text_cv');
  };

  insertFromInternet = function(surl) {
    var url = new Url("drawing", "ajax_get_content");
    url.addParam('url', surl);
    url.addParam('format', 'uri');
    url.requestJSON(DrawObject.insertImg);
  };

  selectThisElement = function(element) {
    var file_id = element.get('file_id');
    var is_svg = element.get('file_type').indexOf('svg') != -1;
    if (is_svg) {
      //old
      DrawObject.insertSVG("?m=files&raw=thumbnail&document_guid=CFile-"+file_id);
    }
    else {
      //new
      var url = new Url("drawing", "ajax_get_content");
      url.addParam('format', 'uri');
      url.addParam("file_id", file_id);
      url.requestJSON(DrawObject.insertImg);
    }
  };



  keyBoardEvent = function(ev) {
    var key = ev.keyCode;

    if (key == 32) {  // spacebar = toggle
      //ev.preventDefault();
      toggleMode();
      return false;
    }

    if (key == 46) { // suppr
      //ev.preventDefault();
      DrawObject.removeActiveObject();
      return false;
    }

    if (key == 65) { // a = annuler
    }

    return false;
  };

  rightclicEvent = function(ev) {
    var type = ev.target.tagName.toLowerCase();
    if (type == "canvas") {
      ev.preventDefault();
      return false;
    }
  };

  Main.add(function() {
    var tabs = Control.Tabs.create('tabs_draw', false);

    // keyboard down
    //document.observe('keydown', keyBoardEvent);

    require(["lib/fabricjs/fabric.require"], function(util) {
      // init
      DrawObject.init('canvas');
      {{if $draw->_id}}
        DrawObject.loadDraw({{$draw->_binary_content|smarty:nodefaults}});
      {{else}}
        DrawObject.canvas.backgroundColor = '#'+Preferences.drawing_background;
        DrawObject.canvas.renderAll();
      {{/if}}
      DrawObject.refresh();
    });
});
</script>

<table class="main">
  <tr>
    <td style="text-align: center; width: 600px;">
      <canvas id="canvas" width="{{if "drawing General drawing_square"|gconf}}600{{else}}800{{/if}}" height="600" style="border: solid 1px black;" >
        {{tr}}Canvas.unsupported{{/tr}}
      </canvas>
    </td>
    <td style="text-align: center;">
      {{if $draw->_id}}{{tr}}common-Changing{{/tr}}{{else}}{{tr}}common-Creation{{/tr}}{{/if}} ({{tr var1=$draw->_ref_object}}CDrawingCategory-Linked to %s{{/tr}})
      <ul class="control_tabs" id="tabs_draw">
        <li><a href="#draw_tools"><img src="modules/drawing/images/icon.png" alt="{{tr}}CDrawingCategory-Drawing-desc{{/tr}}" style="width: 16px" /></a></li>
        <li><a href="#ressources"><img src="modules/dPfiles/images/icon.png" alt="{{tr}}CFile|pl{{/tr}}" style="width: 16px" /></a></li>
        <li><a href="#save"><i class="me-icon save me-primary"></i></a></li>
      </ul>
      <div id="draw_tools">
        {{mb_include module=drawing template=inc_draw/draw_tools}}
      </div>

      <div id="ressources" style="height: 500px; overflow-y: auto; display: none;">
        {{mb_include module=drawing template=inc_draw/draw_ressources}}
      </div>

      <div id="draw_help" style="display: none;">
        <h2>{{tr}}CDrawingCategory-Keyboard shortcut|pl{{/tr}}</h2>
        <table class="tbl">
          <tr>
            <th>{{tr}}to{{/tr}}</th>
            <td>{{tr}}CDrawingCategory-action-Undo last action{{/tr}}</td>
          </tr>
          <tr>
            <th>{{tr}}CDrawingCategory-Spacebar{{/tr}}</th>
            <td>{{tr}}CDrawingCategory-Mode Drawing / edition{{/tr}}</td>
          </tr>
          <tr>
            <th>{{tr}}common-action-Delete-court{{/tr}}</th>
            <td>{{tr}}CDrawingCategory-action-Delete active object{{/tr}}</td>
          </tr>
        </table>
      </div>

      <div id="save" style="display: none">
        {{mb_include module=drawing template=inc_draw/draw_save}}
      </div>
    </td>
  </tr>
</table>
