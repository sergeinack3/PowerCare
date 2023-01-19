{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPplanningOp script=commande_mat ajax=true}}
<script>
checkFormPrint = function() {
  var form = document.PrintFilter;
    
  if(!(checkForm(form))){
    return false;
  }
  
  var url = new Url("dPbloc", "print_materiel");
  url.addFormData(form);
  url.addParam('blocs_ids[]', $V(form.blocs_ids), true);
  url.addParam('praticiens_ids[]', $V(form.praticiens_ids), true);
  url.popup(900, 750, 'Materiel');
};

refreshLists = function() {
  var form = getForm("PrintFilter");
  var url = new Url("dPbloc", "ajax_vw_materiel");
  url.addFormData(form);
  url.addParam('blocs_ids[]', $V(form.blocs_ids), true);
  url.addParam('praticiens_ids[]', $V(form.praticiens_ids), true);
  url.requestUpdate("list_materiel");
};

Main.add(function() {
  refreshLists();
});
</script>

<form name="PrintFilter" action="?m=dPbloc" method="post">
  <input type="hidden" name="type_commande" value="{{$type_commande}}"/>
  <table class="form">
    <tr>
      <th>{{mb_label object=$filter field="_date_min"}}</th>
      <td>{{mb_field object=$filter field="_date_min" form="PrintFilter" canNull="false" register=true onchange="refreshLists();"}} </td>
      <th>{{tr}}CBlocOperatoire{{/tr}}</th>
      <td>
        <select name="blocs_ids" size="5" multiple>
          {{foreach from=$listBlocs item=_bloc}}
            <option value="{{$_bloc->_id}}" {{if is_array($blocs_ids) && in_array($_bloc->_id, $blocs_ids)}}selected{{/if}}>{{$_bloc->nom}}</option>
          {{foreachelse}}
            <option value="" disabled="disabled">{{tr}}CBlocOperatoire.none{{/tr}}</option>
          {{/foreach}}
        </select>
      </td>

      <th>{{tr}}common-Practitioner{{/tr}}</th>
      <td>
        <select name="praticiens_ids" onchange="this.form.function_id.value = '';" size="5" multiple>
          <option value="" {{if !$praticiens_ids|@count}}selected{{/if}}>&mdash; {{tr}}common-Choice a practitioner{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_mediuser list=$praticiens}}
        </select>
      </td>
      <td class="button">
        <button type="button" onclick="checkFormPrint()" class="search">{{tr}}CBlocOperatoire-action-View history{{/tr}}</button>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$filter field="_date_max"}}</th>
      <td>{{mb_field object=$filter field="_date_max" form="PrintFilter" canNull="false" register=true onchange="refreshLists();"}}</td>
      <td colspan="2"></td>

      <th>{{tr}}CBlocOperatoire-Doctor s office{{/tr}}</th>
      <td>
        <select name="function_id" onchange="this.form.praticiens_ids.value = '';refreshLists();" style="width: 23em !important">
          <option value="">&mdash; {{tr}}common-Choice of a cabinet{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_function selected=$function_id list=$functions}}
        </select>
      </td>
      <td></td>
    </tr>
    <tr>
      <td colspan="7" class="button">
        <button type="button" class="search" onclick="refreshLists();" >{{tr}}Filter{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
<div id="list_materiel" class="me-align-auto me-padding-0"></div>
