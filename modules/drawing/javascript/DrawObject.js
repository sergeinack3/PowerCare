/**
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

DrawObject = window.DrawObject || {
  canvas : null,
  canvas_element : 'canvas',

  init : function(canvas) {
    DrawObject.canvas = new fabric.Canvas('canvas');
    DrawObject.canvas.freeDrawingBrush.color = 'black';
    DrawObject.canvas.freeDrawingBrush.width = 3;
    DrawObject.canvas.isDrawingMode = true;

    // object selected
    DrawObject.canvas.on('object:selected', function(obj) {
      if (obj.target.type == 'text') {
        $V('content_text_cv', obj.target.text, true);
        $V('color_text_cv', obj.target.fill, true);
        $V('bgcolor_text_cv', obj.target.shadow, true);
      }

      $$('#draw_object_tool button, #draw_object_tool input').each(function(elt) {
        elt.disabled = false;
      });
    });

    // selection cleared
    DrawObject.canvas.on('selection:cleared', function(obj) {
      $$('#draw_object_tool button, #draw_object_tool input').each(function(elt) {
        elt.disabled = true;
      });
    });

    return DrawObject.canvas;

  },

  getSvgStr : function() {
    return DrawObject.canvas.toSVG();
  },

  getJsonStr : function() {
    var json = DrawObject.canvas.toDatalessJSON();
    return JSON.stringify(json);
  },

  loadDraw : function(data) {
    DrawObject.canvas.loadFromDatalessJSON(data).renderAll();
  },

  setProperty : function(name, value) {
    DrawObject.canvas.name = value;
  },

  refresh : function() {
    DrawObject.canvas.renderAll();
  },

  toggleMode : function(button) {
    DrawObject.canvas.isDrawingMode = !DrawObject.canvas.isDrawingMode;
    if (button) {
      var text = button.innerText;
    }
    return DrawObject.canvas.isDrawingMode;
  },

  changeMode : function(type) {
    DrawObject.canvas.isDrawingMode = (type == 'draw');
    return DrawObject.canvas.isDrawingMode;
  },

  insertFromUpload : function(input) {
    var file = input.files[0];

    var reader = new FileReader();
    reader.onload = function(e) {
      var blob = e.target.result;
      var img = new Image();
      img.src = blob;
      img.onload = function() {
        var image = new fabric.Image(img);
        DrawObject.findBetterRatio(image);
        DrawObject.canvas.add(image);
        DrawObject.canvas.renderAll();
      };
    };
    reader.readAsDataURL(file);
  },

  addEditText : function(str, col, ctr) {
    var text = str;
    var color = col || '#000000';
    var color_2 = ctr;
    if (!text || !color) {
      return;
    }
    var active = DrawObject.canvas.getActiveObject();
    if (active && active.type == 'text') {
      active.set({
        text: text,
        fill : color,
        shadow: color_2
      });
      DrawObject.refresh();
    }
    //add text
    else {
      var canvas_text = new fabric.Text(text, {});
      canvas_text.set({
        left: (DrawObject.canvas.width-canvas_text.width)/2,
        top: (DrawObject.canvas.height-canvas_text.height)/2,
        fill: color,
        shadow: color_2
      });
      DrawObject.canvas.add(canvas_text);
    }
  },

  changeDrawWidth : function(value) {
    if (value) {
      DrawObject.canvas.freeDrawingBrush.width = value;
    }

    // multiple objects
    var objects = DrawObject.canvas.getActiveGroup();
    if (objects) {
      objects.forEachObject(function(_o){
        if (_o.type == 'path') {
          _o.set(
            {strokeWidth : value}
          );
        }
      });
    }

    // one object
    var active = DrawObject.canvas.getActiveObject();
    if (active && active.type == 'path') {
      active.set({strokeWidth : value});
    }

    if (active || objects) {
      DrawObject.refresh();
    }
  },

  changeOpacty : function(ivalue) {
    var active = DrawObject.canvas.getActiveObject();
    if (active && ivalue) {
      active.set({opacity : ivalue/100})
    }
    DrawObject.refresh();
  },

  changeDrawColor : function(value) {
    DrawObject.canvas.freeDrawingBrush.color = value;

    // multiple objects
    var objects = DrawObject.canvas.getActiveGroup();
    if (objects) {
      objects.forEachObject(function(_o){
        if (_o.type == 'path') {
          _o.set(
            {stroke : value}
          );
        }
      });
    }

    // one object
    var active = DrawObject.canvas.getActiveObject();
    if (active && active.type == 'path') {

      active.set({stroke : value});
    }

    if (active || objects) {
      DrawObject.refresh();
    }
  },

  zoomInObject: function() {
    var object = DrawObject.canvas.getActiveObject();
    if (object) {
      object.scaleX = object.scaleX+ (10*object.scaleX)/100;
      object.scaleY = object.scaleY+ (10*object.scaleY)/100;
    }
    DrawObject.canvas.renderAll();
  },

  zoomOutObject: function() {
    var object = DrawObject.canvas.getActiveObject();
    if (object) {
      object.scaleX = object.scaleX - (10*object.scaleX)/100;
      object.scaleY = object.scaleY - (10*object.scaleY)/100;
    }
    DrawObject.canvas.renderAll();
  },

  sendToBack : function() {
    var activeObject = DrawObject.canvas.getActiveObject();
    if (activeObject) {
      DrawObject.canvas.sendToBack(activeObject);
    }
  },

  sendBackwards : function() {
    var activeObject = DrawObject.canvas.getActiveObject();
    if (activeObject) {
      DrawObject.canvas.sendBackwards(activeObject);
    }
  },

  bringForward  : function() {
    var activeObject = DrawObject.canvas.getActiveObject();
    if (activeObject) {
      DrawObject.canvas.bringForward(activeObject);
    }
  },

  bringToFront : function() {
    var activeObject = DrawObject.canvas.getActiveObject();
    if (activeObject) {
      DrawObject.canvas.bringToFront(activeObject);
    }
  },

  /**
   * remove an object from canvas
   *
   * @param object_to_remove
   * @param unique
   */
  removeObject : function (object_to_remove, unique) {
    if (object_to_remove) {
      DrawObject.canvas.remove(object_to_remove);
    }
    if (unique) {
      DrawObject.canvas.renderAll();
    }
  },

  clearCanvas : function () {
    if (confirm($T('CDrawingCategory-msg-Clear the entire drawing area ?'))) {
      DrawObject.canvas.clear().renderAll();
    }
  },

  undo : function() {
    var objects = DrawObject.canvas.getObjects();
    if (objects.length > 0 && objects[(objects.length)-1]) {
      DrawObject.removeObject(objects[(objects.length)-1], true);
    }
  },

  /**
   * remove the selected object
   */
  removeActiveObject : function() {
    var object = DrawObject.canvas.getActiveObject();
    if (object) {
      DrawObject.removeObject(object, true);
    }
  },

  flipXObject : function() {
    var object = DrawObject.canvas.getActiveObject();
    if (object) {
      object.flipX = !object.flipX;
      DrawObject.canvas.renderAll();
    }
  },

  flipYObject : function() {
    var object = DrawObject.canvas.getActiveObject();
    if (object) {
      object.flipY = !object.flipY;
      DrawObject.canvas.renderAll();
    }
  },

  /** IMAGES **/

  insertSVGStr : function(str) {
    var imgfjs = fabric.loadSVGFromString(str, function(objects, options) {
      objects.each(function(img) {
        DrawObject.canvas.add(img);
      });
      DrawObject.canvas.calcOffset();
      DrawObject.canvas.renderAll.bind(DrawObject.canvas);
    });
  },

  insertSVG : function(uri) {
    var group = [];
    var imgfjs = fabric.loadSVGFromURL(uri, function(objects, options) {
      var shape = fabric.util.groupSVGElements(objects, options);
      $(DrawObject.canvas_element).width = shape.getWidth() || 600;
      $(DrawObject.canvas_element).height = shape.getHeight() || 600;
      DrawObject.canvas = new fabric.Canvas('canvas', { backgroundColor: '#fff' });
      DrawObject.canvas.add(shape);
      //shape.center();
      DrawObject.canvas.renderAll();
    });
  },

  findBetterRatio : function(img) {
    //if the pic is bigger than canvas we resize
    if (img.width && img.height && (img.width > DrawObject.canvas.width || img.height > DrawObject.canvas.height)) {
      var width_sup_height = img.width > img.height;
      var ratio = img.width/img.height;
      img.set({
        width: width_sup_height ? DrawObject.canvas.width : DrawObject.canvas.height*ratio,
        height: width_sup_height ? DrawObject.canvas.width/ratio : DrawObject.canvas.height
      });
    }

    // positionning
    img.set({
      left: (DrawObject.canvas.width - img.width)/2,
      top: (DrawObject.canvas.height - img.height)/2
    });

    return img;
  },

  insertImg : function(uri) {
    var imgfjs = fabric.Image.fromURL(uri, function(img) {
      DrawObject.findBetterRatio(img);
      DrawObject.canvas.add(img);
      DrawObject.canvas.renderAll.bind(DrawObject.canvas);
    });
  }
};