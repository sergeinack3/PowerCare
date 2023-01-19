{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(Control.Tabs.create.curry('tabs-owner', true));
</script>

<ul id="tabs-owner" class="control_tabs">
  {{foreach from=$packs key=owner item=_packs}}
    <li>
      <a href="#owner-{{$owner}}" {{if !$_packs|@count}}class="empty"{{/if}}>
        {{$owners.$owner}} <small>({{$_packs|@count}})</small>
      </a>
    </li>
  {{/foreach}}
</ul>

<table class="tbl me-no-align">
  <tr>
    <th style="width: 12em;">{{mb_label class=CPack field=nom}}</th>
    <th style="width: 16em;">{{tr}}CPack-modeles{{/tr}}</th>
    <th style="width:  8em;">{{tr}}CPack-object_class{{/tr}}</th>
    <th style="width:  8em;">{{tr}}CPack-category_id{{/tr}}</th>
  </tr>

  {{foreach from=$packs item=packs_by_owner key=owner}}
    <tbody id="owner-{{$owner}}" style="display: none">
    {{foreach from=$packs_by_owner item=_pack}}

      {{assign var=readonly value=0}}

      {{if $_pack->_is_for_instance && !$can->admin}}
        {{assign var=readonly value=1}}
      {{/if}}

      <tr id="{{$_pack->_guid}}">
        {{assign var=header value=$_pack->_header_found}}
        {{assign var=footer value=$_pack->_footer_found}}
        <td class="text">

          {{if $_pack->fast_edit_pdf}}
            <img style="float: right;" src="modules/dPcompteRendu/fcke_plugins/mbprintPDF/images/mbprintPDF.png"/>
          {{elseif $_pack->fast_edit}}
            <img style="float: right;" src="modules/dPcompteRendu/fcke_plugins/mbprinting/images/mbprinting.png"/>
          {{/if}}
          {{if $_pack->fast_edit || $_pack->fast_edit_pdf}}
            <img style="float: right;" src="images/icons/lightning.png"/>
          {{/if}}

          {{if !$readonly}}
            <button class="edit notext" onclick="Pack.edit('{{$_pack->_id}}');">{{tr}}Modify{{/tr}}</button>
          {{/if}}

          {{$_pack}}
          <div class="compact">
            {{if $header->_id}}
              <div>
                {{tr}}CCompteRendu-header_id{{/tr}} : <span onmouseover="ObjectTooltip.createEx(this, '{{$header->_guid}}')">{{$header->nom}}</span>
              </div>
            {{/if}}
            {{if $footer->_id}}
              <div>
                {{tr}}CCompteRendu-footer_id{{/tr}} : <span onmouseover="ObjectTooltip.createEx(this, '{{$footer->_guid}}')">{{$footer->nom}}</span>
              </div>
            {{/if}}
          </div>
        </td>
        <td class="text">
          {{foreach from=$_pack->_back.modele_links item=_link name=links}}
            {{if $smarty.foreach.links.index < 5}}
              <div class="compact">{{$_link|spancate:60}}</div>
            {{/if}}
            {{foreachelse}}
            <div class="empty">{{tr}}CPack-back-modele_links.empty{{/tr}}</div>
          {{/foreach}}
          {{if $_pack->_back.modele_links|@count > 5}}
            <div class="compact">
              <strong>
                + {{math equation="x-5" x=$_pack->_back.modele_links|@count}} {{tr}}others{{/tr}}
              </strong>
            </div>
          {{/if}}
        </td>
        <td class="text">{{tr}}{{$_pack->object_class}}{{/tr}}</td>
        <td class="text">
          {{if $_pack->_ref_categorie}}
            {{tr}}{{$_pack->_ref_categorie}}{{/tr}}
          {{else}}
            &dash;
          {{/if}}
        </td>
      </tr>
      {{foreachelse}}
      <tr>
        <td colspan="10" class="empty">{{tr}}CPack.none{{/tr}}</td>
      </tr>
    {{/foreach}}
    </tbody>
  {{/foreach}}
</table>