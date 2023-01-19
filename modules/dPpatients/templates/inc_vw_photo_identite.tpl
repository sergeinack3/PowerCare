{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=mode value=read}}
{{mb_default var=size value=120}}
{{mb_default var=sejour_conf value=false}}

{{assign var=file value=$patient->_ref_photo_identite}}

{{if $mode == "edit"}}
    <script>
        {{if !$file->_id}}
        Main.add(function () {
            WebcamImage.initButton($('patient_image_displayer'), '{{$patient->_guid}}', {rename: "identite.jpg"});
        });
        {{/if}}

        reloadAfterUploadFile = function () {
            var url = new Url("patients", "httpreq_vw_photo_identite");
            url.addParam("patient_id", "{{$patient->_id}}");
            url.addParam("mode", "edit");
            url.requestUpdate("{{$patient->_guid}}-identity");
        };

        deletePhoto = function (file_id) {
            var options = {
                typeName: 'la photo',
                objName: 'identite.jpg'
            };

            var ajax = {
                onComplete: reloadAfterUploadFile
            };

            var form = getForm('delete-photo-identite-form');
            $V(form.file_id, file_id);

            return confirmDeletion(form, options, ajax);
        };
    </script>
{{/if}}

{{if $file->_id}}
    {{if !$file->private || $patient->_can_see_photo}}
        {{assign var=src value="?m=files&raw=thumbnail&document_guid=`$file->_class`-`$file->_id`&profile=medium&crop=1"}}
        {{assign var=_src value="?m=files&raw=thumbnail&document_guid=`$file->_class`-`$file->_id`&profile=large"}}
    {{else}}
        {{assign var=src value="images/pictures/identity_anonymous.png"}}
        {{assign var=_src value="images/pictures/identity_anonymous.png"}}
    {{/if}}
{{else}}
    {{if $patient->_annees < 2 && $patient->naissance && $patient->naissance != "0000-00-00"}}
        {{assign var=src value="images/pictures/identity_baby.png"}}
    {{elseif $patient->_annees < "dPpatients CPatient adult_age"|gconf}}
        {{assign var=src value="images/pictures/identity_child.png"}}
    {{else}}
        {{assign var=src value="images/pictures/user.png"}}
    {{/if}}
{{/if}}

{{assign var=border_photo value="#f88"}}
{{assign var=background_photo value="#fee"}}
{{if $patient->sexe == "m"}}
  {{assign var=border_photo value="#88f"}}
  {{assign var=background_photo value="#eef"}}
{{/if}}

{{if $sejour_conf}}
  {{assign var=src value="images/pictures/sejour_conf.png"}}
  {{assign var=border_photo value="transparent"}}
  {{assign var=background_photo value="transparent"}}
{{/if}}

{{assign var=style value="width: `$size`px; height: `$size`px; border: 2px solid `$border_photo`; background: `$background_photo`;"}}
<div style="{{$style}}" class="me-identity-picture me-identity-picture-{{$size}}">
  {{* Laisser le src sur la meme ligne que img !! pour le mode all in one, sinon le parseur ne le reconnait pas *}}
  <img src="{{$src}}" alt="Identité"
    {{if $file->_id}}
    onmouseover="ObjectTooltip.createDOM(this, 'tooltip-content-patient-{{$patient->_id}}')"
    {{/if}}
    {{if $sejour_conf}}
      title="{{tr}}CSejour-presence_confidentielle-desc{{/tr}}"
    {{/if}}
  />
</div>

{{if $file->_id}}
    <div id="tooltip-content-patient-{{$patient->_id}}" style="display: none;">
        <img src="{{$_src}}" style="border: 2px solid #777" alt="Identité"/>
    </div>
{{/if}}

{{if $mode == "edit"}}
    <br/>
    {{if !$file->_id}}
        <button type="button" class="search me-tertiary notext" title="{{tr}}Browse{{/tr}}"
                onclick="uploadFile('{{$patient->_guid}}', null, 'identite.jpg', 1)">
            {{tr}}Browse{{/tr}}
        </button>
        <button type="button" id="patient_image_displayer"
                class="fas fa-camera me-tertiary notext" title="{{tr}}Take a picture{{/tr}}" style="display:none">
            {{tr}}Take a picture{{/tr}}
        </button>
    {{else}}
        <button onclick="deletePhoto('{{$file->_id}}')" class="trash me-tertiary me-dark" type="button">
            {{tr}}Delete{{/tr}}
        </button>
    {{/if}}
{{/if}}
