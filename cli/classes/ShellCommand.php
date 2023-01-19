<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli;

use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

// todo methodes curl + df -i

/**
 * Class ShellCommand
 * Allows to perform shell commands on the system
 */
class ShellCommand
{
    /** @var bool Run the command in an asynchronous way */
    protected static $asynchronous_mode = false;

    /** @var integer Duration of the asynchronous commands */
    protected static $asynchronous_wait = 3;

    /** @var array $last_output Stores the output of the last command */
    public $last_output = [];

    /** @var string */
    public $last_error = '';

    /** @var bool Use or not ssh to perform command */
    private $remote_command = false;

    /** @var bool If SSH used, tell if quiet argument should be set */
    private $quiet_remote_command = false;

    /** @var string SSH username */
    private $ssh_user = "";

    /** @var string SSH Host */
    private $ssh_host = "";

    /** @var int SSH Port */
    private $ssh_port = 22;

    /** @var bool Use or not a timeout */
    public $use_timeout = false;

    /** @var int Timeout of the commands */
    public static $timeout_command = 5;

    /** @var bool */
    public $verbose = false;

    /** @var string */
    private $output_newline_character = "\n";

    /**
     * ShellCommand constructor.
     *
     * @param string $hostname    Specify the hostname if the object must perform remote commands
     * @param int    $port        Port of the remote hostname
     * @param bool   $use_timeout Use or not timeout command
     */
    public function __construct($hostname = null, $port = 22, $use_timeout = false)
    {
        $this->use_timeout = $use_timeout;
        if ($hostname) {
            $tmp_hostname = explode('@', $hostname);

            if (count($tmp_hostname) === 2) {
                $this->ssh_host       = $tmp_hostname[1];
                $this->ssh_user       = $tmp_hostname[0];
                $this->remote_command = true;
            }

            if ($port !== 22) {
                $this->ssh_port = $port;
            }
        }
    }

    /**
     * Tells if the command must be started in an asynchronous way
     *
     * @return bool
     */
    protected static function isAsynchronous()
    {
        return static::$asynchronous_mode;
    }

    /**
     * Get the number of seconds to wait before killing asynchronous process
     *
     * @return int
     */
    protected static function getAsynchronousWait()
    {
        return static::$asynchronous_wait;
    }

    /**
     * Enable the process asynchronous mode
     *
     * @param integer $wait Number of seconds before we kill the process
     *
     * @return void
     */
    protected static function setAsynchronousMode($wait = 3)
    {
        static::$asynchronous_mode = true;
        static::$asynchronous_wait = (is_integer($wait)) ? $wait : 3;
    }

    /**
     * Disable the asynchronous mode
     *
     * @return void
     */
    protected static function disableAsynchronousMode()
    {
        static::$asynchronous_mode = false;
    }

    /**
     * Write $data to the $last_output buffer.
     * Usually used to store command output results
     *
     * @param string|array $data             Data to write in the buffer
     * @param boolean      $store_raw_output Explodes or no the output
     *
     * @return void
     */
    protected function writeOutput($data, $store_raw_output = false)
    {
        $this->last_output = [];

        if (is_string($data)) {
            if (!$store_raw_output) {
                $data                = explode($this->output_newline_character, $data);
                $this->last_output[] = $data;
            } else {
                $this->last_output = $data;
            }
        }

        if (is_array($data)) {
            $this->last_output = $data;
        }
    }

    /**
     * Specify if the object has to perform it's commands through SSH or no
     *
     * @param bool $remote Use or no SSH commands
     *
     * @return void
     */
    public function useSSH($remote, bool $quiet = false)
    {
        $this->remote_command       = $remote;
        $this->quiet_remote_command = $quiet;
    }

    /**
     * Change the SSH hostname (the string must be like user@ssh_ip)
     *
     * @param string $hostname Hostname used to perform remote commands
     *
     * @return void
     */
    public function setHostname($hostname)
    {
        $tmp_hostname = explode('@', $hostname);

        if (count($tmp_hostname) === 2) {
            [$this->ssh_user, $this->ssh_host] = $tmp_hostname;
        }
    }

    /**
     * Performs a mkdir on the server
     *
     * @param string $directory                 Path to the directory to create
     * @param bool   $create_parent_directories Create intermediate directories (perform a mkdir -p)
     *
     * @return array|bool Associative array containing the return code of the command and it's output
     */
    public function mkdir($directory, $create_parent_directories = false)
    {
        if ($directory === '') {
            $this->writeOutput('Cannot create a directory, argument is empty!');

            return false;
        }

        $parent_directories_arg = ($create_parent_directories) ? '-p ' : '';

        $command = "mkdir {$parent_directories_arg}" . escapeshellarg($directory);

        return $this->runCommand($command);
    }

