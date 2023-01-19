{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=in_dhe value=0}}

{{assign var=alert_email_a_jour     value="dPpatients CPatient alert_email_a_jour"|gconf}}
{{assign var=alert_telephone_a_jour value="dPpatients CPatient alert_telephone_a_jour"|gconf}}
{{assign var=alert_adresse_a_jour   value="dPpatients CPatient alert_adresse_a_jour"|gconf}}
{{assign var=email_mandatory        value="dPplanningOp CSejour email_mandatory"|gconf}}
{{assign var=tel_mandatory          value="dPplanningOp CSejour tel_mandatory"|gconf}}
{{assign var=med_trait_mandatory    value="dPplanningOp CSejour med_trait_mandatory"|gconf}}
{{assign var=patient_id             value=$patient->_id}}
{{assign var=form_name              value="editEmailTelPatient_$patient_id"}}

{{mb_script module=patients script=autocomplete ajax=1}}

{{if "appFineClient"|module_active}}
  {{mb_script module="appFineClient" script="appFineClient" ajax="true"}}
{{/if}}

{{if $in_dhe && $med_trait_mandatory}}
  {{mb_script module=patients script=medecin ajax=1}}
  <script>
    Medecin.set = function (id, view) {
      if (this.form.medecin_traitant) {
        $V(this.form.medecin_traitant, id);
      } else if (this.form.medecin_id) {
        $V(this.form.medecin_id, id);
      } else {
        $V(this.form.pharmacie_id, id);
      }
      $V(this.form._view, view);
    };

    Main.add(function () {
      var form = getForm('{{$form_name}}');
      new Url('patients', 'httpreq_do_medecins_autocomplete')
        .autoComplete(form._view, form._view.id + '_autocomplete', {
          minChars:      3,
          updateElement: function (element) {
            $V(form.medecin_traitant, element.id.split('-')[1]);
            $V(form._view, element.select('.view')[0].getText().stripTags());
          }
        });
    });
  </script>
{{/if}}

<script>
  toggleFieldRefus = function (input) {
    var form = input.form;
    var fields = [];

    switch (input.name) {
      default:
        fields.push(form.tel);
        fields.push(form.tel2);
        break;
      case 'email_refus':
        fields.push(form.email);
        break;
    }

    fields.each(function (_field) {
      _field.writeAttribute('disabled', input.value === '1');
    });

    updateStatusCloseButton();
  };

  updateStatusCloseButton = function () {
    var form = getForm('{{$form_name}}');
    var button = form.down('button.cancel');
    var email_mandatory = parseInt('{{$email_mandatory}}');
    var tel_mandatory = parseInt('{{$tel_mandatory}}');

    var active = true;

    if (form.medecin_traitant && !$V(form.medecin_traitant)) {
      active = false;
    } else if (form.email && email_mandatory && (!form.__email_refus || !form.__email_refus.checked) && !$V(form.email)) {
      active = false;
    } else if (form.tel && tel_mandatory && (!form.__tel_refus || !form.__tel_refus.checked) && (!$V(form.tel) && !$V(form.tel2))) {
      active = false;
    }

    button.writeAttribute('disabled', active ? null : 'disabled');
  };

  Main.add(function () {
    var form = getForm('{{$form_name}}');
    {{if $email_mandatory}}
    toggleFieldRefus(form.email_refus);
    {{/if}}

    {{if $tel_mandatory}}
    toggleFieldRefus(form.tel_refus);
    {{/if}}

    InseeFields.initCPVille("{{$form_name}}", "cp", null, null, 'pays', null);
  });
</script>

<div class="small-info">
  {{tr}}CPatient-info_maj_data{{/tr}}
  <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
    {{$patient}}
  </span>
</div>

<form name="{{$form_name}}" method="post" onsubmit="onSubmitFormAjax(this, updateStatusCloseButton);">
  {{mb_key   object=$patient}}
  {{mb_class object=$patient}}
  <table class="form">
    {{if $alert_email_a_jour || ($in_dhe && $email_mandatory)}}
      <tr>
        <th>{{mb_label object=$patient field=email}}</th>
        <td>
          {{mb_field object=$patient field=email onchange="this.form.onsubmit();"}}

          {{if $in_dhe && $email_mandatory}}
            {{mb_field object=$patient field=email_refus typeEnum=checkbox onchange="toggleFieldRefus(this); this.form.onsubmit();"}}
            {{mb_label object=$patient field=email_refus typeEnum=checkbox}}
          {{/if}}
        </td>
      </tr>
    {{/if}}
    {{if $alert_telephone_a_jour || ($in_dhe && $tel_mandatory)}}
      <tr>
        <th class="narrow">{{mb_label object=$patient field=tel}}</th>
        <td>
          {{mb_field object=$patient field=tel onchange="this.form.onsubmit();"}}

          {{if $in_dhe && $tel_mandatory}}
            {{mb_field object=$patient field=tel_refus typeEnum=checkbox onchange="toggleFieldRefus(this); this.form.onsubmit();"}}
            {{mb_label object=$patient field=tel_refus typeEnum=checkbox}}
          {{/if}}
        </td>
      </tr>
      <tr>
        <th>{{mb_label object=$patient field=tel2}}</th>
        <td>{{mb_field object=$patient field=tel2 onchange="this.form.onsubmit();"}}</td>
      </tr>
    {{/if}}
    {{if $alert_adresse_a_jour}}
      <tr>
      <tr>
        <th>{{mb_label object=$patient field=adresse}}</th>
        <td>{{mb_field object=$patient field=adresse onchange="this.form.onsubmit();"}}</td>
      </tr>

      <tr>
        <th>{{mb_label object=$patient field=cp}}</th>
        <td>{{mb_field object=$patient field=cp onchange="this.form.onsubmit();"}}</td>
      </tr>

      <tr>
        <th>{{mb_label object=$patient field=pays}}</th>
        <td>{{mb_field object=$patient field=pays onchange="this.form.onsubmit();"}}</td>
      </tr>
      </tr>
    {{/if}}
    {{if $in_dhe && $med_trait_mandatory}}
      <tr>
        <th>{{mb_label object=$patient field=medecin_traitant}}</th>
        <td>
          {{mb_field object=$patient field=medecin_traitant hidden=1
          onchange="if (this.value) { this.form.onsubmit(); }"}}

          <input type="text" name="_view" size="50" value="{{$patient->_ref_medecin_traitant}}"
                 ondblclick="Medecin.edit(this.form, $V(this.form._view))" class="autocomplete" />
          <button class="search" type="button" onclick="Medecin.edit(this.form, $V(this.form._view))">{{tr}}Choose{{/tr}}</button>
        </td>
      </tr>
    {{/if}}
    {{if $in_dhe}}
      <tr>
        <tr>
          <th>{{mb_label object=$patient field=rques}}</th>
          <td>{{mb_field object=$patient field="rques" onchange="this.form.onsubmit();"}}</td>
        </tr>
        <td colspan="2" class="button">
          <button type="button" class="cancel"
            {{if ($med_trait_mandatory && !$patient->medecin_traitant)
            || ($email_mandatory && !$patient->email && !$patient->email_refus)
            || ($tel_mandatory && !$patient->tel && !$patient->tel2 && !$patient->tel_refus)}}
              disabled
            {{/if}}
                  onclick="Control.Modal.close();
                  {{if "appFineClient"|module_active}} appFineClient.refresButtonAccountAppFine('{{$patient->_id}}') {{/if}}">
            {{tr}}Close{{/tr}}
          </button>
        </td>
      </tr>
    {{/if}}
  </table>
</form>
