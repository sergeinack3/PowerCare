/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

window.ExClass || (ExClass = {});

ExClass.getBoxCoords = function(box) {
  var style = box.style;

  return {
    coord_top:    Number.getInt(style.top,        0),
    coord_left:   Number.getInt(style.left,       0),
    coord_width:  Number.getInt(style.width,      ""),
    coord_height: Number.getInt(style.height,     ""),
    coord_angle:  Number.getInt(box.get("angle"), 0)
  };
};

ExClass.startDrag = function(draggable) {
  var element = draggable.element;
  if (
    element.hasClassName("field-input") ||
    element.hasClassName("subgroup") ||
    element.hasClassName("draggable-message") ||
    element.hasClassName("host-field") ||
    element.hasClassName("form-picture") ||
    element.hasClassName("action-button") ||
    element.hasClassName("action-button-wrapper") ||
    element.hasClassName("form-widget") ||
    element.hasClassName("form-widget-wrapper")
  ) {
    // Div principale et tous les subgroups
    draggable._subgroups = draggable.element.up(".group-layout").select(".subgroup, .pixel-grid").without(element);

    draggable._subgroups.each(function(subgroup) {
      Droppables.add(subgroup, {
        onDrop: function(dragged, dropped, event) {
          var dragSubgroup = dragged.up(".subgroup");
          var fromGroup = !dragSubgroup;
          var parent;

          // Déplacement au sein d'un même groupe ou vers la div principale
          if (dragSubgroup == dropped || dropped.hasClassName("pixel-grid") && !dragSubgroup) {
            return;
          }

          var pos = {
            left: parseInt(dragged.style.left),
            top:  parseInt(dragged.style.top)
          };

          // offset from group to subgroup
          parent = dropped;
          while (parent && parent != dragSubgroup) {
            var style = parent.style;
            pos.left -= Number.getInt(style.left, 0)+1;
            pos.top  -= Number.getInt(style.top, 0) +1;
            parent = parent.up(".subgroup");
          }

          // group to subgroup
          if (fromGroup) {
            if (!dragged.descendantOf(dropped)) {
              dropped.down("fieldset").insert(dragged);
            }
          }

          // subgroup to other
          else {
            // offset from subgroup to group
            parent = dragSubgroup;
            while (parent) {
              var style = parent.style;
              pos.left += Number.getInt(style.left, 0)+1;
              pos.top  += Number.getInt(style.top, 0) +1;
              parent = parent.up(".subgroup");
            }

            dropped.insert(dragged);
          }

          dragged.setStyle({
            left: pos.left+"px",
            top:  pos.top+"px"
          });

          try {
            dragged.focus();
          } catch(e) {}
        },
        hoverclass: "dropactive"
      });
    });
  }
};

/**
 * Fonction appelée à la fin du drag.
 * Fait l'appel ajax pour enregistrer la nouvelle position du champ.
 * Si le champ est un widget ou un bouton on réinitialise sa position à la fin
 *
 * @param drag
 * @param event
 */
