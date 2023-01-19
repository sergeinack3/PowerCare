{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=form}}
{{mb_default var=callback value='function(){}'}}

{{if $object->_ref_abonnement}}
  <form name="{{$form}}" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: {{$callback}} });">
    {{mb_key object=$object->_ref_abonnement}}
    {{mb_class object=$object->_ref_abonnement}}
    {{mb_field object=$object->_ref_abonnement field=object_class hidden=true}}
    {{mb_field object=$object->_ref_abonnement field=object_id hidden=true}}
    {{mb_field object=$object->_ref_abonnement field=user_id hidden=true}}

    {{if $object->_ref_abonnement->_id}}
      <input type="hidden" name="del" value="1" />

      <button type="button" class="fa fa-bookmark notext me-color-care" style="color: forestgreen !important;" onclick="this.form.onsubmit();">
        {{tr}}CAbonnement-action-Delete{{/tr}}
      </button>
    {{else}}
      <button type="button" class="fa fa-bookmark notext me-color-care" style="color: firebrick !important;" onclick="this.form.onsubmit();">
        {{tr}}CAbonnement-action-Create{{/tr}}
      </button>
    {{/if}}
  </form>
{{/if}}