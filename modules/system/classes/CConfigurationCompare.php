<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CMbString;
use Ox\Core\Module\CModule;

class CConfigurationCompare implements IShortNameAutoloadable
{
    protected $result_instance = [];
    protected $result_groups   = [];
    protected $result          = [];
    /** @var DOMXPath */
    protected $xpath;
    protected $file;
    protected $files_names = [];

    protected static $xpath_queries = [
        'check_file_type'  => '/configurations-export',
        'configs_instance' => [
            'root'   => 'instance-configs',
            'config' => 'instance-config',
        ],
        'configs_groups'   => [
            'root'    => 'groups-configs',
            'modules' => 'module-config',
            'configs' => 'config',

        ],
    ];

    /**
     * Compare all the XML conf files from a directory
     *
     * @param array $upload_files Directory to search files in
     *
     * @return void
     */
    public function compare($upload_files)
    {
        if (!$upload_files) {
            CAppUI::commonError('CConfigurationCompare-error file not exists');
        }

        foreach ($upload_files['tmp_name'] as $_idx => $_file) {
            if (!file_exists($_file)) {
                CAppUI::commonError('CConfigurationCompare-error file does not exists');
            }

            $this->file          = $upload_files['name'][$_idx];
            $this->files_names[] = static::unsanitizeString($this->file);
            $this->getConfigurations($_file);
        }
    }


    /**
     * Call the medthod to get all the configurations of a kind
     *
     * @param string $file_path Path to the file to parse
     *
     * @return void
     */
    protected function getConfigurations($file_path)
    {
        $this->openXMLFile($file_path);

        if (!$this->isFileValid()) {
            return;
        }

        $this->getInstanceConfigs();
        $this->getGroupsConfigs();

        return;
    }

    /**
     * Open an XML file
     *
     * @param string $file_path Path to the file
     *
     * @return void
     */
    protected function openXMLFile($file_path)
    {
        $dom = new DOMDocument();

        $xml = file_get_contents($file_path);

        // Suppression des caractères invalides pour DOMDocument
        $xml = CMbString::convertHTMLToXMLEntities($xml);

        $dom->loadXML($xml);

        $this->xpath = new DOMXPath($dom);
    }

    /**
     * Check if a file id valid for comparison or not (if it contains the element get by
     * $xpath_queries['check_file_type'])
     *
     * @return bool
     */
    protected function isFileValid()
    {
        $node = $this->xpath->query(static::$xpath_queries['check_file_type']);

        return $node->length > 0;
    }

    /**
     * Get all the instance configurations and put them into $this->result_instance
     *
     * @return void
     */
    protected function getInstanceConfigs()
    {
        $instance_root = $this->xpath->query(static::$xpath_queries['configs_instance']['root']);
        if ($instance_root->length == 0) {
            return;
        }

        $instance_configs = $this->xpath->query(
            static::$xpath_queries['configs_instance']['config'],
            $instance_root->item(0)
        );

        if ($instance_configs->length == 0) {
            return;
        }

        /** @var DOMElement $_config */
        foreach ($instance_configs as $_config) {
            $feature = static::unsanitizeString($_config->getAttribute('feature'));
            $value   = static::unsanitizeString($_config->getAttribute('value'));
            $parts   = explode(' ', $feature);

            if (count($parts) == 1 || !CModule::exists($parts[0])) {
                if (!array_key_exists('none', $this->result)) {
                    $this->result['none'] = [
                        'instance' => [],
                        'groups'   => [],
                        'trad'     => CAppUI::tr("None"),
                    ];
                }

                if (!array_key_exists($feature, $this->result['none']['instance'])) {
                    $this->result['none']['instance'][$feature] = [];
                }

                if (!array_key_exists($this->file, $this->result['none']['instance'][$feature])) {
                    $this->result['none']['instance'][$feature][$this->file] = $value;
                }
            } else {
                if (!array_key_exists($parts[0], $this->result)) {
                    $this->result[$parts[0]] = [
                        'instance' => [],
                        'groups'   => [],
                        'trad'     => CAppUI::tr("module-{$parts[0]}-court"),
                    ];
                }

                if (!array_key_exists($feature, $this->result[$parts[0]]['instance'])) {
                    $this->result[$parts[0]]['instance'][$feature] = [];
                }

                if (!array_key_exists($this->file, $this->result[$parts[0]]['instance'][$feature])) {
                    $this->result[$parts[0]]['instance'][$feature][$this->file] = $value;
                }
            }
        }
    }

    /**
     * Get all the groups configs and put them into $this->result_groups
     *
     * @return void
     */
    protected function getGroupsConfigs()
    {
        $groups_root = $this->xpath->query(static::$xpath_queries['configs_groups']['root']);
        if ($groups_root->length == 0) {
            return;
        }

        $modules = $this->xpath->query(static::$xpath_queries['configs_groups']['modules'], $groups_root->item(0));
        if ($modules->length == 0) {
            return;
        }

        /** @var DOMElement $_module */
        foreach ($modules as $_module) {
            $mod_name = static::unsanitizeString($_module->getAttribute('mod_name'));

            if (!array_key_exists($mod_name, $this->result)) {
                $this->result[$mod_name] = [
                    'instance' => [],
                    'groups'   => [],
                    'trad'     => CAppUI::tr("module-{$mod_name}-court"),
                ];
            }

            $configs = $this->xpath->query(static::$xpath_queries['configs_groups']['configs'], $_module);

            /** @var DOMElement $_conf */
            foreach ($configs as $_conf) {
                $feature = static::unsanitizeString($_conf->getAttribute('feature'));
                $value   = static::unsanitizeString($_conf->getAttribute('value'));

                if (!array_key_exists($feature, $this->result[$mod_name]['groups'])) {
                    $this->result[$mod_name]['groups'][$feature] = [];
                }

                $this->result[$mod_name]['groups'][$feature][$this->file] = $value;
            }
        }
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Get the names of the files parsed
     *
     * @return array
     */
    public function getFilesNames()
    {
        return $this->files_names;
    }

    /**
     * Convert a string to utf8
     *
     * @param string $str String to convert
     *
     * @return string
     */
    static function sanitizeString($str)
    {
        return utf8_encode($str);
    }

    /**
     * Convert the value from utf8 to iso-8859-1
     *
     * @param string $str String to convert
     *
     * @return string
     */
    static function unsanitizeString($str)
    {
        return trim(utf8_decode($str));
    }
}
