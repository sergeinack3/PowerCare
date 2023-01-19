{{*
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function deleteAffectation(affectation_id, object_guid, personnel_id, klass) {
    if (klass != "error") {
      if (!confirm("{{tr}}CAffectation-confirm-deletion{{/tr}}")) {
        return;
       }
    }

    var form = getForm("Affectation");
    $V(form.affect_id, affectation_id);
    var oAjaxOptions = {
      onComplete: function() {
        var url = new Url("personnel", "ajax_affectations_multiple");
        url.addParam("object_guid", object_guid);
        url.addParam("personnel_id", personnel_id);
        url.requestUpdate(object_guid+"-"+personnel_id);
      }
    };

    onSubmitFormAjax(form, oAjaxOptions);
  }
</script>

<form name="Affectation" method="post">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="dosql" value="do_affectation_aed" />
  <input type="hidden" name="affect_id" value="" />
  <input type="hidden" name="del" value="1" />
</form>

<table class="tbl">
  <tr>
    <th>{{mb_title class=CAffectationPersonnel field=object_id}}</th>
    <th>{{mb_title class=CAffectationPersonnel field=personnel_id}}</th>
    <th style="width: 40px">{{tr}}Infos{{/tr}}</th>
    <th>{{mb_title class=CAffectationPersonnel field=realise}}</th>
    <th>{{mb_title class=CAffectationPersonnel field=debut}}</th>
    <th>{{mb_title class=CAffectationPersonnel field=fin}}</th>
    <th>{{tr}}Actions{{/tr}}</th>
  </tr>

  {{foreach from=$multiples item=_multiple}}
  {{assign var=object_guid value=$_multiple.object->_guid}}
  {{assign var=personnel_id value=$_multiple.personnel->_id}}
  <tbody id="{{$object_guid}}-{{$personnel_id}}" class="hoverable">
    {{mb_include module=personnel template=inc_affectations_multiple}}
  </tbody>
  {{/foreach}}
</table>
   