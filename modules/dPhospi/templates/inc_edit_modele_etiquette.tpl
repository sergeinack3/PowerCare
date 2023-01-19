{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  window.text_focused = null;

  Main.add(function () {
    var form = getForm("edit_etiq");
    form.texte.observe("focus", function (e) {
      window.text_focused = e.target;
    });
    form.texte_2.observe("focus", function (e) {
      window.text_focused = e.target;
    });
    form.texte_3.observe("focus", function (e) {
      window.text_focused = e.target;
    });
    form.texte_4.observe("focus", function (e) {
      window.text_focused = e.target;
    });

    Control.Tabs.create("fields_tabs");
  });
</script>

<form name="edit_etiq" onsubmit="return ModeleEtiquette.onSubmit(this);" method="post">
  {{mb_class object=$modele_etiquette}}
  {{mb_key   object=$modele_etiquette}}
  <input type="hidden" name="group_id" value="{{$modele_etiquette->group_id}}" />
  
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$modele_etiquette colspan=4}}

    <tr>
      <th class="category" colspan="4">
        {{tr}}CModeleEtiquette.main_fields{{/tr}}
      </th>
    </tr>
    <tr>
      <th>
        {{mb_label object=$modele_etiquette field=nom}}
      </th>
      <td>
        {{mb_field object=$modele_etiquette field=nom}}
      </td>
      <th>
        {{mb_label object=$modele_etiquette field=object_class}}
      </th>
      <td>
        <select name="object_class" class="{{$modele_etiquette->_props.object_class}}">
          <option value="">&mdash; {{tr}}CModeleEtiquette-object_class-select{{/tr}}</option>
          {{foreach from=$classes|smarty:nodefaults key=_class item=_class_tr}}
          <option value="{{$_class}}" {{if $_class == $modele_etiquette->object_class}}selected{{/if}}>
            {{tr}}{{$_class}}{{/tr}}
          </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    
    <!-- Formattage de la page et des étiquettes-->
    <tr>
      <th class="category" colspan="4">
        {{tr}}CModeleEtiquette.format{{/tr}}
      </th>
    </tr>
    <tr>
      <th style="width: 20%;">
        {{mb_label object=$modele_etiquette field=largeur_page}}
      </th>
      <td style="width: 30%;">
        {{mb_field object=$modele_etiquette field=largeur_page}} cm
      </td>
      <th style="width: 20%;">
        {{mb_label object=$modele_etiquette field=hauteur_page}}
      </th>
      <td style="width: 30%;">
        {{mb_field object=$modele_etiquette field=hauteur_page}} cm
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$modele_etiquette field=marge_horiz}}
      </th>
      <td>
        {{mb_field object=$modele_etiquette field=marge_horiz}} cm
      </td>
      <th>
        {{mb_label object=$modele_etiquette field=marge_vert}}
      </th>
      <td>
        {{mb_field object=$modele_etiquette field=marge_vert}} cm
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$modele_etiquette field=marge_horiz_etiq}}
      </th>
      <td>
        {{mb_field object=$modele_etiquette field=marge_horiz_etiq}} cm
      </td>
      <th>
        {{mb_label object=$modele_etiquette field=marge_vert_etiq}}
      </th>
      <td>
        {{mb_field object=$modele_etiquette field=marge_vert_etiq}} cm
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$modele_etiquette field=nb_lignes}}
      </th>
      <td>
        {{mb_field object=$modele_etiquette field=nb_lignes}}
      </td>
      <th>
        {{mb_label object=$modele_etiquette field=nb_colonnes}}
      </th>
      <td>
        {{mb_field object=$modele_etiquette field=nb_colonnes}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$modele_etiquette field=hauteur_ligne}}
      </th>
      <td>
        {{mb_field object=$modele_etiquette field=hauteur_ligne}}
      </td>
      <th>
        <b>{{mb_label object=$modele_etiquette field=font}}</b>
      </th>
      <td>
        <select name="font">
          <option value="">&mdash; {{tr}}CModeleEtiquette.choose_font{{/tr}} </option>
          {{foreach from=$listfonts key=_font item=_font_name}}
          <option value="{{$_font}}" {{if $_font == $modele_etiquette->font}}selected{{/if}}>{{$_font_name}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$modele_etiquette field=show_border}}
      </th>
      <td>
        {{mb_field object=$modele_etiquette field=show_border}}
      </td>
      <th>
        {{mb_label object=$modele_etiquette field=text_align}}
      </th>
      <td colspan="3">
        {{mb_field object=$modele_etiquette field=text_align typeEnum=radio}}
      </td>
    </tr>

    <tr>
      <th class="category" colspan="4">
        {{tr}}CModeleEtiquette.content{{/tr}}
      </th>
    </tr>
    
    <tr>
      <!-- Contenu principal de l'étiquette -->
      <td colspan="2">
        <fieldset class="me-no-box-shadow">
          <legend>{{mb_label object=$modele_etiquette field=texte}}</legend>
          {{mb_field object=$modele_etiquette field=texte rows=4}}
        </fieldset>
      </td>
      <!-- Contenu seconde colonne optionnelle -->
      <td colspan="2">
        <fieldset class="me-no-box-shadow">
          <legend>{{mb_label object=$modele_etiquette field=texte_2}}</legend>
          {{mb_field object=$modele_etiquette field=texte_2 rows=4}}
        </fieldset>
      </td>
    </tr>
    <tr>
      <!-- Contenu troisième colonne optionnelle -->
      <td colspan="2">
        <fieldset class="me-no-box-shadow">
          <legend>{{mb_label object=$modele_etiquette field=texte_3}}</legend>
          {{mb_field object=$modele_etiquette field=texte_3 rows=4}}
        </fieldset>
      </td>
      <!-- Contenu quatrième colonne optionnelle -->
      <td colspan="2">
        <fieldset class="me-no-box-shadow">
          <legend>{{mb_label object=$modele_etiquette field=texte_4}}</legend>
          {{mb_field object=$modele_etiquette field=texte_4  rows=4}}
        </fieldset>
      </td>
    </tr>
  </table>

  <!-- Liste des champs disponibles -->
  <table class="form">
    <tr>
      <th class="category" colspan="6">{{tr}}CModeleEtiquette.fields{{/tr}}</th>
    </tr>
    <tr>
      <th>{{mb_label object=$modele_etiquette field="_write_bold"}}</th>
      <td>{{mb_field object=$modele_etiquette field="_write_bold" typeEnum="radio"}}
      <th>{{mb_label object=$modele_etiquette field="_write_upper"}}</th>
      <td>{{mb_field object=$modele_etiquette field="_write_upper" typeEnum="radio"}}
      <th>{{mb_label object=$modele_etiquette field="_field_size"}}</th>
      <td>{{mb_field object=$modele_etiquette field="_field_size" value=$modele_etiquette->hauteur_ligne increment=1 form="edit_etiq"}}</td>
    </tr>
    <tr>
      <td colspan="6">
        <ul id="fields_tabs" class="control_tabs">
          {{foreach from=$fields key=_class item=_by_class}}
          <li>
            <a href="#fields_{{$_class}}">{{tr}}{{$_class}}{{/tr}}</a>
          </li>
          {{/foreach}}
        </ul>
        {{foreach from=$fields key=_class item=_by_class}}
        <div id="fields_{{$_class}}" class="text">
          <br class="me-no-display" />
          {{foreach from=$_by_class item=_field}}
          <button class="add me-margin-4 me-secondary" type="button" value="{{$_field}}"
                  onclick="ModeleEtiquette.insertField(this);">{{$_field|lower}}</button>
          {{/foreach}}
        </div>
        {{/foreach}}
      </td>
    </tr>
  </table>

  <div class="small-warning">
    <div>{{tr}}CModeleEtiquette-warning-taille_reelle{{/tr}}</div>
    <strong>{{tr}}CModeleEtiquette-warning-aucun_ajustement{{/tr}}</strong>
  </div>

  <table class="form">
    <tr>
      <td colspan="4" style="text-align: center;">
        <button class="search" type="button" onclick="if (checkForm(this.form)){ModeleEtiquette.preview();
}">
          {{tr}}Preview{{/tr}}
        </button>
        <button id="edit_etiq_modify" class="modify me-primary">
          {{tr}}Save{{/tr}}
        </button>
        <button class="cancel" onclick="ModeleEtiquette.confirmDeletion(this.form);">
          {{tr}}Delete{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>

<!-- Formulaire de téléchargement du PDF d'aperçu des étiquettes -->
<form name="download_prev" method="post" target="_blank" action="?m=hospi&raw=print_modele_etiquette">
  <input type="hidden" name="largeur_page" />
  <input type="hidden" name="hauteur_page" />
  <input type="hidden" name="nb_lignes" />
  <input type="hidden" name="nb_colonnes" />
  <input type="hidden" name="marge_horiz" />
  <input type="hidden" name="marge_vert" />
  <input type="hidden" name="marge_horiz_etiq" />
  <input type="hidden" name="marge_vert_etiq" />
  <input type="hidden" name="hauteur_ligne" />
  <input type="hidden" name="nom" />
  <input type="hidden" name="texte" />
  <input type="hidden" name="texte_2" />
  <input type="hidden" name="texte_3" />
  <input type="hidden" name="texte_4" />
  <input type="hidden" name="font" />
  <input type="hidden" name="show_border" />
  <input type="hidden" name="text_align" />
</form>