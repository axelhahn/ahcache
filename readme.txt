###############################################################################

cache.class.php

###############################################################################

AXELS CONTENT CACHE CLASS
V2.5

DOCS: https://www.axel-hahn.de/docs/ahcache/index.htm
License: GNU/GPL v3

--------------------------------------------------------------------------------

2009-07-20  1.0  cache class on www.axel-hahn.de
2011-08-27  1.1  comments added; sCacheFile is private
2012-02-04  2.0  cache serialzable types; more methods, i.e.:
                  - comparison of timestimp with a sourcefile
                  - cleanup unused cachefiles
2012-05-15  2.1  isExpired() returns as bool; new method iExpired() to get
                 expiration in sec
2012-05-15  2.2  - rename to AhCache
                 - _cleanup checks with file_exists
2014-03-31  2.3  - added _setup() that to includes custom settings
                 - limit number of files in cache directory
2019-11-24  2.4  - added getCachedItems() to get a filtered list of cache files
                 - added remove file to make complete cache of a module invalid
                 - rename var in cache.class_config.php to "$this->_sCacheDirDivider"
2019-11-26  2.5  - added getModules() to get a list of existing modules that stored
                   a cached item
2019-11-xx  2.7  - class was moved to folder src
                 - added admin webgui
                 - method getCachedItems - fix filter lifetime_greater
###############################################################################

--- typical usage:

--------------------------------------------------------------------------------

example using expiration (ttl value):

    $sContent='';  
    $iTtl=60*5; // 5 min 
      
    require_once("/php/cache.class.php");  
    $myCache=new AhCache("my-app","task-id");  
      
    if($myCache->isExpired()) {  
        // cache does not exist or is expired
        $sContent=...  
      
        // save cache
        $myCache->write($sContent, $iTtl);  
      
    } else {  
        // read cached data
        $sContent=$myCache->read();  
    }  
      
    // output
    echo $sContent;  
	
--------------------------------------------------------------------------------

example compare age of cache with age of a sourcefile

	require_once("/php/cache.class.php");  
	$sCsvFile="my_source_file.csv"  
	  
	$myCache=new AhCache("my-app","task-id");  
	$sContent=$myCache->read(); // read cached data
	  
	// comparison of last modified time (mtime)
	if (!$myCache->isNewerThanFile($sCsvFile)) {  
	  
		// update content 
		$sContent=...  
	  
		// ... and save cache
		$myCache->write($sContent);  
	};  
	  
	// output
	echo $sContent;

--------------------------------------------------------------------------------

cleanup cache directory 

    require_once("/php/cache.class.php");  
    $o=new AhCache("my-app");  
    $o->cleanup(60*60*24*1); // delete all Cachefiles of the module "my-app" older 1 day

	or cleanup cachefiles of all modules
    $o=new Cache(); $o->cleanup(60*60*24*1);


###############################################################################
