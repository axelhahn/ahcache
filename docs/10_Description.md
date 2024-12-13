## Description

The cache class AhCache caches all serializable objects in local files. In short: nearly all kind of data you can create in PHP: strings, arrays, ... whatever.

You can use it for any long running process, i.e. database requests, requests to external resources/ APIs, any long running procedure. 

Write it ... and instead of repeating the long running process on a frequent requests you can access a cached result. What is quite fast.
