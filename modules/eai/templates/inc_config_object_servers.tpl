{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <td colspan="10">
      <div class="small-info">
        {{tr}}system-msg-Handlers are now configured from system module.{{/tr}}
      </div>
    </td>
  </tr>
</table>

{{mb_return}}

<table class="tbl">
  <tr>
    <th class="category" style="width: 50%">Serveurs d'objets</th>
    <th class="category">Gestionnnaire</th>
  </tr>
    
  {{foreach from=$object_servers key=_module item=_objects_server}}  
    {{if $_module|"module_active"}}
      {{foreach from=$_objects_server item=_object_server}}
      <tr>
        <td>{{tr}}config-object_server-{{$_object_server}}{{/tr}}</td>
        <td>
          <form name="editConfig_object_server-{{$_object_server}}" method="post" onsubmit="return onSubmitFormAjax(this);">
            {{mb_configure module=$m}}
            
            <label for="object_handlers_{{$_object_server}}_1">{{tr}}bool.1{{/tr}}</label>
            <input type="radio" name="object_handlers[{{$_object_server}}]" value="1" onchange="this.form.onsubmit();" 
              {{if array_key_exists($_object_server, $conf.object_handlers) &&
                $conf.object_handlers.$_object_server == "1"}}checked
              {{/if}} />
            <label for="object_handlers_{{$_object_server}}_0">{{tr}}bool.0{{/tr}}</label>
            <input type="radio" name="object_handlers[{{$_object_server}}]" value="0" onchange="this.form.onsubmit();" 
              {{if array_key_exists($_object_server, $conf.object_handlers) &&
                $conf.object_handlers.$_object_server == "0"}}checked
              {{/if}} />
          </form>
        </td>
      </tr>
      {{/foreach}}
    {{/if}}
  {{/foreach}}
</table>