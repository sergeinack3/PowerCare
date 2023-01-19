{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=categorie_function ajax=1}}
<script>
  Main.add(function() {
    CategorieFunction.refreshList();
  });
</script>

<table class="main">
  <tr>
    <td>
      <form  name="category_filters" action="?m={{$m}}" method="get" onsubmit="CategorieFunction.refreshList(); return false;">
      <input type="hidden" name="m" value="{{$m}}" />
        <select name="selCabinet" onchange="$V(this.form.selPrat, '', false); this.form.onsubmit();">
          <option  value="">&mdash; {{tr}}common-Choice of the cabinet{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_function list=$listFunctions selected=$selCabinet}}
        </select>
        <select name="selPrat" onchange="$V(this.form.selCabinet, '', false); this.form.onsubmit();">
          <option  value="">&mdash; {{tr}}common-Choice a practitioner{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_mediuser list=$listPraticiens selected=$selPraticien}}
        </select>
      </form>
      {{if $selCabinet}}
        <button class="new" onclick="CategorieFunction.edit();">
          {{tr}}CConsultationCategorie-action-create{{/tr}}
        </button>
      {{/if}}
    </td>
  </tr>

  {{if $selCabinet}}
  <tr>
    <td id="listCategories">
    </td>
  </tr>
  {{/if}}
</table>