    /**
     * Performs a copy on the server
     *
     * @param string $from                 Source file
     * @param string $to                   Destination file
     * @param bool   $is_dir               Copy or no a directory (uses the -r option)
     * @param bool   $preserve_permissions Uses the -p option to preserve source permissions
     *
     * @return array|bool Associative array containing the return code of the command and it's output
     */
    public function cp($from, $to, $is_dir = false, $preserve_permissions = false)
    {
        if ($from === $to) {
            $this->writeOutput('Cannot copy the backup to the same location');

            return false;
        }

        $recursive_arg            = ($is_dir) ? '-r ' : '';
        $preserve_permissions_arg = ($preserve_permissions) ? '-p ' : '';

        $command = "cp -v {$recursive_arg}{$preserve_permissions_arg}" . escapeshellarg($from) . " " . escapeshellarg(
                $to
            );

        return $this->runCommand($command);
    }

    /**
     * Performs a find to list files and folders
     *
     * @param string $path                Path where to start the research
     * @param bool   $hidden_files        Display or no the found files
     * @param bool   $names_only          Display only the file names rather than the full path
     * @param bool   $print_creation_date Display the file's creation date
     * @param bool   $files_only          Only displays filename rather than absolute filepath
     * @param string $filename_to_search  File name to search (can have wildcards)
     *
     * @return array Associative array containing the return code of the command and it's output
     */
    public function find(
        $path,
        $hidden_files = false,
        $names_only = false,
        $print_creation_date = false,
        $files_only = false,
        $filename_to_search = null
    ) {
        $print_arg       = '-printf "';
        $item_filter     = '';
        $filename_filter = '';

        $this->output_newline_character = "#new_line#";

        $hidden_files_arg = ($hidden_files) ? '' : ' -not -name \'.*\' ';

        if ($names_only) {
            $print_arg .= '%f';
        } else {
            $print_arg .= '%p';
        }

        if ($print_creation_date) {
            $print_arg .= ' %TY-%Tm-%Td|%TH:%TM:%TS';
        }

        if ($files_only) {
            $item_filter = ' -type f ';
        }

        if ($filename_to_search) {
            $filename_filter = '-name ' . escapeshellarg($filename_to_search) . ' ';
        }

        $print_arg .= $this->output_newline_character . '"';

        $command = "find "
            . escapeshellarg($path)
            . ' -maxdepth 1 '
            . $item_filter
            . $filename_filter
            . $hidden_files_arg
            . $print_arg
            . ' | sort';

        if ($this->remote_command) {
            $command = escapeshellcmd($command);
        }

        $ret_code = $this->runCommand($command);

        //Removing the last entry of the array which will always be an empty one
        array_pop($ret_code['output']);

        if ($ret_code['return_code'] === 0 && ($ret_code['return_code'] === 0 && !$ret_code['error'])) {
            $output = [];
            foreach ($ret_code['output'] as $_item) {
                $line             = [];
                $exploded_line    = explode(' ', $_item);
                $line['filename'] = $exploded_line[0];

                if ($print_creation_date) {
                    $datetime_tmp = explode('|', $exploded_line[1]);

                    // Separating the decimal part of the seconds (sometime, find returns a decimal part on the seconds)
                    $seconds_exploded = explode('.', $datetime_tmp[1]);
                    $datetime_tmp[1]  = $seconds_exploded[0];

                    $datetime = implode(' ', $datetime_tmp);

                    $line['creation_date'] = $datetime;
                }
                $output[] = $line;
            }

            $ret_code['output'] = $output;
        } else {
            $ret_code['return_code'] = 1;
        }

        $this->output_newline_character = "\n";

        return $ret_code;
    }

    /**
     * Perform a ls (unused yet because find is easier to parse!)
     *
     * @param string $path           path of the directory
     * @param bool   $human_readable toggle human readability
     * @param bool   $hidden_files   display or not the hidden files
     *
     * @return array Associative array containing the return code of the command and it's output
     */
    public function ls($path, $human_readable = false, $hidden_files = false)
    {
        $hidden_files_arg = ($hidden_files) ? '-a ' : '';

        $command = 'ls -l ' . escapeshellarg($hidden_files_arg) . escapeshellarg($path);

        $return_code = $this->runCommand($command);
        $output      = $this->last_output;
        array_shift($output);

        $new_output = [];

        foreach ($output as $_line) {
            $exploded_line = explode(' ', $_line, -1);
            $new_output[]  = $exploded_line;
        }

        $this->writeOutput($new_output);

        return $return_code;
    }

