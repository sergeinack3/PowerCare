{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  completeTrans = function (type, button) {
    var form = getForm("editTrans");
    var field = form.text;
    var text = button.get("text");
    $V(field, $V(field) ? $V(field) + "\n" + text : text);
  };

  Main.add(function () {
    updateListTransmissions('{{$transmission->object_id}}', 'CCategoryPrescription', '{{$transmission->cible_id}}');
  });
</script>

<form name="editTrans" method="post">
  {{mb_class class=CTransmissionMedicale}}
  {{mb_key object=$transmission}}
  {{mb_field object=$transmission field=sejour_id    hidden=1}}
  {{mb_field object=$transmission field=user_id      hidden=1}}
  {{mb_field object=$transmission field=type         hidden=1}}
  {{mb_field object=$transmission field=object_class hidden=1}}
  {{mb_field object=$transmission field=object_id    hidden=1}}
  <input type="hidden" name="_force_new_cible" />

  {{mb_include module=hospi template=inc_transmission_caracs macrocible=1}}

  <fieldset>
    <legend>
      {{mb_label object=$transmission field="text"}}
    </legend>
    {{mb_field object=$transmission field="text" rows=6 form="editTrans"
    aidesaisie="property: 'text',
                 dependField1: getForm('editTrans').type,
                 dependField2: getForm('editTrans').object_id,
                 classDependField2: 'CCategoryPrescription',
                 validateOnBlur: 0,
                 updateDF: 0,
                 strict: true"}}
  </fieldset>

  <div style="text-align: center;">
    <button type="button" class="add" onclick="submitTrans(this.form, 1);">{{tr}}Add{{/tr}}</button>
  </div>
</form>

<div style="margin-top: 20px;" id="list_transmissions"></div>