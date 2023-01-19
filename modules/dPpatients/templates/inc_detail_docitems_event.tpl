{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $object->_nb_docs}}{{$object->_nb_docs}} Doc.{{/if}}
{{if $object->_nb_files}}{{if $object->_nb_docs}}, {{/if}}{{$object->_nb_files}} Fichier(s){{/if}}
{{if $object->_nb_forms}}{{if $object->_nb_docs || $object->_nb_files}}, {{/if}}{{$object->_nb_forms}} Form.{{/if}}