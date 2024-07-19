
# cache.class.php

AXELS CONTENT CACHE CLASS

Version 2.12

👤 Author: Axel Hahn \
🧾 Source: <https://github.com/axelhahn/ahcache/> \
📜 License: GNU GPL 3.0 \
📗 Docs: <https://www.axel-hahn.de/docs/ahcache/>

## Description

The cache class AhCache caches all serializable objects in local files. In short: nearly all kind of data you can create in PHP: strings, arrays, ... whatever.

You can use it for any long running process, i.e. database requests, requests to external resources/ APIs, any long running procedure. 

Write it ... and instead of repeating the long running process on a frequent requests you can access a cached result. What is quite fast.

## Reqirements

PHP8 (up to PHP 8.3)

## Features

* Fast filebased local cache
* cache items are initialized with any application and an id to make it unique and to separate all cache items by an application or task
* invalidate your cache with different methods:
  * by a TTL vlue in seconds
* by a local reference file (you can touch a file to invalidate a cache)
  * touch a single module based file to all cache items of th module
* a cleanup method can delete all outdated cache items of all applications or a selected application
* a cache admin (web ui) visualizes the created cache items 

## History
```
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
2021-09-28  2.6  added a simple admin UI; the cache class got a few new methods
                 - update: cleanup() now always deletes expired items
                 - update: dump() styles output as table
                 - added: getCurrentModule 
                 - added: deleteModule 
                 - added: loadCachefile
                 - added: removefileDelete
                 - added: setCacheId
                 - added: setModule
2021-10-07  2.7  FIX: remove chdir() in _readCacheItem()
                 ADD reference file to expire a cache item
                 - added: getRefFile
                 - added: setRefFile
                 - update: dump, isExpired, isNewerThanFile, write
                 - update cache admin
2021-10-07  2.8  FIX: remove chdir() in _readCacheItem()
                 ADD reference file to expire a cache item
                 - added: getRefFile
                 - added: setRefFile
                 - update: dump, isExpired, isNewerThanFile, write
                 - update cache admin
2023-03-17  2.9  FIX: harden _getAllCacheData to prevent PHP warnings
2023-06-02  2.10 shorten code: defaults using ??; short array syntax
2023-11-20  2.11 check data subkey before writing
2024-06-25  2.12 WIP: add type declarations for PHP 8
```

## Code examples

In your application for caching and cleanup you need the file cache.class.php only (which is in the folder src). Copy it into your classes folder.

Typical usage:

Example using expiration (ttl value):

```php
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
```
	
Example: compare age of cache with age of a sourcefile (before version v 2.8)

```php
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
```

Cleanup cache directory 

```php
require_once("/php/cache.class.php");  

// delete all Cachefiles of the module "my-app" older 1 day
$o=new AhCache("my-app");
$o->cleanup(60*60*24*1); 

// or cleanup cachefiles of all modules
$o=new Cache(); $o->cleanup(60*60*24*1);
```

## Cache admin

Optionally you can enable a web ui to browse all modules and  its known cache items.

![Cache admin](./doc/cache-admin.png)
