{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function () {
    var aideSaisie = new AideSaisie.AutoComplete(getForm("edit-constantes-medicales{{$unique_id}}")._comment, {
      objectClass:            'CConstantesMedicales',
      contextUserId:          User.id,
      timestamp:              "{{$conf.dPcompteRendu.CCompteRendu.timestamp}}",
      filterWithDependFields: false,
      validateOnBlur:         0,
      property:               'comment'
    });
  });
</script>

<table class="form me-no-box-shadow me-no-align">
  <tr>
    <th class="title" colspan="2">
      {{tr}}CConstantComment-title-create{{/tr}}
    </th>
  </tr>
  <tr>
    <td colspan="2" style="height: 20px"></td>
  </tr>
  <tr>
    <th>
      {{mb_label object=$constantes field=_constant_comments}}
    </th>
    <td class="me-padding-right-26">
      <input type="hidden" name="_constant_comment" value="" />
      <textarea name="_comment" row="3" style="height: 50px;"></textarea>
    </td>
  </tr>
  <tr>
    <td class="button" colspan="2">
      <button type="button" class="tick" id="validate_comment_cte" onclick="addComment(this.form);">{{tr}}Validate{{/tr}}</button>
    </td>
  </tr>
</table>

<div id="list_comments{{$unique_id}}"></div>
