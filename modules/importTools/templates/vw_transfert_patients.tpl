{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=importTools script=importTools}}
{{mb_script module=system script=object_selector ajax=true}}

<script>
  Main.add(function () {
    ObjectSelector.init = function () {
      this.sForm = "export_group";
      this.sView = "object_view";
      this.sId = "object_id";
      this.sClass = "object_class";
      this.onlyclass = "true";
      this.pop();
    };

    ImportTools.updateFunctionList('{{$current_group->_id}}');
  });
</script>

<br/><br/>

<table class="main form">
  <tr>
    <td class="narrow">
      <form method="get" id="export_group">
        <label for="input_group">{{tr}}importTools-label-export-group{{/tr}}</label>
        <input type="hidden" name="object_id" value="{{$current_group->_id}}"
               onchange="ImportTools.updateFunctionList(this.value)"/>
        <input type="hidden" name="object_class" value="CGroups"/>
        <input id="input_group_id" type="text" name="object_view" readonly="readonly" value="{{$current_group}}"/>
        <button type="button" onclick="ObjectSelector.init()" class="search notext">{{tr}}Search{{/tr}}</button>
        <button type="button" class="notext cancel" onclick="ImportTools.emptyFieldGroup()">{{tr}}Empty{{/tr}}
      </form>
    </td>
  </tr>

  <tr>
    <td class="narrow">
      <div id="listFunctions"></div>
    </td>
    <td>
      <button class="fa fa-upload"
              onclick="ImportTools.exportObject()">{{tr}}importTools-export-group{{/tr}}</button>
    </td>
  </tr>

  <tr>
    <td class="narrow"></td>
    <td>
      <button class="fa fa-download"
              onclick="ImportTools.executeRedirectImportExport('vw_import_group', 'etablissement')">{{tr}}importTools-import-group{{/tr}}</button>
    </td>
  </tr>
  <tr>
    <td class="narrow"></td>
    <td>
      <button class="fa fa-upload" onclick="ImportTools.executeRedirectImportExport('vwExportPatients', 'patients')">{{tr}}importTools-export-patients{{/tr}}</button>
    </td>
  </tr>
  <tr>
    <td class="narrow"></td>
    <td>
      <button class="fa fa-download"
              onclick="ImportTools.executeRedirectImportExport('vw_import_patients', 'patients')">{{tr}}importTools-import-patients{{/tr}}</button>
    </td>
  </tr>
</table>



