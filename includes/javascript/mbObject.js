/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

var MbObject = {
  edit: function(object, options) {
    var object_guid = object;
    
    if (Object.isElement(object)) {
      object_guid = object.get("object_guid");
    }
    
    options = Object.extend({
      target: "object-editor",
      customValues: null,
      onComplete: null
    }, options);
    
    var url = new Url("system", "ajax_edit_object");
    
    if (options.customValues) {
      url.addObjectParam("_v", options.customValues);
    }
    
    if (object_guid) {
      url.addParam("object_guid", object_guid);
    }
    url.requestUpdate(options.target, function(){
      var listContainer = $("tag-tree");
      var lines = listContainer.select("a[data-object_guid="+object_guid+"]").invoke("up", "tr");

      if (lines.length > 0) {
        listContainer.select("tr.selected").invoke("removeClassName", "selected");
        lines.invoke("addClassName", "selected");
      }
      
      options.onComplete && options.onComplete();
    });
  },
  editCallback: function(id, obj) {
    MbObject.list(obj._class);
    MbObject.edit(obj._guid);
  },
  list: function(object_class, columns, group_id) {
    var url = new Url("system", "ajax_object_tag_tree");
    url.addParam("object_class", object_class);
    url.addParam("group_id", group_id);
    url.addParam("col[]", columns);
    url.requestUpdate("tag-tree");
  },
  viewBackRefs: function(object_class, object_ids) {
    object_ids = object_ids instanceof Array ? object_ids : [object_ids];
    var url = new Url("system", "view_back_refs");
    url.addParam("object_class", object_class);
    url.addParam("object_ids[]", object_ids);
    url.popup(300 * object_ids.length + 200, 600, "View back refs");
  },
  exportObject: function(guid) {
    var url = new Url("system", "export_object");
    url.addParam("suppressHeaders", 1);
    url.addParam("object_guid", guid);
    url.pop(10, 10, "export", null, null, {}, Element.getTempIframe());
  },
  merge: function(object_class, ids) {
    if (ids.length == 0) return;
    
    var url = new Url("system", "object_merger");
    url.addParam("objects_class", object_class);
    url.addParam("objects_id", ids.join("-"));
    url.popup(800, 600, "merge objects "+object_class);
  },
  toggleColumn: function(toggler, column) {
    var visible = column.visible();
    toggler.toggleClassName("expand", visible);

    column.toggle();

    document.fire("ui:reflow");
  }
};