    /**
     * Performs a df on the server
     *
     * @param string $path           Path where to perform the df
     * @param bool   $human_readable Toggle the human readable sizes
     * @param int    $block_size     size of the block (used to display the sizes)
     * @param bool   $inodes         Show inodes values
     *
     * @return array|bool Associative array containing the return code of the command and it's output
     */
    public function df($path = '', $human_readable = false, $block_size = 1, $inodes = false)
    {
        $human_readable_arg = '';
        $inodes_arg         = '';
        $block_size_arg     = '-B ' . $block_size;

        if ($human_readable) {
            $human_readable_arg = '-h ';
            $block_size_arg     = '';
        }

        if ($inodes) {
            $inodes_arg = '-i ';
        }

        if ($path) {
            $path = escapeshellarg($path);
        }

        $command = 'df -P '
            . $human_readable_arg
            . $inodes_arg
            . $block_size_arg
            . ' '
            . $path
            . ' | '
            . "awk '{print $1 \" \" $6 \" \" $2 \" \" $4}'";

        $ret_object = $this->runCommand($command, true);

        if ($ret_object['return_code'] !== 0 || ($ret_object['return_code'] === 0 && $ret_object['error'])) {
            return false;
        }


        // Deleting first line of the output (columns title of the df command)
        $output = preg_replace('|^([^\r\n]*\n)|', ' ', $this->last_output);
        // Deleting line return + space to separate mount points from values on Redhat family OSes
        $output = preg_replace('|(\n\h+)|', '', $output);

        // Splitting each horizontal space and new line character into an array
        $output = preg_split('#[\h|\n]#', $output);
        //Deleting a mysterious empty first line
        array_shift($output);
        array_pop($output);

        $partitions = [];
        //Building mounts point dictionnary
        for ($i = 0; $i < count($output); $i += 4) {
            $partition    = [
                'filesystem'  => $output[$i],
                'mount_point' => $output[$i + 1],
                'total_space' => $output[$i + 2],
                'free_space'  => $output[$i + 3],
            ];
            $partitions[] = $partition;
        }

        $ret_object['output'] = $partitions;

        return $ret_object;
    }

    /**
     * Performs a df on the server
     *
     * @param string $path           Path where to perform the df
     * @param bool   $human_readable Toggle the human readable sizes
     * @param int    $block_size     size of the block (used to display the sizes)
     *
     * @return array|bool Associative array containing the return code of the command and it's output
     */
    public function df_inodes($path = '', $human_readable = false, $block_size = 1)
    {
        return $this->df($path, $human_readable, $block_size, true);
    }

    /**
     * Remove a file from the remote server
     *
     * @param string $target_to_delete File path to delete
     * @param bool   $is_dir           true if the filepath is a directory
     * @param bool   $dry_run          true if the rm command will be executed as a dry run, false to run the real
     *                                 command
     *
     * @return array|integer Associative array containing the return code of the command and it's output
     */
    public function rm($target_to_delete, $is_dir = false, $dry_run = true)
    {
        $path_to_delete = $target_to_delete;
        if (!$this->remote_command) {
            if (!file_exists($target_to_delete)) {
                echo "\"{$target_to_delete}\" does not exists";

                return 1;
            }
        }

        if (!$this->checkPathSafety($target_to_delete)) {
            return -1;
        }

        $delete_dir_arg = "";

        if ($is_dir) {
            $delete_dir_arg = 'r';
            $path_to_delete = $target_to_delete . '/';
        }


        if (!$dry_run) {
            $command = ' rm -' . $delete_dir_arg . 'f '
                . escapeshellarg($path_to_delete);
        } else {
            return $this->find($path_to_delete);
        }

        return $this->runCommand($command);
    }

