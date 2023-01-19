{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=board script=board}}

<script>
  Main.add(function () {
    const form = getForm("selectPraticien");
    Calendar.regField(form._date_min);
    Board.reloadDocuments({{$praticiens|@array_keys|@json_encode}},form);
    Board.selectPraticien(form.praticien_id_view,form);
    Board.selectFunction(form._date_min);

  });
  emptyField = function(field) {
      $V(field.form.elements['function_id'], '', false);
      $V(field.form.elements['_function_view'], '');
      Board.reloadTdbSecretaire(null, getForm("selectPraticien"));
  };
</script>
<div id="reload_tdb">
<form  name="selectPraticien" id = "selectPraticien" method="get">
  <input type="hidden" name = "praticiens" value ="{{$praticiens|@array_keys|@json_encode}}"/>
  <table class="main form" id="">
    <tr>
      <td>
        <input type="text" name="praticien_id_view" class="autocomplete" style="width:15em;"
               placeholder="&mdash; Choisir un praticien"
               value=""/>
      </td>
      <td>
      <input type="hidden" name="function_id" value="{{$function->_id}}">
      <input type="text" class="autocomplete" name="_function_view" placeholder="{{tr}}CFunctions-select{{/tr}}" value="{{if $function->_id}}{{$function}}{{/if}}">
      <button type="button" class="cancel notext" onclick="emptyField(this.form.elements['function_id']);">{{tr}}Empty{{/tr}}</button>
      </td>
      <th>{{tr}}date.From_long{{/tr}}</th>
      <td>
        <input type="hidden" name="_date_min" class="date notNull" value="{{$date_min}}"
               onchange=""/>
      </td>
      <td>
        <button type="button" class="search" onclick="Board.reloadDocuments({{$praticiens|@array_keys|@json_encode}},this.form)">
            {{tr}}Search{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>
  <div id="container_praticiens">
      {{if $praticiens}}
          {{foreach from =$praticiens key=_id item=_prat}}
            <button class='remove rtl' value='{{$_id}}' id="praticien{{$_id}}" onclick="
              Board.removePraticien(this.id, {{$praticiens|@array_keys|@json_encode}},getForm(getForm('selectPraticien')))">
                {{$_prat->_view}}</button>
          {{/foreach}}
      {{/if}}
  </div>



<table class="main" id="refresh_list_documents"></table>
</div>
