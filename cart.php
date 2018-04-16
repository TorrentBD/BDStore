<?php  
error_reporting(E_ALL);
ini_set('display_errors', '1');
include "storescripts/connect_to_mysql.php"; 
?>
<?php 
if(isset($_POST['cid']) && isset($_POST['pid'])){
	$cid = $_POST['cid'];
	$pid = $_POST['pid'];
	$sql = mysqli_query($con,"SELECT * FROM customer_cart WHERE customerid='$cid' LIMIT 1");
	$count = mysqli_num_rows($sql);
	$prodquery = mysqli_query($con,"SELECT * FROM products WHERE id='$pid' LIMIT 1");
	while($row = mysqli_fetch_array($prodquery)){
		$product_name = $row["product_name"];
		$details = $row["details"];
		$price = $row["price"];
		$date_added = $row["date_added"];
	}
	$quantity=1;	
	if($count==0){
		$ssql = mysqli_query($con,"INSERT INTO customer_cart (productid, customerid, product_name, details, price, quantity, date_added) VALUES('$pid','$cid','$product_name','$details','$price','$quantity',now())") or die (mysqli_error());
	} else {
		$already = mysqli_query($con,"SELECT * FROM customer_cart WHERE productid='$pid' AND customerid='$cid' LIMIT 1");
		$acount = mysqli_num_rows($already);
		if($acount!=0){
			while($row = mysqli_fetch_array($already)){
				$aquantity = $row["quantity"];
				}
			$aquantity=$aquantity+1;
			$ssql = mysqli_query($con,"UPDATE customer_cart SET quantity='$aquantity' WHERE productid='$pid' AND customerid='$cid' ") or die (mysqli_error());
		} else {
			$ssql = mysqli_query($con,"INSERT INTO customer_cart (productid, customerid, product_name, details, price, quantity, date_added) 
        	VALUES('$pid','$cid','$product_name','$details','$price','$quantity',now())") or die (mysqli_error());
		}	
	}
	header("location: cart.php"); 
    exit();
}

?>
<?php
session_start();
if (isset($_GET['cmd']) && $_GET['cmd'] == "emptycart" && isset($_SESSION["id"])) {
		$cid = $_SESSION["id"];
		$sql = mysqli_query($con,"SELECT * FROM customer_cart WHERE customerid='$cid' LIMIT 1");
		$count = mysqli_num_rows($sql);
		if($count!=0){
			$sql = mysqli_query($con,"DELETE FROM customer_cart WHERE customerid='$cid'") or die(mysqli_error());
		}
}
?>

<?php
if (isset($_POST['item_to_adjust']) && $_POST['item_to_adjust'] != "" && isset($_SESSION["id"])) {
	$cid = $_SESSION["id"];
	$item_to_adjust = $_POST['item_to_adjust'];
	$quantity = $_POST['quantity'];
	$quantity = preg_replace('#[^0-9]#i', '', $quantity); 
	if ($quantity >= 100) { $quantity = 99; }
	if ($quantity < 1) { $quantity = 1; }
	if ($quantity == "") { $quantity = 1; }
	$ssql = mysqli_query($con,"UPDATE customer_cart SET quantity='$quantity' WHERE productid='$item_to_adjust' AND customerid='$cid' ") or die (mysqli_error());
}
?>

<?php
if (isset($_POST['index_to_remove']) && $_POST['index_to_remove'] != ""  && isset($_SESSION["id"])) {
	$cid = $_SESSION["id"];
	$toremove = $_POST['index_to_remove'];
	$sql = mysqli_query($con,"DELETE FROM customer_cart WHERE customerid='$cid' AND productid='$toremove'") or die(mysqli_error());
}
?>

<?php
$cartOutput = "";
$cartTotal = "";
if(isset($_SESSION["id"])){
	$cid=$_SESSION["id"];
	$sql = mysqli_query($con,"SELECT * FROM customer_cart WHERE customerid='$cid'") or die(mysqli_error());
	$count = mysqli_num_rows($sql);	
	if($count==0){
		$cartOutput = "<h2 align='center'>Your shopping cart is empty</h2>";
	}
	else {
		$i=0;
		while ($lists = mysqli_fetch_array($sql)) {
				$item_id = $lists["productid"];
				$sqls = mysqli_query($con,"SELECT * FROM products WHERE id='$item_id' LIMIT 1") or die(mysqli_error());
				$list = mysqli_fetch_array($sqls);
				$product_name = $list["product_name"];
				$price = $list["price"];
				$details = $list["details"];
				$quantity = $lists["quantity"];
				$pricetotal = $price * $quantity;
				$cartTotal = $pricetotal + $cartTotal;
				$cartOutput .= "<tr>";
		$cartOutput .= '<td><a href="product.php?id=' . $item_id . '">' . $product_name . '</a><br /><img src="inventory_images/' . $item_id . '.jpg" alt="' . $product_name. '" width="40" height="52" border="1" /></td>';
		$cartOutput .= '<td>' . $details . '</td>';
		$cartOutput .= '<td>$' . $price . '</td>';
		$cartOutput .= '<td><form action="cart.php" method="post">
		<input name="quantity" type="text" value="' . $quantity . '" size="1" maxlength="2" />
		<input name="adjustBtn' . $item_id . '" type="submit" value="change" />
		<input name="item_to_adjust" type="hidden" value="' . $item_id . '" />
		</form></td>';
		$cartOutput .= '<td>' . $pricetotal . '</td>';
		$cartOutput .= '<td><form action="cart.php" method="post"><input name="deleteBtn' . $item_id . '" type="submit" value="X" /><input name="index_to_remove" type="hidden" value="' . $item_id . '" /></form></td>';
		$cartOutput .= '</tr>';
		}
		$cartTotal = "<div style='font-size:18px; margin-top:12px;' align='right'>Cart Total : ".$cartTotal." USD</div>";

	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Your Cart</title>
<link rel="stylesheet" href="style/style.css" type="text/css" media="screen" />
</head>
<body>
<div align="center" id="mainWrapper">
  <?php include_once("template_header.php");?>
  <div id="pageContent">
    <div style="margin:24px; text-align:left;">
	
    <br />
    <table width="100%" border="1" cellspacing="0" cellpadding="6">
      <tr>
        <td width="18%" bgcolor="#C5DFFA"><strong>Product</strong></td>
        <td width="45%" bgcolor="#C5DFFA"><strong>Product Description</strong></td>
        <td width="10%" bgcolor="#C5DFFA"><strong>Unit Price</strong></td>
        <td width="9%" bgcolor="#C5DFFA"><strong>Quantity</strong></td>
        <td width="9%" bgcolor="#C5DFFA"><strong>Total</strong></td>
        <td width="9%" bgcolor="#C5DFFA"><strong>Remove</strong></td>
      </tr>
     <?php echo $cartOutput; ?>
     <!-- <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr> -->
    </table>
    <?php echo $cartTotal; ?>
    <br />
<br />
    <br />
    <br />
    <a href="cart.php?cmd=emptycart">Click Here to Empty Your Shopping Cart</a>
    </div>
   <br />
  </div>
  <?php include_once("template_footer.php");?>
</div>
</body>
</html>