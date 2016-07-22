<?php
header("Content-Type: text/plain");

$d=strtotime("2016-07-21 16:00:00");
echo "voucher to adalah  " . date("Y-m-d H:i:s", $d)."\n";
echo "sekarang tanggal ".  date("Y-m-d H:i:s")."\n";
if(date("Y-m-d H:i:s", $d) < date("Y-m-d H:i:s")){
    echo 'voucher ini sudah kadaluarsa';
}
 else {
    echo 'voucher ini masih berlaku';    
}
?>