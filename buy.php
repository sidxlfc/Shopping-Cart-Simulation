<?php

	session_start();
	//header('Refresh:0');
	if(empty($_SESSION['cart']))
	{
		$cart = [];
		$_SESSION['cart'] = $cart;
	}
	//unset($_SESSION['cart']);
?>

<html>
<head>
	<title>Buy Products</title>
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">	

	<link rel="stylesheet" href="/css/style.css">
	
</head>
<body>

<form action = "buy.php" method = "GET">
	
	<fieldset><legend>Find products:</legend>
	<label> Category : 
	
<?php
	header('Content-Type: text/html');
	//XML :
	$items = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/CategoryTree?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId=72&showAllDescendants=true');
	$xml = new SimpleXMLElement($items);
	//print $items;
		
		print "<select name='category'><option value='72'>Computers</option>";
		foreach ($xml->category->categories->category as $key) 
		{
			print "<optgroup label = " . $key->name . ">";
			foreach ($key->categories->category as $newkey) 
			{
				print "<option value = " . $newkey['id'] . ">" . $newkey->name . "</option>";
			}
			print "</optgroup>";	
		}

		print "</select>";
?>
	
	</label>
	<br/>
	<label>Search keywords: <input type="text" name="search" style="cursor: auto; background-image: none; background-position: 0% 0%; background-repeat: repeat;">
		<label>
			<input type="submit" value="Search">
		</label>
	</label>
	</fieldset>

</form>

<?php
		if(isset($_GET['category']) && isset($_GET['search']))
		{
			print "<table name = 'table' border = '1'>
					<th>Product Name</th>
					<th>Image</th>
					<th>Description</th>
					<th>Price</th>";
			$searchCategory = $_GET['category'];
			$searchTerm = $_GET["search"];
			//print $searchCategory . " " . $searchTerm;
			$result = file_get_contents('http://sandbox.api.shopping.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId=' . $searchCategory . '&keyword=' . $searchTerm);
			$result = new SimpleXMLElement($result);
			
			foreach ($result->categories->category->items->product as $key) 
			{
				print "<tr>";
				print "<td>" . (String)$key->name . "</td>";
				print "<td><a href = buy.php?buy=" . $key['id'] . "><img src=" . $key->images->image[0]->sourceURL . "></img></td>";
				print "<td>" . (String)$key->fullDescription . "</td>";
				print "<td>" . (String)$key->minPrice . "</td>";	
				print "</tr>";
			}

			print "</table>";
		}

		if(isset($_GET['buy']))
		{
			$product_id = $_GET['buy'];
			$item = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&productId=' . $product_id);
			//print $item;
			$item = new SimpleXMLElement($item);
			$main = $item->categories->category->items->product;
			$temp = [];
			array_push($temp, (String)$product_id);
			array_push($temp, (String)$main->name);
			array_push($temp, (String)$main->images->image[0]->sourceURL);
			array_push($temp, (String)$main->minPrice);
			array_push($temp, (String)$main->productOffersURL);
			array_push($_SESSION['cart'], $temp);
			//header('Refresh:0');
		}

		if (isset($_GET['clear']))
		{
			unset($_SESSION['cart']);
			//header('Refresh:0');
		}

		if (isset($_GET['delete']))
		{
			$itemToBeDeleted = $_GET['delete'];
			$basket = $_SESSION['cart'];
			for ($i = 0; $i < sizeof($basket); $i++) 
			{
				if($basket[$i][0] == $itemToBeDeleted)
				{
					unset($basket[$i]);
					$_SESSION['cart'] = $basket;
					sort($_SESSION['cart']);
					break;
				}
			}
		}
?>

<?php

	print "<h3>Your basket : </h3>";

	print "<table name = 'cart' border = '1' class = 'table table-striped table-bordered table-condensed table-hover'>
		<th>Product ID</th>
		<th>Product Name</th>
		<th>Image</th>
		<th>Price</th>";
		//<th>Offers URL</th>";
	
	if(isset($_SESSION['cart']))
	{
		$basket = $_SESSION['cart'];

		$i = 0;

		for ($i = 0; $i < sizeof($basket); $i++) 
		{
			print "<tr>";
			print "<td>" . $basket[$i][0] . "</td>";
			print "<td>" . $basket[$i][1] . "</td>";
			print "<td><a href='" . $basket[$i][4] . "'><img src=" . $basket[$i][2] . "</a></img></td>";
			print "<td>" . $basket[$i][3] . "</td>";	
			print "<td><a href='buy.php?delete=" . $basket[$i][0] . "'>Delete</a></td>";
			print "</tr>";
		}
		print "</table>";
	}

	print "<br/><br/>";
?>

<form action = "buy.php" method = "GET">
	<input type="hidden" name="clear" value="1">
	<input type="submit" value="Empty Basket">
</form>

</body>
</html>