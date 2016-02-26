<?php
/** 
 * AXELS CACHE CLASS<br>
 * <br>
 * THERE IS NO WARRANTY FOR THE PROGRAM, TO THE EXTENT PERMITTED BY APPLICABLE <br>
 * LAW. EXCEPT WHEN OTHERWISE STATED IN WRITING THE COPYRIGHT HOLDERS AND/OR <br>
 * OTHER PARTIES PROVIDE THE PROGRAM ?AS IS? WITHOUT WARRANTY OF ANY KIND, <br>
 * EITHER EXPRESSED OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED <br>
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE. THE <br>
 * ENTIRE RISK AS TO THE QUALITY AND PERFORMANCE OF THE PROGRAM IS WITH YOU. <br>
 * SHOULD THE PROGRAM PROVE DEFECTIVE, YOU ASSUME THE COST OF ALL NECESSARY <br>
 * SERVICING, REPAIR OR CORRECTION.<br>
 * <br>
 * --------------------------------------------------------------------------------<br>
 * <br>
 * --- HISTORY:<br>
 * 2009-07-20  1.0  cache class on www.axel-hahn.de<br>
 * 2011-08-27  1.1  comments added; sCacheFile is private<br>
 * 2012-02-04  2.0  cache serialzable types; more methods, i.e.:<br>
 *                   - comparison of timestimp with a sourcefile<br>
 *                   - cleanup unused cachefiles<br>
 * 2012-05-15  2.1  isExpired() returns as bool; new method iExpired() to get <br>
 *                  expiration in sec<br>
 * 2014-02-27  2.2  - rename to AhCache<br>
 *                  - _cleanup checks with file_exists<br>
 * 2014-03-31  2.3  - added _setup() that to includes custom settings<br>
 *                  - limit number of files in cache directory<br>
 * --------------------------------------------------------------------------------<br>
 * @version 2.3
 * @author Axel Hahn
 * @link http://www.axel-hahn.de/php_contentcache
 * @license GPL
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL 3.0
 * @package Axels Cache
 */
class AhCache {

    /**
     * a module name string is used as relative cache path
     * @var string 
     */
    var $sModule = '';

    /**
     * id of cachefile (filename will be generated from it)
     * @var string 
     */
    var $sCacheID = '';

    /**
     * where to store all cache data - it can be outside docRoot. If it is 
     * below docRoot think about forbidding access
     * If empty it will be set in the constructor to [webroot]/~cache/
     * or $TEMP/~cache/ for CLI
     * @var string 
     */
    private $_sCacheDir = '';
    // private $_sCacheDir='/tmp/';

    /**
     * absolute filename of cache file
     * @var string 
     */
    private $_sCacheFile = '';

    /**
     * divider to limit count of cachefiles
     * @var type 
     */
    private $_sCacheDirDevider = false;
    
    /**
     * fileextension for storing cachefiles (without ".")
     * @var string
     */
    private $_sCacheExt = 'cacheclass2';

    /**
     * Expiration timestamp; 
     * It will be calculated with current time + ttl in the write() method
     * TTL can be read with getExpire()
     * @var integer
     */
    private $_tsExpire = -1;

    /**
     * TTL (time to live) in s; 
     * TTL can be set in methods setTtl($iTtl) or write($data, $iTtl)
     * TTL can be read with getTtl()
     * @var integer
     */
    private $_iTtl = -1;

    /**
     * cachedata and file infos of cachefile (returned array of php function stat)
     * @var array
     */
    private $_aCacheInfos = array();

    /* ----------------------------------------------------------------------
      constructor
      ---------------------------------------------------------------------- */

    /**
     * constructor
     * @param  string  $sModule   name of module or app that uses the cache
     * @param  string  $sCacheID  cache-id (must be uniq for a module; used to generate filename of cachefile)
     * @return boolean 
     */
    public function __construct($sModule = ".", $sCacheID = '.') {
        $this->_setup();
        if (!$sModule)
            die("ERROR: no module was given.<br>");
        if (!$sCacheID)
            die("ERROR: no id was given.<br>");

        $this->sModule = $sModule;
        $this->sCacheID = $sCacheID;

        $this->_getCacheFilename();
        $this->read();

        return true;
    }