    /**
     * This method is used by rm() to avoid bad things
     *
     * @param string $path Path to perform the checks
     *
     * @return bool true if the check went with no safety concerns, false otherwise
     */
    private function checkPathSafety($path)
    {
        $forbidden_dirs = [
            '/',
            '/*',
            '/bin',
            '/etc',
            '/home',
            '/boot',
            '/lib',
            '/lib32',
            '/lib64',
            '/media',
            '/mnt',
            '/opt',
            '/proc',
            '/root',
            '/run',
            '/sbin',
            '/srv',
            '/sys',
            '/tmp',
            '/usr',
            '/var',
        ];


        if (in_array($path, $forbidden_dirs)) {
            $this->writeOutput(
                "<error>I WON'T TAKE THE RESPONSIBILITY TO DELETE " . $path
                . " AS IT CAN SEVERLY DAMAGE THE SERVER!\n"
                . "Please provide a less dangerous path and perform your backup anywhere else!\n"
                . "By the way, please clear the directory where you performed your backup before doing anything else</error>"
            );
            echo "<error>I WON'T TAKE THE RESPONSIBILITY TO DELETE " . $path
                . " AS IT CAN SEVERLY DAMAGE THE SERVER!\n"
                . "Please provide a less dangerous path and perform your backup anywhere else!\n"
                . "By the way, please clear the directory where you performed your backup before doing anything else</error>";

            return false;
        }

        if ((strpos($path, '/..') !== false) || (strpos($path, '../') !== false)) {
            $this->writeOutput(
                "<error>The path (" . $path . ") you want to remove is unsafe as it contains '../' in it.<br />"
                . "It can leads to unpredictable behavior and damage the server"
            );

            return false;
        }

        return true;
    }

    /**
     * Perform a tar command to archive (and compress) a given file or directory
     *
     * @param string $archivePath        Path to the future .tar(.gz) file
     * @param string $target             Target path to the file(s) to archive (it's possible to archive a directory)
     * @param bool   $compress           Compress the tar archive or no
     * @param string $compress_algorithm Compress algorithm to use (Possible values: gz, bz2, lzma)
     * @param string $archive_from       Specify a directory where to execute the tar command
     *                                   (it can be useful to avoid absolute path within the archive)
     *
     * @return array|bool Associative array containing the return code of the command and it's output
     */
    public function tar($archivePath, $target, $compress = true, $compress_algorithm = "gz", $archive_from = null)
    {
        if (!$this->remote_command) {
            if (!file_exists($target)) {
                $this->writeOutput("{$target} path does not exists, cannot compress anything");

                return false;
            }
        }

        $command      = '';
        $tarArguments = 'cfv';
        $tarExtraArgs = '';
        $fileName     = basename($target);

        /**
         * Temporary hack before creating a string intersection function..
         */
        /*if (dirname($archivePath) !== dirname($target)) {
          $this->write_output("Cannot create archive, parent paths of the archive name and target are different");
          return false;
        }*/

        if ($compress) {
            switch ($compress_algorithm) {
                case 'gz':
                    $tarArguments .= 'z';
                    break;

                case 'bz2':
                    $tarArguments .= 'J';
                    break;

                case 'lzma':
                    $tarArguments .= 'l';
                    break;

                default:
            }
        }

        if ($archive_from) {
            if (file_exists($archive_from)) {
                $archive_from = escapeshellarg($archive_from);
                $tarExtraArgs .= '-C ' . $archive_from;
                $command      = 'cd ' . $archive_from . ' && ';
            }
        }

        $command .= 'tar '
            . $tarArguments . ' '
            . escapeshellarg($archivePath) . ' '
            . $tarExtraArgs . ' '
            . escapeshellarg($fileName);

        if ($this->remote_command) {
            $command = escapeshellcmd($command);
        }

        return $this->runCommand($command);
    }

