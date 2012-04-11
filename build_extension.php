<?php
define('LF', "\n");
define('TYPO3_OS', 'Linux');

class Tx_T3xtools_Base {


    /**
     * Make upload array out of extension
     *
     * @param   string      Extension key
     * @param   array       Extension information array
     * @return  mixed       Returns array with extension upload array on success, otherwise an error string.
     * @formallyknownas makeUploadarray()
     */
    protected function compileT3xData($extensionKey, $extensionPath) {

        // Get files for extension:
        $fileArr = array();
        $fileArr = $this->getAllFilesAndFoldersInPath($fileArr, $extensionPath, 99);
        // Calculate the total size of those files:
        $totalSize = 0;
        foreach ($fileArr as $file) {
            $totalSize += filesize($file);
        }

        // If the total size is less than the upper limit, proceed:
        // TODO: do we need this?
        if (1 == 1 || $totalSize < $this->maxUploadSize) {

            // Initialize output array:
            $uploadArray = array();
            $uploadArray['extKey'] = $extensionKey;
            $uploadArray['EM_CONF'] = $this->getExtEmConf($extensionKey, $extensionPath);
            $uploadArray['misc']['codelines'] = 0;
            $uploadArray['misc']['codebytes'] = 0;

            // TODO: do we need this? $uploadArray['techInfo'] = $this->install->makeDetailedExtensionAnalysis($extensionKey, $conf, 1);

            // Read all files:
            foreach ($fileArr as $file) {
                $relFileName = substr($file, strlen($extensionPath));
                $fI = pathinfo($relFileName);
                if ($relFileName != 'ext_emconf.php') { // This file should be dynamically written...
                    $uploadArray['FILES'][$relFileName] = array(
                        'name' => $relFileName,
                        'size' => filesize($file),
                        'mtime' => filemtime($file),
                        'is_executable' => (TYPO3_OS == 'WIN' ? 0 : is_executable($file)),
                        'content' => file_get_contents($file)
                    );
                    if (isset($fI['extension']) && in_array(strtolower($fI['extension']), array('php', 'inc'))) {
                        $uploadArray['FILES'][$relFileName]['codelines'] = count(explode(LF, $uploadArray['FILES'][$relFileName]['content']));
                        $uploadArray['misc']['codelines'] += $uploadArray['FILES'][$relFileName]['codelines'];
                        $uploadArray['misc']['codebytes'] += $uploadArray['FILES'][$relFileName]['size'];
                    }
                    $uploadArray['FILES'][$relFileName]['content_md5'] = md5($uploadArray['FILES'][$relFileName]['content']);
                }
            }

            // Return upload-array:
            return $uploadArray;
        } else {
            // return ERROR:makeUploadArray_error_size;
        }
    }
    
    
    /**
     * loads the ext_emconf.php file in a variable
     */
    protected function getExtEmConf($extensionKey, $extensionPath) {
        $EM_CONF = array();
        $_EXTKEY = $extensionKey;
        include($extensionPath . 'ext_emconf.php');
        return $EM_CONF[$_EXTKEY];
    }




    /** helpers, copied from t3lib_div **/
    /**
     * Recursively gather all files and folders of a path.
     * Usage: 5
     *
     * @param   array       $fileArr: Empty input array (will have files added to it)
     * @param   string      $path: The path to read recursively from (absolute) (include trailing slash!)
     * @param   integer     $recursivityLevels: The number of levels to dig down...
     * @return  array       An array with the found files/directories.
     */
    protected function getAllFilesAndFoldersInPath(array $fileArr, $path, $recursivityLevels = 99) {
        $fileArr = array_merge($fileArr, $this->getFilesInDir($path, 1));
        $dirs = $this->getDirectories($path);
        if (is_array($dirs) && $recursivityLevels > 0) {
            foreach ($dirs as $subdirs) {
                if ((string) $subdirs != '' && (!strlen($this->excludeFilePatterns) || !preg_match('/^' . $this->excludeFilePatterns . '$/', $subdirs))) {
                    $fileArr = $this->getAllFilesAndFoldersInPath($fileArr, $path . $subdirs . '/', $recursivityLevels - 1);
                }
            }
        }
        return $fileArr;
    }



