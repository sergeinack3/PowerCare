{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>  
  <th>{{mb_label object=$actor field="user_id"}}</th>
  <td>
    {{mb_field object=$actor field="user_id" hidden=true}}
    
    {{if $actor->user_id}}
      <input type="text" size="30" readonly="readonly" ondblclick="ObjectSelector.init()" name="_user_view" value="{{$actor->_ref_user->_view|stripslashes}}" />
    {{else}}
      <input type="text" size="30" readonly="readonly" ondblclick="ObjectSelector.init()" name="_user_view" value="" />
    {{/if}}
      <button type="button" onclick="ObjectSelector.init()" class="search">{{tr}}Search{{/tr}}</button>   
      
      <input type="hidden" name="_selector_class" value="CUser" />          
      <script type="text/javascript">
        ObjectSelector.init = function(){
          this.sForm     = "edit{{$actor->_guid}}";
          this.sId       = "user_id";
          this.sView     = "_user_view";
          this.sClass    = "_selector_class";
          this.onlyclass = "true";
         
          this.pop();
        } 
       </script>
  </td>
</tr>

<tr>
  <th>{{mb_label object=$actor field="response"}}</th>
  <td>{{mb_field object=$actor field="response"}}</td>
</tr>

{{mb_include module=$actor->_ref_module->mod_name template="`$actor->_class`_inc" ignore_errors=true}}