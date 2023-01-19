{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $context->_id}}
  {{mb_return}}
{{/if}}

{{mb_default var=form value=""}}

{{if !$form}}
  {{mb_return}}
{{/if}}

{{mb_default var=new_dhe value=0}}

<script>
  if (Object.isUndefined(window.docitems_guid)) {
    window.docitems_guid = [];
  }

  window.docitems_guid["{{$context->_class}}"] = [];

  editDocItems = function(button, context_class, form_name) {
    new Url("files", "ajax_docitems")
      .addParam("context_class", context_class)
      .addParam("docitems_guid", window.docitems_guid[context_class].join(","))
      .requestModal("40%", "40%", {onClose: function() {
        var form = getForm(form_name);

        $V(form._docitems_guid, window.docitems_guid[context_class].join(","));

        button.down("span").update("(" + window.docitems_guid[context_class].length + ")");
      }});
  };

  updateDocItemsInput = function(object_class, input, docitems_guids) {
    var docitems_guid = $V(input);

    if (docitems_guid) {
      docitems_guid += ",";
    }

    docitems_guid += docitems_guids;

    $V(input, docitems_guid);

    var button_docitems = $(object_class + "_docitems");

    if (!button_docitems) {
      return;
    }

    var split_docitems = docitems_guid.length ? docitems_guid.split(",") : [];
    button_docitems.down("span").update("(" + split_docitems.length + ")");
  };

  synchronizeDocItems = function(value, object_class) {
    window.docitems_guid[object_class] = value.length ? value.split(",") : [];
  };

  Main.add(function() {
    {{if $context->_docitems_guid}}
      {{foreach from=","|explode:$context->_docitems_guid item=_docitem_guid}}
        window.docitems_guid["{{$context->_class}}"].push("{{$_docitem_guid}}");
      {{/foreach}}
    {{/if}}
  });
</script>

{{assign var=count value=0}}

{{if $context->_docitems_guid}}
  {{assign var=count value=","|explode:$context->_docitems_guid|@count}}
{{/if}}

<input type="hidden" name="_docitems_guid" onchange="synchronizeDocItems(this.value, '{{$context->_class}}');"
       value="{{$context->_docitems_guid}}" />

<button type="button" id="{{$context->_class}}_docitems" class="search me-tertiary"
        onclick="editDocItems(this, '{{$context->_class}}', '{{$form}}');">{{tr}}CMbObject-back-documents{{/tr}} <span>({{$count}})</span></button>
