{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=class value=CFile}}

<script>
  unlockConfig = function (oForm) {
    Modal.confirm($T("CFile-confirm-Modify these configurations ?"), {
      onOK: function () {
        oForm['{{$m}}[{{$class}}][prefix_format]'].disabled = false;
        oForm['{{$m}}[{{$class}}][prefix_format_qualif]'].disabled = false;
        oForm['{{$m}}[{{$class}}][hierarchy]'].disabled = false;
      }
    });
  };
  
  Main.add(function () {
    var oForm = getForm('EditConfig-{{$class}}');
    oForm['{{$m}}[{{$class}}][prefix_format]'].disabled = true;
    oForm['{{$m}}[{{$class}}][prefix_format_qualif]'].disabled = true;
    oForm['{{$m}}[{{$class}}][hierarchy]'].disabled = true;
  });
</script>

<form name="EditConfig-{{$class}}" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}
  
  <table class="form">
    <col style="width: 50%;" />
    <tr>
      <th class="category" colspan="2">{{tr}}CFile-Storage{{/tr}}</th>
    </tr>

    {{assign var=class value="CFile"}}
    {{mb_include module=system template=inc_config_str var=upload_directory size=40}}
    {{mb_include module=system template=inc_config_str var=upload_directory_private size=40}}

    <tr>
      <th></th>
      <td>
        <div class="small-warning">
          {{tr}}CFile-msg-Be careful, providing a root signature filename which does not exist may have unexpected results.{{/tr}}
        </div>
      </td>
    </tr>

    {{mb_include module=system template=inc_config_str var=signature_filename size=40}}
    {{mb_include module=system template=inc_config_str var=gs_alias size=40 class='CThumbnail'}}

    <tr>
      <th class="category" colspan="2">{{tr}}CFile-Format{{/tr}}</th>
    </tr>
    <tr>
      <td colspan="2" class="button">
        <div class="small-info markdown">
          {{tr}}CFile-msg-Prefix setted{{/tr}}
          <br/>
          {{tr}}CFile-msg-Prefix accepted format{{/tr}}
          <br/>
          {{tr}}CFile-msg-Hierarchy format{{/tr}}
          <br/>
          {{tr}}CFile-msg-Prefix example{{/tr}}
        </div>
      </td>
    </tr>
    {{mb_include module=system template=inc_config_str var=prefix_format maxlength=6}}
    {{mb_include module=system template=inc_config_str var=prefix_format_qualif maxlength=6}}
    {{mb_include module=system template=inc_config_str var=hierarchy maxlength=6}}

    <tr>
      <td colspan="2" class="button">
        <button type="button" class="unlock" onclick="unlockConfig(this.form);">{{tr}}common-action-Unlock{{/tr}}</button>
      </td>
    </tr>
    
    <tr>
      <th class="category" colspan="2">{{tr}}CFile-Migration{{/tr}}</th>
    </tr>
    {{mb_include module=system template=inc_config_bool var=migration_started}}
    {{mb_include module=system template=inc_config_str var=migration_limit}}
    {{mb_include module=system template=inc_config_str var=migration_ratio}}

    <tr>
      <th class="category" colspan="2">{{tr}}extract server{{/tr}}</th>
    </tr>

    {{mb_include module=system template=inc_config_str class=tika var=host}}
    {{mb_include module=system template=inc_config_str class=tika var=port}}
    {{mb_include module=system template=inc_config_bool class=tika var=active_ocr_pdf}}
    {{mb_include module=system template=inc_config_str class=tika var=timeout}}

    {{mb_include module=system template=configure_handler class_handler=CFileTraceabilityHandler}}

    <tr>

      <td class="button" colspan="2">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
