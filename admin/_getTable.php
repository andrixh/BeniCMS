<?php
header('Content-Type:text/html; charset=UTF-8');
require_once('Config/config.php');
require_once('_loginLock.php');
require_once('Includes/debug.inc.php');
require_once('Includes/db.inc.php');
require_once('Routines/mlstrings.class.php');
require_once('Routines/images.php');

//----init variables-----
$tableName = '';
$fields = '';
$fieldLabels = '';
$pageSize = 5;
$pageNum = 1;
$sortColumn = '';
$sortDir = '';
$searchString = '';
$searchField = '';
$specialFields = '';
$specialData = '';
//-----populate variables and construct query------
$tableName = $_GET['t']; // basic input
$fields = $_GET['f'];
$fieldLabels = $_GET['fl'];

//$query = "SELECT ".$fields." FROM ".$tableName." ";

$q = Query::Select($tableName)->fields(explode(',', $fields));

if (isset($_GET['s']) && $_GET['s'] != '') { //Search Criteria
    $searchString = $_GET['s'];
    $searchField = $_GET['sf'];

    $q->contains($searchField, $searchString);
    //$query = $query."WHERE ".$searchField."='".$searchString."' OR ".$searchField." LIKE '%".$searchString."' OR ".$searchField." LIKE '".$searchString."%' OR ".$searchField." LIKE '%".$searchString."%' ";
}

if (isset($_GET['sc']) && $_GET['sc'] != '') { // Sort Directives
    $sortColumn = $_GET['sc'];
    $sortDir = $_GET['sd'];
    //$query = $query."ORDER BY ".$sortColumn." ".$sortDir;
    if (strtoupper($sortDir) == 'ASC') {
        $q->asc($sortColumn);
    } else {
        $q->desc($sortColumn);
    }
}

if (isset($_GET['ps']) && $_GET['ps'] != '') { // pagination
    $pageSize = $_GET['ps'];
    $pageNum = $_GET['pn'];
}


if (isset($_GET['spf']) && $_GET['spd'] != '') { // special Fields
    $specialFields = $_GET['spf'];
    $specialData = $_GET['spd'];
}

//echo ($query);
$data = DB::get($q, DB::NUM); //->get_results($query,ARRAY_N);

