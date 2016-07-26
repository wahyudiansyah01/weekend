<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
require_once dirname(__FILE__).'/../../init.php';

setlocale(LC_MONETARY, 'id_ID.utf8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");
header("Content-Type: application/json; charset=UTF-8");

$deskripsivoucherminggupertama = "VOUCHER1";
$deskripsivoucherminggukedua = "VOUCHER2";


define($deskripsivoucherminggupertama, "abc");
define($deskripsivoucherminggukedua, "xyz");
define("DOMAIN","http://localhost/prestashop");

class produk{
    var $id;
    var $nama;
    var $gambar;
    var $harga_awal;
    var $harga_final;
    var $nilai_voucher;
    var $code_voucher;
    var $voucher_from;
    var $voucher_to;
    var $token;
    var $active;
   
}

function ambilproduk($deskripsivoucher){
    $sql = "select ps_cart_rule.id_cart_rule, description, date_from, date_to, code, reduction_amount, id_item from ps_cart_rule join ps_cart_rule_product_rule_group on ps_cart_rule.id_cart_rule = ps_cart_rule_product_rule_group.id_cart_rule join ps_cart_rule_product_rule on ps_cart_rule_product_rule_group.id_product_rule_group = ps_cart_rule_product_rule.id_product_rule_group join ps_cart_rule_product_rule_value on ps_cart_rule_product_rule.id_product_rule = ps_cart_rule_product_rule_value.id_product_rule  where description = '".$deskripsivoucher."';";
        if($row = Db::getInstance()->ExecuteS($sql)){
         $array = [];
        foreach ($row as $rows){
            $produk = new produk();
            $produk->id = $rows['id_item'];
            $p = new ProductCore($produk->id);
            $produk->nama = $p->name[1];
            $image = Image::getCover($produk->id);
            
            $link = new LinkCore;
            $sqli = "select * from ps_image where id_product=".$produk->id." and cover=1;";
           
            $rowi = Db::getInstance()->getRow($sqli);
           
            $imagePath = $link->getImageLink($p->link_rewrite, $rowi['id_image'], 'home_default');
            $produk->gambar = $imagePath;
            $produk->harga_awal = $p->getPrice();
            $produk->nilai_voucher = $rows['reduction_amount'];
            $produk->harga_final = $produk->harga_awal - $produk->nilai_voucher;
            $produk->code_voucher = $rows['code'];
            $produk->voucher_from = $rows['date_from'];
            $produk->voucher_to = $rows['date_to'];
            $produk->token = Tools::getToken(false);
            $produk->active = $p->active;
           
            $array[] = $produk;
            
        }
        return $array;
    }
   
}



function converjson($array){
    $json = '{ "produk": [';
    $index = 0;
    $lastElement = end($array);
    foreach ((array)$array as $produk){
        
       if($produk->active==="1" && (date("Y-m-d H:i:s", strtotime($produk->voucher_from)) <  date("Y-m-d H:i:s") ) && ( date("Y-m-d H:i:s") < date("Y-m-d H:i:s", strtotime($produk->voucher_to)) ) ){
           $index++;
           if($index>1){
            $json.=",";
        }  
           
           
            $json .= ' { "id":"'.$produk->id.'",
         "nama": "'.$produk->nama.'",
         "gambar": "'.$produk->gambar.'",
         "harga_awal": "'.money_format("%.0n", $produk->harga_awal).'",
         "harga_final": "'.money_format("%.0n", $produk->harga_final).'",
             "nilai_voucher": "'.money_format("%.0n",$produk->nilai_voucher).'",
                  "code_voucher": "Kode Voucher '.$produk->code_voucher.'",
                       "voucher_from": "'.$produk->voucher_from.'",
                           "voucher_to": "'.$produk->voucher_to.'",
                                "token": "'.$produk->token.'",
                                    "active": "'.$produk->active.'"
       
      }';
            
      if($lastElement === $produk){
          $json.='],"adadiskon":"DISKON HARI INI"}';
      }
         
       
       }
       
       else if ($produk->active==="1" &&  date("Y-m-d H:i:s") < date("Y-m-d H:i:s", strtotime($produk->voucher_from))  ){
               $index++;
           if($index>1){
            $json.=",";
        }  
           
           
            $json .= ' { "id":"'.$produk->id.'",
         "nama": "'.$produk->nama.'",
         "gambar": "'.$produk->gambar.'",
         "harga_awal": "'.money_format("%.0n", $produk->harga_awal).'",
         "harga_final": "diskonhingga",
             "nilai_voucher": "",
                  "code_voucher": "",
                       "voucher_from": "",
                           "voucher_to": "",
                                "token": "",
                                    "active": ""
       
      }';
            
      if($lastElement === $produk){
          $json.='],"adadiskon":"DISKON YANG AKAN DATANG"}';
      }
         
      
       }
       
         
 
     
 
       
    }
    if($index == 0){
        $json.='],"adadiskon":""}';
    }
    return $json;
    
}


$produk1 =  converjson(ambilproduk(VOUCHER1));
$produk2 = converjson(ambilproduk(VOUCHER2));

echo "[" .$produk1. "  , " .$produk2."  , \"" .DOMAIN. "\"]";

?>




























































