<?php
include('../application/db_config.php');
//callback a=AC0001&o=10004&ak=Accepted&m=OK&dt=17:10&u=testuser&p=test
/**
 * Created by Alex Media.
 * User: Goran Trajilovic
 * Date: 20/08/19
 * Time: 22:28
 * ordercallback.php
 */
$sUserAgent="";
$useraccount="test";
$userpwd="test";
$resId="";
$orderId="";
$orderStatus="";
$orderReason="";
$orderTime="";
$defaultResId="AC0001";
$defaultcount="testuser";
$defaultpwd="test";


if(isset($_SERVER["HTTP_USER-AGENT"])){
	$sUserAgent=$_SERVER["HTTP_USER-AGENT"];
}

if(isset($_GET['u'])){
 $useraccount=strtolower($_GET['u']);
}
if(isset($_GET['p'])){
 $userpwd=strtolower($_GET['p']);
}

if(isset($_GET['a'])){
 $resId=$_GET['a'];
}

if(isset($_GET['o'])){
 $orderId=$_GET['o'];
}

if(isset($_GET['ak'])){
 $orderStatus=$_GET['ak'];
}
if(isset($_GET['m'])){
 $orderReason=$_GET['m'];
}
if(isset($_GET['dt'])){
 $orderTime=$_GET['dt'];
}
if(($useraccount==$defaultcount)&&($userpwd==$defaultpwd) && ($resId==$defaultResId))
{
	$ret=0;
	if($orderStatus=='Accepted'){
		$ret=updateOrderstatus($orderId,'Delivered'); // stavi Status na 1
	}
	else if($orderStatus=='Rejected'){
		$ret=updateOrderstatus($orderId,'Cancel:'.$orderReason); // stavi status na Reject
	}
	if($ret==1){
		$ret=0;
		$ret=checkNewOrder();
		header("HTTP/1.1 206 Partial Content");
		if($ret!=0){
			header("More-orders: 1");
		}
		echo $ret;
	}

}
else{
	print substr("",0,1);
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
  $sql = "UPDATE fooddelivery_bookorder SET status='$orderStatus' where id='$orderId'";
if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();




?>
