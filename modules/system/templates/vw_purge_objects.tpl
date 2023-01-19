{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    var form = getForm("filterPurge");

    // Autocomplete de la classe
    $(form.object_class).makeAutocomplete({width: "200px"});

    // Autocomplete des users
    var element = form._view;
    var url = new Url("system", "ajax_seek_autocomplete");
    url.addParam("object_class", "CMediusers");
    url.addParam("input_field", element.name);
    url.autoComplete(element, null, {
      minChars: 3,
      method: "get",
      select: "view",
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        var id = selected.getAttribute("id").split("-")[2];
        $V(form.user_id, id);
        if ($V(element) == "") {
          $V(element, selected.down('.view').innerHTML);
        }
      }
    });
  });

  submitPurge = function(form, next_time) {
    if (Object.isUndefined(next_time)) {
      if (!$V(form.object_class) || !$V(form.id_min) || !$V(form.id_max) || !$V(form._date_min) || !$V(form._date_max) || !$V(form.step)) {
        alert("Vérifiez les paramètres !");
        return false;
      }
      if (!$V(form.user_id) && !confirm("Vous n'avez pas choisi d'utilisateur, souhaitez-vous continuer ?")) {
        return false;
      }
      if (!confirm('Confirmez-vous cette action ?') || !confirm('Confirmez-vous vraiment cette action ?')) {
        return false;
      }
    }
    return onSubmitFormAjax(form, function() {
      if (!form.simulate.checked && form.repetition.checked) {
        submitPurge(form, 1);
      }
    }, 'area_purge');
  }
</script>

<form name="filterPurge" method="get" onsubmit="return submitPurge(this)">
  <input type="hidden" name="m" value="system" />
  <input type="hidden" name="a" value="ajax_purge_objects" />
  <table class="form">
    <tr>
      <th>
        {{mb_label object=$user_log field=user_id}}
      </th>
      <td>
        <input type="hidden" name="user_id" />
        <input type="text" name="_view" class="autocomplete" value="&mdash; Tous les utilisateurs" />
      </td>
      <th>{{mb_label object=$user_log field=object_class}}</th>
      <td>
        <select name="object_class" class="str">
          <option value="0">&mdash; Choisir une classe</option>
          {{foreach from=$classes item=curr_class}}
            <option value="{{$curr_class}}">
              {{tr}}{{$curr_class}}{{/tr}} - {{$curr_class}}
            </option>
          {{/foreach}}
        </select>
        <button type="button" class="cancel notext" onclick="emptyClass(this.form)"></button>
      </td>
      <th>{{mb_label object=$user_log field="_date_min"}}</th>
      <td>{{mb_field object=$user_log field="_date_min" form="filterPurge" register=true}}</td>
    </tr>
    <tr>
      <th>Id. min</th>
      <td>
        <input type="text" name="id_min" />
      </td>
      <th>
        Nombre d'objets à traiter
      </th>
      <td>
        <input type="text" name="step" value="10" />
      </td>
      <th>{{mb_label object=$user_log field="_date_max"}}</th>
      <td>{{mb_field object=$user_log field="_date_max" form="filterPurge" register=true}}</td>
    </tr>
    <tr>
      <th>Id. max</th>
      <td colspan="5">
        <input type="text" name="id_max" />
      </td>
    </tr>
    <tr>
      <td class="button" colspan="6">
        <label>
          <input type="checkbox" name="simulate" checked />
          Simulation
        </label>
        <label>
          <input type="checkbox" name="repetition" />
          Répétition
        </label>
        <button type="button" class="tick" onclick="this.form.onsubmit()">Purger</button>
      </td>
    </tr>
  </table>
  <div id="area_purge">

  </div>
</form>