{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=salleOp           script=geste_perop        ajax=true}}
{{mb_script module=monitoringPatient script=surveillance_perop ajax=true}}

{{assign var=pref_show_all_gestes value=$app->user_prefs.show_all_gestes_perop}}

<script>
  Main.add(function () {
    GestePerop.initializeView();

    var form = getForm("bindingGestes");
    Calendar.regField(form.datetime, {
      limit:
        {
          {{if $limit_date_min}}
          start: '{{$limit_date_min}}'
          {{/if}}
        }
    });
  });
</script>

<table class="main">
  <tr>
    {{if $pref_show_all_gestes}}
      <td style="width: 18%;" class="me-valign-top">
        <fieldset style="padding-bottom: 23px;">
          <legend><i class="fas fa-user-shield"></i> {{tr}}CPermModule-permission{{/tr}}</legend>
          <label>
            <input type="checkbox" name="see_all_gestes"
                   onclick="SurveillancePerop.getGestePeropContextMenu('{{$operation_id}}', '{{$datetime}}', 1, $V(this) ? 1 : 0);"
                   {{if $see_all_gestes}}checked{{/if}} />
            <span
              title="{{tr}}CGestePerop-action-See all the gestures perop of the establishment-desc{{/tr}}">{{tr}}CGestePerop-action-See all the gestures perop of the establishment{{/tr}}</span>
          </label>
        </fieldset>
      </td>
    {{/if}}
    <td style="width: 38%;">
      <fieldset style="padding-bottom: 15px;">
        <legend><i class="fas fa-filter"></i> {{tr}}filters{{/tr}}</legend>
        <form name="filterGeste" method="post">
          <input type="hidden" name="see_all_gestes" value="{{$see_all_gestes}}">

          <table class="main">
            <tr>
              <th><label for="keywords" class="">{{tr}}Keywords{{/tr}}</label></th>
              <td>
                <input type="text" name="keywords" class="me-small" value=""/>

                <input type="radio" name="context" value="chapitre"/> {{tr}}CAnesthPeropChapitre-court{{/tr}}
                <input type="radio" name="context" value="categorie"/> {{tr}}CAnesthPeropCategorie-court{{/tr}}
                <input type="radio" name="context" value="geste" checked="checked"/> {{tr}}CAnesthPerop-geste_perop_id{{/tr}}
                <input type="radio" name="context" value="precision"/> {{tr}}CGestePeropPrecision{{/tr}}

                <button type="button" class="search notext me-primary" onclick="GestePerop.searchIntoMenu(this.form);">
                  {{tr}}Search{{/tr}}
                </button>
              </td>
            </tr>
          </table>
        </form>
      </fieldset>
    </td>
    <td style="max-width: 750px;">
      <form name="bindingGestes" method="post" action="#">
        <fieldset>
          <legend>
            <i class="fas fa-book-open"></i> {{tr}}CGestePerop-Summary of selected items{{/tr}} :
            <input type="hidden" name="datetime" class="dateTime me-small" value="{{$datetime}}"/>
          </legend>
          <input type="hidden" name="operation_id" value="{{$operation_id}}"/>

          <div id="flag_codes" style="display: inline-block;">
            <ul id="show_tags_gestes" class="tags">
              <li id="tag_geste_none" class="empty me-padding-5">{{tr}}CGestePerop-No selected item{{/tr}}</li>
            </ul>
          </div>
          <div style="margin: auto; width: 1%;">
            <button id="button_validate_geste" type="button" class="singleclick me-small me-primary"
                    onclick="GestePerop.saveBindingAllGestes(this.form, '{{$limit_date_min}}', '{{$type}}');" disabled>
              <i class="fas fa-edit"></i> {{tr}}Save{{/tr}}
            </button>
          </div>
        </fieldset>
      </form>
    </td>
  </tr>
</table>

<div id="list_elements_gestes">
  <table class="main">
    <tr>
      <td style="width: 20%;" class="me-valign-top">
        <fieldset>
          <legend>
            <i class="fas fa-sitemap"></i>
            {{tr}}CAnesthPeropChapitre|pl{{/tr}} (<span id="counter_chapitre">{{$chapters|@count}}</span>)
          </legend>
          <div id="list_chapitres">
            {{mb_include module=salleOp template=inc_vw_menu_geste_chapitres}}
          </div>
        </fieldset>
      </td>
      <td style="width: 20%;" class="me-valign-top">
        <fieldset>
          <legend>
            <i class="fas fa-layer-group"></i> {{tr}}CAnesthPerop-Category|pl{{/tr}} (<span id="counter_categorie">0</span>)
          </legend>
          <div id="list_categories">
            {{mb_include module=salleOp template=inc_vw_menu_geste_categories}}
          </div>
        </fieldset>
      </td>
      <td style="width: 20%;" class="me-valign-top">
        <fieldset>
          <legend>
            <i class="fas fa-map-signs"></i> {{tr}}CGestePerop{{/tr}} (<span id="counter_geste">0</span>)
          </legend>
          <div id="list_gestes_perop">
            {{mb_include module=salleOp template=inc_vw_menu_gestes_perop}}
          </div>
        </fieldset>
      </td>
      <td style="width: 20%;" class="me-valign-top">
        <fieldset>
          <legend>
            <i class="fas fa-map-pin"></i> {{tr}}CGestePeropPrecision|pl{{/tr}} (<span id="counter_precision">0</span>)
          </legend>
          <div id="list_precisions">
            {{mb_include module=salleOp template=inc_vw_menu_geste_precisions}}
          </div>
        </fieldset>
      </td>
      <td style="width: 20%;" class="me-valign-top">
        <fieldset>
          <legend>
            <i class="fas fa-pencil-ruler"></i> {{tr}}CPrecisionValeur|pl{{/tr}} (<span id="counter_valeur">0</span>)
          </legend>
          <div id="list_valeurs">
            {{mb_include module=salleOp template=inc_vw_menu_geste_precision_valeurs}}
          </div>
        </fieldset>
      </td>
    </tr>
  </table>
</div>
