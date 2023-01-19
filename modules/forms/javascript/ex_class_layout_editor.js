/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Object.extend(ExClass, {
  submitLayout: function(drag, drop) {
    var coord_x = drop.get("x"),
        coord_y = drop.get("y"),
        type    = drag.get("type"),
        form = getForm("form-layout-field");
    
    $(form).select('input.coord').each(function(coord){
      $V(coord.disable(), '');
    });
    
    $V(form.ex_class_field_id, drag.get("field_id"));
    $V(form["coord_"+type+"_x"].enable(), coord_x);
    $V(form["coord_"+type+"_y"].enable(), coord_y);
    
    form.onsubmit();
    
    // source parent
    var oldParent = drag.up();
    /*
    var cell = oldParent.up(".cell");
    ExClass.setRowSpan(cell, 1);
    ExClass.setColSpan(cell, 1);
      
    drag.down(".size input").each(function(input){
      input.value = 1;
    });*/
    
    if (drop.hasClassName("grid")) {
      drop.update(drag);
    }
    else {
      drop.insert(drag);
    }
    
    if (oldParent.hasClassName("grid")) {
      oldParent.update("&nbsp;");
    }
  },
  submitLayoutMessage: function(drag, drop) {
    var coord_x = drop.get("x"),
        coord_y = drop.get("y"),
        type    = drag.get("type").split("_")[1],
        form = getForm("form-layout-message");
    
    $(form).select('input.coord').each(function(coord){
      $V(coord.disable(), '');
    });
    
    $V(form.ex_class_message_id, drag.get("message_id"));
    $V(form["coord_"+type+"_x"].enable(), coord_x);
    $V(form["coord_"+type+"_y"].enable(), coord_y);
    
    form.onsubmit();
    
    // source parent
    var oldParent = drag.up();
    
    if (drop.hasClassName("grid")) {
      drop.update(drag);
    }
    else {
      drop.insert(drag);
    }
    
    if (oldParent.hasClassName("grid")) {
      oldParent.update("&nbsp;");
    }
  },
  submitLayoutHostField: function(drag, drop) {
    var coord_x = drop.get("x"),
        coord_y = drop.get("y"),
        type    = drag.get("type"),
        form = getForm("form-layout-hostfield");
        
    if (!drop.hasClassName("droppable")) return;
    
    $(form).select('input.coord').each(function(coord){
      $V(coord.disable(), '');
    });
    
    $V(form.ex_class_host_field_id, drag.get("field_id") || "");
    $V(form.elements.field, drag.get("field") || "");
    $V(form.elements.ex_group_id, drag.get("ex_group_id") || "");
    $V(form.elements.host_class, drag.get("host_class") || "");
    $V(form["coord_"+type+"_x"].enable(), coord_x);
    $V(form["coord_"+type+"_y"].enable(), coord_y);
    
    $V(form.elements.callback, "");
    $V(form.del, 0);
      
    // source parent
    var oldParent = drag.up();
      
    // dest = LIST
    if (drop.hasClassName("out-of-grid")) {
      var del = drag.get("field_id");
      
      $V(form.elements.callback, "");
    
      if (del) {
        drag.remove();
        oldParent.update("&nbsp;");
        $V(form.del, 1);
      }
      else {
        return;
      }
    }
    
    // dest = GRID
    else {
      var fromGrid = true;
      
      if (!drag.up(".grid")) {
        fromGrid = false;
        drag = drag.clone(true);
        ExClass.initDraggableHostField(drag);
        drag.setStyle({
          position: "static",
          opacity: 1
        });
      }
      
      drop.update(drag);
      
      if (fromGrid) {
        oldParent.update("&nbsp;");
      }
      
      var id = drag.identify();
      $V(form.elements.callback, "ExClass.setHostFieldId.curry("+id+")");
    }
    
    onSubmitFormAjax(form);
  },
  setHostFieldId: function(element_id, object_id) {
    var drag = $(element_id);
    if (drag) {
      drag.setAttribute("data-field_id", object_id);
    }
  },
  initDraggable: function(d, containerSelector){
    new Draggable(d, {
      revert: 'failure',
      scroll: window,
      ghosting: true,
      onStart: function(drag, event){
        drag.element.addClassName("dragging");
        $$(containerSelector).invoke("addClassName", "dropactive");

        ExClass.startDrag(drag, event);
      },
      onEnd: function(drag, event){
        drag.element.removeClassName("dragging");
        $$(containerSelector).invoke("removeClassName", "dropactive");

        ExClass.endDrag(drag, event);
      }
    });
  },
  initDraggableHostField: function(d){
    ExClass.initDraggable(d, ".tab-action-buttons");
  },
  initDraggableActionButton: function(d){
    ExClass.initDraggable(d, "#tab-action-buttons");
  },
  initDraggableWidget: function(d){
    ExClass.initDraggable(d, "#tab-widgets");
  },
  
  getSpanningCells: function(cell, span) {
    span = span || {};
    
    var rowspan = parseInt(span.rowspan || parseInt(cell.getAttribute("rowspan")) || 1);
    var colspan = parseInt(span.colspan || parseInt(cell.getAttribute("colspan")) || 1);
    
    var cellPosition = cell.previousSiblings().length;
    var row = cell.up("tr");
    
    // get rows
    var rowSiblings = row.nextSiblings();
    rowSiblings.unshift(row);
    rowSiblings = rowSiblings.slice(0, rowspan);
    
    // get cells in each row
    var grid = [];
    rowSiblings.each(function(r){
      var cells = r.childElements().slice(cellPosition, cellPosition+colspan);
      grid.push(cells);
    });
    
    return grid;
  },
  
  getMaxSpanning: function(grid) {
    var maxEmpty = function(line, j) {
      for (var i = 0; i < line.length; i++) {
        if (i + j == 0) continue;
        
        if (line[i].down(".draggable")) {
          return i;
        }
      }
      return i;
    };
    
    var linesMax = [];
    for(var y = 0; y < grid.length; y++) {
      var line = grid[y];
      var maxX = maxEmpty(line, y);
      if (maxX == 0) break;
      linesMax.push(maxX);
    }
    
    var max = {
      x: linesMax.max(),
      y: linesMax.length
    };
    
    var newGrid = [];
    grid.each(function(row, y){
      if (y < max.y) {
        newGrid.push(row.slice(0, max.x));
      }
    });
    
    return {grid: newGrid, width: max.x, height: max.y};
  },
  
  putCellSpans: function(grid) {
    grid.select(".size input").each(ExClass.setSpan);
  },
  
  changeSpan: function(button) {
    var value = button.value;
    var input = button.up(".arrows").down("input");
    input.value = Math.max(1, parseInt(input.value) + parseInt(value));
    input.fire("ui:change");
  },
  
  setRowSpan: function(cell, rowspan){
    var currentGrid = ExClass.getSpanningCells(cell);
    currentGrid.invoke("invoke", "show");
    
    var newGrid = ExClass.getMaxSpanning(ExClass.getSpanningCells(cell, {rowspan: rowspan}));
    newGrid.grid.invoke("invoke", "hide");
    cell.show();
    
    var max = newGrid.height;
    cell.writeAttribute("rowspan", max);
    return max;
  },
  
  setColSpan: function(cell, colspan){
    var currentGrid = ExClass.getSpanningCells(cell);
    currentGrid.invoke("invoke", "show");
    
    var newGrid = ExClass.getMaxSpanning(ExClass.getSpanningCells(cell, {colspan: colspan}));
    newGrid.grid.invoke("invoke", "hide");
    cell.show();
    
    var max = newGrid.width;
    cell.writeAttribute("colspan", max);
    return max;
  },
  
  setSpan: function(input) {
    var type = input.className;
    var cell = input.up(".cell");
    
    if (type == "rowspan") {
      $V(input, ExClass.setRowSpan(cell, input.value));
    }
    else {
      $V(input, ExClass.setColSpan(cell, input.value));
    }
  },
  
  initLayoutEditor: function(){
    /*$$(".draggable .size").each(function(size) {
      size.observe("mousedown", Event.stop);
    });*/
    
    $$(".size input").each(function(select) {
      select.observe("ui:change", function(event){
        var elt = Event.element(event);
        ExClass.setSpan(elt);
      });
    });
    
    // :not pseudo element is slow in IE
    var draggables = $$(".draggable").notMatch(".hostfield");
    
    draggables.each(function(d){
      d.observe("mousedown", function(event){
        if (!ExClass.pickMode) {
          return;
        }
        
        Event.stop(event);
        
        var element = Event.element(event);
        if (!element.hasClassName("draggable")) {
          element = element.up(".draggable");
        }
        
        var has = element.hasClassName("picked");
        $$(".draggable.picked").invoke("removeClassName", "picked");
        
        if (!has){
          element.toggleClassName("picked");
        }
      });
      
      new Draggable(d, {
        revert: 'failure', 
        scroll: window, 
        ghosting: true,
        onStart: function(draggable){
          var element = draggable.element;
          
          if (!ExClass.pickMode && element.up(".out-of-grid")) {
            element.up(".group-layout").down(".drop-grid").scrollTo();
          }
          
          $$(".out-of-grid").invoke("addClassName", "dropactive");
        },
        onEnd: function(){
          $$(".out-of-grid").invoke("removeClassName", "dropactive");
        }
      });
    });
    
    $$(".draggable.hostfield").each(ExClass.initDraggableHostField);
    
    /*
    $$(".draggable.hr").each(function(d){
      new Draggable(d, {
        //revert: true, 
        scroll: window, 
        ghosting: true
      });
    });*/
    
    function dropCallback(drag, drop) {
      drag.style.position = ''; // a null value doesn't work on IE
      
      // prevent multiple fields in the same cell
      if (drop.hasClassName('grid') && drop.select('.draggable').length) return;
        
      // grid to trash for ExFields
      if (drop.hasClassName("out-of-grid") && !drag.hasClassName('hostfield')) {
        if (drag.hasClassName('field')) {
          drop = drop.down(".field-list");
        }
        
        if (drag.hasClassName('label')) {
          drop = drop.down(".label-list");
        }
        
        if (drag.hasClassName('message_title')) {
          drop = drop.down(".message_title-list");
        }
        
        if (drag.hasClassName('message_text')) {
          drop = drop.down(".message_text-list");
        }
      }
      
      if (drag.hasClassName('hostfield')) {
        ExClass.submitLayoutHostField(drag, drop);
      }
      else if (drag.hasClassName('field') || drag.hasClassName('label')) {
        ExClass.submitLayout(drag, drop);
      }
      else {
        ExClass.submitLayoutMessage(drag, drop);
      }
    }
    
    $$(".droppable").each(function(drop){
      drop.observe("mousedown", function(event){
        if (!ExClass.pickMode || event.element().hasClassName("dont-lock")) {
          return;
        }
        
        Event.stop(event);
        
        if (drop.childElements().length) {
          return;
        }
        
        var drag = $$(".picked")[0];
        
        if (!drag) {
          return;
        }
        
        dropCallback(drag, drop);
        drop.insert(drag.removeClassName("picked"));
      });
      
      Droppables.add(drop, {
        hoverclass: 'dropover',
        onDrop: dropCallback
      });
    });
    
    ExClass.layourEditorReady = true;
  }
});