    /**
     * Perform a tar command to unarchive (and compress) a given file or directory
     *
     * @param string $archivePath        Path to the future .tar(.gz) file
     * @param bool   $uncompress         Specify if it's required to ungzip the archive too
     * @param string $compress_algorithm Compress algorithm to use (Possible values: gz, bz2, lzma)
     * @param string $unarchive_from     Specify a directory where to execute the tar command
     *                                   (it can be useful to avoid absolute path within the archive)
     *
     * @return array|bool Associative array containing the return code of the command and it's output
     */
    public function untar($archivePath, $uncompress = true, $compress_algorithm = "gz", $unarchive_from = null)
    {
        if (!$this->remote_command) {
            if (!file_exists($archivePath)) {
                $this->writeOutput("{$archivePath} path does not exists, cannot uncompress anything");

                return false;
            }
        }

        $command      = '';
        $tarArguments = 'xfv';
        $tarExtraArgs = '';

        /**
         * Temporary hack before creating a string intersection function..
         */
        /*if (dirname($archivePath) !== dirname($target)) {
          $this->write_output("Cannot create archive, parent paths of the archive name and target are different");
          return false;
        }*/

        if ($uncompress) {
            switch ($compress_algorithm) {
                case 'gz':
                    $tarArguments .= 'z';
                    break;

                case 'bz2':
                    $tarArguments .= 'J';
                    break;

                case 'lzma':
                    $tarArguments .= 'l';
                    break;

                default:
            }
        }

        if ($unarchive_from) {
            if (file_exists($unarchive_from)) {
                $unarchive_from = escapeshellarg($unarchive_from);
                $tarExtraArgs   .= '-C ' . $unarchive_from;
                $command        = 'cd ' . $unarchive_from . ' && ';
            }
        }

        $command .= 'tar '
            . $tarArguments . ' '
            . escapeshellarg($archivePath) . ' '
            . $tarExtraArgs;

        return $this->runCommand($command);
    }

    /**
     * Changes the owner of a given file or directory
     *
     * @param string $filepath     File to change the ownership
     * @param string $owner        New owner
     * @param bool   $recursive    Set the command as recursive
     * @param bool   $change_group Change also the group (eg: chown $owner: $filepath instead of chown $owner $filepath)
     *
     * @return array|bool Associative array containing the return code of the command and it's output
     */
    public function chown($filepath, $owner, $recursive = false, $change_group = false)
    {
        if (!$this->remote_command) {
            if (!file_exists($filepath)) {
                $this->last_error = 'Cannot chown ' . $filepath . ', no such file or directory';

                return false;
            }
        }

        $recursive_arg = '';

        if ($recursive) {
            $recursive_arg = '-R ';
        }

        if ($change_group) {
            $owner .= ':';
        }

        $command = 'chown '
            . $recursive_arg . ' '
            . escapeshellarg($owner) . ' '
            . escapeshellarg($filepath);

        return $this->runCommand($command);
    }

    /**
     * Changes the group of a given file or directory
     *
     * @param string $filepath  File to change the ownership
     * @param string $group     New group
     * @param bool   $recursive Set the command as recursive
     *
     * @return array|bool Associative array containing the return code of the command and it's output
     */
    public function chgrp($filepath, $group, $recursive = false)
    {
        if (!$this->remote_command) {
            if (!file_exists($filepath)) {
                $this->last_error = 'Cannot chgrp ' . $filepath . ', no such file or directory';

                return false;
            }
        }

        $recursive_arg = '';

        if ($recursive) {
            $recursive_arg = '-R ';
        }

        $command = 'chgrp '
            . $recursive_arg . ' '
            . escapeshellarg($group) . ' '
            . escapeshellarg($filepath);

        return $this->runCommand($command);
    }

    /**
     * Changes the permissions of a given file or directory
     *
     * @param string $filepath  File to change the permissions
     * @param string $mode      New permissions (eg: '755')
     * @param bool   $recursive Performs the chmod recursively or no
     *
     * @return array|bool Associative array containing the return code of the command and it's output
     */
    public function chmod($filepath, $mode, $recursive = false)
    {
        if (!$this->remote_command) {
            if (!file_exists($filepath)) {
                $this->last_error = 'Cannot chmod ' . $filepath . ', no such file or directory';

                return false;
            }
        }

        if ($mode === '') {
            $this->last_error = 'Cannot chmod ' . $filepath . ' because no permissions are specified';

            return false;
        }

        $recursive_arg = '';

        if ($recursive) {
            $recursive_arg = '-R ';
        }

        $command = 'chmod '
            . $recursive_arg . ' '
            . escapeshellarg($mode) . ' '
            . escapeshellarg($filepath);

        return $this->runCommand($command);
    }

