<?php
include('../core/init.inc.php');

$article = 'meme_main';
$commentName = "";
if (isset($_POST['name']))
	$commentName = $_POST['name'];

if (isset($_POST['firstname'], $_POST['lastname'], $_POST['email'])) {
	$errors = array();
	
	if(preg_match('/^[a-z]+$/i', $_POST['firstname']) === 0) {
		$errors[] = '<b>Your first name must contain only letters.</b>';
	}	//Check to see if the forms contain letters
	


	if(preg_match('/^[a-z]+$/i', $_POST['lastname']) === 0) {
		$errors[] = '<b>Your last name must contain only letters.</b>';
	}	//Check to see if the forms contain letters
	
		if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === false) {
		//filter_var filters a string, in this case the 'email'
		//The chosen filter is 'FILTER_VALIDATE_EMAIL' this checks to see if the string is a valid email.
			$errors[] = '<b>The email address is invalid.</b>';
		}
}
if(isset($_POST['name'], $_POST['comment'], $_POST['submit'])) {
	$name=$_POST['name'];
	$comment=$_POST['comment'];
	$submit=$_POST['submit'];
 
	$dbLink = mysql_connect("localhost", "root", "CDTJD49E42FHM");
		mysql_query("SET character_set_client=utf8", $dbLink);
		mysql_query("SET character_set_connection=utf8", $dbLink);
	 
	if($submit)
		{
		if($name&&$comment)
		{
			$insert=mysql_query("INSERT INTO commenttable (name,comment, articleTitle) VALUES ('$name','$comment', '$article') ");
			}
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
<!--Replace link with good web font you want.<link href='http://fonts.googleapis.com/css?family=PT+Sans+Narrow' rel='stylesheet' type='text/css' /> - See more at: http://www.newthinktank.com/2011/09/how-to-layout-a-website/#sthash.lWAaNgcS.dpuf -->

<link rel="stylesheet" type="text/css" href="../Core/CPLS_Stylesheet.css">

<title>CollegePartyLifestyle</title>

</head>

<body>

<script src="../core/scripts/jquery-1.6.2.min.js"></script>
	<div id="content">
		
		<div id="header">
		
		<a href = "../index.php"><img src="../core/images/cplswebsitebanner.pdf" alt="Insert logo here" width="100%"
		height="109px" id="Logo" /></a>
		
		<div id = "socialNetworks">
			<a href="facebook.com"><img src="../core/images/Facebook.jpg" alt="Insert logo here" width="40"
		height="40px" id = "facebook"/></a>
			<a href="instagram.com"><img src="../core/images/instagram.jpg" alt="Insert logo here" width="40"
		height="40px" id = "instagram"/></a>
			<a href="twitter.com"><img src="../core/images/twitter.jpg" alt="Insert logo here" width="40"
		height="40px" id = "twitter"/></a>
		</div>
		
		<div id = "searchBar">

	<form action="../search/search.php" method="post">
		<input type = "text" name="search" placeholder= "Search Articles" />
		<input type = "submit" value="Search" />
	</form>

		</div><!--End of SearchBar-->
		
		</div><!--End of Header-->
		<div id = "menuBar">
		<?php echo '<a href ="../party/' . $party . '.php">Party</a> | 
		<a href ="../fashion/' . $fashion . '.php">Fashion</a> | 
		<a href ="../girls/' . $girls . '.php">Girls</a> | 
		<a href ="../memes/' . $memes . '.php">Memes</a>'  ?> 
		</div><!--End of menuBar-->
		
		<div id="contentBackground">
			<div id="leftSidebar">
				<div class="leftContainer">
					<div class = "leftBanner"><h4>WHO WE ARE</h4></div>
					
					<p class ="sidebarExcerpt"><img src="../core/images/index/CPLS_Logo.jpg" alt="Logo"
								width="73px" height="73px" id="logo" /><br />Here is some textHere is some text
								Here is some textHere is some textHere is some textHere is some text
								Here is some textHere is some textHere is some textHere is some text
					
					</p>
				</div><!-- End of leftContainer -->
				
				<div class = "recentArticles">
					<div class = "leftBanner"><h4>RECENT ARTICLES</h4></div>
					<?php
				 $query = mysql_query("SELECT * FROM articles ORDER BY article_id DESC")
							or die(mysql_error());
						$iterations = 0;
						while($row = mysql_fetch_array($query)){
						$title = $row['articleTitle'];
						$date = $row['date'];
						$summary = $row['articleSummary'];
						$views = $row['views'];
						$likes = $row['likes'];
						$dislikes = $row['dislikes'];
						$recentArticle = $row['recentArticle'];
						$section = $row['articleSection'];
						
						$iterations = $iterations + 1;
						
						if ($iterations <= 5) {
								if($section == "Memes")
									$link = '/collegepartylifestyle/Memes/article/' . $title . '.php';
								else
									$link = '../' . $section . '/article/' . $title . '.php';
								echo '<div class = "recentArticleTitle"><a href="' . $link . '">' . $title . '</div></a>' . $recentArticle;
								echo '<a href="' . $section . '/article/' . $title . '">Read More...</a>';
								echo '<div class = "horzRule"> </div>';
						}
					}
					
				?>
			
				</div><!--End of recent articles-->
			</div> <!--End of left side bar-->
				<div class = "articleMiddleColumn">
					<div class = "articleMiddleBannerTitle">MEMES</div>
						<div class = "article">
						<div class = "articleTitle">MEMES</div>
					
					<?php
					$page = 0;
					
					//Print out articles						
						
					    $query = mysql_query("SELECT * FROM girls")
							or die(mysql_error());
							
						//$iterations = $page * $girls_per_page;
						$iterator = 0;
					
						while($iterator < $num_of_memes) {
							echo '<a href = "meme.php?id=' . $iterator . '"><div id = "memes"><img src = "Images/' . $iterator . '.jpg" alt="Inset Image here" width = "250px" height = "250px" id="girlsImage" /><p></div></a>';
							$iterator++;
						}
					
						//if ($iterations <= 0) {
					//	echo $iterations;
						//$image_link = "images/" . $title . "/0.jpg";
						//$title = 'article/' . $title . '.php';
						//$iterations = $iterations - 1;
						/*if($iterations <= ($girls_per_page * -1)){
						$nextPage = $page + 1;
							//echo '<div class = "pageNav"><a href = "echo '$nextPage';">Next Page</a></div>'; //THIS DOES NOT WORK!
							break;
							}
						}
						else
						$iterations = $iterations - 1;
						}*/
						
						
						
					?>
				
					
					</div> <!--End of article-->
					<hr>


				</div> <!--End of articleMiddleColumn-->
				
			<div id = "rightSidebar">
			
			<div class = "subscribeContainer">
			
				<div class="rightBannerTitle"><h4>SUBSCRIBE NOW!</h4></div>
				
				<div id= "ebookForm">
		<?php		
		if(empty($errors) === false && $_POST['submit']) { //Have errors
			//echo "<script>alert(''<ul>', '<li>',  implode('<li> <li>', $errors), '</li>', '</ul>'');</script>";
			echo "<p><b>Invalid input.</b></p>";
			//echo '<li>';
			
		//	echo implode('<li> <li>', $errors);
			
			//echo '</li>';
			
		//	echo '</ul>';
		
		} else if(!(isset($_POST['firstname'], $_POST['lastname'], $_POST['email'])) && isset($_POST['submit'])){ //Have submitted an empty form
			echo '<p><b>Please fill in the form</b></p>';
		} else{ //Submitted form and no errors
			if(isset($_POST['submit']))
				if(add_user($_POST['firstname'], $_POST['lastname'], $_POST['school'], $_POST['email']) === false) {
					echo "<b>You have already subscribed.</b>";
					$errors[] = "Your email is already in our database."; //This may not be true.
				}
			
			else{
			echo '<b>You have subscribed!</b><br>';
			}
		}

		?> 
		
	
	<form action = "" method="post">
		
		<label for="firstname">First name:</label>
		<input type = "text" name = "firstname" id = "firstname" size="20" /><br>
		<p>
		<label for="lastname">Last name:</label>
		<input type = "text" name = "lastname" id = "lastname" size="20" /><br>
		</p>
		<p>
		<label for="school">School(optional):</label><br>
		<input type = "text" name = "school" id = "school" size="20" /><br>
		</p>
		<p>
		<label for="email">Email:</label> <br>
		<input type="text" name="email" id = "email" size="20" /><br>
		</p>
		<p>
		<input type="submit" name="submit" value="Submit" />
		</p>
	</form>
	</div>

				
			</div><!--End of rightContainer-->
			
			<div class = "whatsHot">
				<div class = "rightBannerTitle"><h4>Whats Hot!</h4></div>
				</div><!--End of Whats Hot!-->
			</div><!--End of rightSidebar"-->
		</div><!--End of contentBackground-->
	</div> <!--End of content-->

</body>
</html>