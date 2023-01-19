{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="EditConfig-gui" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  <table class="form">
    <tr>
      <th class="title" colspan="2">{{tr}}Config{{/tr}}</th>
    </tr>

    {{assign var=class value=occupation_surveillance}}
    {{mb_include module=system template=inc_config_category}}
    {{mb_include module=system template=inc_config_str var=faible}}
    {{mb_include module=system template=inc_config_str var=eleve}}

    {{assign var=class value=occupation_technicien}}
    {{mb_include module=system template=inc_config_category}}
     {{mb_include module=system template=inc_config_str var=faible}}
    {{mb_include module=system template=inc_config_str var=eleve}}
    
    {{assign var=class value=repartition}}
    {{mb_include module=system template=inc_config_category}}
    {{mb_include module=system template=inc_config_bool var=show_tabs}}
    
    {{assign var=class value=recusation}}
    {{mb_include module=system template=inc_config_category}}
    {{mb_include module=system template=inc_config_bool var=sejour_readonly}}
    {{mb_include module=system template=inc_config_bool var=view_services_inactifs}}
    {{mb_include module=system template=inc_config_bool var=use_recuse}}

    {{mb_include module=system template=configure_handler class_handler=CEvenementSSRHandler}}

    <tr>
      <td class="button" colspan="2">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