    /**
     * Encrypt a file with the given algorithm
     *
     * @param string $input_file           Path to the file to encrypt
     * @param string $output_file          Path to the future encrypted file
     * @param string $encryption_password  Encryption password
     * @param string $encryption_algorithm Encryption algorithm
     * @param string $mode                 Encryption mode ('encrypt' or 'decrypt')
     *
     * @return array Associative array containing the return code of the command and it's output
     */
    public function openssl(
        $input_file,
        $output_file,
        $encryption_password,
        $encryption_algorithm = "aes-256-cbc",
        $mode = 'encrypt'
    ) {
        $return_object = [
            'return_code' => -1,
            'output'      => '',
            'error'       => '',
        ];

        if (!$this->fileExists($input_file)) {
            $return_object['return_code'] = 1;
            $return_object['error']       = $input_file . " does not exists";

            return $return_object;
        }

        if (!$encryption_password) {
            $return_object['return_code'] = 1;
            $return_object['error']       = "Cannot perform encryption, password is empty";

            return $return_object;
        }

        switch ($mode) {
            case 'decrypt':
                $openssl_mode = '-d -k ' . escapeshellarg($encryption_password);
                break;

            case 'encrypt':
            default:
                $openssl_mode = '-salt -k ' . escapeshellarg($encryption_password);
        }

        $input_file_argument  = '-in ' . escapeshellarg($input_file);
        $output_file_argument = '-out ' . escapeshellarg($output_file);

        $command = "openssl "
            . escapeshellarg($encryption_algorithm) . ' '
            . $openssl_mode . ' '
            . $input_file_argument . ' '
            . $output_file_argument;


        return $this->runCommand($command);
    }