    /**
     * Returns an array with the names of folders in a specific path
     * Will return 'error' (string) if there were an error with reading directory content.
     *
     * @param   string      Path to list directories from
     * @return  array       Returns an array with the directory entries as values. If no path, the return value is nothing.
     * @formallyknownas t3lib_div::get_dirs
     */
    protected function getDirectories($path) {
        if ($path) {
            if (is_dir($path)) {
                $dir = scandir($path);
                $dirs = array();
                foreach ($dir as $entry) {
                    if (is_dir($path . '/' . $entry) && $entry != '..' && $entry != '.') {
                        $dirs[] = $entry;
                    }
                }
            } else {
                $dirs = 'error';
            }
        }
        return $dirs;
    }

    /**
     * Returns an array with the names of files in a specific path
     * Usage: 18
     *
     * @param   string      $path: Is the path to the file
     * @param   string      $extensionList is the comma list of extensions to read only (blank = all)
     * @param   boolean     If set, then the path is prepended the filenames. Otherwise only the filenames are returned in the array
     * @return  array       Array of the files found
     */
    protected function getFilesInDir($path, $prependPath = 0) {

            // Initialize variabels:
        $filearray = array();
        $sortarray = array();
        $path = rtrim($path, '/');

            // Find files+directories:
        if (@is_dir($path)) {
            $d = dir($path);
            if (is_object($d)) {
                while ($entry = $d->read()) {
                    if (@is_file($path . '/' . $entry)) {
                        $fI = pathinfo($entry);
                        $key = md5($path . '/' . $entry); // Don't change this ever - extensions may depend on the fact that the hash is an md5 of the path! (import/export extension)
                        if (!strlen($this->excludeFilePatterns) || !preg_match('/^' . $this->excludeFilePatterns . '$/', $entry)) {
                            $filearray[$key] = ($prependPath ? $path . '/' : '') . $entry;
                            $sortarray[$key] = $entry;
                        }
                    }
                }
                $d->close();
            } else {
                return 'error opening path: "' . $path . '"';
            }
        }

            // Sort them:
        asort($sortarray);
        $newArr = array();
        foreach ($sortarray as $k => $v) {
            $newArr[$k] = $filearray[$k];
        }
        $filearray = $newArr;

            // Return result
        reset($filearray);
        return $filearray;
    }


    /**
     * Wrapper function for rmdir, allowing recursive deletion of folders and files
     *
     * @param   string      Absolute path to folder, see PHP rmdir() function. Removes trailing slash internally.
     * @param   boolean     Allow deletion of non-empty directories
     * @return  boolean     true if @rmdir went well!
     */
    protected function rmdir($path, $removeNonEmpty = FALSE) {
        $OK = FALSE;
        $path = preg_replace('|/$|', '', $path); // Remove trailing slash

        if (file_exists($path)) {
            $OK = TRUE;

            if (is_dir($path)) {
                if ($removeNonEmpty == TRUE && $handle = opendir($path)) {
                    while ($OK && FALSE !== ($file = readdir($handle))) {
                        if ($file == '.' || $file == '..') {
                            continue;
                        }
                        $OK = $this->rmdir($path . '/' . $file, $removeNonEmpty);
                    }
                    closedir($handle);
                }
                if ($OK) {
                    $OK = rmdir($path);
                }

            } else { // If $dirname is a file, simply remove it
                $OK = unlink($path);
            }

            clearstatcache();
        }

        return $OK;
    }

    /**
     * Creates a directory - including parent directories if necessary - in the file system
     *
     * @param   string      Base folder. This must exist! Must have trailing slash! Example "/root/typo3site/"
     * @param   string      Deep directory to create, eg. "xx/yy/" which creates "/root/typo3site/xx/yy/" if $destination is "/root/typo3site/"
     * @return  string      If error, returns error string.
     */
    protected function mkdir_deep($destination, $deepDir) {
        $allParts = $this->trimExplode('/', $deepDir, 1);
        $root = '';
        foreach ($allParts as $part) {
            $root .= $part . '/';
            if (!is_dir($destination . $root)) {
                mkdir($destination . $root);
                if (!@is_dir($destination . $root)) {
                    return 'Error: The directory "' . $destination . $root . '" could not be created...';
                }
            }
        }
    }

    /**
     * Extracts the directories in the $files array
     *
     * @param   array       Array of files / directories
     * @return  array       Array of directories from the input array.
     * @see tx_em_tools::extractDirsFromFileList
     */
    protected function extractDirsFromFileList($files) {
        $dirs = array();

        if (is_array($files)) {
                // Traverse files / directories array:
            foreach ($files as $file) {
                if (substr($file, -1) == '/') {
                    $dirs[$file] = $file;
                } else {
                    $pI = pathinfo($file);
                    if (strcmp($pI['dirname'], '') && strcmp($pI['dirname'], '.')) {
                        $dirs[$pI['dirname'] . '/'] = $pI['dirname'] . '/';
                    }
                }
            }
        }
        return $dirs;
    }


