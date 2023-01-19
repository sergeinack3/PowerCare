{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create("product-conditionnement-tabs", false);

    var editForm = getForm("edit_product");
    if (!$V(editForm.unit_quantity) && !$V(editForm.unit_title)) {
      toggleFractionnedAdministration.defer(editForm, false);
    }
    else {
      $V(editForm._toggle_fractionned, true);
    }

    new BarcodeParser.inputWatcher(editForm.scc_code, {size: 10, field: "scc_prod"});
  });

  toggleFractionnedAdministration = function (form, use) {
    var quantity = $(form.unit_quantity);
    quantity.up("table").select(".arrows").invoke("setVisible", use);
    quantity.disabled = !use;
    quantity.readOnly = !use;
    if (!use) {
      $V(quantity, "");
    }

    var title = $(form.unit_title);
    title.up("div").select(".dropdown-trigger").invoke("setVisible", use);
    title.disabled = !use;
    title.readOnly = !use;
    if (!use) {
      $V(title, "");
    }
  };

  confirmCancel = function (element) {
    var form = element.form;
    var element = form.cancelled;

    // Cancel
    if ($V(element) != "1") {
      if (confirm("Voulez-vous vraiment archiver ce produit ?")) {
        $V(element, "1");
        form.submit();
        return;
      }
    }

    // Restore
    if ($V(element) == "1") {
      if (confirm("Voulez-vous vraiment rétablir ce produit ?")) {
        $V(element, "0");
        form.submit();
        return;
      }
    }
  };

  changePage = function (start) {
    $V(getForm("filter-products").start, start);
  };

  filterReferences = function (form) {
    var url = new Url("dPstock", "httpreq_vw_products_list");
    url.addFormData(form);
    url.requestUpdate("list-products");
    return false;
  };

  duplicateObject = function (form) {
    $V(form.elements._duplicate, 1);
    form.onsubmit();
  };
</script>