ExClass.endDrag = function(drag, event) {
  var box = drag.element;
  var dims = ExClass.getBoxCoords(box);

  if (dims.coord_width && dims.coord_height) {
    box.removeClassName("no-size");
  }

  var subgroup = box.up(".subgroup");
  var group = box.up(".group-layout");

  var url = new Url();

  // Field
  var field_id = box.get("field_id");
  if (field_id) {
    url.addParam("@class", "CExClassField");
    url.addParam("ex_class_field_id", field_id);
    url.addParam("subgroup_id", subgroup ? subgroup.get("subgroup_id") : "");
  }

  // Message
  var message_id = box.get("message_id");
  if (message_id) {
    url.addParam("@class", "CExClassMessage");
    url.addParam("ex_class_message_id", message_id);
    url.addParam("subgroup_id", subgroup ? subgroup.get("subgroup_id") : "");
  }

  // Picture
  var picture_id = box.get("picture_id");
  if (picture_id) {
    url.addParam("@class", "CExClassPicture");
    url.addParam("ex_class_picture_id", picture_id);
    url.addParam("subgroup_id", subgroup ? subgroup.get("subgroup_id") : "");
  }

  // Subgroup
  var subgroup_id = box.get("subgroup_id");
  if (subgroup_id) {
    url.addParam("@class", "CExClassFieldSubgroup");
    url.addParam("ex_class_field_subgroup_id", subgroup_id);

    if (subgroup) {
      url.addParam("parent_class", "CExClassFieldSubgroup");
      url.addParam("parent_id", subgroup.get("subgroup_id"));
    }
    else {
      url.addParam("parent_class", "CExClassFieldGroup");
      url.addParam("parent_id", group.get("group_id"));
    }
  }

  // Host field
  var host_field_id = box.get("host_field_id");
  if (host_field_id) {
    url.addParam("@class", "CExClassHostField");
    url.addParam("ex_class_host_field_id", host_field_id);
    url.addParam("subgroup_id", subgroup ? subgroup.get("subgroup_id") : "");
  }

  // Action button
  var action_button_id = box.get("action_button_id");
  if (action_button_id) {
    url.addParam("@class", "CExClassFieldActionButton");
    url.addParam("ex_class_field_action_button_id", action_button_id);
    url.addParam("subgroup_id", subgroup ? subgroup.get("subgroup_id") : "");
  }

  // Widget
  var ex_class_widget_id = box.get("ex_class_widget_id");
  if (ex_class_widget_id) {
    url.addParam("@class", "CExClassWidget");
    url.addParam("ex_class_widget_id", ex_class_widget_id);
    url.addParam("subgroup_id", subgroup ? subgroup.get("subgroup_id") : "");
  }
  
  // New action button or widget
  var type = box.get("type");
  if (type === "action-button" || type === "form-widget") {
    var subgroup_id = subgroup ? subgroup.get("subgroup_id") : "";
    
    switch (type) {
      case "action-button":
        url.addParam("@class", "CExClassFieldActionButton");
        url.addParam("action", box.get("action"));
        url.addParam("icon", box.get("icon"));
        break;
        
      case "form-widget":
        url.addParam("@class", "CExClassWidget");
        url.addParam("name", box.get("name"));
        break;
    }

    url.addParam("callback", "ExClass.newDraggableCallback");
    url.addParam("ex_group_id", group.get("group_id"));
    url.addParam("subgroup_id", subgroup ? subgroup.get("subgroup_id") : "");
    
    var parent = null;
    
    drag._subgroups.each(function(sg){
      if (subgroup_id) {
        if (subgroup_id == sg.get("subgroup_id")) {
          parent = sg;
        }
      }
      else if (!sg.get("subgroup_id")) {
        parent = sg;
      }
    });
    
    var offset = parent.cumulativeOffset();
    var dimensions = box.getDimensions();
    dims.coord_left = event.pageX - offset.left - drag.offset[0];
    dims.coord_top = event.pageY - offset.top - drag.offset[1];
    dims.coord_width = dimensions.width;
    dims.coord_height = dimensions.height;
    
    ExClass._currentDraggable = {box: box, parent: parent, dims: dims};
  }

  // Correction de l'intégration des widgets dans les sous-groupes de formulaires
  // Dans le cas de l'intégration à un sous-groupe il faut réinitialiser la position du widget
  if (box.get('movable')) {
    box.setStyle({position: 'relative', top: 0, left:0});
    var elem = box.get('reset_id');
    var counter = box.get('reset_pos');
    var container = $(elem);

    if ((container.children.length - 1) < counter) {
      container.children[container.children.length - 1].insert({after: box});
    }
    else {
      container.children[counter].insert({before: box});
    }
  }

  url.mergeParams(dims);
  url.requestUpdate(SystemMessage.id, {method: "post"});
};

ExClass.newDraggableCallback = function(id, obj) {
  var data = ExClass._currentDraggable;
  var box = data.box.down('.clonable').down().clone(true);
  box.removeClassName("draggable");
  
  var handles = {
    nw: "top-left", 
    n: "top-center", 
    ne: "top-right", 
    e: "middle-right", 
    se: "bottom-right", 
    s: "bottom-center", 
    sw: "bottom-left", 
    w: "middle-left" 
  };
  
  var mapClass = {
    "CExClassFieldActionButton": "action-button",
    "CExClassWidget": "form-widget"
  };
  
  var mapKey = {
    "CExClassFieldActionButton": "action_button_id",
    "CExClassWidget": "ex_class_widget_id"
  };
  
  var className = mapClass[obj._class] || ""; 
  
  var attr = {
    className: "resizable no-size " + className,
    tabIndex: 0
  };
  
  attr["data-"+mapKey[obj._class]] = id;
  
  var wrapper = DOM.div(attr);
  
  $H(handles).each(function(pair){
    wrapper.insert(DOM.div({"data-way": pair.key, className: "handle "+pair.value}));
  });

  wrapper.setStyle({
    top: data.dims.coord_top+"px",
    left: data.dims.coord_left+"px",
    width: data.dims.coord_width+"px",
    height: data.dims.coord_height+"px"
  });
  
  wrapper.insert(box);

  data.parent.insert(wrapper);
  
  // Attach events to the handles
  wrapper.observeOnce("focus", ExClass.focusEvent.curry(wrapper, ExClass.startDrag, ExClass.endDrag));
};

