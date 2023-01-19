{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="system" script="object_selector"}}
{{mb_script module="dPcompteRendu" script="modele_selector"}}
{{mb_script module="dPcompteRendu" script="document"}}
{{mb_script module="dPfiles" script="files"}}

{{if $object->_id}}
<script>
  Main.add(
    function() {
      reloadListFile('load');
    }
  );
</script>
{{/if}}

<table class="main">
  <tr>
    <td>
      <form name="FrmClass" action="?m={{$m}}" method="get" onsubmit="reloadListFile('load'); return false;">
      <input type="hidden" name="m" value="{{$m}}" />
      <input type="hidden" name="{{$actionType}}" value="{{$action}}" />
      <input type="hidden" name="dialog" value="{{$dialog}}" />
      <input type="hidden" name="file_id" value="{{$file->file_id}}" />
      
      <table class="form">
        <tr>
          <td>
            <label for="selClass" title="Type de l'objet courant">Type</label>
            <input name="selClass" type="text" readonly="readonly" ondblclick="ObjectSelector.init()" value="{{$selClass}}" />
            <label for="selKey" title="Identifiant de l'objet courant">Identifiant</label>
            <input name="selKey" type="text" readonly="readonly" value="{{$selKey}}" onchange="this.form.submit()" />
          </td>
          <td>
            <label title="Nom de l'objet sélectionné">Nom</label>
            <input name="selView" type="text" size="50" readonly="readonly" ondblclick="ObjectSelector.init()" value="{{$selView}}"
              {{if $object->_id}}
              onmouseover="ObjectTooltip.createEx(this, '{{$object->_guid}}')"
              {{/if}}
            />
            <button type="button" onclick="ObjectSelector.init()" class="search">{{tr}}Search{{/tr}}</button>

            <input type="hidden" name="keywords" value="{{$keywords}}" />
            <script type="text/javascript">
              ObjectSelector.init = function(){
                this.sForm     = "FrmClass";
                this.sId       = "selKey";
                this.sView     = "selView";
                this.sClass    = "selClass";
                this.onlyclass = "false"; 
                this.pop();
              }
            </script>
          </td>

          {{if $selKey}}
          <td>
            <select name="typeVue" onchange="if (this.form.onsubmit()) { this.form.submit() };">
              <option value="0" {{if $typeVue == 0}}selected="selected"{{/if}}>Miniatures et aperçus</option>
              <option value="1" {{if $typeVue == 1}}selected="selected"{{/if}}>Miniatures seules</option>
            </select>
          </td>
          {{/if}}
        </tr>
      </table>
      </form>
    </td>
  </tr>
</table>

<div id="listView"></div>