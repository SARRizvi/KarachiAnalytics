<?php
header("Access-Control-Allow-Origin: *");


$con = mysqli_connect("localhost","mmsonlin_waqas","waqas123!","mmsonlin_test");
if(!empty($_POST)){
    if($_POST['action'] == "get district"){
        $q = mysqli_query($con,"SELECT * FROM `districtsindh`");
        while($row = mysqli_fetch_assoc($q)){
            echo "<option value='".$row['DistrictID']."'>".$row['DistrictName']."</option>";
        }
    }
    if($_POST['action'] == "get crimes"){
        $q = mysqli_query($con,"SELECT * FROM `crimesubcategory`");
        while($row = mysqli_fetch_assoc($q)){
            echo "<option value='".$row['CrimeSubCategoryID']."'>".$row['CrimeSubCategoryName']."</option>";
        }
    }
    if($_POST['action'] == "get years"){
        $q = mysqli_query($con,"SELECT DISTINCT(YEAR(`CrimeDate`)) as `years` FROM `crime` ORDER BY `years` ASC");
        while($row = mysqli_fetch_assoc($q)){
            echo "<option value='".$row['years']."'>".$row['years']."</option>";
        }
    }
    if($_POST['action'] == "get result"){
        $q = mysqli_query($con,"SELECT SUM(`CrimeFigure`) as `sum`,YEAR(`CrimeDate`)as `year` FROM `crime` WHERE `CrimeSubCategoryID` = '{$_POST['crime']}' AND `DistrictID` = '{$_POST['dist']}' GROUP BY `year`");
        while($row = mysqli_fetch_assoc($q)){
            if($row['year'] >= $_POST['from'] AND $row['year'] <= $_POST['to']){
                $data['year'][] = $row['year'];
                $data['sum'][] = $row['sum'];
            }

        }
        echo json_encode($data);
    }
    if($_POST['action'] == "get result3"){
        $new = array();
        $q = mysqli_query($con,"SELECT * FROM `crimecategory`");
        while($row = mysqli_fetch_assoc($q)){
            $crimeParent[] = $row;
        }
        foreach($crimeParent as $k => $val){
            $q = mysqli_query($con,"SELECT * FROM `crimesubcategory` WHERE `CrimeCategoryID` = '{$val['CrimeCategoryID']}'");
            while($row = mysqli_fetch_assoc($q)){
                $crimeParent[$k]['sub'][] = $row;
                $crimesub[] = $row;
            }
        }
        foreach($crimeParent as $k => $val){
            foreach($val['sub'] as $ksubs => $subs){
                $q = mysqli_query($con,"SELECT SUM(`CrimeFigure`)as `sum` FROM `crime` WHERE `CrimeSubCategoryID` = '{$subs['CrimeSubCategoryID']}' AND `DistrictID` = '{$_POST['dist']}'");
                $new[$k]['sum'] = 0;
                while($row = mysqli_fetch_assoc($q)){
                    $new[$k]['sum'] += $row['sum'];
                    $new[$k]['crimeParent'] = $val['CrimeCategoryName'];
                }
            }
        }
        $x = array();
        foreach($new as $k=>$v){
            $x['crime'][] = $v['crimeParent'];
            $x['sum'][] = $v['sum'];
        }
        echo json_encode($x);die;

    }
    if($_POST['action'] == "get result2"){
        $q = mysqli_query($con,"SELECT * FROM `crimesubcategory`");
        $crimes = array();
        while($row = mysqli_fetch_assoc($q)){
            $crimes[] = $row;
        }
        foreach($crimes as $k => $v){
            $q = mysqli_query($con,"SELECT SUM(`CrimeFigure`)as `sum` FROM `crime` WHERE `DistrictID` = '{$_POST['dist']}' AND `CrimeSubCategoryID` = '{$v['CrimeSubCategoryID']}' AND (YEAR(`CrimeDate`) BETWEEN '{$_POST['from']}' AND '{$_POST['to']}')");
            $crimes[$k]['sum'] = 0;
            while($row = mysqli_fetch_assoc($q)){
                $crimes[$k]['sum'] += $row['sum'];
            }
        }
        $new = array();
        foreach($crimes as $k=>$v){
            if(isset($new['crime'][$v['CrimeSubCategoryName']])){
                $new[$v['CrimeSubCategoryName']] += $v['sum'];
            }else{
                $new[$v['CrimeSubCategoryName']] = $v['sum'];
            }
        }
        arsort($new);
        $i = 0 ;
        $x = array();
        foreach($new as $k=>$v){
            if($i < 10){
                $x['crime'][] = $k;
                $x['sum'][] = $v;
            }
            $i++;
        }
        echo json_encode($x);
    }
}


