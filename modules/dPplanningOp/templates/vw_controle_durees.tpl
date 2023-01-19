{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  window.refreshPage = true;
  
  refreshListProtocoles = function (page) {
    var form = getForm("filterControle");
    new Url("planningOp", "ajax_list_protocoles_controle")
      .addFormData(form)
      .addNotNullParam("page", page)
      .requestUpdate("list_protocoles");
  };
  
  updateDuree = function (protocole_id, duree, page) {
    var form = getForm("updateDureeProtocole");
    $V(form.protocole_id, protocole_id);
    $V(form.temp_operation, duree);
    onSubmitFormAjax(form, window.refreshPage ? refreshListProtocoles.curry(page) : Prototype.emptyFunction);
  };
  
  updateDurees = function (page) {
    var protocoles = $("list_protocoles").select("button.update_duree");
    if (!protocoles.length) {
      return;
    }
    
    var last_protocole = null;
    if (protocoles.length > 1) {
      last_protocole = protocoles.pop();
      window.refreshPage = false;
    }
    
    protocoles.invoke("click");
    
    if (last_protocole) {
      window.refreshPage = true;
      last_protocole.click();
    }
  };
  
  Main.add(function () {
    {{if $chir_id || $function_id}}
    refreshListProtocoles();
    {{/if}}
  });
</script>
<form name="updateAllDurations" method="post">
  <input type="hidden" name="dosql" value="do_update_group_durations"/>
  <input type="hidden" name="ajax" value="1"/>
  <input type="hidden" name="m" value="{{$m}}"/>
  <table class="tbl">
    <tr>
      <td>
        <button id="updateAllDurationsButton" type="button" class="change me-float-right"
                onclick="ProtocoleDHE.updateAllDurations(this.form)"
                title="{{tr}}CProtocole-update_all_protocols_durations_title{{/tr}}">
          {{tr}}CProtocole-update_all_protocols_durations_libelle{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>
<form name="updateDureeProtocole" method="post">
  {{mb_class class=CProtocole}}
  <input type="hidden" name="protocole_id"/>
  <input type="hidden" name="temp_operation"/>
</form>

<form name="filterControle" method="get" onsubmit="return false">
  <table class="form">
    <tr>
      <th><label for="chir_id" title="{{tr}}CProtocole-filter-practitioner-protocols{{/tr}}">{{tr}}common-Practitioner{{/tr}}</label></th>
      <td>
        <select name="chir_id" style="width: 20em;"
                onchange="if (this.form.function_id || this.form.libelle) { $V(this.form.libelle, '', false); this.form.function_id.selectedIndex = 0; } refreshListProtocoles();">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          <option value="0">&mdash; {{tr}}common-Practitioner.all{{/tr}}</option>
          {{foreach from=$praticiens item=_prat}}
            <option class="mediuser"
                    style="border-color: #{{$_prat->_ref_function->color}}; {{if !$_prat->_count_protocoles}}color: #999;{{/if}}"
                    value="{{$_prat->user_id}}" {{if ($chir_id == $_prat->user_id) && !$function_id}}selected{{/if}}>
              {{$_prat}} ({{$_prat->_count_protocoles}})
            </option>
          {{/foreach}}
        </select>
      </td>
      <th><label for="function_id" title="{{tr}}CProtocole-filter-function-protocols{{/tr}}">{{tr}}Function{{/tr}}</label></th>
      <td>
        <select name="function_id" style="width: 20em;"
                onchange="if (this.form.chir_id || this.form.libelle ) { $V(this.form.libelle, '', false); this.form.chir_id.selectedIndex = 0; } refreshListProtocoles();">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$functions item=_function}}
            <option class="mediuser"
                    style="border-color: #{{$_function->color}}; {{if !$_function->_count_protocoles}}color: #999;{{/if}}"
                    value="{{$_function->_id}}" {{if $_function->_id == $function_id}}selected{{/if}}>
              {{$_function}} ({{$_function->_count_protocoles}})
            </option>
          {{/foreach}}
        </select>
      </td>
      <th>{{tr}}Search{{/tr}}</th>
      <td>
        <input name="libelle" type="text" placeholder="Libellé"
               onchange="if (this.form.chir_id || this.form.function_id) {
                 this.form.chir_id.selectedIndex = 0;
                 this.form.function_id.selectedIndex = 0;
               }
               refreshListProtocoles();"/>
      </td>
    </tr>
  </table>
</form>
<div id="list_protocoles"></div>