    /* ----------------------------------------------------------------------
      private funtions
      ---------------------------------------------------------------------- */

    // ----------------------------------------------------------------------
    /**
     * load custom config from cache.class_config.php and set a cache
     * directory
     */
    private function _setup() {
        $sCfgfile="cache.class_config.php";
        if (file_exists(__DIR__ . "/".$sCfgfile)){
            include($sCfgfile);
        }
        if (!$this->_sCacheDir) {
            if (getenv("TEMP"))
                $this->_sCacheDir = str_replace("\\", "/", getenv("TEMP"));
            if ($_SERVER['DOCUMENT_ROOT'])
                $this->_sCacheDir = $_SERVER['DOCUMENT_ROOT'];
            if (!$this->_sCacheDir)
                $this->_sCacheDir = ".";
            $this->_sCacheDir .= "/~cache";
        }
    }
            
    // ----------------------------------------------------------------------
    /**
     * recursive cleanup of a given directory; this function is used by
     * public function cleanup()
     * @since 2.0
     * @param string $sDir full path of a local directory
     * @param string $iTS  timestamp
     * @return     true
     */
    private function _cleanupDir($sDir, $iTS) {
        echo "<ul><li class=\"dir\">DIR: <strong>$sDir</strong><ul>";

        if (!file_exists($sDir)) {
            echo "\t Directory does not exist - [$sDir]</ul></li></ul>";
            return;
        }
        if (!($d = dir($sDir))) {
            echo "\t Cannot open directory - [$sDir]</ul></li></ul>";
            return;
        }
        while ($entry = $d->read()) {
            $sEntry = $sDir . "/" . $entry;
            if (is_dir($sEntry) && $entry != '.' && $entry != '..') {
                $this->_cleanupDir($sEntry, $iTS);
            }
            if (file_exists($sEntry)) {
                $ext = pathinfo($sEntry, PATHINFO_EXTENSION);
                $ext = substr($sEntry, strrpos($sEntry, '.') + 1);

                $exts = explode(".", $sEntry);
                $n = count($exts) - 1;
                $ext = $exts[$n];

                if ($ext == $this->_sCacheExt) {

                    $aTmp = stat($sEntry);
                    $iAge = date("U") - $aTmp['mtime'];
                    if ($aTmp['mtime'] <= $iTS) {
                        echo "<li class=\"delfile\">delete cachefile: $sEntry ($iAge s)<br>";
                        unlink($sEntry);
                    } else {
                        echo "<li class=\"keepfile\">keep cachefile: $sEntry ($iAge s; " . ($aTmp['mtime'] - $iTS) . " s left)</li>";
                    }
                }
            }
        }
        echo "</ul></li></ul>";

        // try to delete if it should be empty
        @rmdir($sDir);
        return true;
    }

    // ----------------------------------------------------------------------
    /**
     * private function _getAllCacheData() - read cachedata and its meta infos
     * @since 2.0
     * @return     array  array with data, file stat
     */
    private function _getAllCacheData() {
        if (!$this->_sCacheFile)
            return false;
        $this->_aCacheInfos = array();
        if (file_exists($this->_sCacheFile)) {
            $aTmp = unserialize(file_get_contents($this->_sCacheFile));
            $this->_aCacheInfos['data'] = $aTmp['data'];
            $this->_iTtl = $aTmp['iTtl'];
            $this->_tsExpire = $aTmp['tsExpire'];
            $this->_aCacheInfos['stat'] = stat($this->_sCacheFile);
        }
        return $this->_aCacheInfos;
    }

    // ----------------------------------------------------------------------
    /**
     * private function _getCacheFilename() - get full filename of cachefile
     * @return     string  full filename of cachefile
     */
    private function _getCacheFilename() {
        $sMyFile=md5($this->sCacheID);
        if($this->_sCacheDirDevider && $this->_sCacheDirDevider>0){
            $sMyFile=preg_replace('/([0-9a-f]{'.$this->_sCacheDirDevider.'})/', "$1/", $sMyFile);
        }
        $sMyFile.=".".$this->_sCacheExt;
        $sMyFile=str_replace("/.", ".", $sMyFile);
        // $this->_sCacheFile = $this->_sCacheDir . "/" . $this->sModule . "/" . md5($this->sCacheID) . "." . $this->_sCacheExt;
        $this->_sCacheFile = $this->_sCacheDir . "/" . $this->sModule . "/" . $sMyFile;
        return $this->_sCacheFile;
    }

