{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=protocole_items            value=$protocole_geste_perop->_ref_protocole_geste_items}}
{{assign var=protocole_items_categories value=$protocole_geste_perop->_ref_protocole_geste_item_by_categories}}

<table class="main tbl">
  <tr>
    <th class="title" colspan="8">{{tr}}CProtocoleGestePerop-List of elements associated with the protocol{{/tr}}
      ({{$protocole_items|@count}})
    </th>
  </tr>
  <tr>
    <th class="narrow">{{mb_title class=CProtocoleGestePeropItem field=checked}}</th>
    <th class="narrow">{{mb_title class=CProtocoleGestePeropItem field=rank}}</th>
    <th>{{mb_title class=CGestePerop field=libelle}}</th>
    <th class="narrow">{{tr}}CGestePeropPrecision|pl{{/tr}}</th>
    <th class="narrow">{{tr}}CPrecisionValeur|pl{{/tr}}</th>
    <th class="narrow">{{tr}}common-Action{{/tr}}</th>
  </tr>

  {{foreach from=$protocole_items_categories key=category item=_item}}
    <tr>
      <th class="section me-category" colspan="8">
        <strong>{{$category}}</strong>
      </th>
    </tr>
    {{foreach from=$_item item=_context}}
      {{assign var=item_checked value=$_context.checked}}
      {{assign var=item         value=$_context.item}}
      {{assign var=isCategory   value=$_context.isCategory}}

      {{foreach from=$_context.gestes item=_geste}}
        {{mb_ternary var=object test=$isCategory value=$_geste other=$item}}
        {{assign var=precisions value=$_geste->_ref_precisions}}

        <tr class="{{if !$_geste->actif}}hatching{{/if}}">
          <td class="button">
            <form name="editCheckedItem{{$object->_id}}" action="?" target="#" method="post"
                  onsubmit="return onSubmitFormAjax(this, GestePerop.refreshListProtocoleItems.curry('{{$protocole_geste_perop->_id}}'));">
              {{mb_key   object=$object}}
              {{mb_class object=$object}}

              {{if $_geste->actif}}
                {{mb_field object=$object field=checked typeEnum=checkbox class="me-small" onchange="this.form.onsubmit()"}}
              {{/if}}
            </form>
          </td>
          <td>
            <form name="editRankItem{{$object->_id}}" action="?" target="#" method="post"
                  onsubmit="return onSubmitFormAjax(this, GestePerop.refreshListProtocoleItems.curry('{{$protocole_geste_perop->_id}}'));">
              {{mb_key   object=$object}}
              {{mb_class object=$object}}

              {{if $_geste->actif}}
                {{mb_field object=$object field=rank increment=true form="editRankItem`$object->_id`" class="me-small" onchange="this.form.onsubmit()"}}
              {{/if}}
            </form>
          </td>
          <td class="text">
            <span id="geste_element_{{$_geste->_id}}" onmouseover="ObjectTooltip.createEx(this, '{{$_geste->_guid}}')">
              {{$_geste->_view}}
            </span>
          </td>
          <td>
            {{if $precisions|@count}}
              <form name="gestePrecision{{$item->_id}}" action="?" target="#" method="post"
                    onsubmit="return onSubmitFormAjax(this);">
                {{mb_key   object=$item}}
                {{mb_class object=$item}}

                {{mb_field object=$item field=precision_valeur_id hidden=true}}

                <select class="select_{{$_geste->_id}}" name="geste_perop_precision_id"
                        onchange="GestePerop.protocoleItemPrecisionSettings(this, '{{$item->_id}}', '{{$_geste->_id}}', '{{$_geste->libelle|smarty:nodefaults|JSAttribute}}'); this.form.onsubmit();" style="width: 250px;">
                  <option value="">&mdash; {{tr}}None|f{{/tr}}</option>
                  {{foreach from=$precisions item=_precision}}
                    <option id="precision_element_{{$_precision->_id}}"
                            value="{{$_precision->_id}}"
                            {{if $_precision->_id == $item->geste_perop_precision_id}}selected{{/if}}>
                      {{$_precision->_view}}
                    </option>
                  {{/foreach}}
                </select>
              </form>

              {{if $item->geste_perop_precision_id}}
                <script>
                  Main.add(function () {
                    GestePerop.showListValeurs(getForm('gestePrecision{{$item->_id}}').geste_perop_precision_id, '{{$_geste->_id}}', '{{$_geste->libelle|smarty:nodefaults|JSAttribute}}', '{{$item->_id}}', 1);
                  });
                </script>
              {{/if}}
            {{else}}
              <div class="empty" style="width: 250px;">
                  &mdash; {{tr}}CGestePeropPrecision.none{{/tr}}
              </div>
            {{/if}}
          </td>
          <td id="select_list_valeurs_{{$_geste->_id}}">
            <div class="empty" style="width: 250px;">
              &mdash; {{tr}}CPrecisionValeur.none{{/tr}}
            </div>
          </td>
          <td class="button">
            <form name="deleteProtocoleItem{{$item->_id}}" action="?" target="#" method="post"
                  onsubmit="return onSubmitFormAjax(this, Control.Modal.refresh);">
              {{mb_key   object=$item}}
              {{mb_class object=$item}}
              <input type="hidden" name="del" value="1"/>
              <button class="me-small" type="button" onclick="confirmDeletion(this.form, {
                typeName: 'L\'élément',
                objName: '{{$item->_view|smarty:nodefaults|JSAttribute}}',
                ajax: true},
                {onComplete: Control.Modal.refresh})"
                      title="{{tr}}CProtocoleGestePeropItem-action-Delete the perop gesture protocol element{{/tr}}">
                <i class="fas fa-trash-alt" style="font-size: 1.2em;"></i>
              </button>
            </form>
          </td>
        </tr>
      {{/foreach}}
    {{/foreach}}
  {{/foreach}}

  {{if !$protocole_items|@count}}
    <tr>
      <td class="empty" colspan="8">
        {{tr}}CProtocoleGestePeropItem.none{{/tr}}
      </td>
    </tr>
  {{/if}}
</table>
