{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=object_class value=false}}
{{mb_default var=stylized     value=true}}
{{mb_default var=token        value=false}}

{{assign var=cadre value=$manager->getRGPDText($object_class)}}

{{if $stylized}}
  <div style="text-align: center;">
    <div style="width: 50%; display: inline-block;">
      <div class="big-info">
        {{if $cadre && $cadre.intro}}
          <div>
            {{$cadre.intro|markdown}}
          </div>
        {{/if}}

        {{if $cadre && $cadre.data}}
          <div>
            {{$cadre.data|markdown}}
          </div>
        {{/if}}

        {{if $cadre && $cadre.droits}}
          <div>
            {{$cadre.droits|markdown}}
          </div>
        {{/if}}

        {{if $cadre && $cadre.contact}}
          <div>
            {{$cadre.contact|markdown}}
          </div>
        {{/if}}

        {{if $cadre && $cadre.conclusion}}
          <div>
            {{$cadre.conclusion|markdown}}
          </div>
        {{/if}}
      </div>
    </div>
  </div>
{{else}}
  {{if $cadre && $cadre.intro}}
    <div>
      {{$cadre.intro|markdown}}
    </div>
  {{/if}}

  {{if $cadre && $cadre.data}}
    <div>
      {{$cadre.data|markdown}}
    </div>
  {{/if}}

  {{if $cadre && $cadre.droits}}
    <div>
      {{$cadre.droits|markdown}}
    </div>
  {{/if}}

  {{if $cadre && $cadre.contact}}
    <div>
      {{$cadre.contact|markdown}}
    </div>
  {{/if}}

  {{if $cadre && $cadre.conclusion}}
    <div>
      {{$cadre.conclusion|markdown}}
    </div>
  {{/if}}
{{/if}}

{{if $token && $token->_id && $manager->canNotifyWithActions($object_class)}}
  <div style="text-align: center;">
    <div style="width: 50%; display: inline-block;">
      <div>
        <a href="{{$token->getURL()}}&consent=1" style="display: inline-block;">
          {{tr}}CRGPDConsent-action-Accept{{/tr}}
        </a>

        <a href="{{$token->getURL()}}&consent=0" style="display: inline-block;">
          {{tr}}CRGPDConsent-action-Decline{{/tr}}
        </a>
      </div>
    </div>
  </div>
{{/if}}