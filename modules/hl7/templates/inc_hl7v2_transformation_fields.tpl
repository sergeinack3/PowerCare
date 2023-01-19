{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=hl7 script=hl7_transformation ajax=true}}

<script>
  Main.add(function(){
    var tree = new TreeView("hl7-transformation-tree");
    tree.collapseAll();

    var cont = $('hl7-transformation'),
      element = getForm("editHL7Transformation").elements.components,
      tokenField = new TokenField(element);

    cont.select('input[type=checkbox]').invoke('observe', 'click', function(event){
      var elt = Event.element(event);
      tokenField.toggle(elt.value, elt.checked);

      var values = tokenField.getValues();
      var container = $('ignored_fields_text').update("");
      values.each(function(v) {
        container.insert(DOM.span({className:'circled'}, v));
      })
    });
  });
</script>

<div class="small-info">
  Sélectionnez les champs à exclure.
  <div id="ignored_fields_text">
  </div>

  <div>
    <form name="editHL7Transformation" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
      <input type="hidden" name="components" value="" />

      <button class="submit" type="submit" onclick="EAITransformationRule.refreshTarget($V(this.form.elements.components),'{{$target}}')">{{tr}}Validate{{/tr}}</button>
    </form>
  </div>
</div>

<ul id="hl7-transformation-tree" class="hl7-tree">
  {{mb_include module=hl7 template=inc_hl7v2_transformation tree=$tree_segments target=$target}}
</ul>