    /**
     * Tests if a file exists. The purpose of this function is not to reinvent the wheel (file_exist from php) but to
     * allow to check file existence on a remote server from php
     *
     * @param string $filepath Path to be tested
     *
     * @return bool True if the file exists, false otherwise
     */
    public function fileExists($filepath)
    {
        $command = "stat "
            . escapeshellarg($filepath);

        $returned_object = $this->runCommand($command);

        // If stat command returned something else than 0 it means the file does not exists
        if ($returned_object['return_code'] !== 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Convenience method, get the size of a given filepath
     *
     * @param string $filepath Path of the file
     *
     * @return array|bool Associative array containing the return code of the command and it's output
     */
    public function filesize($filepath)
    {
        if (!$this->remote_command) {
            if (!file_exists($filepath)) {
                $this->writeOutput($filepath . " path does not exists, cannot stat anything");

                return false;
            }
        }

        $command = 'stat --printf="%s" '
            . escapeshellarg($filepath);

        return $this->runCommand($command);
    }

    /**
     * Ping command and detailed metrics
     *
     * @param string  $host Hostname
     * @param integer $n    ECHO_REQUEST count
     *
     * @return array|bool|null
     */
    public function ping($host, $n = 4)
    {
        if (!$host) {
            return false;
        }

        if ($n < 1) {
            $n = 4;
        }

        $command = 'ping -c ' . escapeshellarg($n) . ' -q ' . escapeshellarg($host) . ' | tr -s "\n" | tail -n 1';

        $result = $this->runCommand($command);

        if (!static::checkErrors($result) || $result['error']) {
            return null;
        }

        $pattern = '#min/avg/max/mdev = (?P<min>.*)/(?P<avg>.*)/(?P<max>.*)/(?P<mdev>.*)\sms#';

        if (preg_match($pattern, reset($result['output']), $match)) {
            return [
                'min'  => $match['min'],
                'avg'  => $match['avg'],
                'max'  => $match['max'],
                'mdev' => $match['mdev'],
            ];
        }

        return null;
    }

    /**
     * Find running processes
     *
     * @param string $process Process name
     * @param bool   $count   Count active processes
     *
     * @return bool|null|array
     */
    public function pgrep($process, $count = false)
    {
        if (!$process) {
            return false;
        }

        $command = 'pgrep ';

        $command .= escapeshellarg($process);

        if ($count) {
            $command .= '|wc -l ';
        }

        $result = $this->runCommand($command);

        if (!static::checkErrors($result) || $result['error']) {
            return null;
        }

        return ($count) ? reset($result['output']) : array_filter($result['output']);
    }

    /**
     * Get system load average
     *
     * @return array|null
     */
    public function getLoadAverage()
    {
        $result = $this->runCommand('uptime');

        if (!static::checkErrors($result) || $result['error']) {
            return null;
        }

        if (preg_match(
            '/(?P<la1>[\d\.,]+),\s+(?P<la5>[\d\.,]+),\s+(?P<la10>[\d\.,]+)$/',
            reset($result['output']),
            $match
        )) {
            return [
                'la1'  => str_replace(',', '.', $match['la1']),
                'la5'  => str_replace(',', '.', $match['la5']),
                'la10' => str_replace(',', '.', $match['la10']),
            ];
        }

        return null;
    }

    /**
     * Get core number
     *
     * @return mixed|null
     */
    public function getNBCores()
    {
        $result = $this->runCommand("egrep -c '^processor' /proc/cpuinfo");

        if (!static::checkErrors($result) || $result['error']) {
            return null;
        }

        return reset($result['output']);
    }

    /**
     * Get memory statistics
     *
     * @return array|null
     */
    public function getMemoryStats()
    {
        $result = $this->runCommand('cat /proc/meminfo');

        if (!static::checkErrors($result) || $result['error']) {
            return null;
        }

        $rows = [];

        foreach ($result['output'] as $_row) {
            $_matches = [];
            if (preg_match('/([^:]+):\s+(\d+) kB/', $_row, $_matches)) {
                $rows[$_matches[1]] = $_matches[2];
            }
        }

        return $rows;
    }

    /**
     * @param string $filename
     *
     * @return array|null
     */
    public function cat(string $filename): ?array
    {
        $result = $this->runCommand('cat ' . escapeshellarg($filename));

        if (!static::checkErrors($result) || $result['error']) {
            return null;
        }

        return $result['output'];
    }

    /**
     * Function that returns a semver-like version string for a target binary, returns null if binary is not available
     * on the server
     *
     * @param string  $binary_name  Binary name or main command
     * @param integer $semver       Integer representing the precision of a target semver-like version (2 for minor, 3
     *                              for patch which is default)
     * @param string  $version      String used to fetch binary version (short, full, custom)
     * @param array   $alternatives Optional array of alternative binary names or alternative commands
     *
     * @return string|null
     */
    public function getMiddlewareVersion($binary_name, $version = 'short', $semver = 3, $alternatives = [])
    {
        switch ($semver) {
            case 2:
                $semver_pattern = "/(\d+\.)(\d)/";
                break;
            default:
                $semver_pattern = "/(\d+\.)(\d+\.)(\d)/";
                break;
        }

        switch ($version) {
            case 'short':
                $version_string = '-v';
                break;
            case 'full':
                $version_string = '--version';
                break;
            default:
                $version_string = $version;
                break;
        }

        $binary_names = array_merge(
            [$binary_name],
            $alternatives
        );

        foreach ($binary_names as $name) {
            $command = "{$name} {$version_string}";

            $result = $this->runCommand($command);
            if (!static::checkErrors($result) || $result['error']) {
                continue;
            }

            foreach ($result['output'] as $output_line) {
                if (preg_match($semver_pattern, $output_line, $match)) {
                    return reset($match);
                }
            }
        }

        return null;
    }

    /**
     * Get Apache version
     *
     * @param string $apache_bin Apache binary name
     *
     * @return bool|null
     */
    public function getApacheVersion($apache_bin)
    {
        if (!$apache_bin || !in_array($apache_bin, ['apache', 'apache2', 'http', 'httpd'])) {
            return false;
        }

        $command = "{$apache_bin} -v";

        $result = $this->runCommand($command);

        if (!static::checkErrors($result) || $result['error']) {
            return null;
        }

        $apache_version = reset($result['output']);

        if (preg_match('#Server version: Apache/(?P<version>((\d)+(\.)?)+)#', $apache_version, $match)) {
            return $match['version'];
        }

        return null;
    }

    /**
     * Get REDIS server latency (in an asynchronous way)
     *
     * @param string $host Host
     * @param string $port Post
     *
     * @return float|null
     */
    public function getRedisLatency($host, $port)
    {
        if (!$host || !$port) {
            return null;
        }

        $command = 'redis-cli --latency -h ' . escapeshellarg($host) . ' -p ' . escapeshellarg($port);

        static::setAsynchronousMode(3);
        $result = $this->runCommand($command);

        // Return code is 143 because we had to kill the process in an asynchronous mode
        if (/*!static::checkErrors($result) || */ $result['error']) {
            return null;
        }

        $output = reset($result['output']);

        // Get the last matching pattern because of multiple lines returned from STDOUT
        if (preg_match_all('/, avg: (\d+\.\d+)/', $output, $matches)) {
            $_matches = $matches[1];

            return end($_matches);
        }

        return null;
    }

    public function curl(string $url): array
    {
        $command = "curl '" . escapeshellarg($url) . "'";
        $result  = $this->runCommand($command);

        if (static::checkErrors($result)) {
            return $result['output'];
        }

        return [];
    }

    /**
     * Checks if given mount point is effectively mounted
     *
     * @param string $dir Directory name
     *
     * @return bool
     */
    public function isMounted($dir)
    {
        if (!$dir) {
            return false;
        }

        $command = "grep -s '" . escapeshellarg($dir) . "' /proc/mounts";

        $result = $this->runCommand($command);

        return (static::checkErrors($result) && !$result['error']);
    }

    /**
     * Checks if directory content is reachable
     *
     * @param string $dir Directory name
     *
     * @return bool
     */
    public function isReachable($dir)
    {
        if (!$dir) {
            return false;
        }

        $command = 'ls ' . escapeshellarg($dir) . ' /dev/null';

        $result = $this->runCommand($command);

        return (static::checkErrors($result) && !$result['error']);
    }

    /**
     * Write data to a file
     *
     * @param $filepath
     * @param $data
     * @param $append
     *
     * @return array
     */
    public function writeFile($filepath, $data, $append = false)
    {
        $command            = "echo ";
        $stream_redirection = ">";

        if ($append) {
            $stream_redirection .= ">";
        }

        $command .= escapeshellarg($data)
            . $stream_redirection . ' '
            . $filepath;

        return $this->runCommand($command);
    }

    /**
     * Builds and return the server host used to connect to a SSH shell
     *
     * @return string
     */
    protected function getSSHHost()
    {
        return "{$this->ssh_user}@{$this->ssh_host}";
    }

    /**
     * Returns a built command with optional timeout and ssh commands
     *
     * @param string $command Shell command to be built
     *
     * @return string Command built with the optional ssh and timeout linux commands
     */
    protected function buildCommand($command)
    {
        if ($this->remote_command) {
            $command = $this->getRemoteCommand($command);
        }

        if ($this->use_timeout) {
            $command = $this->getTimeoutCommand($command, static::$timeout_command);
        }

        return $command;
    }

    /**
     * Build and runs a command
     * Write the output into $last_output container and the error output in the $last_error attribute
     *
     * @param string $command          Command to be run
     * @param bool   $store_raw_output Explodes or no the output string
     *
     * @return array array containing the return code of the command, it's output (sometime parsed if needed) and it's
     *               error output
     */
    protected function runCommand($command, $store_raw_output = false)
    {
        //Builds the command and add wrappers if needed (ssh -p and timeout)
        $command = $this->buildCommand($command);
        if ($this->verbose) {
            echo "Executing command: {$command}\n";
        }

        try {
            $process = Process::fromShellCommandline($command, null, null, null, null);
        } catch (RuntimeException $e) {
            $this->writeOutput($e->getMessage());
            return [
                'return_code' => 1,
                'output'      => $this->last_output,
                'error'       => $e->getMessage(),
            ];
        }

        if (static::isAsynchronous()) {
            $process->start();
            sleep(static::getAsynchronousWait());
            $return_var = $process->stop(0);

            static::disableAsynchronousMode();
        } else {
            $return_var = $process->run();
        }

        $this->last_error = $process->getErrorOutput();

        $this->writeOutput($process->getOutput(), $store_raw_output);

        $return_object = [
            'return_code' => $return_var,
            'output'      => $this->last_output,
            'error'       => $this->last_error,
        ];

        return $return_object;
    }

    /**
     * Add ssh -p <user>@<host> to a given command
     *
     * @param string $command shell command, it must be escaped before passing to this function
     *
     * @return string modified command with ssh wrapping it
     */
    protected function getRemoteCommand(string $command): string
    {
        if (!$this->ssh_user || !$this->ssh_host) {
            echo 'Some credentials are missing, cannot perform remote command';

            return $command;
        }

        $host = $this->getSSHHost();

        $args = '-p';

        if ($this->quiet_remote_command) {
            $args = '-q -p';
        }

        return "ssh {$args} "
            . escapeshellarg($this->ssh_port) . ' '
            . escapeshellarg($host) . ' '
            . $command;
    }

    /**
     * Merci Kevin :)
     *
     * @param string  $command Command to be wrapped with timeout linux command
     * @param integer $timeout Number of seconds before we stop the command
     *
     * @return string
     */
    protected function getTimeoutCommand($command, $timeout = null)
    {
        $cmd = '';

        if ($timeout) {
            $cmd .= "timeout {$timeout} ";
        }

        $cmd .= $command;

        return $cmd;
    }

    /**
     * Check if the command that returned $return_object returned an error
     *
     * @param array $return_object Object returned by the command
     *
     * @return bool true if no error have been found, false otherwise
     */
    public static function checkErrors($return_object)
    {
        return ($return_object['return_code'] === 0);
    }
}
