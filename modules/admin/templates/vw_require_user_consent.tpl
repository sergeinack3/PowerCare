{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=admin template=inc_vw_rgpd_document object_class=$user->_class manager=$consent->getManager()}}

<div style="text-align: center;">
  <div style="width: 50%; display: inline-block;">
    <form name="user-consent" method="post" onsubmit="return onSubmitFormAjax(this);">
      <input type="hidden" name="m" value="admin" />
      <input type="hidden" name="dosql" value="do_collect_user_consent" />
      <input type="hidden" name="consent" value="" />

      <div>
        <button type="submit" class="big fas fa-check oneclick" onclick="$V(this.form.elements.consent, '1');"
          {{if $consent && $consent->_id && $consent->isAccepted()}} title="{{mb_value object=$consent field=acceptance_datetime}}"{{/if}}>
          {{tr}}CRGPDConsent-action-Accept{{/tr}}
        </button>

        <button type="submit" class="big fas fa-ban oneclick" onclick="$V(this.form.elements.consent, '0');"
          {{if $consent && $consent->_id && $consent->isRefused()}} title="{{mb_value object=$consent field=refusal_datetime}}"{{/if}}>
          {{tr}}CRGPDConsent-action-Decline{{/tr}}
        </button>
      </div>
    </form>
  </div>
</div>