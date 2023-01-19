{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  button.valued:before {
    color: forestgreen !important;
  }
</style>

{{assign var=name value="`$ex_field->name`_constant_comment"}}

<div id="{{$name}}_container" style="display: none;">
  <div style="margin: 10px;">
    <textarea id="{{$name}}_value" name="{{$name}}" rows="3"></textarea>

    <div style="text-align: center;">
      <button type="button" class="tick" onclick="Control.Modal.close();">
          {{tr}}common-action-Validate{{/tr}}
      </button>

      <button type="button" class="erase" onclick="$V(this.form.elements['{{$name}}_value'], '');">
          {{tr}}common-action-Reset{{/tr}}
      </button>
    </div>
  </div>
</div>

<button id="{{$name}}_button" type="button" class="fas fa-comment notext compact not-printable" tabindex="-1"
        onclick="Modal.open('{{$name}}_container', {title: $T('CConstantComment-title-create'), showClose: true, width: '400px', onClose: function() { $('{{$name}}_button').toggleClassName('valued', ($V('{{$name}}_value') !== '')); } });">
  {{tr}}CConstantComment-title-create{{/tr}}
</button>