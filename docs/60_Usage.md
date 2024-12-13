## Code examples

In your application for caching and cleanup you need the file cache.class.php only (which is in the folder src). Copy it into your classes folder.

### Typical usage: TTL

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

### Reference file

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

### Cleanup

Cleanup cache directory 

```php
require_once("/php/cache.class.php");  

// delete all Cachefiles of the module "my-app" older 1 day
$o=new AhCache("my-app");
$o->cleanup(60*60*24*1); 

// or cleanup cachefiles of all modules
$o=new Cache(); $o->cleanup(60*60*24*1);
```
