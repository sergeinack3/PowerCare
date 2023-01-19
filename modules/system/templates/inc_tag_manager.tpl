{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  editTag = function(id, oclass) {
    var url = new Url("system", "ajax_edit_tag");
    if (id) {
      url.addParam("object_guid", "CTag-"+id);
    }
    else {
      if (oclass) {
        url.addParam("object_class", oclass);
      }
    }
    url.requestModal("500");
    url.modalObject.observe('afterClose', function() {
      refreshTagList();
    });
  };

  doMerge = function(oForm) {
    var url = new Url("system", "object_merger");
    url.addParam("objects_class", "CTag");
    if ($V(oForm["objects_id[]"])) {
      url.addParam("objects_id", $V(oForm["objects_id[]"]).join("-"));
    }
    url.popup(800, 600, "merge_patients");
  };

  removeParent = function() {
    var oform = getForm('filterTag');
    $V(oform.parent_id, '', true);
    refreshTagList();
  };

  refreshTagList = function(page_number, parent_id) {
    var oform = getForm('filterTag');
    $V(oform.page, page_number? page_number : 0);
    if (parent_id) {
      $V(oform.parent_id, parent_id, true);
    }
    oform.onsubmit();
  };

  purgeTag = function(tag_id, name) {
    var form = getForm('delete_tag');
    $V(form.elements.tag_id, tag_id ? tag_id : '');
    var message = tag_id ? 'Voulez vous purger l\'étiquette "'+name+'" non utilisée ?' : 'Voulez vous purger les étiquettes non utilisées ?' ;
    if (confirm(message)) {
      form.onsubmit();
    }
  };

  Main.add(function() {
    refreshTagList(0);
  });
</script>

<form name="delete_tag" method="post" onsubmit="onSubmitFormAjax(this, Control.Modal.refresh)">
  <input type="hidden" name="m" value="system" />
  <input type="hidden" name="dosql" value="do_purge_unused_tag" />
  <input type="hidden" name="object_class" value="{{$object_class}}" />
  <input type="hidden" name="tag_id" value=""/>
</form>

<button class="cleanup" style="float:right;" onclick="purgeTag();">{{tr}}Purge{{/tr}}</button>
<button class="new" onclick="editTag(null, '{{$object_class}}')" style="float:right">{{tr}}CTag.add{{/tr}} ({{tr}}{{$object_class}}{{/tr}})</button>
<form name="filterTag" method="get" onsubmit="return onSubmitFormAjax(this, null, 'result_tags')" style="margin:0 auto;">
  <input type="hidden" name="m" value="system"/>
  <input type="hidden" name="a" value="ajax_search_tag"/>
  <input type="hidden" name="object_class" value="{{$object_class}}"/>
  <input type="hidden" name="parent_id" value=""/>
  <input type="text" name="name" value="" placeholder="{{tr}}Search{{/tr}}" onkeyup="$V(this.form.page, 0)"/>
  <!--<label>Utilisés au moins 1 fois<input type="checkbox" name="no_item" onchange="$V(this.form.page, 0)"/></label>-->
  <label><input type="checkbox" name="is_child" onchange="$V(this.form.page, 0)"/>Ayant un parent</label>
  <input type="hidden" name="page" value="0"/>
  <button class="search notext">{{tr}}Search{{/tr}}</button>
</form>
<hr/>

{{if $tag->_can->edit}}
  <form name="fusion-tag" method="get">
    <input type="hidden" name="" value=""/>
{{/if}}
  <div id="result_tags">

  </div>

{{if $tag->_can->edit}}
  </form>
{{/if}}