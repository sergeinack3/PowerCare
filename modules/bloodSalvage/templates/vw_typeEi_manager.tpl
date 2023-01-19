{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  var oEvenementField = null;

  function viewItems(iCategorie) {
    var oForm = getForm("edit_type_ei");
    $('Items' + oForm._elemOpen.value).hide();
    $('Items' + iCategorie).show();
    $V(oForm._elemOpen, iCategorie);
  }

  function toggleCode(iCode, bForceTo) {
    var oForm = getForm("edit_type_ei");
    oEvenementField.toggle(iCode, bForceTo);
    oForm.evenements.fire("ui:change");
    var oElement = oForm["_ItemsSel_cat_" + oForm._elemOpen.value];
    oItemSelField = new TokenField(oElement);
    oItemSelField.toggle(iCode, bForceTo);

    refreshListChoix();
  }

  function refreshListChoix() {
    var oForm = getForm("edit_type_ei");
    var oCategorie = oForm._cat_evenement.options;
    var sListeChoix = "";
    for (i = 0; i < oCategorie.length; i++) {
      var oElement = oForm["_ItemsSel_cat_" + oCategorie[i].value];
      if (oElement.value) {
        oItemSelField = new TokenField(oElement);
        sListeChoix += "<strong>" + oCategorie[i].text + "</strong><ul>";
        var aItems = oItemSelField.getValues();
        iCode = 0;
        while (sCode = aItems[iCode++]) {
          sListeChoix += "<li>" + $('titleItem' + sCode).title + "</li>";
        }
        sListeChoix += "</ul>";
      }
    }
    $('listChoix').innerHTML = sListeChoix;
  }

  Main.add(function () {
    refreshListChoix();
    oEvenementField = new TokenField(getForm("edit_type_ei").evenements);
  });
</script>

<table class="main">
  <tr>
    <td colspan="2">
      <a class="button new me-margin-top-4" href="?m={{$m}}&tab=vw_typeEi_manager&type_ei_id=0">{{tr}}CTypeEi.create{{/tr}}</a>
    </td>
  </tr>
  <tr>
    <td class="halfPane">
      <table class="tbl">
        <tr>
          <th class="title" colspan="4">{{tr}}CTypeEi{{/tr}}</th>
        </tr>
        <tr>
          <th>{{mb_label object=$type_ei field="name"}}</th>
          <th>{{mb_label object=$type_ei field="concerne"}}</th>
          <th>{{mb_label object=$type_ei field="desc"}}</th>
        </tr>
        {{foreach from=$type_ei_list key=id item=type}}
          <tr>
            <td>
              <a href="?m={{$m}}&tab=vw_typeEi_manager&type_ei_id={{$type->_id}}" title="{{tr}}CCellSaver-modele-see_or_edit{{/tr}}">
                {{mb_value object=$type field=name}}
              </a>
            </td>
            <td>
              <a href="?m={{$m}}&tab=vw_typeEi_manager&type_ei_id={{$type->_id}}" title="{{tr}}CCellSaver-modele-see_or_edit{{/tr}}">
                {{mb_value object=$type field=concerne}}
              </a>
            </td>
            <td style="absolute">
              <a href="?m={{$m}}&tab=vw_typeEi_manager&type_ei_id={{$type->_id}}" title="{{tr}}CCellSaver-modele-see_or_edit{{/tr}}">
                {{mb_value object=$type field=desc}}
              </a>
            </td>
          </tr>
          {{foreachelse}}
          <tr>
            <td colspan="3"><i>{{tr}}CTypeEi.none{{/tr}}</i></td>
          </tr>
        {{/foreach}}
      </table>
    </td>
    <td class="halfPane">
      <form name="edit_type_ei" method="post" onsubmit="return checkForm(this)">
        {{mb_class object=$type_ei}}
        {{mb_key object=$type_ei}}
        <input type="hidden" name="type_ei_id" value="{{$type_ei->_id}}" />
        <input type="hidden" name="del" value="0" />
        <table class="form">
          {{mb_include module=system template=inc_form_table_header object=$type_ei}}
          <tr>
            <th>{{mb_label object=$type_ei field="name"}}</th>
            <td>{{mb_field object=$type_ei size=30 field="name"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$type_ei field="concerne"}}</th>
            <td>{{mb_field object=$type_ei field="concerne"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$type_ei field="type_signalement"}}</th>
            <td>{{mb_field object=$type_ei field="type_signalement"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$type_ei field="evenements"}}</th>
            <td>
              {{mb_field object=$type_ei field="evenements" hidden=1}}
              <input type="hidden" name="_elemOpen" value="{{$firstdiv}}" />
              <select name="_cat_evenement" onchange="viewItems(this.value);">
                {{foreach from=$listCategories item=curr_evenement}}
                  <option value="{{$curr_evenement->ei_categorie_id}}"
                          {{if $curr_evenement->ei_categorie_id == $firstdiv}}selected{{/if}}>
                    {{$curr_evenement->nom}}
                  </option>
                {{/foreach}}
              </select>
            </td>
          <tr>
            <th></th>
            <td colspan="2">
              {{foreach from=$listCategories item=curr_evenement}}
                <input type="hidden" name="_ItemsSel_cat_{{$curr_evenement->ei_categorie_id}}" value="{{$curr_evenement->checked}}" />
                <table class="tbl" id="Items{{$curr_evenement->ei_categorie_id}}"
                       {{if $curr_evenement->ei_categorie_id!=$firstdiv}}style="display:none;"{{/if}}>
                  {{counter start=0 skip=1 assign=curr_data}}
                  {{foreach name=itemEvenement from=$curr_evenement->_ref_items item=curr_item}}
                    {{if $curr_data is div by 3 || $curr_data == 0}}
                      <tr>
                    {{/if}}
                    <td class="text">
                      <input type="checkbox" name="{{$curr_item->_id}}" onclick="toggleCode(this.name, this.checked);"
                             {{if $curr_item->checked}}checked{{/if}} />
                      <label for="{{$curr_item->ei_item_id}}" id="titleItem{{$curr_item->_id}}"
                             title="{{$curr_item->nom}}">{{$curr_item->nom}}</label>
                    </td>
                    {{if (($curr_data+1) is div by 3 || $smarty.foreach.itemEvenement.last)}}
                      </tr>
                    {{/if}}
                    {{counter}}
                    {{foreachelse}}
                    <tr>
                      <td class="empty">
                        {{tr}}_CFicheEi-noitemscat{{/tr}}
                      </td>
                    </tr>
                  {{/foreach}}
                </table>
                {{foreachelse}}
                {{tr}}CEiItem.none{{/tr}}
              {{/foreach}}
            </td>
          </tr>
          <tr>
            <th></th>
            <td colspan="2" id="listChoix"></td>
          </tr>
          <tr>
            <th>{{mb_label object=$type_ei field="desc"}}</th>
            <td>{{mb_field object=$type_ei field="desc"}}</td>
          </tr>
          <tr>
            <td class="button" colspan="4">
              {{if $type_ei->_id}}
                <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
                <button type="button" class="trash"
                        onclick="confirmDeletion(this.form,{typeName:'',objName:'{{$type_ei->_view|smarty:nodefaults|JSAttribute}}'})">
                  {{tr}}Delete{{/tr}}
                </button>
              {{else}}
                <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
              {{/if}}
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>
