<?php
include('../application/db_config.php');
/**
 * Created by Alex Media.
 * User: Goran Trajilovic
 * Date: 22/08/19
 * Time: 08:28
 */
	$resid = isset($_GET['a'])?$_GET['a']:'0';

	if (mysqli_connect_errno()) {
		$data = array("Code"=>403,"Message"=>"Could not connect with database","Status"=>"error");
		echo json_encode($data);
		exit;
	}
	

        $sql = "select * from fooddelivery_bookorder where status='0' and res_id=$resid limit 1";
	$result = mysqli_query($conn,$sql);
	if(mysqli_num_rows($result)>0)
	{
		$data = '';
		$row=mysqli_fetch_assoc($result);
		//$orderid = $row['orderid'];
		$orderid = $row['id'];

		$s = "SELECT fb.id,fb.total_price,fb.delivery_date_time,  fb.created_at , fb.address , fb.payment , fb.lat , fb.long , fb.status, count(fd.order_id) as count , fd.ItemId , fu.fullname , fu.phone_no  from 
        fooddelivery_bookorder fb inner join fooddelivery_food_desc fd on fb.id = fd.order_id 
        inner join fooddelivery_users fu on fb.user_id = fu.id
        WHERE fb.id = $orderid";

		$r = mysqli_query($conn,$s);
		$row1=mysqli_fetch_assoc($r);
		
		if($row1['status'] == '0'){
			$method = 0;
		}
		
		if($row1['status'] == '1'){
			$method = 1;
		}
		
		$data = '#1*'.$method.'*'.$orderid.'*';

		$order = "SELECT fd.order_id,fd.ItemId , fd.ItemQty , fd.ItemAmt , fs.id , fs.name , fs.desc from fooddelivery_food_desc fd inner join fooddelivery_submenu fs on fd.ItemId = fs.id WHERE fd.order_id = $resid";
                $order_result = mysqli_query($conn,$order);
		while($order_row = mysqli_fetch_assoc($order_result)){
			
			$ordersid = "$resid";
			$data .= $order_row['ItemQty'].';';
			$data .= $order_row['name'].';';
			$data .= $order_row['ItemAmt'].';';

			$extra = "SELECT extra,spice,extra_price FROM `extras` WHERE res_id=$ordersid";
			$extra_result = mysqli_query($conn,$extra);
			while($extra_row = mysqli_fetch_assoc($extra_result)){
				if($extra_row['extra'] != ''){
					$extra_data = explode(',',$extra_row['extra']);
					$price_data = explode(',',$extra_row['extra_price']);
					$length = count($extra_data);
					for($i = 0; $i<$length; $i++){
						$data .= "1;".$extra_data[$i].';'.$price_data[$i].';';
					}
				}
				
				if($extra_row['spice']!='')
					$data .= "1;".$extra_row['spice'].';---;';
			}
		}
		
		if($row1['payment'] == 'Credit')
		{
			$c = 6;
		}
		
		if($row1['payment'] == 'COD'){
			$c = 7;
		}
		$data .= '*0*0;';
		$data .= $row1['total_price'].';4;';
		$data .= $row1['fullname'].';';
		$data .= $row1['address'].';'.$row1['delivery_date_time'].';113;';
		$data .= $c.";cod:;";
		$data .= $row1['phone_no'].';*Comment#';
		send_to_printer($data);
	}
	function send_to_printer($content)
	{
		$isRange = '';
		
		if(isset($_SERVER["HTTP_RANGE"]))
		{
			$isRange=$_SERVER["HTTP_RANGE"];
		}
		
		$contentLength = strlen($content);
		
		if ($isRange)
		{
			$bytes=explode("=",$isRange);
			$range=explode("-",$bytes[1]);

			$startBytes=intval($range[0]);
			$toBytes = intval($range[1]);
			
			if($toBytes>=$contentLength)
			{
				if($contentLength>1){
				  $toBytes=$contentLength-1;
				}
				else
				{
					$toBytes=1;
				}
			} 
			
	
			if (($startBytes>$contentLength))
			{
			  $startBytes=$toBytes;
			  header("HTTP/1.1 416 Request Range Not Satisfialbe");
			  $sStr1="";
			  print substr($sStr1,$startBytes+1-1,$toBytes+1-$startBytes);
		
			}
			else
			{
				ob_get_clean(); //added to fix ZIP file corruption
				ob_start(null, 0,PHP_OUTPUT_HANDLER_REMOVABLE);
				$contentRange="bytes ".($startBytes)."-".($toBytes)."/".($contentLength);
				$rangesize = ($toBytes+1 - $startBytes) > 0 ? ($toBytes+1 - $startBytes) : 0;  
				
				$sStr1 = substr($content, $startBytes, $toBytes);
					
				if($startBytes>0)
					$contentLength = strlen($sStr1);
					
				if(($contentLength-1) > ($toBytes - $startBytes)){
					header("HTTP/1.1 206 Partial Content");
				}
				else{
					header("HTTP/1.1 200 OK");
				}
				header("Content-Range".": ".$contentRange);	
				header("Content-Length:" .$rangesize);
				echo $sStr1;
				ob_flush();
		     	flush();
				
			}
		}
		else 

		{

			$startBytes=0;
			$toBytes=$contentLength;
			$sStr1 = substr($content, $startBytes, $toBytes);
			print substr($sStr1,$startBytes+1-1,$toBytes+1-$startBytes); 
		}
	} 
	
	function closeFlush(){
		ob_flush();
		flush();
	}

