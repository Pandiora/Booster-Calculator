<?php
header("Content-Type: text/html; charset=utf-8");

function getBoosterRows($steamid){

	include 'db_connect.php'; # DB-Verbindung herstellen
	$mysqli->set_charset("utf8"); # Charset setzen
	
	$urlpart = 'http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key=';
	$steamapi_key = 'insertyourapikeyhere';
	$steam_id = $steamid;
	$appid = '';

	// Use Curl instead of file_get_contents because its 3 times faster
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $urlpart.$steamapi_key.'&steamid='.$steam_id.'&format=json');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$api_call = curl_exec($ch);
	$json = json_decode($api_call, true);

	// Iterate over decoded json and get appid?
	foreach ($json as $k=>$v) {
		foreach($v as $key=>$value) {
			foreach($value as $arKey => $arValue) {
				$appid .= '\''.$arValue['appid'].'\',';
			}
		}
	}
	
	if($_COOKIE['currency'] == 'USD'){
		$get = file_get_contents('https://www.google.com/finance/converter?a=1&from=EUR&to=USD');
		$get = explode("<span class=bld>",$get);
		$get = explode("</span>",$get[1]);  
		$converted_amount = preg_replace("/[^0-9\.]/", null, $get[0]);
	}

	$appid = rtrim($appid, ","); // remove last comma
	$result = mysqli_query($mysqli, 
	"SELECT 
		(ROUND((b.gems/b.avg_price_buy),0)) as gems_ratio,
		(ROUND(((b.gems/1000)*(SELECT price_sell FROM steam_games_items WHERE appid = '753')),2)) as gems_price,
		(ROUND((((b.lowest_price_buy/1.15)*100)/100),2)) as low_buy,
		(ROUND((((b.avg_price_buy/1.15)*100)/100),2)) as avg_buy,
		(ROUND((((b.highest_price_buy/1.15)*100)/100),2)) as high_buy, 
		(ROUND((((b.lowest_price_sell/1.15)*100)/100),2)) as low_sell,
		(ROUND((((b.avg_price_sell/1.15)*100)/100),2)) as avg_sell, 
		(ROUND((((b.highest_price_sell/1.15)*100)/100),2)) as high_sell,
		b.total_buy_orders as total_buy,
		b.total_sell_orders as total_sell,
		b.gems as gems, 
		b.gametitle as title, 
		b.appid,
		TIMEDIFF(NOW(), b.last_updated) as updated,
		i.price_buy as booster_price_buy,
		i.price_sell as booster_price_sell,
		i.item_image_high as image
		FROM steam_games_booster b
	INNER JOIN (SELECT appid, price_sell, price_buy, item_image_high FROM steam_games_items WHERE itemtype = 'Booster-Pack') i ON b.appid = i.appid 
	WHERE b.appid IN($appid)
	ORDER BY gems_ratio ASC 
	LIMIT 25") or die(mysqli_error($mysqli));
	
	$boosterrow = $low_sell = $avg_sell = $high_sell = $low_buy = $avg_buy = $high_buy = $gems_price = $booster_price_sell = '';
	
	
	while ($row = $result->fetch_assoc()){
		
		if($_COOKIE['currency'] == 'USD'){
			$low_sell = number_format(round($row['low_sell'] * $converted_amount, 2), 2).' $';
			$avg_sell = number_format(round($row['avg_sell'] * $converted_amount, 2), 2).' $';
			$high_sell = number_format(round($row['high_sell'] * $converted_amount, 2), 2).' $';
			$low_buy = number_format(round($row['low_buy'] * $converted_amount, 2), 2).' $';
			$avg_buy = number_format(round($row['avg_buy'] * $converted_amount, 2), 2).' $';
			$high_buy = number_format(round($row['high_buy'] * $converted_amount, 2), 2).' $';
			$gems_price = number_format(round($row['gems_price'] * $converted_amount, 2), 2).' $';
			$booster_price_sell = number_format(round($row['booster_price_sell'] * $converted_amount, 2), 2).' $';
		} else if($_COOKIE['currency'] == 'EUR'){
			$low_sell = $row['low_sell'].' €';
			$avg_sell = $row['avg_sell'].' €';
			$high_sell = $row['high_sell'].' €';
			$low_buy = $row['low_buy'].' €';
			$avg_buy = $row['avg_buy'].' €';
			$high_buy = $row['high_buy'].' €';
			$gems_price = $row['gems_price'].' €';
			$booster_price_sell = $row['booster_price_sell'].' €';			
		}
		
		$updated = date('i',strtotime($row['updated']));
		$boosterrow .= "<div class='booster-row'>
				<div class='booster-img' style='background-image: url(".str_replace('http', 'https', $row['image']).");'></div>
				<a target='_blank' href='http://store.steampowered.com/app/".$row['appid']."/' class='booster-title'>".$row['title']."</a>
				<div class='booster-data'>
					<div class='booster-gems card-data'>
						<div class='card-data-title'>Card-Price (Sell) 
							<div class='info'>i
								<span>
									<span class='arrow-overlay'></span>
									<span class='tooltip-title'>Card-Price (Sell):</span><br>
									This category displays all selling card-prices from low to high.<br><br>
									<span class='low'>green -</span> Is the lowest Card-Price<br>
									<span class='avg'>yellow -</span> Is the average Card-Price<br>
									<span class='high'>red -</span> Is the highest Card-Price<br><br>
									Taxes are already substracted.<br><br>
									Booster-Packs have 3 cards so the sum of these prices<br>
									would be:<span class='low'> ".($low_sell * 3)." </span><span class='avg'> ".($avg_sell * 3)." </span><span class='high'> ".($high_sell * 3)." </span>
								</span>
							</div>
						</div>
						<div class='card-data-value'>
							<div class='prices low'>".$low_sell."</div>
							<div class='prices avg'>".$avg_sell."</div>
							<div class='prices high'>".$high_sell."</div>
						</div>					
					</div>
					<div class='booster-gems card-data'>
						<div class='card-data-title'>Card-Price (Buy)
							<div class='info'>i
								<span>
									<span class='arrow-overlay'></span>
									<span class='tooltip-title'>Card-Price (Buy):</span><br>
									This category displays all buying card-prices from low to high.<br><br>
									<span class='low'>green -</span> Is the lowest Card-Price<br>
									<span class='avg'>yellow -</span> Is the average Card-Price<br>
									<span class='high'>red -</span> Is the highest Card-Price<br><br>
									Taxes are already substracted.<br><br>
									Booster-Packs have 3 cards so the sum of these prices<br>
									would be:<span class='low'> ".($low_buy * 3)." </span><span class='avg'> ".($avg_buy * 3)." </span><span class='high'> ".($high_buy * 3)." </span>
								</span>
							</div>
						</div>
						<div class='card-data-value'>
							<div class='prices low'>".$low_buy."</div>
							<div class='prices avg'>".$avg_buy."</div>
							<div class='prices high'>".$high_buy."</div>
						</div>						
					</div>
					<div class='booster-gems card-data'>
						<div class='card-data-title'>Sell Total
							<div class='info'>i
								<span>
									<span class='arrow-overlay'></span>
									<span class='tooltip-title'>Sell Total:</span><br>
									This category displays the sum of Sell-Orders for all cards and prices.
								</span>
							</div>
						</div>
						<div class='card-data-value'>".$row['total_sell']."</div>					
					</div>
					<div class='booster-gems card-data'>
						<div class='card-data-title'>Buy Total
							<div class='info'>i
								<span>
									<span class='arrow-overlay'></span>
									<span class='tooltip-title'>Buy Total:</span><br>
									This category displays the sum of Buy-Orders for all cards and prices.
								</span>
							</div>						
						</div>
						<div class='card-data-value'>".$row['total_buy']."</div>
					</div>			
					<div class='booster-gems'>
						<div class='info special'>i
							<span>
								<span class='arrow-overlay'></span>
								<span class='tooltip-title'>Gem-Price:</span><br>
								This displays the gems needed to craft this Booster-Pack.
							</span>
						</div>	
						".$row['gems']."
					</div>
					<div class='booster-gems'>
						<div class='info special'>i
							<span>
								<span class='arrow-overlay'></span>
								<span class='tooltip-title'>Gem-Costs:</span><br>
								This displays the money you would need to buy ".$row['gems']."<br>
								gems depending on the current market-price.<br>
								(lowest sell-order)
							</span>
						</div>				
						".$gems_price."
					</div>
					<div class='booster-gems' style='background-image: url(".str_replace('http', 'https', $row['image']).");'>
						<div class='info special'>i
							<span>
								<span class='arrow-overlay'></span>
								<span class='tooltip-title'>Booster-Price:</span><br>
								This displays the currently lowest<br>
								sell-order for this booster.
							</span>
						</div>					
						".$booster_price_sell."
					</div>
				</div>
				<div class='booster-footer'>
					<a target='_blank' href='http://steamcommunity.com/market/search?q=&category_753_Game%5B%5D=tag_app_".$row['appid']."&category_753_cardborder%5B%5D=tag_cardborder_0&category_753_cardborder%5B%5D=tag_cardborder_1&category_753_item_class%5B%5D=tag_item_class_2&appid=753#p1_name_asc' class='button'>Show all Cards</a>
					<a target='_blank' href='http://steamcommunity.com/market/listings/753/".$row['appid']."-".$row['title']." Booster Pack' class='button'>Show Booster</a>
					<a target='_blank' href='http://steamcommunity.com/tradingcards/boostercreator/#".$row['appid']."' class='button'>Craft Booster</a>
					<!-- <a class='button'>Refresh Data</a>
					<a class='button'>More Details</a> -->
					<div class='result'>Updated: ".$updated." Minutes ago</div>
				</div>
			</div>";
	}
	
	return $boosterrow;
}
?>