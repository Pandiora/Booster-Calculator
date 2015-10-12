<?php include('lib/getBoosterData.php'); ?>
<html>
<head>
	<meta charset="utf-8"/>
	<title>Booster-Calculator</title>
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
	<link rel='stylesheet' href='css/style-booster.css'/>
	<link rel='stylesheet' href='css/nanoscroller.css'/>
	<script type='text/javascript' src='js/jquery-2.1.4.min.js'></script>
	<script type='text/javascript' src='js/jquery.nanoscroller.min.js'></script>
	<script type='text/javascript' src='js/jquery.cookie.js'></script>
	<script type='text/javascript' src="js/functions-booster.js"></script>
</head>
</body>
	<div id='main'>
		<div id='top-container'>
			<div id='portrait'></div>
			<div class='container-row'>
				<div class='bigtext'>Steam Booster-Profit-Calculator //SBPF</div>
				<div class='liltext'>Calculate the most profitable booster-packs depending on the games you own.</div>
			</div>
			<div class='container-row'>
			</div>
		</div>
		<div id='content' class='nano'>
			<div class='nano-content'>
				<?php if(isset($_COOKIE['steam_id'])) { echo getBoosterRows($_COOKIE['steam_id']); } ?>
				<div class='nano-pane'></div>
			</div>
		</div>
	</div>
	<?php if(!isset($_COOKIE['steam_id'])) { echo "<div id='black'><div id='modal'><div class='modal-text'>Please insert your Steam64-ID</div><div class='modal-row'><input class='id-input' placeholder='765611980422451040'></input><button class='id-submit'>Submit</button></div></div></div>"; } ?>
	<?php if(isset($_COOKIE['steam_id']) && !isset($_COOKIE['currency'])){ echo "<div id='black'><div id='modal'><div class='modal-text'>Please choose your currency</div><div class='modal-row'><button class='input-euro'>€</button><button class='input-dollar'>$</button></div></div></div>"; } ?>
</body>
</html>







