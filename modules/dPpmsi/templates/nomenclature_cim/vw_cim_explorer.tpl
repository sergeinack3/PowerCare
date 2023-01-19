{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=pmsi script=DiagPMSI ajax=true}}
<script>
  changePage = function (page) {
    var oForm = getForm("filter-cim");
    $V(oForm.current, page);
    oForm.onsubmit();
  };

  Main.add(function () {
    var oForm = getForm("filter-cim");
    oForm.onsubmit();
  });
</script>

<fieldset class="me-align-auto">
  <legend class="fas fa-filter"> {{tr}}filters{{/tr}}</legend>
  <form action="?" name="filter-cim" method="get" onsubmit=" return onSubmitFormAjax(this, null, 'filter_results');">
    <input type="hidden" name="m" value="dPpmsi"/>
    <input type="hidden" name="current" value="0"/>
    <input type="hidden" name="modal" value="{{$modal}}"/>
    <input type="hidden" name="a" value="ajax_search_nomenclature_cim10"/>

    <table class="form me-no-box-shadow">
      <tr>
        {{me_form_field nb_cells=2 label="Code"}}
          <input name="code" type="text" value="" onchange="$V(this.form.elements.current, 0)"/>
        {{/me_form_field}}

        {{me_form_field nb_cells=2 label="CCodeCIM10-search-category" title_label="CCodeCIM10-search-category-desc"}}
          <select name="category_id" id="filter-cim_chapter" style="width: 200px;">
            <option value="">
              &mdash; {{tr}}CCodeCIM10-search-category-placeholder{{/tr}}
            </option>
              {{foreach from=$categories_cim item=_category}}
                <option value="{{$_category->id}}" data-code="{{$_category->code}}">
                    {{$_category->libelle|smarty:nodefaults}} {{$_category->code}}
                </option>
              {{/foreach}}
          </select>
        {{/me_form_field}}
      </tr>
      <tr>
        {{me_form_field nb_cells=3 label="Keywords"}}
          <input name="words" type="text" value="{{$words}}" onchange="$V(this.form.elements.current, 0)"/>
        {{/me_form_field}}
      </tr>
      <tr>
        <td colspan="4" class="button">
          <button type="submit" class="search me-primary">
            {{tr}}Search{{/tr}}
          </button>
        </td>
      </tr>
    </table>
  </form>
</fieldset>

<div id="filter_results" class="me-padding-0 me-align-auto"></div>
