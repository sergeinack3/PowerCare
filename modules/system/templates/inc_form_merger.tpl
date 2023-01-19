{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=object_selector}}
{{mb_script module=system script=class_indexer}}

<script type="text/javascript">

var objectsList = [];

ObjectSelector.init = function(key) {
  this.sForm     = "objects-selector";
  this.sView     = "objects_view["+key+"]";
  this.sId       = "objects_id["+key+"]";
  this.sClass    = "objects_class";
  this.onlyclass = "true";
  this.pop();
};

var emptyObjectField = '<div class="object readonly">';
emptyObjectField += '<input type="hidden" name="objects_id[%KEY]" value="%ID" />';
emptyObjectField += '<input type="text" size="40" name="objects_view[%KEY]" readonly="readonly" value="%VIEW" ondblclick="ObjectSelector.init(%KEY)" />';
emptyObjectField += '<button type="button" onclick="ObjectSelector.init(%KEY)" class="search notext">{{tr}}Search{{/tr}}</button>';
emptyObjectField += '</div>';
var key = 0;

function addObjectField(id, view) {
  var field = emptyObjectField.gsub('%KEY', key++).gsub('%ID', id || '').gsub('%VIEW', view || '');
  $("objects-list").insert(field);
}

Main.add(function() {
  {{foreach from=$objects item=object}}
    addObjectField('{{$object->_id}}', '{{$object->_view}}');
    objectsList.push({{$object->_id}});
  {{foreachelse}}
    addObjectField();
    addObjectField();
  {{/foreach}}

  {{if !$readonly_class}}
    var form = getForm("objects-selector");
    ClassIndexer.autocomplete(form.autocomplete_input, form.objects_class);
  {{/if}}
});

function emptyClass(form) {
  $V(form.autocomplete_input, '');
  $V(form.object_class, '');
}
</script>

<form name="objects-selector" method="get" action="">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="{{$actionType}}" value="{{$action}}" />
  <input type="hidden" name="readonly_class" value="{{$readonly_class}}" />
  <table class="main form">
    <tr>
      <th>Type d'objet</th>
      <td>
        {{if $readonly_class}}
          <input type="hidden" name="objects_class" value="{{$objects_class}}" />
          <strong>{{tr}}{{$objects_class}}{{/tr}}</strong>
        {{else}}
          <input type="hidden" name="objects_class" value=""/>
          <input type="text" name="autocomplete_input" size="40">
          <button type="button" class="cancel notext" onclick="emptyClass(this.form)"></button>
        {{/if}}
      </td>
      <th>Objets</th>
      <td id="objects-list"></td>
    </tr>

    <tr>
      <td></td>
      <td>
        <button type="submit" class="hslip">{{tr}}Comparer{{/tr}}</button>
      </td>
      <th></th>
      <td></td>
    </tr>
 </table>
</form>
