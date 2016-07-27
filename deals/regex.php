<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
require_once dirname(__FILE__).'/../../init.php';

setlocale(LC_MONETARY, 'id_ID.utf8');
header("Content-Type: application/json; charset=UTF-8");



class produk {
    var $id;
    var $name;
    var $active;
    var $denyorder;
    var $link;
    var $image;
    var $baseprice;
    var $finalprice;
    var $codevoucher;
    var $vouchervalue;
    var $voucherfrom;
    var $voucherto;
    
    function __construct($idproduct,$idcartrule) {
        $this->id = $idproduct;
        $p = new ProductCore($this->id);
        $this->active = $p->active;
        $this->name = $p->name[1];
        $link = new LinkCore;
        $sqli = "select * from ps_image where id_product=".$this->id." and cover=1;";
        $rowi = Db::getInstance()->getRow($sqli);
        $this->image = $link->getImageLink($p->link_rewrite, $rowi['id_image'], 'home_default');
        $this->baseprice = $p->getPrice();
        $sqlii = "select  id_cart_rule, date_from, date_to, code, reduction_amount from ps_cart_rule where id_cart_rule=".$idcartrule;
        $rowii = Db::getInstance()->getRow($sqlii);
        $this->vouchervalue = $rowii['reduction_amount'];
        $this->finalprice = $this->baseprice - $this->vouchervalue;
        $this->codevoucher = $rowii['code'];
        $this->voucherfrom = $rowii['date_from'];
        $this->voucherto = $rowii['date_to']; 
        
       }
}


class app{
    
    var $domain;
    var $expired = [];
    var $today = [];
    var $tomorrow = [];
    var $allproduct = [];
    var $descvoucher = [];
    
    
    
    function __construct($domain,$descvoucher){
       $this->domain = $domain;
       $this->descvoucher = $descvoucher;
       $this->allproduct = $this->getallproduct($this->descvoucher);
       $this->today = $this->gettoday($this->allproduct);
       $this->tomorrow = $this->gettomorrow($this->allproduct);
       $this->expired = $this->getexpired($this->allproduct);
    }
    
    function getallproduct($descvoucher){
        $array = [];
        foreach($descvoucher as $descvouchers){
           $sql = "select ps_cart_rule.id_cart_rule, id_item from ps_cart_rule join ps_cart_rule_product_rule_group on ps_cart_rule.id_cart_rule = ps_cart_rule_product_rule_group.id_cart_rule join ps_cart_rule_product_rule on ps_cart_rule_product_rule_group.id_product_rule_group = ps_cart_rule_product_rule.id_product_rule_group join ps_cart_rule_product_rule_value on ps_cart_rule_product_rule.id_product_rule = ps_cart_rule_product_rule_value.id_product_rule  where description = '".$descvouchers."';";
            if($row = Db::getInstance()->ExecuteS($sql)){
                foreach ($row as $rows){
                    $array[] = new produk($rows['id_item'],$rows['id_cart_rule']);
                }
            }
       }
        return $array; 
    } 
    
    function gettoday($allproduct){
        $array = [];
        foreach ($allproduct as $allproducts){
            if($allproducts->active==='1' && (date("Y-m-d H:i:s", strtotime($allproducts->voucherfrom)) <  date("Y-m-d H:i:s") ) && ( date("Y-m-d H:i:s") < date("Y-m-d H:i:s", strtotime($allproducts->voucherto)) )){
                $array[] = $allproducts;
            }
        }
        return $array;
    }
    
    function gettomorrow($allproduct){
        $array = [];
        foreach ($allproduct as $allproducts){
            if($allproducts->active==='1' && (date("Y-m-d H:i:s", strtotime($allproducts->voucherfrom)) >  date("Y-m-d H:i:s") ) ){
                $array[] = $allproducts;
            }
        }
        return $array;
    }
    
    function getexpired($allproduct){
        $array = [];
        foreach ($allproduct as $allproducts){
            if($allproducts->active==='1' && (date("Y-m-d H:i:s", strtotime($allproducts->voucherto)) <  date("Y-m-d H:i:s") ) ){
                $array[] = $allproducts;
            }
        }
        return $array;
    }
    
    function arraytojson($array){
        $json= "";
        foreach ($array as $produk) {
            if($produk != $array[0]){
                $json .=",";
            }
            $json .="{";
            $json .='"id":"'.$produk->id.'"';
            $json .=',';
            $json .='"nama":"'.$produk->name.'"';
            $json .=',';
            $json .='"gambar":"'.$produk->image.'"';
            $json .=',';
            $json .='"harga_awal":"'.$produk->baseprice.'"';
            $json .=',';
            $json .='"harga_final":"'.$produk->finalprice.'"';
            $json .=',';
            $json .='"nilai_voucher":"'.$produk->vouchervalue.'"';
            $json .=',';
            $json .='"code_voucher":"'.$produk->codevoucher.'"';
            $json .=',';
            $json .='"voucher_from":"'.$produk->voucherfrom.'"';
            $json .=',';
            $json .='"voucher_to":"'.$produk->voucherto.'"';
            $json .="}";
        }
        
        return $json;
    }
}

$domain = "localhost/prestashop";
$descvoucher = array("xyz","abc");
$app = new app($domain,$descvoucher);
echo '[['.$app->arraytojson($app->expired).'],['.$app->arraytojson($app->today).'],['.$app->arraytojson($app->tomorrow).'], "'.$app->domain.'" ]';


?>




























































