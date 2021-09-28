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
 * 2021-09-28  2.6  first version for admin UI<br>
 * --------------------------------------------------------------------------------<br>
 * @version 2.6
 * @author Axel Hahn
 * @link https://www.axel-hahn.de/docs/ahcache/index.htm
 * @license GPL
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL 3.0
 * @package Axels Cache
 */

require_once('cache.class.php');
class AhCacheAdmin extends AhCache {


	protected function _addForm($sUrl, $aAction, $sHtml=''){
		return '
			<form action="'.$sUrl.'" method="POST" style="float: left;">
			<input type="hidden" name="action" value="'.$aAction.'">
			'.($sHtml ? $sHtml : '<button>'.$aAction.'</button>').'
		</form>';
	}
	
	/**
	 * get html code - render ul list of existing modules
	 *
	 * @param array $aOptions  array with the keys
	 *                         - baseurl - url prefix
	 *                         - module  - active module
	 * @return string
	 */
	public function renderModuleList($aOptions=array()){
		$sReturn='';
		$aMods=$this->getModules();
		if(count($aMods)){
			$sReturn.=''
				.'Modules: <strong>' .count($aMods).'</strong><br><br>'
				.'<nav><ul>'
				// .'<li><a href="'.$aOptions['baseurl'].'&module=">all</a></li>'
				;
			foreach($aMods as $sModulename){
				$sReturn.='<li'
					.(isset($aOptions['module']) && $aOptions['module']==$sModulename
						? ' class="active"'
						: ''
					)
					.'><a href="'.$aOptions['baseurl'].'&module='.$sModulename.'">'.$sModulename.'</a></li>';
			}
			$sReturn.='</ul></nav>';
		} else {
			$sReturn='No Module was found. The cache is not in use - or wasn\'t inizialized with the right cache dir.<br>';
		}
		return $sReturn;
	}


	/**
	 * get html code - render ul list of existing cache items of a module
	 *
	 * @param array $aOptions  array with the keys
	 *                         - baseurl - url prefix
	 *                         - module  - active module
	 * @return string
	 */
	public function renderModuleItems($aOptions=array()){
		$sReturn='';
		$bHasOutdated=false;
		$iSize=0;
		$aItems=$this->getCachedItems(array(

		));
		// echo '<pre>'.print_r($aItems, 1).'</pre>';
		if(count($aItems)){
			$sReturn.=''
				.'<table class="datatable"><thead>
					<tr>
						<th>TTL [s]</th>
						<th>time left [s]</th>
						<th>visual</th>
						<th>cache id</th>
					</tr>
				</thead>
				<tbody>'
				// .'<li><a href="'.$aOptions['baseurl'].'&module=">all</a></li>'
				;
			foreach($aItems as $sFile=>$aItem){

				$iSize+=filesize($sFile);
				// $bSelected=isset($aOptions['item']) && $aOptions['item']==$aItem['cacheid'];
				$bSelected=isset($aOptions['file']) && $aOptions['file']==$sFile;

				$sLabel=strlen($aItem['cacheid'])<100
					? $aItem['cacheid']
					: substr($aItem['cacheid'],0,100).'...'
				;

				$iLeft=max($aItem['_lifetime'], 0);
				$sBar=$aItem['iTtl']>0 ? '<div class="bar"><div class="left" style="width:'.($iLeft/$aItem['iTtl']*100).'%;"></div></div>' : '';
				$sClasses='';
				$sClasses.=$bSelected ? 'active' : '';
				if($aItem['_lifetime']<$aItem['iTtl']*0.33){
						if($aItem['_lifetime']<0){
							$sClasses.=' outdated';
							$bHasOutdated=true;
						} else {
							$sClasses.=' less30';
						}
				} else {
					$sClasses.=' ok';
				}

				// Array ( [iTtl] => 86400 [tsExpire] => 1559303108 [module] => ahdiashow [cacheid] => dirD:/htdocs/axel-hahn.de/c58/diashows/images/2015-2018/MachicoArray ( [skip] => Array ( [0] => /_orig/ ) [remove] => D:/htdocs/axel-hahn.de/c58/diashows/images [intelligent] => 1 ) [_lifetime] => -15574681 [_age] => 15661081 ) 
				$sReturn.='<tr'
					.($sClasses ? ' class="'.$sClasses.'"' : '' )
					.'>
						<td align="right">'.($aItem['iTtl'] ? $aItem['iTtl'] : '-').'</td>
						<td align="right">'.($iLeft ? $iLeft : '-').'</td>
						<td><span style="display:none;">'.$iLeft.'</span>'.$sBar.'</td>
						<td><a href="'.$aOptions['baseurl'].'&module='.$aOptions['module'].'&file='.$sFile.'" title="'.$aItem['cacheid'].'">'.$sLabel.'</a></td>
						</tr>
						';
			}
			$sReturn.='</tbody></table>';
		} else {
			$sReturn='No Item was found.<br>';
		}
		$sSize=($iSize > 1024 
			? ($iSize > 1024 * 1024 
				? number_format($iSize/1024/1024,2).' MB' 
				: number_format($iSize/1024,2).' kB'
				)
			: $iSize.' byte'
		);

		$sUrl=$aOptions['baseurl'].'&module='.$aOptions['module'];

		return 
		'<table>'
		.'<tr><td>Items</td><td><strong>' .count($aItems).'</strong></td></tr>'
		.'<tr><td>Size</td><td><strong>' .$sSize.'</strong></td></tr>'
		.'</table><br>'
		.(count($aItems) ? $this->_addForm($sUrl, 'makeInvalid') : '')
		.($bHasOutdated 
			? $this->_addForm($sUrl, 'delete', '<button>delete outdated</button>')
			: ''
		)
		. $this->_addForm($sUrl, 'deleteModule', '<button>delete module</button>')
		.'<div style="clear: both;"></div><br>'
		.$sReturn;

	}
}