    /**
     * Creates directories in $extDirPath
     *
     * @param   array       Array of directories to create relative to extDirPath, eg. "blabla", "blabla/blabla" etc...
     * @param   string      Absolute path to directory.
     * @return  mixed       Returns false on success or an error string
     * @see tx_em_tools::createDirsInPath
     */
    protected function createDirsInPath($dirs, $extDirPath) {
        if (is_array($dirs)) {
            foreach ($dirs as $dir) {
                $error = $this->mkdir_deep($extDirPath, $dir);
                if ($error) {
                    return $error;
                }
            }
        }

        return FALSE;
    }

    /**
     * Explodes a string and trims all values for whitespace in the ends.
     * If $onlyNonEmptyValues is set, then all blank ('') values are removed.
     * Usage: 256
     *
     * @param   string      Delimiter string to explode with
     * @param   string      The string to explode
     * @param   boolean     If set, all empty values will be removed in output
     * @param   integer     If positive, the result will contain a maximum of
     *                       $limit elements, if negative, all components except
     *                       the last -$limit are returned, if zero (default),
     *                       the result is not limited at all. Attention though
     *                       that the use of this parameter can slow down this
     *                       function.
     * @return  array       Exploded values
     */
    protected function trimExplode($delim, $string, $removeEmptyValues = FALSE, $limit = 0) {
        $explodedValues = explode($delim, $string);

        $result = array_map('trim', $explodedValues);

        if ($removeEmptyValues) {
            $temp = array();
            foreach ($result as $value) {
                if ($value !== '') {
                    $temp[] = $value;
                }
            }
            $result = $temp;
        }

        if ($limit != 0) {
            if ($limit < 0) {
                $result = array_slice($result, 0, $limit);
            } elseif (count($result) > $limit) {
                $lastElements = array_slice($result, $limit - 1);
                $result = array_slice($result, 0, $limit - 1);
                $result[] = implode($delim, $lastElements);
            }
        }

        return $result;
    }


    /**
     * Parses the version number x.x.x and returns an array with the various parts.
     *
     * @param   string      Version code, x.x.x
     * @param   string      Increase version part: "main", "sub", "dev"
     * @return  string
     */
    protected function renderVersion($v, $raise = '') {
        $parts = $this->intExplode('.', $v . '..');
        $parts[0] = $this->intInRange($parts[0], 0, 999);
        $parts[1] = $this->intInRange($parts[1], 0, 999);
        $parts[2] = $this->intInRange($parts[2], 0, 999);

        switch ((string) $raise) {
            case 'main':
                $parts[0]++;
                $parts[1] = 0;
                $parts[2] = 0;
                break;
            case 'sub':
                $parts[1]++;
                $parts[2] = 0;
                break;
            case 'dev':
                $parts[2]++;
                break;
        }

        $res = array();
        $res['version'] = $parts[0] . '.' . $parts[1] . '.' . $parts[2];
        $res['version_int'] = intval($parts[0] * 1000000 + $parts[1] * 1000 + $parts[2]);
        $res['version_main'] = $parts[0];
        $res['version_sub'] = $parts[1];
        $res['version_dev'] = $parts[2];

        return $res;
    }


    /**
     * Enter description here...
     *
     * @param   unknown_type        $array
     * @param   unknown_type        $lines
     * @param   unknown_type        $level
     * @return  unknown
     */
    protected function arrayToCode($array, $level = 0) {
        $lines = 'array(' . LF;
        $level++;
        foreach ($array as $k => $v) {
            if (strlen($k) && is_array($v)) {
                $lines .= str_repeat(TAB, $level) . "'" . $k . "' => " . $this->arrayToCode($v, $level);
            } elseif (strlen($k)) {
                $lines .= str_repeat(TAB, $level) . "'" . $k . "' => " . ($this->testInt($v) ? intval($v) : "'" . $this->slashJS(trim($v), 1) . "'") . ',' . LF;
            }
        }

        $lines .= str_repeat(TAB, $level - 1) . ')' . ($level - 1 == 0 ? '' : ',' . LF);
        return $lines;
    }



