{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=with_med value=false}}

<script>
  // Selection ou deselection de tous les elements d'une catégorie
  selectCategory = function(oCheckboxCat) {
    var checked = oCheckboxCat.checked;
    var guid = oCheckboxCat.getAttribute('data-guid');
    var checkboxes = $('categories').select('input.' + guid);
    var count_cat   = guid == "aerosol" || guid == "oxygene" || guid == "perfusion" || guid == "preparation"
                      || guid == "inj" || guid == "med" ? 1 : checkboxes.length;

    checkboxes.invoke("writeAttribute", "checked", checked);

    var counter = $("countSelected_" + guid);
    if (counter) {
      counter.update(checked ? count_cat : 0);
      selectTr(counter);
    }

    updateChapter(oCheckboxCat.up("tbody"));
  };

  toggleCheckbox = function(elt) {
    elt.toggleClassName("cancel");
    elt.toggleClassName("tick");
    var checked = elt.hasClassName("cancel");
    var categories = $('categories');
    categories.select('input[type=checkbox]:not([name=premedication],[name=highlight])').invoke("writeAttribute", "checked", checked);
    categories.select('tr').invoke(checked ? "addClassName" : "removeClassName", "selected");
    if (checked) {
      categories.select('.counter').each(function(counter) {
        var split = counter.id.split("_");
        counter.update($$(".category_"+split[1]).length);
      });
    }
    else {
      categories.select('.counter').invoke("update", "0");
    }
  };

  // Affichage des elements au sein des catégories
  toggleElements = function(category_guid) {
    $('categories').select('.category_'+category_guid).invoke('toggle');
  };

  // Mise a jour du compteur lors de la selection d'un element
  updateCountCategory = function(checkbox, category_guid) {
    var counter = $('countSelected_'+category_guid);
    var count = parseInt(counter.innerHTML);
    count = checkbox.checked ? count+1 : count-1;
    counter.update(count);
    selectTr(counter);
    var all_checked = $$("."+category_guid).all(function(elt) { return elt.checked });
    var input_category = $("categories").select("input[data-guid="+category_guid+"]")[0];
    if (input_category) {
      input_category.checked = all_checked;
      updateChapter(input_category.up("tbody"));
    }
  };

  toggleChapter = function(tbody, checked) {
    tbody.select('input[type=checkbox]').each(function(input) {
      if ((checked && !input.checked) || (!checked && input.checked)) {
        input.click();
      }
    });
  };

  updateChapter = function(tbody) {
    var all_checkboxes = tbody.select("input[type=checkbox][name=elts]").length;
    var checkboxes_checked = tbody.select("input[type=checkbox][name=elts]:checked").length;

    var checkbox_chapitre = tbody.select("input[name=_elts_cat]")[0];

    if (!checkbox_chapitre) {
      return;
    }

    if (checkboxes_checked == 0) {
      checkbox_chapitre.checked = "";
      checkbox_chapitre.style.opacity = "1";
    }
    else if (all_checkboxes == checkboxes_checked) {
      checkbox_chapitre.checked = "checked";
      checkbox_chapitre.style.opacity = "1";
    }
    else {
      checkbox_chapitre.checked = "checked";
      checkbox_chapitre.style.opacity = "0.5";
    }
  };

  selectTr = function(counter) {
    var count = parseInt(counter.innerHTML);
    count ? counter.up("tr").addClassName("selected") : counter.up("tr").removeClassName("selected");
  };

  fillCategories = function() {
    var table_categories = $("categories");
    if (table_categories) {
      {{foreach from=$categories_id item=_category_id}}
        var elts = table_categories.select("input[name='elts'][value={{$_category_id}}]");
        if (elts.length > 0 && !elts[0].checked) {
          elts[0].click();
        }
      {{/foreach}}
    }
  };

  Main.add(function() {
    getForm("selectElts").select("tbody").each(function(tbody) {
      updateChapter(tbody);
    });
    {{if !$app->user_prefs.show_categorie_pancarte}}
      $('categories').hide();
    {{/if}}
  });