?>
<table>
    <tr class="tableHead">
        <?php
        $heads = explode(',', $fields);
        $headLabels = explode(',', $fieldLabels);
        for ($x = 0; $x < count($heads); $x++) {
            ?>
            <th><a rel="<?php echo trim($heads[$x]) ?>" href=# <?php if (trim($heads[$x]) == $sortColumn) {
                    echo('class="sort' . $sortDir . '"');
                } ?>><?php echo $headLabels[$x] ?></a></th>
        <?php } ?>
    </tr>

    <?php
    $totalRecords = count($data);
    $totalPages = ceil($totalRecords / $pageSize);

    $startCount = ($pageNum - 1) * $pageSize;
    $endCount = $startCount + $pageSize;
    if ($endCount > $totalRecords) {
        $endCount = $totalRecords;
    }

    $specialFlds = explode(',', $specialFields);
    $specialDta = explode(',', $specialData);

    for ($i = $startCount; $i < $endCount; $i++) {
        echo '<tr>';
        for ($j = 0; $j < count($data[$i]); $j++) {
            $isSpecial = false;
            for ($k = 0; $k < count($specialFlds); $k++) {
                if ($specialFlds[$k] == $heads[$j]) {
                    $isSpecial = true;
                    if ($specialDta[$k] == 'DS') { //Special Field is dynamic String
                        _d($data[$i][$j], 'strid to create');
                        $strings = mlString::Create($data[$i][$j])->getValues();
                        _d($strings, 'strings');
                        $string = $strings[key($strings)];
                        echo '<td class="dynamicString"><a href="mlstringsEdit.php?strID=' . $data[$i][$j] . '" title="Dynamic String [' . $data[$i][$j] . '] - Click to edit"></a>' . mlString::excerpt(strip_tags($string), 12) . '</td>';
                    } else if (substr($specialDta[$k], 0, 4) == 'TRIM') { //Excerpt from total value
                        $excerptLength = intval(substr($specialDta[$k], 4));
                        if ($excerptLength == 0) {
                            $excerptLength = 18;
                        }
                        echo '<td>' . mlString::excerpt(strip_tags($data[$i][$j]), $excerptLength) . '</td>';
                    } else if ($specialDta[$k] == 'EX') { //Special Field is File Extension
                        echo '<td class="fileExtension"><img src="Gfx/Extensions/' . strip_tags($data[$i][$j]) . '.png" />' . strip_tags($data[$i][$j]) . '</td>';
                    } else if ($specialDta[$k] == 'EX_s') { //Special Field is File Extension
                        echo '<td class="fileExtension"><img src="Gfx/Extensions/' . strip_tags($data[$i][$j]) . '.png" /></td>';
                    } else if ($specialDta[$k] == 'IMG') { //Special Field is Gallery Image
                        $d = $data[$i][$j];
                        if (substr($d, 0, 1) != '[') { //mlgallery
                            $strings = mlString::Create($data[$i][$j])->getValues();
                            $d = $strings[key($strings)];
                        }

                        $ia = json_decode($d);

                        if (count($ia) > 0) {
                            echo '<td class="image">';
                            foreach ($ia as $ii) {
                                echo '<img src="' . conf('IMAGE_RESIZED_DIRECTORY') . normalizedImageName($ii->physicalName, '', 0, 25, 25, 'S') . '"/>';
                            }
                            echo '</td>';
                        } else {
                            echo '<td class="image"></td>';
                        }
                        unset($ia);
                        unset($d);
                    } else if ($specialDta[$k] == 'BO') { //Special Field is Boolean On/Off
                        if ($data[$i][$j] != false) {
                            echo '<td class="boolean"><span class="booleanOn"></span>on</td>';
                        } else {
                            echo '<td class="boolean"><span class="booleanOff"></span>off</td>';
                        }
                    } else if ($specialDta[$k] == 'BO_T') { //Special Field is Boolean On/Off TOGGLE
                        if ($data[$i][$j] != false) {
                            echo '<td field="'.$heads[$j].'" class="boolean toggle"><span class="booleanOn"></span>on</td>';
                        } else {
                            echo '<td field="'.$heads[$j].'" class="boolean toggle"><span class="booleanOff"></span>off</td>';
                        }
                    } else if ($specialDta[$k] == 'BO_X') { //Special Field is Boolean On/Off TOGGLE
                        if ($data[$i][$j] != false) {
                            echo '<td field="'.$heads[$j].'" class="boolean exclusive"><span class="booleanOn"></span>on</td>';
                        } else {
                            echo '<td field="'.$heads[$j].'" class="boolean exclusive"><span class="booleanOff"></span>off</td>';
                        }
                    } else if ($specialDta[$k] == 'C') { //Special Field is Content
                        $exData = json_decode($data[$i][$j]);
                        $count = 0;

                        $res = [];

                        foreach ($exData as $f => $v) {
                            if ($count >= 8) {
                                break;
                            }
                            if (is_bool($v)) {
                                $res[] = $f . ': <b>' . (($v == true) ? 'yes' : 'no') . '</b>';
                            } else if (is_string($v)) {
                                $res[] = $f . ': <b>' . mlString::excerpt(strip_tags($v), 10) . '</b>';
                            } else if (is_array($v)) {
                                $res[] = $f . ': [list]';
                            }
                            $count++;
                        }
                        $outStr = implode(', ', $res);
                        if ($count >= 8) {
                            $outStr .= ' ' . '&#8230;';
                        }
                        echo '<td>' . $outStr . '</td>';
                    } else if ($specialDta[$k] == 'RK') {
                        echo '<td class="rank">'/*.$data[$i][$j]*/.'<a href="_rerank.php=">▲</a><a href="_rerank.php">▼</a></td>';

                    } else { //Special Fiels is an admin image in PNG Format. Data Contains Path
                        echo '<td><img src="' . $specialDta[$k] . '/' . strip_tags($data[$i][$j]) . '.png" /></td>';
                    }
                }
            }
            if ($isSpecial == false) {
                echo '<td>' . strip_tags($data[$i][$j]) . '</td>';
            }
        }
        echo '</tr>';
    } ?>

</table>

<div class="tableControls">
    <div class="tableNav">
        <?php if ($totalPages > 1) {

            if ($pageNum > 1) {
                echo '<a href="' . ($pageNum - 1) . '"><</a>';
            } else {
                echo '<a class="disabled" href="#"><</a>';
            }

            for ($i = 0; $i < $totalPages; $i++) {
                if (($i + 1) == $pageNum) {
                    echo '<a href="#" class="current">' . ($i + 1) . '</a>';
                } else {
                    echo '<a href="' . ($i + 1) . '">' . ($i + 1) . '</a>';
                }
            }

            if ($pageNum < $totalPages) {
                echo '<a href="' . ($pageNum + 1) . '">></a>';
            } else {
                echo '<a class="disabled" href="#">></a>';
            }
        } ?>
    </div>
    <div class="pageCount">
        <p>Displaying Results <strong><?php echo $startCount + 1 ?></strong> to <strong><?php echo $endCount ?></strong>
            of <strong><?php echo $totalRecords ?></strong>. Results per Page: </p>
        <a href="<?php if ($pageSize == 10) {
            echo '#';
        } else {
            echo '10';
        } ?>" <?php if ($pageSize == 10) {
            echo 'class="current"';
        } ?>>10</a>
        <a href="<?php if ($pageSize == 25) {
            echo '#';
        } else {
            echo '25';
        } ?>"  <?php if ($pageSize == 25) {
            echo 'class="current"';
        } ?>>25</a>
        <a href="<?php if ($pageSize == 50) {
            echo '#';
        } else {
            echo '50';
        } ?>"  <?php if ($pageSize == 50) {
            echo 'class="current"';
        } ?>>50</a>
        <a href="<?php if ($pageSize == 100) {
            echo '#';
        } else {
            echo '100';
        } ?>"  <?php if ($pageSize == 100) {
            echo 'class="current"';
        } ?>>100</a>
        <a href="<?php if ($pageSize == 200) {
            echo '#';
        } else {
            echo '200';
        } ?>"  <?php if ($pageSize == 200) {
            echo 'class="current"';
        } ?>>200</a>
    </div>
</div>