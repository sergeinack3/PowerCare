{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>Environnement d'execution</h2>

<form name="editCIdSante400" action="?" method="post" onsubmit="return onSubmitFormAjax(this)">

  {{mb_configure module=$m}}

  <table class="form">

    <tr>
      <th class="category" colspan="100">Connexion à la source de données</th>
    </tr>

    {{assign var=m value=interop}}
    {{assign var=mod value=interop}}

    {{mb_include module=system template=inc_config_enum var=mode_compat values='|'|implode:$modes}}

    {{assign var=m value=dPsante400}}
    {{mb_include module=system template=inc_config_enum var=prefix values=odbc|mysql}}
    {{mb_include module=system template=inc_config_str var=dsn}}
    {{mb_include module=system template=inc_config_str var=other_dsn}}
    {{mb_include module=system template=inc_config_str var=user}}
    {{mb_include module=system template=inc_config_str var=pass}}

    <tr>
      <th class="category" colspan="100">Traitement des mouvements</th>
    </tr>

    {{mb_include module=system template=inc_config_str var=group_id}}
    {{mb_include module=system template=inc_config_str var=nb_rows}}
    {{mb_include module=system template=inc_config_bool var=mark_row}}

    <tr>
      <th class="category" colspan="100">Synchronisation des objets</th>
    </tr>

    {{mb_include module=system template=inc_config_str var=cache_hours}}
    {{mb_include module=system template=inc_config_bool var=fix_encoding}}

    {{assign var=class value=CSejour}}
    <tr>
      <th class="category" colspan="100">{{tr}}{{$class}}{{/tr}}</th>
    </tr>

    {{mb_include module=system template=inc_config_str var=sibling_hours}}

    <tr>
      <td class="button" colspan="6">
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>

</form>