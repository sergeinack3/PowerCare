{{*
* @package Mediboard\Sante400
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=sante400 script=Idex}}
{{mb_script module=system script=class_indexer}}

<script>
  Main.add(function () {
    const form = getForm('filterFrm');
    // Class selector autocomplete with full param false
    ClassIndexer.autocomplete(form.autocomplete_input, form.object_class, {profile: 'full'});
    form.onsubmit();
  });
</script>


<form name="filterFrm">
  <table class="main layout">
    <tr>
      <td class="separator expand" onclick="MbObject.toggleColumn(this, $(this).next())"></td>

      <td>
        <table class="form">
          <tr>
            <td>{{mb_label object=$filter field="object_class"}}</td>
            <td>
              <input type="text" name="autocomplete_input" size="40">
              <input type="hidden" id="object_class"/>
            </td>

            <td>{{mb_label object=$filter field="object_id"}}</td>
            <td>
              <input id="object_id" name="object_id" class="ref" value="{{$filter->object_id}}"/>
              <button class="search" type="button" onclick="ObjectSelector.initFilter()">{{tr}}Search{{/tr}}</button>
              <script type="text/javascript">
                ObjectSelector.initFilter = function () {
                  this.sForm = "filterFrm";
                  this.sId = "object_id";
                  this.sClass = "object_class";
                  this.onlyclass = "false";
                  this.pop();
                }
              </script>
            </td>
          </tr>

          <tr>
            <td>{{mb_label object=$filter field="id400"}}</td>
            <td id="id400">{{mb_field object=$filter field="id400" canNull=true}}</td>
            <td>{{mb_label object=$filter field="tag"}}</td>
            <td id="tag">{{mb_field object=$filter field="tag" size=30}}</td>
          </tr>

          <tr>
            <td>
              <button type="button" class="submit search" onclick="Idex.list_identifiants()">
                  {{tr}}Filter{{/tr}}
              </button>
            </td>
              {{if $is_admin}}
            <td>
              <button class="search" type="button" onclick="Idex.find_duplicated()">{{tr}}mod-dPsante400-find_duplicated{{/tr}}</button>
            </td>
            {{/if}}
          </tr>
        </table>
      </td>
    </tr>
  </table>
</form>
