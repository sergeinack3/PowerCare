{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="system" script="object_selector"}}

<script>
  function searchDocument(form) {
    new Url("hl7", "ajax_list_xdsb_files")
      .addFormData(form)
      .requestUpdate("xdsb-list");

    return false;
  }
</script>

<table class="main form">
  <tr>
    <td>
      <form method="get" name="xdsb-object-selector-form" onsubmit="return searchDocument(this)">
        <input type="text" name="_object_view" value="{{$object}}" readonly="readonly" size="50" />
        <input type="hidden" name="object_id" value="{{$object_id}}" />
        <input type="hidden" name="object_class" value="CPatient" />
        <input type="hidden" name="_search_fast" value="1">

        <button type="button" class="search notext" onclick="ObjectSelector.init()">
          Chercher un objet
        </button>
        <script type="text/javascript">
          ObjectSelector.init = function(){
            this.sForm     = "xdsb-object-selector-form";
            this.sId       = "object_id";
            this.sView     = "_object_view";
            this.sClass    = "object_class";
            this.onlyclass = "true";
            this.pop();
          }
        </script>

        <button class="search" type="submit">{{tr}}Search{{/tr}}</button>
      </form>
    </td>
  </tr>
</table>

<div id="xdsb-list"></div>