{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    changePageFileError = function (start) {
      var form = getForm('list-file-error');
      $V(form.elements.start, start);
      form.onsubmit();
    };
    
    getForm('list-file-error').onsubmit();
  });
</script>

<h3>{{if $object_class}}{{$object_class}} &dash;{{/if}} {{if $error_type}}{{$error_type}}{{/if}}</h3>

<form name="list-file-error" method="get" onsubmit="return onSubmitFormAjax(this, null, 'file-errors');">
  <input type="hidden" name="m" value="dPfiles" />
  <input type="hidden" name="a" value="ajax_list_file_error" />
  <input type="hidden" name="start" value="0" />
  <input type="hidden" name="object_class" value="{{$object_class}}"/>
  <input type="hidden" name="error_type" value="{{$error_type}}"/>
  <input type="hidden" name="_show_empty_files" value="{{$file_report->_show_empty_files}}"/>
  
  <table class="main tbl">
    <tr>
      <td class="button" colspan="6">
        <a style="float: right;" class="button download" target="_blank"
           href="?m=dPfiles&raw=ajax_export_file_report&object_class={{$object_class}}&error_type={{$error_type}}">
          Export CSV
        </a>
      </td>
    </tr>
  </table>
</form>

<div id="file-errors"></div>