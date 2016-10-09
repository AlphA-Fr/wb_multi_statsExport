<?php
  require('conf.php');
  session_start();

 /* Server Infos */
  
	$url="http://188.165.224.84:7277/index.xml";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);    // get the url contents

	$Xmldata = curl_exec($ch); // execute curl request
	curl_close($ch);

	$xml = simplexml_load_string($Xmldata);
	 
 /* Data queries */
 // navbar $_GET
 
 
 if (isset($_GET['username'])) {$action = "username";}
 else if (isset($_GET['kills'])) {$action = "kill_count";}
 else if (isset($_GET['deaths'])) {$action = "death_count";}
 else if (isset($_GET['teamkills'])) {$action = "teamkill_count";}
 else if (isset($_GET['ratio'])) {$action = "ratio";}
 if (isset($action)) {
	 if ($action == "username") {
		$data = $dbh->prepare("SELECT * FROM wb_players ORDER BY username ASC LIMIT 50");
		$data->execute();
		$data = $data->fetchAll();
	 }
	 else {
	 $data = $dbh->prepare("SELECT * FROM wb_players ORDER BY ".$action." DESC LIMIT 50");
	 $data->execute();
	 $data = $data->fetchAll();
	 }
 }
 else {
	 $data = $dbh->prepare("SELECT * FROM wb_players ORDER BY kill_count DESC LIMIT 50"); 
	 $data->execute(); 
	 $data = $data->fetchAll();
 }
 
 $TotalDatas = $dbh->prepare("SELECT SUM(kill_count) AS 'kills', SUM(death_count) AS 'deaths', SUM(teamkill_count) AS 'teamkills', COUNT(username) as 'count' FROM wb_players"); 
 $TotalDatas->execute(); 
 $TotalDatas = $TotalDatas->fetch();
 
 $BestTeamkill = $dbh->prepare("SELECT username AS 'teamkills' FROM wb_players ORDER BY teamkill_count DESC LIMIT 1"); 
 $BestTeamkill->execute(); 
 $BestTeamkill = $BestTeamkill->fetch();
 
 $BestPlayer = $dbh->prepare("SELECT username AS 'username' FROM wb_players ORDER BY kill_count DESC LIMIT 1"); 
 $BestPlayer->execute(); 
 $BestPlayer = $BestPlayer->fetch();
 
  /* End of data queries */
 
		
 $file = fopen('test.txt', 'w+'); 
 fputs($file, "username = ". $_GET['username']. "\n");
 fputs($file, "unique_id = ". $_GET['uniqueid']. "\n");
 fputs($file, "key = ". $_GET['key']. "\n");
 fputs($file, "event = ". $_GET['event']. "\n");
 fclose($file);
 
  /* warband server process */
	if (isset($_GET['uniqueid']) && isset($_GET['key']) && isset($_GET['username']) && isset($_GET['event'])) {
	   if ($_GET['key'] == "5v948JeA7u8pYtVA") { // security key verification
			$username = $_GET['username'];
			$req = $dbh->prepare("SELECT * FROM wb_players WHERE username = :username");
			$req->execute(array(":username" => $_GET['username']));
			$req = $req->fetch();
			
			echo "requniqueid=".$req['unique_id'];
			
			
			if (empty($req)) { // if no username found add a row
				$req = $dbh->prepare("INSERT INTO wb_players (username, unique_id) VALUES (:username, :uniqueid);");
				$req->execute(array(":username" => $username,":uniqueid" => $_GET['uniqueid']));
			
				echo "Adding a new user to the database => username = $username<br/>";
			}
			else if ($_GET['uniqueid'] == $req['unique_id']) {echo "User found in the database => username = $username<br/>";
		 
			if ($_GET['event'] == 0) { // if event == 0 add death to player
				echo "Adding +1 death<br/>";
				$req = $dbh->prepare("UPDATE wb_players SET death_count = death_count + 1 WHERE username = :username");
				$req->execute(array(":username" => $username)); 
			}
			else if ($_GET['event'] == 1) { // if event == 1 add kill to player
				echo "Adding +1 kill<br/>";
				$req = $dbh->prepare("UPDATE wb_players SET kill_count= kill_count + 1 WHERE username = :username");
				$req->execute(array(":username" => $username)); 
			}
			else if ($_GET['event'] == 2) { // if event == 2 add teamkill to player and remove 1 kill
				echo "Adding 1 teamkill and remove 1 kill<br/>";
				$req = $dbh->prepare("UPDATE wb_players SET teamkill_count = teamkill_count + 1, kill_count = kill_count - 1 WHERE username = :username");
				$req->execute(array(":username" => $username)); 
			} 
			
			}
			
		}
	}
