<?php
$app->get('/', function ($request, $response, $args) {
	$db = new Common_model;

	/*$res = $db->selTable("login","*");
	print_r($res); exit;*/
	 // Load query common model
    echo json_encode((object)array("status" => true, "data" => "hjfd"));
});

/*Get all customer names*/
$app->post('/getCustomerList', function ($request, $response, $args) {
	$db = new Common_model;
	$allPostVars = $request->getParsedBody();
	// print_r($allPostVars); exit;
	if($allPostVars["q"] != ""){
		$res = $db->selTable("customer","*","name LIKE '%".$allPostVars["q"]."%'");

	    if ($res) {
	    	echo json_encode((object)array("status" => true, "data" => $res));
	    }else{
	    	echo json_encode((object)array("status" => false, "data" => "Something went Wrong"));
	    }
	}else{
		echo json_encode((object)array("status" => false, "data" => "no records"));
	}
});

/*Save the bill details with bill items*/
$app->post('/saveBill', function ($request, $response, $args) {
	$db = new Common_model;
	$allPostVars = $request->getParsedBody();
	// echo number_format((float)120.560, 3, '.', ''); exit;
	// print_r($allPostVars); exit;
	if (!isset($allPostVars["custName"]["customerId"]) || $allPostVars["custName"]["customerId"] == "") {
		$insArr = array();
		$insArr["name"] = $allPostVars["custName"];
		$insArr["gst_no"] = $allPostVars["custGst"];
		$insArr["mobile"] = $allPostVars["custMob"];
		$insArr["email"] = $allPostVars["custEmail"];
		$insArr["address"] = $allPostVars["custCity"];

		
		$custInsert = $db->insert("customer",$insArr);
		// $allPostVars["custName"] = $custInsert;
		$custId = $custInsert;
	}else{
		$custId = $allPostVars["custName"]["customerId"];
		$upArr = array();
		if($allPostVars["custName"]["gst_no"] != $allPostVars['custGst']){
			$upArr['gst_no'] = $allPostVars['custGst'];
		}
		if($allPostVars["custName"]["mobile"] != $allPostVars['custMob']){
			$upArr['mobile'] = $allPostVars['custMob'];
		}
		if($allPostVars["custName"]["email"] != $allPostVars['custEmail']){
			$upArr['email'] = $allPostVars['custEmail'];
		}
		if($allPostVars["custName"]["address"] != $allPostVars['custCity']){
			$upArr['address'] = $allPostVars['custCity'];
		}

		$itemInsert = $db->update("customer",$upArr,"customerId =".$custId);
	}

	$total = 0;
	$items = array();
	/*$condCustName = array();
	$condCustName["customerId"] = $allPostVars["custName"];
	*/
	$insBill = array();

	$billId = $db->nextInsertId("bills");
	$invNo = time().$billId;
	// print_r($invNo) ; exit;

	$insBill["billNo"] = $invNo;
	$insBill["customerId"] = $custId;
	$insBill["date"] = date("Y/m/d");
	$insBill["gstStatus"] = $allPostVars["gst"];
	$insBill["status"] = $allPostVars["isPaid"];
	$insBill["engineersName"] = $allPostVars["engineersName"];
	$insBill["location"] = $allPostVars["siteLocation"];
	$insBill["siteName"] = $allPostVars["siteName"];


	$itemCnt = 0;
	for ($i=1; $i <= $allPostVars["noOfItem"]; $i++) { 
		$items[$i] = array();
		$insItem = array();

		$allPostVars["item".$i."Unit"] = number_format((float)$allPostVars["item".$i."Unit"], 3, '.', '');
		array_push($items[$i], $allPostVars["item".$i]);
		array_push($items[$i], $allPostVars["item".$i."Price"]);
		array_push($items[$i], $allPostVars["item".$i."Rate"]);
		array_push($items[$i], $allPostVars["item".$i."Unit"]);
		
		$insItem["billId"] = $billId;
		$insItem["name"] = $allPostVars["item".$i];
		$insItem["rate"] = $allPostVars["item".$i."Rate"];
		$insItem["unit"] = $allPostVars["item".$i."Unit"];
		$insItem["amount"] = $allPostVars["item".$i."Price"];
		$insItem["date"] = $allPostVars["item".$i."Date"];

		$itemInsert = $db->insert("bill_items",$insItem);
		$total += $allPostVars["item".$i."Price"];
		$itemCnt++;
	}
	if($allPostVars["gst"] == 1){
		$insBill["amount"] = $total+($total*0.18);
		$insBill["gstAmount"] = $total*0.18;
	}else{
		$insBill["amount"] = $total;
		$insBill["gstAmount"] = 0;
	}

	if($allPostVars["isPaid"] == 2){
		$insBill["amountReceived"] = $allPostVars["amountReceived"];
		$insBill["amountPending"] = $insBill["amount"] - $allPostVars["amountReceived"];
	}else if($allPostVars["isPaid"] == 1){
		$insBill["amountReceived"] = $insBill["amount"];
		$insBill["amountPending"] = 0;
	}else{
		$insBill["amountReceived"] = 0;
		$insBill["amountPending"] = $insBill["amount"];
	}

	$insBill["itemCount"] = $itemCnt;
	// $insBill["status"] = 0;
	// print_r($insBill); exit;
	$billInsert = $db->insert("bills",$insBill);
	// echo "mfhbhsdbf"; exit;
	if ($billInsert) {
    	echo json_encode((object)array("status" => true, "data" => $billInsert));
    }else{
    	echo json_encode((object)array("status" => false, "data" => "Something went Wrong"));
    }
});

