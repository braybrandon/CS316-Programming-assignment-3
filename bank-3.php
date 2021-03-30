
<?php

/*
 * bank-3.php is a bank account file that utilizes front-end jquery with ajax to do all of its 
 * back-end calls. Whenever the user loads the page the jquery does an ajax call to the server
 * requesting for the bank account information. The bank account information is stored via MySQL
 * in a table called bankAccount. The table has 3 columns, an id column, a checking column, and a 
 * savings column. The checking column holds the amount that's in the persons checking account. The
 * savings column holds the amount that's in the persons savings account. After the query for the 
 * account information the server encodes the data into JSON and sends it back to the browser. The
 * browser then decodes the information and displays the account information to the user. The user 
 * has 4 options they can either deposit into checking, deposit into savings, transfer from
 * checking to savings, or transfer from savings into checking. When the user decides to enter
 * information in any 4 of these input fields the browser verifies the information is a valid 
 * numerical number and that it is not negative as well as overdraw the account. Then the browser 
 * will do a post call to update the database without reloading the browser. Then the browser 
 * request for the new account information to update the table on the webpage. There is also a 
 * reset button for testing purposes that will reset the database back to the original bank account
 * values of 100 in checking and 1000 in savings. The reset would not be used during deployment.
 */

require_once './db_creds.inc';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
try {
	//getPDO();
	$PDO = new PDO(K_CONNECTION_STRING, K_USERNAME, K_PASSWORD);
	$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exeption $ex) {
	echo $ex->getMessage();
}
function deposit($account, $amount) {
	return $account + $amount;

}
function withdraw($account, $amount) {
	return $account - $amount;
}
function retrieveData() {
	global $PDO;
	$sql = "SELECT checking, savings FROM bankAccount WHERE id='1'";
	try {
		$stmt = $PDO->query($sql);
		$account = $stmt->fetch();
	} catch (Exception $ex) {
		echo $ex->getMessage();
	}
	return $account;
}
function createTable() {
	global $PDO;
	$sql = "CREATE TABLE IF NOT EXISTS bankAccount (
		id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		checking decimal(15,2) NOT NULL,
		savings decimal(15,2) NOT NULL,
		reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)";
	try {
		$PDO->exec($sql);
		$newData = "INSERT INTO bankAccount (id, checking, savings)
			VALUES(1, 100.00, 1000.00) ON DUPLICATE KEY  UPDATE checking = checking + 0";
		$PDO->exec($newData);
	} catch(Exception $ex) {
		echo $ex->getMessage();	
	}
}
function updateTable($account) {
	global $PDO;
	try {
		$sql = " UPDATE bankAccount SET checking = " . $account["checking"] 
			. ", savings = " . $account["savings"] . " WHERE id = 1";
		$stmt = $PDO->prepare($sql);
		$stmt->execute();
	} catch (Exception $ex) {
		echo $ex->getMessage();	
	}
}
function testInput($data) {
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	if(is_numeric($data)) {
		if($data >= 0) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}
function testTransfer($data, $account) {
	if(testinput($data)) {
		if($data <= $account) {
			return true;
		}else {
			return false;
		}	
	} else {
		return false;
	}

}
createTable();

//if the server requested using a post method then the server finds out what the server is requesting
if($_SERVER["REQUEST_METHOD"] == "POST") {
	$account = retrieveData();
	if(!empty($_POST["getData"])) {
		$myJSON = json_encode($account);
		echo $myJSON;
		return;
	}
	if(!empty($_POST["reset"])) {
		$account["checking"] = 100.00;
		$account["savings"] = 1000.00;

	}
	if(!empty($_POST["dChecking"])) {
		if(testInput($_POST["dChecking"])) {
			$account["checking"] = 
				deposit($account["checking"], $_POST["dChecking"]);
		}
	}
	if(!empty($_POST["dSavings"])) {
		if(testInput($_POST["dSavings"])) {
			$account["savings"] = 
				deposit($account["savings"], $_POST["dSavings"]);
		}
	}
	if(!empty($_POST["tChecking"])) {
		if(testTransfer($_POST["tChecking"], $account["checking"])) {
			$account["savings"] = 
				deposit($account['savings'], $_POST["tChecking"]);
			$account["checking"] = 
				withdraw($account["checking"], $_POST["tChecking"]);
		}
	}
	if(!empty($_POST["tSavings"])) {
		if(testTransfer($_POST["tSavings"], $account["savings"])) {
			$account["checking"] = 
				deposit($account["checking"], $_POST["tSavings"]);
			$account["savings"] = 
				withdraw($account["savings"], $_POST["tSavings"]);
		}
	}
	updateTable($account);
	return;
}


?>
<!DOCTYPE html>
<html lang="en">
	<head>
	<meta charset="UTF-8">
	<title>First Bank of HTML</title>	
	<script
	src="https://code.jquery.com/jquery-3.6.0.min.js"
	integrity="sha384-vtXRMe3mGCbOeY7l30aIg8H9p3GdeSe4IFlP6G8JMa7o7lXvnz3GFKzPxzJdPfGK"
	crossorigin="anonymous"></script>	
<script>

function validateDeposit(num) {
	if (num == "") {
		return false;
	} else if (isNaN(num)) {
		alert(num + " is not a valid integer");
		return false;	
	} else if (num < 0){
		alert("Can not deposit a negative value");
		return false;
	} else {
		return true;
	}	
}
function validateTransfer(transfer, account) {
	if (transfer == "") {
		return false;
	} else if (isNaN(transfer)) {
		alert(transfer + " is not a valid integer");
		return false;	
	} else if (transfer < 0) {
		alert("Can not transfer a negative value");
		return false;

	}else if (parseFloat(transfer)	> parseFloat(account)){
		alert("Cannot transfer $" + transfer + " You only have $" + account + " in your account.");
		return false;
	} else {
		return true;
	}	
}
function clearInput(input) {
	document.forms["myForm"][input].value = null;
}
function getAccountBalance() {

	$.ajax({method: "POST", url: "bank-3.php", data: { getData: " " }}).done(function(response, status) {
		var myData = JSON.parse(response);
		$("#checking").text(myData.checking);
		$("#savings").text(myData.savings);
	});
}
function postBalance(action, amount) {
	var myObj = {};
	myObj[action] = amount;
	$.post("bank-3.php", myObj, function() {
		getAccountBalance();
	});

}
function transferBalance(action, amount, isChecking) {
	var myData;	
	$.ajax({method: "POST", url: "bank-3.php", data: { getData: " " }}).done(function(response, status) {
		myData = JSON.parse(response);
		if(isChecking) {
			if(validateTransfer(amount, myData.checking)) {
				postBalance(action, amount);
			}
		} else {
			if(validateTransfer(amount, myData.savings)) {
				postBalance(action, amount);
			}
		}
	});
}

$(document).ready(function(){
	$("table").css("border", "2px solid blue");
	$("#construction").css("background-color", "yellow");
	getAccountBalance();
	$("form").submit(function(event) {
		var checkingDeposit = document.forms["myForm"]["dChecking"].value;
		var savingsDeposit = document.forms["myForm"]["dSavings"].value;
		var checkingTransfer = document.forms["myForm"]["tChecking"].value;
		var savingsTransfer = document.forms["myForm"]["tSavings"].value;
		if (validateDeposit(checkingDeposit)) {
			postBalance("dChecking", checkingDeposit);
		} if (validateDeposit(savingsDeposit)) {	
		postBalance("dSavings", savingsDeposit);
			} 
		transferBalance("tChecking", checkingTransfer, true);
		transferBalance("tSavings", savingsTransfer, false);
		clearInput("dChecking");
		clearInput("dSavings");
		clearInput("tChecking");
		clearInput("tSavings");
		event.preventDefault();
	});
	$("#reset").click(function() {
		$.post("bank-3.php", {reset: "reset"}, function(response) {
			getAccountBalance();
		});	
	});
});
</script>
	</head>	
	<body>

		<h1>Welcome to the First Bank of HTML&#x2122;</h1>
		<span>Where all our clients are served!</span>
		<span id="construction"
		<br>This web site is under construction, as is our bank.</span>
		<h2>Services offered</h2>
		<form name="myForm" action="">
		<ol>
			<li>Current account information
				<table>
					<tr>
						<td><b>Checking</b></td>
						<td><b>Savings</b></td>
					</tr>
					<tr>
						<td id="checking"></td>
						<td id="savings"></td>
					</tr>
				</table>
			</li>
			<li>Deposit money into checking <input type="text" name="dChecking"> 
				<input type="submit" name="submit" value="Submit"></li>
			<li>Deposit money into savings 
				<input type="text" id="dSavings" name="dSavings"> 
				<input type="submit" name="submit" value="Submit"></li>
			<li>Transfer money from checking into savings 
				<input type="text" id="tChecking" name="tChecking">
				<input type="submit" name="submit" value="Submit"></li>
			<li>Transfer money from savings into checking 
				<input type="text" id="tSavings" name="tSavings"> 
				<input type="submit" name="submit" value="Submit"></li>

		</ol>
		<button id="reset">reset</button>	
		</form>


	</body>
</html>

