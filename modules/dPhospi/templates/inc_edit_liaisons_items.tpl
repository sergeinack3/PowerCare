{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form = getForm("addItemPrestation{{$lit_id}}");
    var input = form.elements._item_prestation_view;
    new Url("dPhospi", "ajax_lit_liaison_item_autocomplete")
      .addParam("keywords", input)
      .addParam("where[object_class]", "CPrestationJournaliere")
      .addParam("lit_id", "{{$lit_id}}")
      .autoComplete(input, null, {
        minChars:           3,
        method:             "get",
        select:             "view",
        dropdown:           true,
        afterUpdateElement: function (field, selected) {
          var id = selected.get("id");
          if (!id) {
            return;
          }
          $V(field.form["item_prestation_id"], id.split("-")[1]);
          field.form.onsubmit();
        }
      });
  });
</script>


<form name="addItemPrestation{{$lit_id}}" method="post"
      onsubmit="return onSubmitFormAjax(this, Infrastructure.editLitLiaisonItem.curry('{{$lit_id}}'));">
  {{mb_class class=CLitLiaisonItem}}
  <input type="text" name="_item_prestation_view" class="autocomplete" placeholder="&mdash; Choisir une prestation" />
  <input type="hidden" name="lit_id" value="{{$lit_id}}" />
  <input type="hidden" name="item_prestation_id" value="" />
</form>

<ul id="itemTags" class="tags" style="float: none">
  {{foreach from=$lits_liaisons_items item=_lit_liaison_item}}
    {{assign var=_item_prestation value=$_lit_liaison_item->_ref_item_prestation}}
    <li class="tag me-margin-top-4 me-margin-bottom-4">
      <form name="delLitLiaisonItem-{{$_item_prestation->_id}}" method="post">
        {{mb_class object=$_lit_liaison_item}}
        {{mb_key object=$_lit_liaison_item}}
        <input type="hidden" name="del" value="1" />

        {{mb_include module=system template=inc_object_history object=$_lit_liaison_item}}

        <button type="button" class="delete" style="display: inline-block !important;"
                onclick="confirmDeletion(this.form, {
                  typeName:'l\'item de prestation',
                  objName:'{{$_item_prestation->_shortview|smarty:nodefaults|JSAttribute}}', ajax: 1},
                  { onComplete: Infrastructure.editLitLiaisonItem.curry('{{$lit_id}}') })">
        </button>
      </form>
      <span {{if !$_item_prestation->actif || !$_item_prestation->_ref_object->actif }}class="hatching opacity-60"{{/if}}>{{$_item_prestation->_shortview}}<br /></span>
    </li>
    {{foreachelse}}
    <span class="empty">{{tr}}CItemPrestation.none{{/tr}}</span>
  {{/foreach}}
</ul>