</script>

<form name="selectElts" method="get">
  <!-- Checkbox vide permettant d'eviter que le $V considere qu'il faut retourner true ou false s'il n'y a qu'une seule checkbox -->
  <input type="checkbox" name="elts" value="" style="display: none;"/>

  <table class="tbl me-no-align">
    <tr>
      <th class="title" colspan="2">
        <div class="pancarte-filters">
          <div>
            <button type="button" class="cancel notext" onclick="toggleCheckbox(this);">{{tr}}Reset{{/tr}}</button>
            Activités
          </div>
          <div>
            <small style="float: left">
              <input type="checkbox" name="premedication"/> {{mb_label class="CPrescriptionLineElement" field="premedication"}}
            </small><br/>
            <small style="float: left">
              <input type="checkbox" name="highlight" /> {{mb_label class="CPrescriptionLineElement" field="highlight"}}
            </small>
          </div>
        </div>
      </th>
    </tr>
    {{if $with_med && $cats_med|@count}}
      <tbody>
        <tr>
          <th>
            <input type="checkbox" name="_elts_cat" onclick="this.style.opacity = '1'; toggleChapter(this.up('tbody'), this.checked);" />
          </th>
          <th>Médicaments</th>
        </tr>
        {{foreach from=$cats_med key=_name_cat_med item=_name_cat_med}}
          <tr>
            <td colspan="2">
              <span style="float: right;"><strong>(<span id="countSelected_{{$_name_cat_med}}" class="counter">0</span>/1)</strong></span>
              <input type="checkbox" name="elts" value="{{$_name_cat_med}}"  data-guid="{{$_name_cat_med}}" onclick="selectCategory(this);" />
              <strong><a href="#1" style="display: inline;">{{tr}}CPrescription._chapitres.{{$_name_cat_med}}{{/tr}}</a></strong>
            </td>
          </tr>
        {{/foreach}}
      </tbody>
    {{/if}}
    {{foreach from=$categories key=_chapitre item=_cats_by_chap}}
    <tbody>
      <tr>
        <th class="narrow">
          <input type="checkbox" name="_elts_cat" onclick="this.style.opacity = '1'; toggleChapter(this.up('tbody'), this.checked);" />
        </th>
        <th>{{tr}}CCategoryPrescription.chapitre.{{$_chapitre}}{{/tr}}</th>
      </tr>
      {{foreach from=$_cats_by_chap item=_elements}}
        {{foreach from=$_elements item=_element name=elts}}
          {{if $smarty.foreach.elts.first}}
            {{assign var=category value=$_element->_ref_category_prescription}}
            <tr>
              <td colspan="2" class="td_cat">
                <span style="float: right;"><strong>(<span id="countSelected_{{$category->_guid}}" class="counter">0</span>/{{$_elements|@count}})</strong></span>
                <input type="checkbox" data-guid="{{$category->_guid}}" onclick="selectCategory(this);" value="{{$category->_id}}" />
                <strong onclick="toggleElements('{{$category->_guid}}');">
                  <a href="#{{$category->_guid}}" style="display: inline;">{{$category}}</a>
                </strong>
              </td>
            </tr>
          {{/if}}
          <tr class="category_{{$category->_guid}}" style="display: none;">
            <td style="text-indent: 2em;" colspan="2" class="text">
              <label>
                <input type="checkbox" name="elts" value="{{$_element->_id}}" class="{{$category->_guid}}" onclick="updateCountCategory(this, '{{$category->_guid}}');" />
                {{$_element}}
              </label>
            </td>
          </tr>
        {{/foreach}}
      {{/foreach}}
      {{foreachelse}}
        {{if !$with_med}}
        <tr>
          <td class="empty">Aucune activité</td>
        </tr>
        {{/if}}
    </tbody>
    {{/foreach}}
  </table>
</form>