    /* ----------------------------------------------------------------------
      public funtions
      ---------------------------------------------------------------------- */

    // ----------------------------------------------------------------------
    /**
     * Cleanup cache directory; delete all cachefiles older than n seconds
     * Other filetypes in the directory won't be touched.
     * Empty directories will be deleted.
     * 
     * Only the directory of the initialized module/ app will be deleted.
     * $o=new Cache("my-app"); $o->cleanup(60*60*24*3); 
     * 
     * To delete all cachefles of all modules you can use
     * $o=new Cache(); $o->cleanup(0); 
     * 
     * @since 2.0
     * @param int $iSec max age of cachefile; older cachefiles will be deleted
     * @return     true
     */
    public function cleanup($iSec = false) {
        echo date("d.m.y - H:i:s") . " START CLEANUP $this->_sCacheDir/" . $this->sModule . ", $iSec s<br>
                <style>
                    .dir{color:#888;}
                    .delfile{color:#900;}
                    .keepfile{color:#ccc;}
                </style>";
        $this->_cleanupDir($this->_sCacheDir . "/" . $this->sModule, date("U") - $iSec);
        echo date("d.m.y - H:i:s") . " END CLEANUP <br>";
        return true;
    }

    // ----------------------------------------------------------------------
    /**
     * public function delete - delete a single cachefile if it exist
     * @return     boolean
     */
    public function delete() {
        if (!file_exists($this->_sCacheFile))
            return false;
        if (unlink($this->_sCacheFile)) {
            $this->_aCacheInfos['data'] = false;
            $this->_aCacheInfos['stat'] = false;
            return true;
        }
        return false;
    }

    // ----------------------------------------------------------------------
    /**
     * public function dump() - dump variables of cache class
     * @return     true
     */
    public function dump() {
        echo "
                <hr>
                <strong>cache->dump()<br></strong>
                <strong>module: </strong>" . $this->sModule . "<br>
                <strong>ID: </strong>" . $this->sCacheID . "<br>
                <strong>filename: </strong>" . $this->_sCacheFile;
        if (!file_exists($this->_sCacheFile))
            echo " (does not exist yet)";
        echo "<br>
                <strong>age: </strong>" . $this->getAge() . " s<br>
                <strong>ttl: </strong>" . $this->getTtl() . " s<br>
                <strong>expires: </strong>" . $this->getExpire() . " (" . date("d.m.y - H:i:s", $this->getExpire()) . ")<br>
                <pre>";
        print_r($this->_aCacheInfos);
        echo "</pre><hr>";
        return true;
    }

    // ----------------------------------------------------------------------
    /**
     * public function getCacheAge() - get age in seconds of exisiting cachefile
     * @return     int  age in seconds; -1 if cachefiles does not exist
     */
    public function getAge() {
        if (!isset($this->_aCacheInfos['stat']))
            $this->_getAllCacheData();
        if (!isset($this->_aCacheInfos['stat']))
            return -1;
        return date("U") - $this->_aCacheInfos['stat']['mtime'];
    }

    // ----------------------------------------------------------------------
    /**
     * public function getExpire() - get TS of cache expiration
     * @since 2.0
     * @return     int  unix ts of cache expiration
     */
    public function getExpire() {
        return $this->_tsExpire;
    }

    // ----------------------------------------------------------------------
    /**
     * public function getTtl() - get TTL of cache in seconds
     * @since 2.0
     * @return     int  get ttl of cache
     */
    public function getTtl() {
        return $this->_iTtl;
    }

    // ----------------------------------------------------------------------
    /**
     * public function isExpired() - cache expired? To check it 
     * you must use ttl while writing data, i.e.
     * $oCache->write($sData, $iTtl);
     * @since 2.0
     * @return     bool  cache is expired?
     */
    public function isExpired() {
        if (!$this->_tsExpire)
            return true;
        return ((date("U") - $this->_tsExpire)>0);
    }
    // ----------------------------------------------------------------------
    /**
     * public function iExpired() - get time in seconds when cachefile expires
     * you must use ttl while writing data, i.e.
     * $oCache->write($sData, $iTtl);
     * @since 2.1
     * @return     int  expired time in seconds; negative if cache is not expired
     */
    public function iExpired() {
        if (!$this->_tsExpire)
            return true;
        return date("U") - $this->_tsExpire;
    }

    // ----------------------------------------------------------------------
    /**
     * function isNewerThanFile($sRefFile) - is the cache (still) newer than
     * a reference file? This function returns difference of mtime of both
     * files.
     * @since 2.0
     * @param   string   $sRefFile  local filename
     * @return  integer  time in sec how much the cache file is newer; negative if reference file is newer
     */
    public function isNewerThanFile($sRefFile) {
        if (!file_exists($sRefFile))
            return false;
        if (!isset($this->_aCacheInfos['stat']))
            return false;

        $aTmp = stat($sRefFile);
        $iTimeRef = $aTmp['mtime'];
        
        //echo $this->_sCacheFile."<br>".$this->_aCacheInfos['stat']['mtime']."<br>".$iTimeRef."<br>".($this->_aCacheInfos['stat']['mtime'] - $iTimeRef);
        return $this->_aCacheInfos['stat']['mtime'] - $iTimeRef;
    }

    // ----------------------------------------------------------------------
    /**
     * public function getCacheData() - read cachedata if it exist
     * @return     various  cachedata or false if cache does not exist
     */
    public function read() {
        if (!isset($this->_aCacheInfos['data']))
            $this->_getAllCacheData();
        if (!isset($this->_aCacheInfos['data']))
            return false;
        return $this->_aCacheInfos['data'];
    }

    // ----------------------------------------------------------------------
    /**
     * public function setData($data) - set cachedata into cache object
     * data can be any serializable type, like string, array or object
     * Remark: You additionally need to call the write() method to store data in the filesystem
     * @since 2.0
     * @param      various  $data  data to store in cache
     * @return   boolean
     */
    public function setData($data) {
        return $this->_aCacheInfos['data'] = $data;
    }

    // ----------------------------------------------------------------------
    /**
     * public function setTtl() - set TTL of cache in seconds
     * You need to write the cache data to ap
     * Remark: You additionally need to call the write() method to store a new ttl value with 
     * data in the filesystem
     * @since 2.0
     * @param type $iTtl  ttl value in seconds
     * @return     int  get ttl of cache
     */
    public function setTtl($iTtl) {
        return $this->_iTtl = $iTtl;
    }

    // ----------------------------------------------------------------------
    /**
     * public function touch() - touch cachefile if it exist
     * For cachedata with a ttl a new expiration will be set
     * @return boolean 
     */
    public function touch() {
        if (!file_exists($this->_sCacheFile))
            return false;

        // touch der Datei reicht nicht mehr, weil tsExpire verloren ginge
        if (!$this->_iTtl)
            $bReturn = touch($this->_sCacheFile);
        else
            $bReturn = $this->write();

        $this->_getAllCacheData();

        return $bReturn;
    }

    // ----------------------------------------------------------------------
    /**
     * Write data into a cache. 
     * - data can be any serializable type, like string, array or object
     * - set ttl in s (from now); optional parameter
     * @param      various  $data  data to store in cache
     * @param      int      $iTtl  time in s if content cache expires (min. 0)
     * @return     bool     success of write action
     */
    public function write($data = false, $iTtl = -1) {
        if (!$this->_sCacheFile)
            return false;

        $sDir = dirname($this->_sCacheFile);
        if (!is_dir($sDir))
            if (!mkdir($sDir, 0750, true))
                die("ERROR: unable to create directory " . $sDir);

        if (!$data === false)
            $this->setData($data);

        if (!$iTtl >= 0) {
            $this->setTtl($iTtl);
        }

        $aTmp = array(
            'iTtl' => $this->_iTtl,
            'tsExpire' => date("U") + $this->_iTtl,
            'module' => $this->sModule,
            'cacheid' => $this->sCacheID,
            'data' => $this->_aCacheInfos['data'],
        );
        return file_put_contents($this->_sCacheFile, serialize($aTmp));
    }

}

// ----------------------------------------------------------------------