/*Select the bill details with bill items*/
$app->post('/fetchBill', function ($request, $response, $args) {
	$db = new Common_model;
	$allPostVars = $request->getParsedBody();
	// print_r($allPostVars); exit;
	$res = $db->selRowData("bills","*","billId=".$allPostVars['billId']);
	if ($res) {
		$items = $db->selTable("bill_items","*","billId=".$allPostVars['billId']);
		$res['item'] = $items;

		$custDetail = $db->selRowData("customer","*","customerId=".$res['customerId']);
		$res['customer'] = $custDetail;
	}
	// print_r($res); exit;
	/*$res = $db->exeQuery("SELECT * FROM bills INNER JOIN bill_items ON bills.billId = bill_items.billId WHERE bills.billId = ".$allPostVars['billId']);
	if ($res) {
    	echo json_encode((object)array("status" => true, "data" => $res));
    }else{
    	echo json_encode((object)array("status" => false, "data" => "Something went"));
    }*/
    if ($res) {
    	echo json_encode((object)array("status" => true, "data" => $res));
    }else{
    	echo json_encode((object)array("status" => false, "data" => "Something went Wrong"));
    }
});

/*Add Accounting Details*/
$app->post('/addAccounting', function ($request, $response, $args) {
	$db = new Common_model;
	$allPostVars = $request->getParsedBody();
	if (isset($allPostVars["shortDesc"]) && $allPostVars["shortDesc"] !=  "") {
		$insArr = array();
		$insArr["shortDesc"] = $allPostVars["shortDesc"];
		$insArr["amount"] = $allPostVars["amount"];
		$insArr["detailDesc"] = $allPostVars["detailDesc"];
		$insArr["date"] = $allPostVars["date"];
		$insArr["status"] = $allPostVars["status"];

		$insertAcc = $db->insert("accounting",$insArr);
		if ($insertAcc) {
    		echo json_encode((object)array("status" => true, "data" => $insertAcc));
	    }else{
	    	echo json_encode((object)array("status" => false, "data" => "Something went"));
	    }
	}else{
    	echo json_encode((object)array("status" => false, "data" => "Something went Wrong"));
    }
});

