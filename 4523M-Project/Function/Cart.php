<?php
session_start();

if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = [];
}

function addToCart($fid,$fname,$fprice,$addQty){
    $cart = $_SESSION['cart'];
    $found = false;
    foreach($cart as &$item){
        if($item['fid'] == $fid){
            $item['qty'] += $addQty;
            $found = true;
            break;
        }
    }
    unset($item);
    if(!$found){
        $cart[] = [
            'fid' => $fid,
            'fname' => $fname,
            'fprice' => $fprice,
            'qty' => $addQty
        ];
    }
    $_SESSION['cart'] = $cart;
}

function delCartItem($fid){
    $newCart = [];
    foreach($_SESSION['cart'] as $item){
        if($item['fid'] != $fid){
            $newCart[] = $item;
        }
    }
    $_SESSION['cart'] = $newCart;
}

function clearCart(){
    $_SESSION['cart'] = [];
}

function getCartList(){
    return $_SESSION['cart'];
}

function getCartTotal(){
    $total = 0;
    foreach($_SESSION['cart'] as $i){
        $total += $i['fprice'] * $i['qty'];
    }
    return $total;
}
?>