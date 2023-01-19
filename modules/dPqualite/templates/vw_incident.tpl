{{*
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  var oEvenementField = null;

  function viewItems(iCategorie) {
    var oForm = document.forms.FrmEI;
    $('Items' + oForm._elemOpen.value).hide();
    $('Items' + iCategorie).show();
    oForm._elemOpen.value = iCategorie;
  }

  function toggleCode(iCode, bForceTo) {
    var oForm = document.forms.FrmEI;
    oEvenementField.toggle(iCode, bForceTo);

    var oElement = oForm["_ItemsSel_cat_" + oForm._elemOpen.value];
    oItemSelField = new TokenField(oElement);
    oItemSelField.toggle(iCode, bForceTo);
    oForm.evenements.fire("ui:change");
    refreshListChoix();
  }

  function refreshListChoix() {
    var oForm = document.FrmEI;
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

  function choixSuiteEven() {
    var oForm = document.FrmEI;
    if (oForm.suite_even.value == "autre") {
      $('suiteEvenAutre').show();
      oForm.suite_even_descr.className = "notNull {{$fiche->_props.suite_even_descr}}";
    } else {
      $('suiteEvenAutre').hide();
      oForm.suite_even_descr.className = "{{$fiche->_props.suite_even_descr}}";
    }
  }

  Main.add(function () {
    refreshListChoix();
    oEvenementField = new TokenField(getForm("FrmEI").evenements);
  });
</script>
<table class="main">
  <tr>
    <td>
      <form name="FrmEI" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
        <input type="hidden" name="dosql" value="do_ficheEi_aed" />
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="fiche_ei_id" value="{{$fiche->fiche_ei_id}}" />

        <table class="form">
          {{if $can->admin}}
            <tr>
              <th colspan="2">
                <label for="user_id" title="{{tr}}CFicheEi-user_id{{/tr}}">{{tr}}CFicheEi-user_id{{/tr}}</label>
              </th>
              <td colspan="2">
                <select name="user_id" class="{{$fiche->_props.user_id}}">
                  {{foreach from=$listFct item=currFct key=keyFct}}
                    <optgroup label="{{$currFct->_view}}">
                      {{foreach from=$currFct->_ref_users item=currUser}}
                        <option class="mediuser" style="border-color: #{{$currFct->color}};" value="{{$currUser->user_id}}"
                          {{if ($fiche->fiche_ei_id && $fiche->user_id==$currUser->user_id)
                          || (!$fiche->fiche_ei_id && $app->user_id==$currUser->user_id)}}
                            selected="selected"
                          {{/if}}
                        >
                          {{$currUser->_view}}
                        </option>
                      {{/foreach}}
                    </optgroup>
                  {{/foreach}}
                </select>
              </td>
            </tr>
          {{else}}
            <input type="hidden" name="user_id"
                   value="{{if $fiche->fiche_ei_id}}{{$fiche->user_id}}{{else}}{{$app->user_id}}{{/if}}" />
          {{/if}}

          <tr>
            {{if $fiche->fiche_ei_id}}
            <th colspan="4" class="title modify">
              <input type="hidden" name="_validation" value="1" />
              {{else}}
            <th colspan="4" class="title">
              {{/if}}
              {{tr}}_CFicheEi-titleFiche{{/tr}}
            </th>
          </tr>

          <tr>
            <th>{{mb_label object=$fiche field="type_incident"}}</th>
            <td>{{mb_field object=$fiche field="type_incident" emptyLabel="Choose"}}</td>
            <th>{{mb_label object=$fiche field="date_incident"}}</th>
            <td>{{mb_field object=$fiche field="date_incident" form=FrmEI register=true}}</td>
          </tr>

          <tr>
            <th>{{mb_label object=$fiche field="elem_concerne"}}</th>
            <td>{{mb_field object=$fiche field="elem_concerne" emptyLabel="Choose"}}</td>
            <th>{{mb_label object=$fiche field="lieu"}}</th>
            <td>{{mb_field object=$fiche field="lieu"}}</td>
          </tr>

          <tr>
            <th>{{mb_label object=$fiche field="elem_concerne_detail"}}</th>
            <td>{{mb_field object=$fiche field="elem_concerne_detail"}}</td>
            <th></th>
            <td></td>
          </tr>

          <tr>
            <th colspan="4" class="category">{{mb_label object=$fiche field="evenements"}}</th>
          </tr>

          <!-- Choix de la catégorie -->
          {{if !count($listCategories)}}
          <tr>
            <td colspan="10">
              <div class="small-warning">{{tr}}CEiCategorie.none{{/tr}}</div>
            </td>
            {{/if}}
          </tr>
          <tr>
            <td colspan="2" rowspan="2" class="halfPane" id="listChoix" style="vertical-align: top"></td>
            <th><label for="_cat_evenement"
                       title="{{tr}}CFicheEi-_cat_evenement-desc{{/tr}}">{{tr}}CFicheEi-_cat_evenement{{/tr}}</label>
            </th>
            <td>
              <input type="hidden" name="evenements" class="{{$fiche->_props.evenements}}" value="{{$fiche->evenements}}" />
              <input type="hidden" name="_elemOpen" value="{{$firstdiv}}" />

              <select name="_cat_evenement" onchange="viewItems(this.value);">
                {{foreach from=$listCategories item=curr_evenement}}
                  <option
                    value="{{$curr_evenement->ei_categorie_id}}"{{if $curr_evenement->ei_categorie_id==$firstdiv}} selected="selected"{{/if}}>
                    {{$curr_evenement->nom}}
                  </option>
                {{/foreach}}
              </select>
            </td>
          </tr>

          <tr>
            <td colspan="2">
              {{foreach from=$listCategories item=curr_evenement}}
                <input type="hidden" name="_ItemsSel_cat_{{$curr_evenement->ei_categorie_id}}" value="{{$curr_evenement->_checked}}" />
                <table class="tbl" id="Items{{$curr_evenement->ei_categorie_id}}"
                       {{if $curr_evenement->ei_categorie_id!=$firstdiv}}style="display:none;"{{/if}}>
                  {{counter start=0 skip=1 assign=curr_data}}
                  {{foreach name=itemEvenement from=$curr_evenement->_ref_items item=curr_item}}
                    {{if $curr_data is div by 3 || $curr_data==0}}
                      <tr>
                    {{/if}}
                    <td class="text">
                      <input type="checkbox" name="{{$curr_item->ei_item_id}}" onclick="toggleCode(this.name, this.checked);"
                             {{if $curr_item->_checked}}checked="checked"{{/if}}/>
                      <label for="{{$curr_item->ei_item_id}}" id="titleItem{{$curr_item->ei_item_id}}"
                             title="{{$curr_item->nom}}">{{$curr_item->nom}}</label>
                    </td>
                    {{if (($curr_data+1) is div by 3 || $smarty.foreach.itemEvenement.last)}}
                      </tr>
                    {{/if}}
                    {{counter}}
                    {{foreachelse}}
                    <tr>
                      <td>
                        {{tr}}_CFicheEi-noitemscat{{/tr}}
                      </td>
                    </tr>
                  {{/foreach}}
                </table>
                {{foreachelse}}
                <div class="empty">{{tr}}CEiItem.none{{/tr}}</div>
              {{/foreach}}
            </td>
          </tr>

          <tr>
            <th colspan="4" class="category">{{tr}}_CFicheEi-infoscompl{{/tr}}</th>
          </tr>

          <tr>
            <th>{{mb_label object=$fiche field="descr_faits"}}</th>
            <td>{{mb_field object=$fiche field="descr_faits"}}</td>
            <th>{{mb_label object=$fiche field="mesures"}}</th>
            <td>{{mb_field object=$fiche field="mesures"}}</td>
          </tr>

          <tr>
            <th>{{mb_label object=$fiche field="descr_consequences"}}</th>
            <td>{{mb_field object=$fiche field="descr_consequences"}}</td>
            <th>{{mb_label object=$fiche field="autre"}}</th>
            <td>{{mb_field object=$fiche field="autre"}}</td>
          </tr>

          <tr>
            <th colspan="2">{{mb_label object=$fiche field="suite_even"}}</th>
            <td colspan="2">{{mb_field object=$fiche field="suite_even" emptyLabel="Choose" onchange="choixSuiteEven();"}}
              <table id="suiteEvenAutre" style="width:100%;{{if $fiche->suite_even!="autre"}}display:none;{{/if}}">
                <tr>
                  <td>{{mb_label object=$fiche field="suite_even_descr"}}</td>
                </tr>
                <tr>
                  <td>{{mb_field object=$fiche field="suite_even_descr"}}</td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <th colspan="2">{{mb_label object=$fiche field="deja_survenu"}}</th>
            <td colspan="2">{{mb_field object=$fiche field="deja_survenu" emptyLabel="CFicheEi.deja_survenu."}}</td>
          </tr>

          <tr>
            <td colspan="4" class="button">
              <button class="submit" type="submit">
                {{if $fiche->fiche_ei_id}}
                  {{tr}}Save{{/tr}}
                {{else}}
                  {{tr}}button-CFicheEi-send{{/tr}}
                {{/if}}
              </button>
            </td>
          </tr>

        </table>
      </form>
    </td>
  </tr>
</table>