{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form method="post" name="uploadCsvFileForm" enctype="multipart/form-data"
      action="m=jfse&a=jfseIndex&route=convention/uploadCsvFile"
      onsubmit="return onSubmitFormAjax(this,{useFormAction: true});">
    <table class="main tbl">
        <tr>
            <th colspan="2">{{tr}}CConvention-import-csv-title{{/tr}}</th>
        </tr>
        <tr>
            <td colspan="2">
                {{mb_include module=system template=inc_inline_upload multi=true paste=false extensions='csv'}}
            </td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-jfse_id{{/tr}}</th>
            <td><input type="text" name="jfse_id"></td>
        </tr>
        <tr>
            <td colspan="2">
                <button type="submit" class="import">{{tr}}Import{{/tr}}</button>
            </td>
        </tr>
    </table>
</form>