?>

<!DOCTYPE html>
	<html lang="en">
	<?php include("head.php"); ?>
		<body>
			<nav class="container text-center center-all header-fixed-top">
				<div class="nav-horyzontal" id="navbar"> 
					<ul class="text-pright">
						<li class="text-right !important"><a href="index.php" class="">Native Groupfighting</a></li>
					</ul>
					<ul class="text-pleft">
						<li class="text-left !important"><a href="https://forums.taleworlds.com/index.php/topic,349008.0.html" class="">Taleworlds Thread</a></li>
					</ul>
				</div>
			</nav>
			<img src="/img/navbar-logo" id="main-logo">	
			<!--<img src="/img/wood" id="main-logo2">	-->			
		</body>
		
		<div id="content">
			<nav class="container">
				<div class="row">
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-1" id="inline" style="text-left; padding: 0 !important"><a href="index.php"><img src="/img/stats.png"> Position</a></div>
					<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 text-left" id="inline" style="text-align: center !important"><a href="index.php?username">Player Name</a></div>
					<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4" id="inline" style="padding: 0!important"><a href="">Awards</a></div>
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-1" id="inline" style="padding: 0!important"><a href="index.php?kills"><img src="/img/kills.png"> Kills</a></div>
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-1" id="inline" style="padding: 0!important"><a href="index.php?deaths"><img src="/img/death.png"> Deaths</a></div>
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 red" id="inline" style="padding: 0 !important"><a href="index.php?teamkills"> Teamkills</a></div>
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-1" id="inline" style="padding: 0 !important"><a href="index.php?ratio"><img src="/img/ratio.png"> Ratio</a></div>
				</div>
				<hr width="100%" style="margin-top: 0px!important">
				
				<!-- player diplay -->
				<?php 
				//search 
				if (isset($_POST['search'])) {
					$Search = $dbh->prepare("SELECT * FROM wb_players WHERE username LIKE :username ORDER BY kill_count DESC");
					$Search->execute(array(":username" => '%'.$_POST['search'].'%'));
					$Search = $Search->fetchAll();
					
					foreach ($Search as $elem) {
					$Pos = $dbh->prepare("SELECT COUNT(*) AS 'count' FROM wb_players WHERE kill_count > :player_kills");
					$Pos->execute(array(":player_kills" => $elem['kill_count']));
					$Pos = $Pos->fetch();
					$deaths = $elem['death_count'];
					$kills = $elem['kill_count'];
					if ($deaths == 0) {$ratio = $kills;}
					else {$ratio = number_format($kills / $deaths, 2, '.', '');}
					$req = $dbh->prepare("UPDATE wb_players SET ratio = :ratio WHERE username = :username");
					$req->execute(array(":ratio" => $ratio, ":username" => $elem['username']));				?> 
					<div class="row" style="">
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 orange" id="inline" style="text-right !important"><p><?php echo $Pos['count'] + 1; ?></p></div>
					<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 orange text-left" id="inline" style="transform: translateX(40%); text-left !important"><p><?php echo $elem['username']; ?></p></div>
					<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 orange" id="inline" style="text-left !important"><p>N/A</p></div>
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 orange" id="inline" style="text-left !important"><p><?php echo $elem['kill_count']; ?></p></div>
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 orange" id="inline" style="text-left !important"><p><?php echo $elem['death_count']; ?></p></div>
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 red" id="inline" style="text-left !important"><p><?php echo $elem['teamkill_count']; ?></p></div>
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 orange" id="inline" style="text-left !important"><p><?php echo $elem['ratio'] ?></p></div>
				</div>
				<?php //end of search	
				} }
				else {
				$i = 0;
				foreach ($data as $elem) { $i++;
				$deaths = $elem['death_count'];
				$kills = $elem['kill_count'];
				$teamkills = $elem['teamkill_count'];
				if ($deaths == 0)
					$ratio = $kills;
				else
					$ratio = number_format($kills / $deaths, 2, '.', '');
				$req = $dbh->prepare("UPDATE wb_players SET ratio = :ratio WHERE username = :username");
				$req->execute(array(":ratio" => $ratio, ":username" => $elem['username']));				?> 
				<div class="row" style="">
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 orange" id="inline" style="text-right !important"><p><?php echo $i ?></p></div>
					 <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 orange text-left" id="inline" style="transform: translateX(40%); text-left !important"><p><?php echo $elem['username']; ?></p></div>
					<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 orange" id="inline" style="text-left !important"><p>N/A</p></div>
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 orange" id="inline" style="text-left !important"><p><?php echo $kills; ?></p></div>
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 orange" id="inline" style="text-left !important"><p><?php echo $deaths; ?></p></div>
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 red" id="inline" style="text-left !important"><p><?php echo $teamkills; ?></p></div>
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 orange" id="inline" style="text-left !important"><p><?php echo $elem['ratio']; ?></p></div>
				</div>
				<?php } } ?>
				
				
				<div class="row" style="padding-top: 150px">
					<div class="col-lg-12">
					<span class="title">More statistics</span>
					<hr width="100%"  style="margin-top: 0px!important">
						<div class="row">
							<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
								<ul class="text-left" id="stats">
									<li><a>Total player kills :</a></li>
									<li><a>Total player teamkills :</a></li>
									<li><a>Total players :</a></li>
								</ul>
							</div>
							<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
								<ul class="text-left" id="stats">
									<li class="orange"><a><?php echo $TotalDatas['deaths']; ?></a></li>
									<li class="orange"><a><?php echo $TotalDatas['teamkills']; ?></a></li>
									<li class="orange"><a><?php echo $TotalDatas['count']; ?></a></li>
								</ul>
							</div>
							<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
								<ul class="text-left" id="stats">
									<li><a>Best player :</a></li>
									<li><a>Best Teamkiller :</a></li>
									<li><a>Current maps : </a></li>
								</ul>
							</div>
							<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
								<ul class="text-left" id="stats">
									<li class="orange"><a><?php echo $BestPlayer['username'] ?></a></li>
									<li class="orange"><a><?php echo $BestTeamkill['teamkills']; ?></a></li>
									<li class="orange"><a>19</a></li>
								</ul>
							</div>
						</div>		
					</div>
				</div>
				
				<!-- Server Informations -->
				<div class="row" style="padding-top: 50px">
					<div class="col-lg-12">
						<div class="row" style="padding-top: 0px">
							<div class="col-lg-12">
								<span class="title">Server Informations</span>
								<hr width="100%"  style="margin-top: 0px!important">
								<div class="row">
							<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
								<ul class="text-left" id="stats">
									<li><a>Server Name : </a></li>
									<li><a>Gamemode : </a></li>
								</ul>
							</div>
							<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
								<ul class="text-left" id="stats">
									<li class="orange"><a><?php print $xml->Name; ?></a></li>
									<li class="orange"><a> <?php print $xml->ModuleName; ?> / <?php print $xml->MapTypeName; ?> </a></li>
								</ul>
							</div>
							<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
								<ul class="text-left" id="stats">
									<li><a>Active players :</a></li>
									<li><a>Map Name :</a></li>
								</ul>
							</div>
							<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
								<ul class="text-left" id="stats">
									<li class="orange"><a><?php print $xml->NumberOfActivePlayers; ?> / <?php print $xml->MaxNumberOfPlayers; ?></a></li>
									<li class="orange"><a><?php print $xml->MapName; ?></a></li>
								</ul>
							</div>
						</div>		
							</div>
						</div>
					</div>
				</div>
				
				
				<!-- Search Bar -->
				<div class="row" style="padding-top: 50px">
					<div class="col-lg-12">
						<div class="box"  style="padding-top: 50px">
							<div class="container-4">
								<form method="post" action="index.php">
									<input type="search" name="search" id="search" placeholder="Search for a player" />
									<button class="icon"><i class="glyphicon glyphicon-search"></i></button>
								</form>
							</div>
						</div>
					</div>
				</div>
				
				
				<!-- Footer  -->
				<div class="row" style="padding-top: 0px">
					<div class="col-lg-12">
						<div class="footer" style="text-align: left !important;padding-top: 20px"><img src="/img/knight.png"> ©Powered by <a href="http://alphas-projects.com">Alphas-Projects</a></div>
					</div>
				</div>
				
			</nav>
		</div>
		
		

		<?php include("js.php"); ?>
	</html>
