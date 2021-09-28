<!doctype html><?php
/**
 * ======================================================================
 *
 * Page for Cache admin
 *
 * Browse and filter all cached modules and their items.
 * Delete all items, all outdated cache items.
 *
 * ======================================================================
 *
 * (1) Copy this file somewhere below your webroot
 * (2) update the line require('classes/cache-admin.class.php'); 
 * (3) protect web access to the admin page (basic auth, ip restriction)
 *
 * ======================================================================
 */
require('../src/cache-admin.class.php');
$oCache=new AhCacheAdmin();


$sOut='';
$sNav='';

$sAction=isset($_POST['action']) ? $_POST['action'] : false;

global $sModule; $sModule=isset($_GET['module']) ? $_GET['module'] : false;
global $sCachefile; $sCachefile=isset($_GET['file']) ? $_GET['file'] : false;

// ----------------------------------------------------------------------
// display functions
// ----------------------------------------------------------------------


function getNav(){
    global $sModule;
    $oCache=new AhCacheAdmin();
    return $oCache->renderModuleList(array(
        'baseurl'=>'?',
        'module'=>$sModule,
    ));
}

function getItems(){
    global $sModule,$sCachefile;
    if($sModule){
        $oCache=new AhCacheAdmin($sModule);
        return $oCache->renderModuleItems(array(
            'baseurl'=>'?',
            'module'=>$sModule,
            'file'=>$sCachefile,
        ));
    } else {
        return 'select a module ...';
    }
}

function getDetails(){
    global $sModule,$sCachefile;
    if($sModule && $sCachefile){
        $sBackUrl='?module='.$sModule;
        echo '<div id="details" onclick="location.href=\''.$sBackUrl.'\';">'
            .'<a href="'.$sBackUrl.'">close</a><br><hr>'
            ;
        $oCache=new AhCacheAdmin();
        $oCache->loadCachefile($sCachefile);
        $oCache->dump();
        echo '</div>';
    } else {
        // return 'select an item ...';
    }
}

// ----------------------------------------------------------------------
// action functions
// ----------------------------------------------------------------------

function actDeleteOutdated(){
    global $sModule,$sCachefile;
    if($sModule){
        $oCache=new AhCacheAdmin($sModule);
		$aItems=$oCache->getCachedItems(array(

		));
        foreach(array_keys($aItems) as $sFile){
            $oCache->loadCachefile($sFile);
            if($oCache->isExpired()){
                echo "expired: $sFile - "
                    .($oCache->delete() ? 'OK: deleted' : 'ERROR: deletion failed.')
                    .'<br>'
                    ;
            }
        }
    } 
}

// ----------------------------------------------------------------------
// actions
// ----------------------------------------------------------------------

switch($sAction){
    case 'delete':
        echo "delete:<br>";
        actDeleteOutdated();
        break;
        ;;
    case 'makeInvalid':
        if($sModule){
            $oCache=new AhCacheAdmin($sModule);
            $oCache->removefileTouch();
        } 
        break;
        ;;
        case 'deleteModule':
        if($sModule){
            echo "deleting module [$sModule] ... ";
            $oCache=new AhCacheAdmin($sModule);
            if ($oCache->deleteModule(true)){
                $sModule=false;
                echo 'OK';
            } else {
                echo 'failed.';
            }
            echo '<br>';
        } 
        break;
        ;;
    default:
        echo $sAction ? "unhandled action: $sAction<br>" : '';
    ;;
}

// ----------------------------------------------------------------------
// output
// ----------------------------------------------------------------------


?><html>
<head>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/jquery.dataTables.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.min.js"></script>
    <style>
        body{background:#eee; background: linear-gradient(90deg,#fff,#eee 20%) fixed;border-top: 4px solid #aaa;color:#333; font-family: verdana,arial; font-size: 1.0em; margin: 0em; padding: 1em;}
        button{padding: 1em; font-size: 1em;}
        h1{margin: 0 0 1em; border-bottom: 1px solid #d62;}
        h1 a{color:#628;text-decoration: none;}
        nav{float: left; margin-right: 1em; border-right: 0px dashed #ccc; border-bottom: 0px dashed #ccc; box-shadow: 0.3em 0.3em 0.7em #ddd; }
        ul{list-style: none; margin: 0; padding: 0;}
        ul li a{text-decoration: none; color:#48c; display:block; padding: 0;}
        ul li a:hover{background:rgba(0,0,0,0.05);}
        .ok,       .ok a{color:#080;}
        .less30,   .less30 a{background:#fec; color:#c80;}
        .outdated, .outdated a{background:#edd !important; color:#a66;}
        .active,   .active a {background:#d62 !important;color:#fff;}

        nav ul li a{padding: 0.5em;}
        table{border: 1px solid #ccc;}
        #maincontent{float: left;  border: 0px solid; max-width:90%;}
        #itemlist{float: left;  border-left: 0px dotted; padding-left: 1em;}
        #details{position: absolute; top: 2em; left: 30%; width: 60%; background: #fff; padding: 1em; border: 3px solid; box-shadow: 0 0 3em #000; }
        .bar{border: 1px solid rgba(0,0,0,0.05); display: block; float: left; width: 100px;}
        .bar .left{background:#9b9; height: 1em;}
        .less30 .bar .left{background:#da8;}
        .button{background:#ccc; color:#333; border-radius: 0.3em; border: 1px solid rgba(0,0,0,0.1); padding: 0.5em; text-decoration: none;}
    </style>

</head>
<body>

    <h1><a href="?">ahCache Admin</a></h1>
    <?php echo getNav();?>

    <div id="maincontent">
        <div id="itemlist">
            <?php 
                
                echo getItems();
            ?>
        </div>
    </div>

    <?php echo getDetails();?>

    <script>
    $(document).ready( function () {
        $('.datatable').DataTable( {"lengthMenu":[[10,50,100,-1],[10,50,100,"..."]], stateSave: true} );
    } );
    </script>
</body>
</html>