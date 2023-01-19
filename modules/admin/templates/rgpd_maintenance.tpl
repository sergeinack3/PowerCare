{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admin script=rgpd}}

{{if 'Ox\Core\Handlers\Facades\HandlerManager::isObjectHandlerActive'|static_call:'CRGPDHandler'}}
  <div class="small-error">Le gestionnaire de consentements RGPD doit être désactivé.</div>

  {{mb_return}}
{{/if}}

<table class="main layout">
  <tr>
    <td>
      <form name="purge-rgpd-consents" method="post" onsubmit="return RGPD.confirmPurge(this);">
        <input type="hidden" name="module" value="admin" />
        <input type="hidden" name="dosql" value="do_purge_rgpd_consents" />
        <input type="hidden" name="start" value="0" />
        <input type="hidden" name="confirmed" value="0" />

        <table class="main form">
          <tr>
            <th class="narrow">{{mb_label class=CRGPDConsent field=generation_datetime}}</th>

            <td class="narrow">
              {{mb_field class=CRGPDConsent field=_min_generation_datetime form='purge-rgpd-consents' register=true}}
              &raquo;
              {{mb_field class=CRGPDConsent field=_max_generation_datetime form='purge-rgpd-consents' register=true}}
            </td>

            <td class="narrow">
              <label>
                <input type="checkbox" name="auto" checked />

                Auto.
              </label>
            </td>

            <td class="narrow">
              <label>
                <input type="checkbox" name="dry_run" checked />

                {{tr}}common-Dry run{{/tr}}
              </label>
            </td>

            <td>
              <button type="submit" class="trash" onclick="">
                {{tr}}common-action-Purge{{/tr}}
              </button>
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>

  <tr>
    <td id="purge-consents-result"></td>
  </tr>
</table>
