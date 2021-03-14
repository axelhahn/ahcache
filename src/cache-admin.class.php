<?php
/** 
 * --------------------------------------------------------------------------------<br>
 *          __    ______           __       
 *   ____ _/ /_  / ____/___ ______/ /_  ___ 
 *  / __ `/ __ \/ /   / __ `/ ___/ __ \/ _ \
 * / /_/ / / / / /___/ /_/ / /__/ / / /  __/
 * \__,_/_/ /_/\____/\__,_/\___/_/ /_/\___/ 
 *                                        
 * --------------------------------------------------------------------------------<br>
 * AXELS CACHE CLASS :: ADMIN<br>
 * --------------------------------------------------------------------------------<br>
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
 * 2019-11-26  2.5  - added getModules() to get a list of existing modules that stored<br>
 *                    a cached item<br>
 * --------------------------------------------------------------------------------<br>
 * @version 2.5
 * @author Axel Hahn
 * @link https://www.axel-hahn.de/docs/ahcache/index.htm
 * @license GPL
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL 3.0
 * @package Axels Cache
 */

require_once('cache.class.php');
class AhCacheAdmin extends AhCache {
	public function renderModuleList(){
	}
}
