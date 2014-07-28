<?php
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Routines/mlstrings.class.php');
require_once('Routines/images.php');

//-----populate variables and construct query------
$tableName = $_GET['t']; // basic input
$dir = $_GET['dir'];
$id = $_GET['id'];

$currRank = DB::val(Query::Select($tableName)->id($id)->fields('rank'));


$crRows = DB::get(Query::Select($tableName)->eq('rank',$currRank));
if (count($crRows)>1){
    rerank($tableName);
} else {
    $orQuery = Query::Select($tableName)->fields(['ID','rank'])->limit(2);
    if ($dir == 0){ //up
        $orQuery->lt('rank',$currRank)->desc('rank');
    } else { //down
        $orQuery->gt('rank',$currRank)->asc('rank');
    }
    $orRows = DB::get($orQuery);

    if (count($orRows) == 0){
        die();
    } else if (count($orRows) == 2) {
        rerank($tableName);
    }
}

swapRanks($tableName, $dir, $id);


function swapRanks($tableName, $dir, $id){
    $currRank = DB::val(Query::Select($tableName)->id($id)->fields('rank'));

    $orQuery = Query::Select($tableName)->fields(['ID','rank'])->limit(1);
    if ($dir == 0){ //up
        $orQuery->lt('rank',$currRank)->desc('rank');
    } else { //down
        $orQuery->gt('rank',$currRank)->asc('rank');
    }

    $otherRow = DB::row($orQuery);

    DB::query(Query::Update($tableName)->pairs('rank',$otherRow->rank)->id($id));
    DB::query(Query::Update($tableName)->pairs('rank',$currRank)->id($otherRow->ID));
}

//perform automatic re-ranking;
function rerank($tableName){
    $all = DB::get(Query::Select($tableName)->fields(['ID','rank'])->asc('rank'),DB::OBJECT);
    $r = 0;
    foreach($all as $item){
        $r++;
        DB::query(Query::Update($tableName)->pairs('rank',$r*10)->id($item->ID));
    }
}