    /**
     * Forces the integer $theInt into the boundaries of $min and $max. If the $theInt is 'false' then the $zeroValue is applied.
     * Usage: 224
     *
     * @param   integer     Input value
     * @param   integer     Lower limit
     * @param   integer     Higher limit
     * @param   integer     Default value if input is false.
     * @return  integer     The input value forced into the boundaries of $min and $max
     */
    protected function intInRange($theInt, $min, $max = 2000000000, $zeroValue = 0) {
            // Returns $theInt as an integer in the integerspace from $min to $max
        $theInt = intval($theInt);
        if ($zeroValue && !$theInt) {
            $theInt = $zeroValue;
        } // If the input value is zero after being converted to integer, zeroValue may set another default value for it.
        $theInt = min($min, $theInt);
        if ($theInt < $min) {
            $theInt = $min;
        }
        if ($theInt > $max) {
            $theInt = $max;
        }
        return $theInt;
    }


    /**
     * This function is used to escape any ' -characters when transferring text to JavaScript!
     * Usage: 3
     *
     * @param   string      String to escape
     * @param   boolean     If set, also backslashes are escaped.
     * @param   string      The character to escape, default is ' (single-quote)
     * @return  string      Processed input string
     */
    protected function slashJS($string, $extended = 0, $char = "'") {
        if ($extended) {
            $string = str_replace("\\", "\\\\", $string);
        }
        return str_replace($char, "\\" . $char, $string);
    }


    /**
     * Tests if the input can be interpreted as integer.
     *
     * @param mixed Any input variable to test
     * @return boolean Returns true if string is an integer
     */
    protected function testInt($var) {
        if ($var === '') {
            return FALSE;
        }
        return (string) intval($var) === (string) $var;
    }

    /**
     * Explodes a $string delimited by $delim and passes each item in the array through intval().
     * Corresponds to t3lib_div::trimExplode(), but with conversion to integers for all values.
     * Usage: 76
     *
     * @param   string      Delimiter string to explode with
     * @param   string      The string to explode
     * @param   boolean     If set, all empty values (='') will NOT be set in output
     * @param   integer     If positive, the result will contain a maximum of limit elements,
     *                       if negative, all components except the last -limit are returned,
     *                       if zero (default), the result is not limited at all
     * @return  array       Exploded values, all converted to integers
     */
    protected function intExplode($delimiter, $string, $onlyNonEmptyValues = FALSE, $limit = 0) {
        $explodedValues = $this->trimExplode($delimiter, $string, $onlyNonEmptyValues, $limit);
        return array_map('intval', $explodedValues);
    }
}

class Tx_T3xtools_Create extends Tx_T3xtools_Base
{
    protected $excludeFilePatterns = '(CVS|.svn|\..*|.*~|.*\.bak)';

    /**
     * high level function 
     *
     * @param   $extkey the name of the extension
     * @param   $pathToExtract  the path where the extension filename should be generated
     */
    public function createT3xFile($extensionKey, $pathToExtensionFiles, $targetDirectory = NULL) {
        $t3xData = $this->compileT3xData($extensionKey, $pathToExtensionFiles);
        if (is_array($t3xData)) {
            $t3xContent = $this->compressOutputDataFromT3xData($t3xData);
            $filename = 'T3X_' . $extensionKey . '-' . str_replace('.', '_', $t3xData['EM_CONF']['version']) . '-z-' . date('YmdHi') . '.t3x';
            if ($targetDirectory) {
                if (is_dir($targetDirectory)) {
                    $fullFile = rtrim($targetDirectory, '/') . $filename;
                } else {
                    $fullFile = rtrim($targetDirectory, '/');
                }
                file_put_contents($fullFile, $t3xContent);
                return TRUE;
            } else {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=' . $filename);
                echo $t3xContent;
                exit;
            }
        }
    }


    /**
     * serializes and gzcompresses the data,
     * adds some md5 hash to the file
     * 
     * @param array $t3xData all data needed for a .t3x file in an array
     * @return string the content that can be written to a .t3x file
     * @formallyknownas makeUploadDataFromarray()
     */
    protected function compressOutputDataFromT3xData(array $t3xData)
    {
        $serializedData = serialize($t3xData);
        $md5sum = md5($serializedData);
        return $md5sum . ':gzcompress:' . gzcompress($serializedData);
    }
}

$t3ext = new Tx_T3xtools_Create();
$t3ext->createT3XFile("doctrine2", __DIR__ . "/build/tx_doctrine2/", __DIR__ . "/build/");

