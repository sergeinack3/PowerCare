/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

var Tag = {
  filterObjectTimer: null,
  attach: function(object_guid, tag_id) {
    var parts = object_guid.split("-");
    
    var url = new Url().mergeParams({
      m:            "system",
      dosql:        "do_tag_item_aed",
      object_class: parts[0],
      object_id:    parts[1],
      tag_id:       tag_id
    });
          
    url.requestUpdate("systemMsg", {method: "post"});
  },
  create: function(object_class, name, parent_tag_id) {
    var url = new Url().mergeParams({
      m:            "system",
      dosql:        "do_tag_aed",
      object_class: object_class,
      name:         name
    });
    
    if (parent_tag_id) {
      url.addParam("parent_tag_id", parent_id);
    }
    
    url.requestUpdate("systemMsg", {method: "post"});
  },
  manage: function(object_class, onCloseCallback) {
    var url = new Url('system', 'ajax_tag_manager');
    url.addParam('object_class', object_class);
    url.requestModal("680", "680");
    if (onCloseCallback) {
      url.modalObject.observe("afterClose", function() {
        onCloseCallback();
      });
    }
  },
  setNodeVisibility: function(node) {
    node = $(node);
    
    var row = node.up('tbody');
    var table = row.up('table');
    var opened = row.hasClassName("opened");
    var tagId = row.get("tag_id");
    
    if (opened) {
      node.show();
      
      var childRows = table.select('tbody[data-parent_tag_id='+tagId+']');
      childRows.invoke("setVisible", opened);
      
      var children = table.select('tbody[data-parent_tag_id='+tagId+'] .tree-folding');
      children.each(function(child) {
        Tag.setNodeVisibility(child);
      });
    }
    else {
      table.select('tbody.tag-'+tagId).invoke("hide");
    }
  },
  loadElements: function(node) {
    node = $(node);
    
    var row = node.up('tbody');
    var table = row.up('table');
    var columns = table.get("columns");
    var objectClass = table.get("object_class");
    var form = getForm("filter-"+objectClass);
    
    if (columns) {
      columns = columns.split(",");
    }
    
    // Don't load if the row is closed
    if (!row.hasClassName("opened")) return;
    
    var nextRow = row.next('tbody');
    var insertAfter = ((nextRow && nextRow.get("tag_id")) || !nextRow);
    var insertion, target;
    var offset = parseInt(node.style.marginLeft)+18;
    var tagId = row.get("tag_id");
    
    if (insertAfter) {
      insertion = "after";
      target = row;
      //target = row.insert({after: "<tbody><tr><td colspan='10'></td></tr></tbody>"}).next('tbody');
    }
    else {
      //return; // don't load if already loaded
      target = nextRow;
    }
    
    var url = new Url('system', 'ajax_list_objects_by_tag');
    url.addParam("tag_id", tagId);
    url.addParam("insertion", insertion);
    url.addParam("group_id", $V(form.group_id));
    //url.addParam("object_class", objectClass);
    
    if (columns && columns.length) {
      url.addParam("col[]", columns, true);
    }
    
    var keywords = $V(form.object_name);
    if (keywords) {
      url.addParam("keywords", keywords);
      //table.hide();
    }
    else {
      //table.show();
    }
    
    url.requestUpdate(target, {
      insertion: insertion, 
      onComplete: function(){
        var tbody = row.next('tbody');
        if (!tbody.hasClassName("object-list")) return;
        
        tbody.className += " "+row.className;
        tbody.addClassName('tag-'+tagId);
        tbody.setAttribute("data-parent_tag_id", tagId);
        
        var firstCells = tbody.select("td:first-of-type");
        firstCells.invoke("setStyle", {paddingLeft: offset+"px"});
      }
    });
  },
  removeItem: function(tag_item_id, onComplete) {
    var url = new Url().mergeParams({
      "@class": "CTagItem",
      tag_item_id: tag_item_id,
      del: 1
    })
    .requestUpdate("systemMsg", {method: "post", onComplete: onComplete || function(){} });
  },
  bindTag: function(object_guid, tag_id, onComplete) {
    var parts = object_guid.split("-");
    var url = new Url().mergeParams({
      "@class": "CTagItem",
      tag_id: tag_id,
      object_class: parts[0],
      object_id: parts[1]
    })
    .requestUpdate("systemMsg", {method: "post", onComplete: onComplete || function(){} });
  },
  filter: function(input) {
    var treegrid = $("tag-tree").down('table.treegrid');
    var tags = treegrid.select("tbody[data-name]");
    var lists = treegrid.select("tbody.object-list");
    
    tags.invoke("show").invoke("addClassName", "opened");
    lists.invoke("hide");
    
    var term = $V(input);
    if (!term) return;
    
    tags.each(function(e) {
      var visible = e.get("name").like(term);
      e.setVisible(visible);
    });
  },
  launchFilterObject: function(input) {
    clearTimeout(Tag.filterObjectTimer);
    Tag.filterObjectTimer = Tag.filterObject.delay(0.4, input);
  },
  filterObject: function(input) {
    var treegrid = $("tag-tree").down('table.treegrid');
    var list = $("tag-tree").down('table.object-list');
    
    var columns = treegrid.get("columns");
    var object_class = treegrid.get("object_class");
    
    if (columns) {
      columns = columns.split(",");
    }
    
    var term = $V(input);
    if (!term) {
      treegrid.show();
      list.hide();
      return;
    }
    else {
      treegrid.hide();
      list.show();
    }
    
    var url = new Url('system', 'ajax_list_objects_by_tag');
    
    if (columns && columns.length) {
      url.addParam("col[]", columns, true);
    }
    
    url.addParam("tag_id", "all-"+object_class);
    url.addParam("keywords", term);
    url.addParam("object_class", object_class);
    url.addParam("group_id", $V(input.form.group_id));
    
    url.requestUpdate(list, {
      onComplete: function(){
        /*var tbody = row.next('tbody');
        if (!tbody.hasClassName("object-list")) return;
        
        tbody.className += " "+row.className;
        tbody.addClassName('tag-'+tagId);
        tbody.setAttribute("data-parent_tag_id", tagId);
        
        var firstCells = tbody.select("td:first-of-type");
        firstCells.invoke("setStyle", {paddingLeft: offset+"px"});*/
      }
    });
  },
  cancelFilter: function(input) {
    $V(input, "");
    Tag.filter(input);
    $(input).tryFocus();
  },
  cancelFilterObject: function(input) {
    $V(input, "");
    Tag.filterObject(input);
    $(input).tryFocus();
  }
};
