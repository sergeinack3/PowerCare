{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form method="post" name="importBinFileForm" enctype="multipart/form-data"
      action="m=jfse&a=jfseIndex&route=convention/importBinFile"
      onsubmit="return onSubmitFormAjax(this,{useFormAction: true});">
    <table class="main tbl">
        <tr>
            <th colspan="2">{{tr}}CConvention-import-bin-title{{/tr}}</th>
        </tr>
        <tr>
            <td>
                {{mb_include module=system template=inc_inline_upload multi=false paste=false extensions='bin'}}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <button type="submit" class="import">{{tr}}Import{{/tr}}</button>
            </td>
        </tr>
    </table>
</form>
