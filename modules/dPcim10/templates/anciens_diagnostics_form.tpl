{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=da_delete value=false}}
{{mb_default var=da_delete value=false}}

<form name="{{$object->_guid}}-cim-{{$code_type}}" action="" method="post">
  {{*Formulaire ajout-suppression de codeCIM à l'objet*}}
  {{mb_class object=$object}}
  {{mb_key object=$object}}
  {{mb_field object=$object field=$code_type hidden=true}}
</form>