/*Fetch Accounting Table*/
$app->post('/fetchTable', function ($request, $response, $args) {
	$db = new Common_model;
	$allPostVars = $request->getParsedBody();
	// print_r($allPostVars); exit;
	$res = $db->selTable("accounting","*","","accId desc");
	if ($res) {
    	echo json_encode((object)array("status" => true, "data" => $res));
    }else{
    	echo json_encode((object)array("status" => false, "data" => "Something went Wrong"));
    }
});

/*Fetch Customers Search*/
$app->post('/fetchCustomersSearch', function ($request, $response, $args) {
	$db = new Common_model;
	$allPostVars = $request->getParsedBody();
	// print_r($allPostVars); exit;
	$res = $db->selTable("customer","*","name LIKE '%".$allPostVars['q']['name']."%'","customerId desc");
	if ($res) {
    	echo json_encode((object)array("status" => true, "data" => $res));
    }else{
    	echo json_encode((object)array("status" => false, "data" => "Something went Wrong"));
    }
});


/*Fetch Customers Table*/
$app->post('/fetchCustomers', function ($request, $response, $args) {
	$db = new Common_model;
	$allPostVars = $request->getParsedBody();
	// print_r($allPostVars); exit;
	$res = $db->selTable("customer","*","","customerId desc");
	if ($res) {
    	echo json_encode((object)array("status" => true, "data" => $res));
    }else{
    	echo json_encode((object)array("status" => false, "data" => "Something went Wrong"));
    }
});


/*Fetch Customers Details Account*/
$app->post('/fetchCustomerDetails', function ($request, $response, $args) {
	$db = new Common_model;
	$allPostVars = $request->getParsedBody();
	$accTotals = getTotalAndPending($allPostVars["customerId"]); 
	// print_r($accTotals); exit;
	$customerDetails = $db->selRowData("customer","*","customerId=".$allPostVars["customerId"]);
	$customerAccDetails = $db->selTable("bills","*","customerId=".$allPostVars["customerId"],"billId desc");

	$result = array();
	$result["customerDetails"] = $customerDetails;
	$result["customerAccDetails"] = $customerAccDetails;
	$result["accTotals"] = $accTotals;
	// print_r($result); exit;
	echo json_encode((object)array("status" => true, "data" => $result));
	/*if ($res) {
    	echo json_encode((object)array("status" => true, "data" => $res));
    }else{
    	echo json_encode((object)array("status" => false, "data" => "Something went Wrong"));
    }*/
});

function getTotalAndPending($customerId = ""){
	$db = new Common_model;
	$customerAccDetails = $db->selTable("bills","sum(amount) as totalAmt, sum(amountReceived) as recAmt, sum(amountPending) as pendingAmt","customerId=".$customerId);
	return($customerAccDetails);
	// print_r($customerAccDetails); exit;
	/*if ($customerAccDetails != "") {
		foreach ($customerAccDetails as $key => $value) {
			$res = $db->selTable("bill_items","sum(amount)","billId=".$value["billId"]);
			print_r($res); exit;
		}
	}*/
}


