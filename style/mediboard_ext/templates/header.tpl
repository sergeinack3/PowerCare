{{*
 * @package Mediboard\Style\Mediboard
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include template=common nodebug=true}}

{{if !$offline && !$dialog}}
<style>
  #main > .OxVueWrap ~ .AppbarSkeleton{
    display: none;
  }
</style>
{{/if}}

<script>
  Main.add(
    function () {
      MediboardExt.initDate("{{$dtnow}}")
    }
  )
</script>

<div id="main" class="{{if $dialog}}dialog{{else}}me-fullpage{{/if}} {{$m}}">
    {{if !$offline && !$dialog}}
      {{mb_entry_point entry_point=$appbar}}
      <div class="AppbarSkeleton">
        <div class="AppbarSkeleton-nav">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" role="img" aria-hidden="true">
            <path d="M3,11H11V3H3M3,21H11V13H3M13,21H21V13H13M13,3V11H21V3"></path>
          </svg>
          <div class="AppbarSkeleton-module">
              {{tr}}module-{{$m}}-court{{/tr}}
          </div>
          <div class="AppbarSkeleton-tab">
              {{tr}}mod-{{$m}}-tab-{{if $tab}}{{$tab}}{{else}}{{$a}}{{/if}}{{/tr}}
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" role="img" aria-hidden="true">
              <path d="M7.41,8.58L12,13.17L16.59,8.58L18,10L12,16L6,10L7.41,8.58Z"></path>
            </svg>
          </div>
        </div>

        <div class="AppbarSkeleton-context">
          <div class="AppbarSkeleton-group">
              {{$current_group}}
          </div>
          <div class="AppbarSkeleton-user">
            <div class="AppbarSkeleton-lastName">
                {{$app->_ref_user->_user_last_name}}
            </div>
            <div class="AppbarSkeleton-firstName">
                {{$app->_ref_user->_user_first_name}}
            </div>
            <div class="AppbarSkeleton-initiales">
                {{$app->_ref_user->_shortview}}
            </div>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" role="img" aria-hidden="true">
            <path d="M7,10L12,15L17,10H7Z"></path>
          </svg>
        </div>
      </div>

      <div class="nav-compenser"></div>
      {{mb_include style=mediboard_ext template=message nodebug=true update_placeholders=false}}
      {{mb_include style=mediboard_ext template=offline_mode}}
    {{/if}}


    {{mb_include template=obsolete_module}}
  <div id="systemMsg">
      {{$errorMessage|nl2br|smarty:nodefaults}}
  </div>
