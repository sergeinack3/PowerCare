{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=pdf_and_thumbs value=$app->user_prefs.pdf_and_thumbs}}

<ul style="text-align: left;">
  {{foreach from=$modeles item=_modele}}
    {{assign var=owner_icon value="group"}}
    {{assign var=owner_mbd_icon value="group me-primary"}}
    {{if $_modele->_owner === "prat"}}
      {{assign var=owner_icon value="user"}}
      {{assign var=owner_mbd_icon value="user me-primary"}}
      {{if $_modele->user_id == $app->user_id}}
        {{assign var=owner_icon value="user-glow"}}
        {{assign var=owner_mbd_icon value="user me-warning"}}
      {{/if}}
    {{elseif $_modele->_owner === "func"}}
      {{assign var=owner_icon value="user-function"}}
      {{assign var=owner_mbd_icon value="function me-primary"}}
      {{if $_modele->function_id == $app->_ref_user->function_id}}
        {{assign var=owner_icon value="user-function-glow"}}
        {{assign var=owner_mbd_icon value="function me-warning"}}
      {{/if}}
    {{/if}}

    <li>

      {{me_img src="`$owner_icon`.png" icon=$owner_mbd_icon style="float: right; clear: both; margin: -1px;"}}

        {{if $_modele->fast_edit_pdf && $pdf_and_thumbs}}
          <img style="float: right;" src="modules/dPcompteRendu/fcke_plugins/mbprintPDF/images/mbprintPDF.png"/>
        {{elseif $_modele->fast_edit}}
          <img style="float: right;" src="modules/dPcompteRendu/fcke_plugins/mbprinting/images/mbprinting.png"/>
        {{/if}}
        {{if $_modele->fast_edit || ($_modele->fast_edit_pdf && $pdf_and_thumbs)}}
          <img style="float: right;" src="images/icons/lightning.png"/>
        {{/if}}

      <div {{if $_modele->fast_edit || ($_modele->fast_edit_pdf && $pdf_and_thumbs)}}class="fast_edit"{{/if}} style="display:inline-block">
        {{$_modele->nom|emphasize:$keywords}} {{if $appFine && $_modele->_owner === "func"}}({{$_modele->_ref_function->text}}){{/if}}
      </div>
      
      <!--{{if $_modele->file_category_id}}
        <small style="color: #666; margin-left: 1em;" class="text">
          {{mb_value object=$_modele field=file_category_id}}
        </small>
      {{/if}}-->
      {{if $_modele->_utilisations}}
        <div style="display:inline-block;float:right" class="compact">
          ({{$_modele->_utilisations}})
          <i class="fa fa-star" style="color:goldenrod;"></i>
        </div>
      {{/if}}
      <div style="display: none;" class="id">{{$_modele->_id}}</div>
    </li>
  {{/foreach}}
  {{if $modele_vierge}}
    <li>
      <div>
        {{tr}}CCompteRendu-blank_modele{{/tr}}
      </div>
      <div style="display: none;" class="id">0</div>
    </li>
  {{/if}}
</ul>