/*Update the bill details with bill items*/
$app->post('/updateBill', function ($request, $response, $args) {
	$db = new Common_model;
	$allPostVars = $request->getParsedBody();
	// print_r($allPostVars); exit;
	/*if (!isset($allPostVars["custName"]["customerId"]) || $allPostVars["custName"]["customerId"] == "") {
		$insArr = array();
		$insArr["name"] = $allPostVars["custName"];
		$custInsert = $db->insert("customer",$insArr);
		$custId = $custInsert;
	}else{
		$custId = $allPostVars["custName"]["customerId"];
	}*/
	$customeDetails = $db->selTable("bills","customerId","billId=".$allPostVars["billId"]);
	// print_r($customeDetails); exit;
	if(count($customeDetails) > 0 && !empty($customeDetails[0]['customerId'])){
		$upCust = array();
		$upCust['gst_no'] = $allPostVars['custGst'];
		$upCust['mobile'] = $allPostVars['custMob'];
		$upCust['email'] = $allPostVars['custEmail'];
		$upCust['address'] = $allPostVars['custCity'];

		$custUpdateRes = $db->update("customer",$upCust,"customerId=".$customeDetails[0]['customerId']);
	}

	$total = 0;
	$items = array();
	$updateBill = array();

	$billId = $allPostVars["billId"];
	$billNo = $allPostVars["billNo"];

	/*$insBill["billNo"] = $invNo;
	$insBill["customerId"] = $custId;*/
	$todayDate = date("Y/m/d");
	/*$insBill["gstStatus"] = $allPostVars["gst"];
	$insBill["status"] = $allPostVars["isPaid"];*/


	$itemCnt = 0;
	for ($i=1; $i <= $allPostVars["noOfItem"]; $i++) { 
		// $items[$i] = array();
		$updateItemArr = array();
		/*array_push($items[$i], $allPostVars["item".$i]);
		array_push($items[$i], $allPostVars["item".$i."Price"]);
		array_push($items[$i], $allPostVars["item".$i."Rate"]);
		array_push($items[$i], $allPostVars["item".$i."Unit"]);*/

		$allPostVars["item".$i."Unit"] = number_format((float)$allPostVars["item".$i."Unit"], 3, '.', '');
		

		$updateItemArr["name"] 		= $allPostVars["item".$i];
		$updateItemArr["rate"] 		= $allPostVars["item".$i."Rate"];
		$updateItemArr["unit"] 		= $allPostVars["item".$i."Unit"];
		$updateItemArr["amount"] 	= $allPostVars["item".$i."Price"];
		$updateItemArr["date"] 		= $allPostVars["item".$i."Date"];

		if(isset($allPostVars["itemId".$i]) && $allPostVars["itemId".$i] != ""){

			$itemId = $allPostVars["itemId".$i];
			$itemInsert = $db->update("bill_items",$updateItemArr,"itemId=".$itemId);
		}else{
			$updateItemArr["billId"] 	= $billId;
			$itemInsert = $db->insert("bill_items",$updateItemArr);
		}

		$total += $allPostVars["item".$i."Price"];
		$itemCnt++;
	}
	$updateBill["gstStatus"] = $allPostVars["gst"];
	$updateBill["engineersName"] = $allPostVars["engineersName"];
	$updateBill["location"] = $allPostVars["siteLocation"];
	$updateBill["siteName"] = $allPostVars["siteName"];
	$updateBill["status"] = $allPostVars["isPaid"];
	// $updateBill["date"] = date("Y/m/d");
	if($allPostVars["gst"] == 1){
		$updateBill["amount"] = $total+($total*0.18);
		$updateBill["gstAmount"] = $total*0.18;
	}else{
		$updateBill["amount"] = $total;
		$updateBill["gstAmount"] = 0;
	}
	// $updateBill["amount"] = round($updateBill["amount"]);

	// print_r($updateBill); exit;

	if($allPostVars["isPaid"] == 2){
		$updateBill["amountReceived"] = $allPostVars["amountReceived"];
		$updateBill["amountPending"] = $updateBill["amount"] - $allPostVars["amountReceived"];
	}else if($allPostVars["isPaid"] == 1){
		$updateBill["amountReceived"] = $updateBill["amount"];
		$updateBill["amountPending"] = 0;
	}else{
		$updateBill["amountReceived"] = 0;
		$updateBill["amountPending"] = $updateBill["amount"];
	}

	$updateBill["itemCount"] = $itemCnt;
	$billUpdateRes = $db->update("bills",$updateBill,"billNo=".$billNo." AND billId=".$billId);
	if ($billUpdateRes) {
    	echo json_encode((object)array("status" => true, "data" => $billId));
    }else{
    	echo json_encode((object)array("status" => false, "data" => "Something went Wrong"));
    }
});

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', 'http://localhost')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

	