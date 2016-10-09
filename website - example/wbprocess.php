 <?php
  require('conf.php');
  session_start();

 
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