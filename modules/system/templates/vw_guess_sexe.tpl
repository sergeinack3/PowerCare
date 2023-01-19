{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  toggleAssure = function() {
    var form = getForm("filterPaires");

    $V(form.assure, form.object_class.selectedIndex == 4 ? 1 : 0);
  };

  searchPaires = function() {
    toggleAssure();

    var form = getForm("filterPaires");
    new Url("patients", "ajax_guess_sexe")
      .addFormData(form)
      .requestUpdate("repair_area");
  };

  processRepairPaires = function() {
    toggleAssure();

    var form = getForm("filterPaires");
    onSubmitFormAjax(form, function() {
      if (form.auto.checked) {
        processRepairPaires();
      }
    }, "repair_area")
  };

  Main.add(function() {
    getForm("filterPaires").limit.addSpinner({min: 1, step: 100});
  });
</script>

<form name="filterPaires" method="post">
  <input type="hidden" name="m" value="patients" />
  <input type="hidden" name="dosql" value="do_repair_paires" />
  <input type="hidden" name="assure" />

  <table class="tbl">
    <tr>
      <td>
        <select name="object_class">
          <option value="">&mdash; Classe</option>
          {{foreach from=$classes item=_classe key=index}}
          <option value="{{$_classe}}">{{tr}}{{$_classe}}{{/tr}} {{if $index == 3}}({{tr}}CDestinataire.tag.assure{{/tr}}){{/if}}</option>
          {{/foreach}}
        </select>

        Limite : <input type="text" name="limit" value="100" size="4" />

        <button type="button" class="search" onclick="searchPaires();">
          {{tr}}Search{{/tr}}
        </button>
        <button type="button" class="tick" onclick="processRepairPaires();">
          Traiter
        </button>
        <label>
          <input type="checkbox" name="auto" /> Auto
        </label>
      </td>
    </tr>
  </table>
</form>

<div id="repair_area"></div>