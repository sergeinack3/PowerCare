{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create('tab-examen');
  });
</script>

<form name="editExamen" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_class object=$examen}}
  {{mb_key   object=$examen}}
  <input type="hidden" name="_locked" value="{{$examen->_locked}}" />
  <input type="hidden" name="del" value="0" />

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$examen}}
  </table>

  <ul id="tab-examen" class="control_tabs">
    <li><a href="#infos">{{tr}}mod-dPlabo-inc-acc_infos{{/tr}}</a></li>
    <li><a href="#realisation">{{tr}}mod-dPlabo-inc-acc_realisation{{/tr}}</a></li>
    <li><a href="#conservation">{{tr}}mod-dPlabo-inc-acc_conservation{{/tr}}</a></li>
  </ul>

  <div id="infos" style="display: none;">{{mb_include module=labo template=inc_examen/acc_infos}}</div>
  <div id="realisation" style="display: none;">{{mb_include module=labo template=inc_examen/acc_realisation}}</div>
  <div id="conservation" style="display: none;">{{mb_include module=labo template=inc_examen/acc_conservation}}</div>

  <table class="form">
    {{mb_include module=system template=inc_form_table_footer object=$examen
                 options="{typeName: \$T('CExamenLabo'), objName: '`$examen->_view`'}"
                 options_ajax="Control.Modal.close"}}
  </table>
</form>

<!-- Liste des packs associés -->
{{if $examen->_id}}
  <table class="tbl">
    <tr>
      <th class="title">Packs d'analyses associés</th>
    </tr>
    <tr>
      <th>Nom du pack</th>
    </tr>
    {{foreach from=$examen->_ref_packs_labo item=_pack}}
      <tr>
        <td>
          <a href="?m={{$m}}&tab=vw_edit_packs&pack_examens_labo_id={{$_pack->_id}}">{{$_pack}}</a>
        </td>
      </tr>
      {{foreachelse}}
      <tr><td class="empty">Analyse présente dans aucun pack</td></tr>
    {{/foreach}}
  </table>
{{/if}}

<!-- Equivalents dans d'autres catalogues -->
{{if $examen->_id}}
  <table class="tbl">
    <tr>
      <th class="title" colspan="10">Equivalents dans d'autres catalogues</th>
    </tr>

    <tr>
      <td colspan="2">
        <form name="createSibling" method="get" onsubmit="return Examen.createSibling(this)">
          <label for="catalogue_labo_id" title="Choisir un catalogue pour créer un équivalent">
            Créer un équivalent dans</label>
          <select class="notNull ref class|CCatalogueLabo" name="catalogue_labo_id">
            <option value="">&mdash; Choisir un catalogue</option>
            {{assign var="selected_id" value=$examen->catalogue_labo_id}}
            {{assign var="exclude_id" value=$examen->_ref_root_catalogue->_id}}
            {{foreach from=$listCatalogues item="_catalogue"}}
              {{mb_include module=labo template=options_catalogues}}
            {{/foreach}}
          </select>
          <button class="new">Créer</button>
        </form>
      <td>
    </tr>

    <tr>
      <th>Analyse</th>
      <th>Catalogue</th>
    </tr>
    {{foreach from=$examen->_ref_siblings item=_sibling}}
      <tr>
        <td>
          <a href="?m=labo&tab=vw_edit_examens&examen_labo_id={{$_sibling->_id}}">
            {{$_sibling}}
          </a>
        </td>
        <td>
          {{foreach from=$_sibling->_ref_catalogues item=_catalogue}}
            <strong>{{tr}}CExamen-catalogue-{{$_catalogue->_level}}{{/tr}} :</strong>
            {{$_catalogue}}
            <br />
          {{/foreach}}
        </td>
      </tr>
      {{foreachelse}}
      <tr><td colspan="2" class="empty">Absent des autres catalogues</td></tr>
    {{/foreach}}
  </table>
{{/if}}