<form name="edit_product" action="" method="post"
      onsubmit="{{if $only_edit}}return onSubmitFormAjax(this, {onComplete: Control.Modal.close});{{else}}return checkForm(this);{{/if}}">
  <input type="hidden" name="m" value="dPstock" />
  <input type="hidden" name="dosql" value="do_product_aed" />
  <input type="hidden" name="product_id" value="{{$product->_id}}" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="_duplicate" value="0" />
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$product}}

    {{if $product->cancelled == 1}}
    <tr>
      <th class="category cancelled" colspan="10">
        {{mb_label object=$product field=cancelled}}
      </th>
    </tr>
    {{/if}}

    <tr>
      <th class="narrow">{{mb_label object=$product field="name"}}</th>
      <td>{{mb_field object=$product field="name" size=40}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$product field="category_id"}}</th>
      <td><select name="category_id" class="{{$product->_props.category_id}}">
        <option value="">&mdash; {{tr}}CProductCategory.select{{/tr}}</option>
        {{foreach from=$list_categories item=curr_category}}
        <option
          value="{{$curr_category->_id}}" {{if $product->category_id == $curr_category->_id || $list_categories|@count==1}}
          selected="selected" {{/if}} >
          {{$curr_category->_view}}
        </option>
        {{/foreach}}
      </select>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$product field="societe_id"}}</th>
      <td>{{mb_field object=$product field="societe_id" form="edit_product" autocomplete="true,1,50,false,true" style="width: 15em;"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$product field="code"}}</th>
      <td>{{mb_field object=$product field="code"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$product field="scc_code"}}</th>
      <td>
        {{mb_field object=$product field="scc_code"}}
        <span style="display: none; color: red;" class="barcode-message">Ce n'est pas un code valide</span>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$product field="description"}}</th>
      <td>{{mb_field object=$product field="description"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$product field="classe_comptable"}}</th>
      <td>{{mb_field object=$product field="classe_comptable" form="edit_product"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$product field="cladimed"}}</th>
      <td>{{mb_field object=$product field="cladimed" form="edit_product"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$product field="auto_dispensed"}}</th>
      <td>{{mb_field object=$product field="auto_dispensed"}}</td>
    </tr>
    {{if $product->_id}}
    <tr>
      <th>Conso. <strong>3 dern. mois</strong></th>
      <td>{{mb_value object=$product field="_consumption"}}</td>
    </tr>
    <tr>
      <th>{{tr}}CProduct-back-selections{{/tr}}</th>
      <td>
        {{foreach from=$product->_back.selections item=_selection name=selection}}
        <!--<button class="remove notext"></button>-->
        {{$_selection->_ref_selection}}{{$smarty.foreach.selection.last|ternary:"":","}}
        {{foreachelse}}
        <div class="empty">{{tr}}CProductSelection.none{{/tr}}</div>
        {{/foreach}}
      </td>
    </tr>
    {{/if}}

    {{if "dmi"|module_active && "dPstock CProduct use_renewable"|gconf}}
    <tr>
      <th>{{mb_label object=$product field="renewable"}}</th>
      <td>{{mb_field object=$product field="renewable"}}</td>
    </tr>
    {{/if}}

    <tr>
      <td colspan="2" class="me-padding-0">
        <ul id="product-conditionnement-tabs" class="control_tabs me-padding-0 me-no-border-radius-top me-no-border-left me-no-border-right">
          <li><a href="#conditionnement">{{tr}}CProduct-packaging{{/tr}}</a></li>
          <li><a href="#composition"
                 {{if !$product->unit_title && !$product->unit_quantity}}class="empty"{{/if}}>{{tr}}Composition{{/tr}}</a></li>
        </ul>
      </td>
    </tr>

    <tbody id="conditionnement" style="display: none;" class="me-no-border">
    <tr>
      <td colspan="2" class="text">
        <div class="small-info">
          Les informations de conditonnement sont maintenant à titre informatif,
          elles ne déterminent plus les quantités dans les commandes.
        </div>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$product field="quantity"}}</th>
      <td>
        {{if $product->code_up_disp}}
        {{mb_value object=$product field="quantity"}}
        {{else}}
        {{mb_field object=$product field="quantity" form="edit_product" increment=true size=4}}
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$product field="item_title"}}</th>
      <td>
        {{if $product->code_up_disp}}
        {{mb_value object=$product field="item_title"}}
        {{else}}
        {{mb_field object=$product field="item_title" form="edit_product"}}
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$product field="packaging"}}</th>
      <td>{{mb_field object=$product field="packaging" form="edit_product"}}</td>
    </tr>
    </tbody>

    <tbody id="composition" style="display: none;">
    <tr>
      <th></th>
      <td>
        <label>
          <input type="checkbox" name="_toggle_fractionned" onclick="toggleFractionnedAdministration(this.form, this.checked)" />
          Permettre l'administration fractionnée
        </label>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$product field="unit_quantity"}}</th>
      <td>
        {{if $product->code_up_disp}}
        {{mb_value object=$product field="unit_quantity"}}
        {{else}}
        {{mb_field object=$product field="unit_quantity" form="edit_product" increment=true size=4}}
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$product field="unit_title"}}</th>
      <td>
        {{if $product->code_up_disp}}
        {{mb_value object=$product field="unit_title"}}
        {{else}}
        {{mb_field object=$product field="unit_title" form="edit_product"}}
        {{/if}}
      </td>
    </tr>
    </tbody>

    <tr>
      <td class="button" colspan="2">
        <hr />

        {{if $product->_id}}
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
        {{mb_field object=$product field=cancelled hidden=1}}
        <button class="{{$product->cancelled|ternary:"change":"cancel"}}" type="button" onclick="confirmCancel(this);">
          {{tr}}{{$product->cancelled|ternary:"Restore":"Archive"}}{{/tr}}
        </button>
        <button type="button" class="trash"
                onclick="confirmDeletion(this.form,{typeName:'',objName:'{{$product->_view|smarty:nodefaults|JSAttribute}}'}
                  {{if $only_edit}}
                  ,{onComplete: function () {Control.Modal.close();}}
                  {{/if}})">
          {{tr}}Delete{{/tr}}
        </button>
        {{if $can->admin}}
        <input type="hidden" name="_purge" value="0" />
        <script>
          confirmPurge = function (element) {
            var form = element.form;
            if (confirm("ATTENTION : Vous êtes sur le point de supprimer un produit, ainsi que tous les objets qui s'y rattachent")) {
              form._purge.value = 1;
              confirmDeletion(form, {
                  typeName: 'le produit',
                  objName:  '{{$product->_view|smarty:nodefaults|JSAttribute}}'
                }
                {{if $only_edit}}
                , {
                  onComplete: function () {
                    Control.Modal.close();
                  }
                }
                {{/if}}
              );
            }
          }
        </script>
        <button type="button" class="cancel" onclick="confirmPurge(this)">
          {{tr}}Purge{{/tr}}
        </button>
        {{/if}}
        <button type="button" class="duplicate" onclick="duplicateObject(this.form)">
          {{tr}}Duplicate{{/tr}}
        </button>
        {{else}}
        <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>