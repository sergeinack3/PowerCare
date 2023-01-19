{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var tabs = Control.Tabs.create("return-forms-list");

    if (window.return_form_id) {
      tabs.setActiveTab("return-form-" + window.return_form_id);
    }
  });
</script>

<ul class="control_tabs" id="return-forms-list">
  {{foreach from=$list_return_forms item=_form}}
    <li onmousedown="$V(getForm('filter-references').societe_id, '{{$_form->supplier_id}}')">
      <a href="#return-form-{{$_form->_id}}" {{if $_form->_count.product_outputs == 0}}class="empty"{{/if}}>
        {{$_form->_ref_supplier}} <br />
        <small class="count">({{$_form->_count.product_outputs}})</small>
      </a>
    </li>
  {{/foreach}}
</ul>

{{foreach from=$list_return_forms item=_form}}
  <div id="return-form-{{$_form->_id}}">
    {{mb_include module=stock template=inc_return_form return_form=$_form}}
  </div>
{{/foreach}}
