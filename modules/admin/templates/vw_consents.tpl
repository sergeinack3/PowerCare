{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form = getForm('search-consent');
    form.onsubmit();
  });

  function changePage(start) {
    var form = getForm('search-consent');
    $V(form.elements.start, start);
    form.onsubmit();
  }
</script>

{{if !'Ox\Core\Handlers\Facades\HandlerManager::isObjectHandlerActive'|static_call:'CRGPDHandler'}}
  <div class="small-error me-no-align me-margin-top-8">
    {{tr}}CRGPDConsent-error-Handler is not enabled.{{/tr}}
  </div>
{{/if}}

{{if !$rgpd_user || !$rgpd_user->_id}}
  <div class="small-error me-no-align me-margin-top-8">
    {{tr}}CRGPDConsent-error-User is not configured.{{/tr}}
  </div>
{{/if}}

<table class="main layout me-padding-0 me-margin-top-8">
  <tr>
    <td class="narrow separator expand me-no-display" onclick="MbObject.toggleColumn(this, this.next()); getForm('search-consent').onsubmit();"></td>

    <td>
      <form name="search-consent" method="get" onsubmit="return onSubmitFormAjax(this, null, 'consent-results');">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="a" value="ajax_search_consent" />
        <input type="hidden" name="start" value="0" />

        <table class="main form me-no-align">
          <col style="width: 10%" />

          <tr>
            <td colspan="3" style="text-align: center;">
              <div style="margin-top: 5px; position: relative;">
                {{foreach name=statuses from='Ox\Mediboard\Admin\Rgpd\CRGPDConsent::getStatuses'|static_call:null item=_status}}
                  <div class="CRGPDConsent_status_counter CRGPDConsent_status_{{$_status}}" data-status="{{$_status}}"
                    {{if $smarty.foreach.statuses.last}} style="margin-right: 45px;" {{/if}}>
                    {{tr}}CRGPDConsent.status.{{$_status}}{{/tr}}
                  </div>

                  <span class="CRGPDConsent_status_tip" data-count="{{$total_by_status.$_status|integer}}" style="position: absolute;"></span>
                {{/foreach}}
              </div>
            </td>

            <td colspan="3" style="text-align: center;">
              <div style="margin-top: 5px;">
                {{foreach from=$classes item=_class}}
                  <div style="padding: 5px; display: inline-block;">
                    <strong>{{tr}}CRGPDConsent.object_class.{{$_class}}{{/tr}}</strong>

                    {{if $manager->inManualMode($_class)}}
                      {{if $_class === 'CUser'}}
                        <i class="fa fa-sign-in-alt fa-lg" style="color: forestgreen;" title="{{tr}}CRGPDConsent-title-Direct ask for consent{{/tr}}"></i>
                      {{else}}
                        <i class="far fa-handshake fa-lg" style="color: forestgreen;" title="{{tr}}CRGPDConsent-title-Consent managed manually|pl{{/tr}}"></i>
                      {{/if}}
                    {{else}}
                      <span class="fa fa-stack" title="{{tr}}CRGPDConsent-title-Manual ask for consent disabled|pl{{/tr}}">
                        <i class="far fa-handshake fa-stack-1x"></i>
                        <i class="fa fa-ban fa-stack-2x" style="color: firebrick;"></i>
                      </span>
                    {{/if}}

                    {{if $manager->canNotify($_class)}}
                      <i class="fa fa-envelope fa-lg" style="color: forestgreen;" title="{{tr}}CRGPDConsent-title-Notification enabled|pl{{/tr}}"></i>
                    {{else}}
                      <span class="fa fa-stack" title="{{tr}}CRGPDConsent-title-Notification disabled|pl{{/tr}}">
                        <i class="fa fa-envelope fa-stack-1x"></i>
                        <i class="fa fa-ban fa-stack-2x" style="color: firebrick;"></i>
                      </span>
                    {{/if}}

                    {{if $manager->canNotifyWithActions($_class)}}
                      <i class="fa fa-comments fa-lg" style="color: forestgreen;" title="{{tr}}CRGPDConsent-title-Notification with action enabled|pl{{/tr}}"></i>
                    {{else}}
                      <span class="fa fa-stack" title="{{tr}}CRGPDConsent-title-Notification with action disabled|pl{{/tr}}">
                        <i class="fa fa-comments fa-stack-1x"></i>
                        <i class="fa fa-ban fa-stack-2x" style="color: firebrick;"></i>
                      </span>
                    {{/if}}
                  </div>
                {{/foreach}}
              </div>
            </td>
          </tr>

          <tr>
            <th>{{mb_label class=CRGPDConsent field=_first_name}}</th>
            <td>{{mb_field class=CRGPDConsent field=_first_name}}</td>

            <th>{{mb_label class=CRGPDConsent field=_last_name}}</th>
            <td>{{mb_field class=CRGPDConsent field=_last_name}}</td>

            <th>{{mb_label class=CRGPDConsent field=_birth_date}}</th>
            <td>{{mb_field class=CRGPDConsent field=_birth_date}}</td>
          </tr>

          <tr>
            <th>{{mb_label class=CRGPDConsent field=object_class}}</th>
            <td>{{mb_field class=CRGPDConsent field=_object_class}}</td>

            <th>{{mb_label class=CRGPDConsent field=status}}</th>
            <td class="text">{{mb_field class=CRGPDConsent field=_status}}</td>

            <th>{{mb_label class=CRGPDConsent field=last_error}}</th>
            <td>{{mb_field class=CRGPDConsent field=_last_error}}</td>
          </tr>

          <tr>
            <th>{{mb_label class=CRGPDConsent field=generation_datetime}}</th>
            <td>
              {{mb_field class=CRGPDConsent field=_min_generation_datetime form='search-consent' register=true}}
              &raquo;
              {{mb_field class=CRGPDConsent field=_max_generation_datetime form='search-consent' register=true}}
            </td>

            <th>{{mb_label class=CRGPDConsent field=send_datetime}}</th>
            <td>
              {{mb_field class=CRGPDConsent field=_min_send_datetime form='search-consent' register=true}}
              &raquo;
              {{mb_field class=CRGPDConsent field=_max_send_datetime form='search-consent' register=true}}
            </td>

            <th>{{mb_label class=CRGPDConsent field=read_datetime}}</th>
            <td>
              {{mb_field class=CRGPDConsent field=_min_read_datetime form='search-consent' register=true}}
              &raquo;
              {{mb_field class=CRGPDConsent field=_max_read_datetime form='search-consent' register=true}}
            </td>
          </tr>

          <tr>
            <th>{{mb_label class=CRGPDConsent field=acceptance_datetime}}</th>
            <td>
              {{mb_field class=CRGPDConsent field=_min_acceptance_datetime form='search-consent' register=true}}
              &raquo;
              {{mb_field class=CRGPDConsent field=_max_acceptance_datetime form='search-consent' register=true}}
            </td>

            <th>{{mb_label class=CRGPDConsent field=refusal_datetime}}</th>
            <td>
              {{mb_field class=CRGPDConsent field=_min_refusal_datetime form='search-consent' register=true}}
              &raquo;
              {{mb_field class=CRGPDConsent field=_max_refusal_datetime form='search-consent' register=true}}
            </td>

            <th>{{mb_label class=CRGPDConsent field=proof_hash}}</th>
            <td>{{mb_field class=CRGPDConsent field=proof_hash prop='str maxLength|64'}}</td>
          </tr>

          <tr>
            <td class="button" colspan="6">
              <button type="submit" class="search">{{tr}}Search{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>

<div id="consent-results" class="me-padding-0"></div>