ExClass.focusEvent = function(element, startDrag, endDrag){
  var drag = new Draggable(element, {
    handle: element.down(".overlayed"),
    starteffect: function(){},
    endeffect: function(){},
    onStart: startDrag,
    onEnd: function(draggable){
      var element = draggable.element;
      endDrag(draggable);
      element.style.zIndex = "";

      if (draggable._subgroups) {
        draggable._subgroups.each(Droppables.remove.bind(Droppables));
      }
    }
  });

  var initDrag = function(event) {
    if (event._stoppedByChild) {
      return;
    }

    event._stoppedByChild = true;

    if(!Object.isUndefined(Draggable._dragging[this.element]) &&
      Draggable._dragging[this.element]) return;
    if(Event.isLeftClick(event)) {
      var pointer = [Event.pointerX(event), Event.pointerY(event)];
      var pos     = this.element.cumulativeOffset();
      this.offset = [0,1].map( function(i) { return (pointer[i] - pos[i]) });

      Draggables.activate(this);
      //Event.stop(event); // This was removed
    }
  };

  Event.stopObserving(drag.handle, "mousedown", drag.eventMouseDown);
  drag.eventMouseDown = initDrag.bindAsEventListener(drag);
  Event.observe(drag.handle, "mousedown", drag.eventMouseDown);

  // we handle the handles...
  var handles = element.select(".handle");

  handles.each(function(handle){
    handle.observe("mousedown", function(e){
      if (window._draggingElement) {
        return;
      }

      var element = e.element();
      var box = element.up(".resizable");

      element.store("orig-x", e.clientX);
      element.store("orig-y", e.clientY);

      ["top", "left"].each(function(carac){
        box.store("orig-"+carac, parseInt(box.style[carac]));
      });

      box.store("orig-width",  parseInt(box.style.width)  || box.getWidth());
      box.store("orig-height", parseInt(box.style.height) || box.getHeight());
      box.store("orig-angle",  parseInt(box.get("angle")));

      window._draggingElement = element;
      Event.stop(e);
    });
  });

  document.observe("mousemove", function(e){
    if (!window._draggingElement) {
      return;
    }

    var dragging = window._draggingElement;
    var box     = dragging.up(".resizable");
    var offsetX = dragging.retrieve("orig-x") - e.clientX;
    var offsetY = dragging.retrieve("orig-y") - e.clientY;
    var origTop    = box.retrieve("orig-top");
    var origLeft   = box.retrieve("orig-left");
    var origWidth  = box.retrieve("orig-width");
    var origHeight = box.retrieve("orig-height");
    var origAngle  = box.retrieve("orig-angle") || 0;

    var way = dragging.get("way");

    switch(way) {
      case "rotate":
        var angle = (origAngle + offsetX / 2) % 360;
        if (angle < 0) {
          angle += 360;
        }

        var transform = "rotate(" + angle + "deg)";
        var style = {
          MozTransform:    transform,
          msTransform:     transform,
          WebKitTransform: transform,
          transform:       transform
        };
        box.setStyle(style);
        box.writeAttribute("data-angle", angle);
        break;

      case "w":
      case "nw":
        var width = (origWidth + offsetX);
        if (width > 0) {
          box.style.left  = (origLeft - offsetX) + "px";
          box.style.width = width + "px";
        }
      case "n":
        if (way !== "w") {
          var height = (origHeight + offsetY);
          if (height > 0) {
            box.style.top    = (origTop - offsetY) + "px";
            box.style.height = height + "px";
          }
        }
        return;

      case "sw":
        var width = (origWidth + offsetX);
        if (width > 0) {
          box.style.left  = (origLeft - offsetX) + "px";
          box.style.width = width + "px";
        }
      case "s":
        var height = (origHeight - offsetY);
        if (height > 0) {
          box.style.height = height + "px";
        }
        return;

      case "e":
      case "se":
      case "ne":
        var width = (origWidth - offsetX);
        if (width > 0) {
          box.style.width = width + "px";
        }

        if (way === "se") {
          var height = (origHeight - offsetY);
          if (height > 0) {
            box.style.height = height + "px";
          }
        }

        if (way === "ne") {
          var height = (origHeight + offsetY);
          if (height > 0) {
            box.style.top    = (origTop - offsetY) + "px";
            box.style.height = height + "px";
          }
        }
        return;
    }
  });

  document.observe("mouseup", function(e){
    if (!window._draggingElement) {
      return;
    }

    endDrag({element: window._draggingElement.up(".resizable")});

    window._draggingElement = null;
  });
};

ExClass.initPixelLayoutEditor = function(startDrag, endDrag, selector){
  selector = selector || ".resizable";
  var fieldInputs = $$(".pixel-positionning "+selector);
  
  fieldInputs.each(function(f){
    // Attach events to the handles
    f.observeOnce("focus", ExClass.focusEvent.curry(f, startDrag, endDrag));
  });
};

ExClass.focusResizable = function(event, element) {
  var resizable = element.up('.resizable');
  if (resizable) {
    resizable.focus();
    Event.stop(event);
  }
};
