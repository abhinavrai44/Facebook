<?php
//NOTE: FOR POST DATA, USE GETGRAPHEDGE SINCE POST IS AN EDGE. 
// CODE TO GET THE PAGE NAMES AND ACCEPT THE PAGE

		if( !isset($_POST["url"]))
		{
		session_start();
		// Load facebook SDK 
		require_once '../facebook-php-sdk-v4-5.0.0/src/Facebook/autoload.php'; 

		// Create fb object
		$fb = new Facebook\Facebook([
		  'app_id' => '',  
		  'app_secret' => '',
		  'default_graph_version' => 'v2.6',
		]);

		//  Get short access token
		$helper = $fb->getRedirectLoginHelper();
		try {
		  $accessToken = $helper->getAccessToken();
		  
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		  // When Graph returns an error
		  echo 'Graph returned an error: ' . $e->getMessage();
		  exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
		  // When validation fails or other local issues
		  echo 'Facebook SDK returned an error: ' . $e->getMessage();
		  exit;
		}


		if (isset($accessToken)) 
		{
			// CODE TO GET LONG LIVE TOKEN AND STORE IN SESSION FOR FUTURE USE
			$_SESSION["token"] = $accessToken;
			$_SESSION['facebook_access_token'] = (string) $accessToken;
			$oAuth2Client = $fb->getOAuth2Client();
			$longLivedAccessToken	 = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
			$_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
			$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
			
			// CODE TO TEST IF ACCESS TOKEN IS WORKING
			try {
				$user = $fb->get('/me');
				$user = $user->getGraphObject()->asArray();
			} catch(Facebook\Exceptions\FacebookResponseException $e) {
				// When Graph returns an error
				echo 'Graph returned an error: ' . $e->getMessage();
				exit;
			} catch(Facebook\Exceptions\FacebookSDKException $e) {
				// When validation fails or other local issues
				echo 'Facebook SDK returned an error: ' . $e->getMessage();
				exit;
			}


			// Get the pages where user is admin, "me" refers to user 	
			// Graph API QUERY	/
			$pages = $fb->get('me/accounts/');
			// Exwcute query
			$pages = $pages->getGraphEdge()->asArray();			
			foreach($pages as $page)
			{
				echo '<br>'.$page['name'].'<br>';
			}
		}
		}
?>

<?php
// CODE TO GET THE POST DATA FOR THE ACCEPTED PAGE
   if( isset($_POST["url"])) {
	    session_start();
	    //Load facebook SDK 
		require_once '../facebook-php-sdk-v4-5.0.0/src/Facebook/autoload.php';
		// Create fb object
		$fb = new Facebook\Facebook([
		  'app_id' => '',  
		  'app_secret' => '',
		  'default_graph_version' => 'v2.6',
		]);
		// Get access token from session
		$accessToken = $_SESSION["token"];
		
		// CODE TO TEST IF ACCESS TOKEN IS WORKING
		if (isset($accessToken)) {
		  // set default access token 
		  $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
			try {
				$user = $fb->get('/me');
				$user = $user->getGraphObject()->asArray();
			} catch(Facebook\Exceptions\FacebookResponseException $e) {
				// When Graph returns an error
				echo 'Graph returned an error: ' . $e->getMessage();
				exit;
			} catch(Facebook\Exceptions\FacebookSDKException $e) {
				// When validation fails or other local issues
				echo 'Facebook SDK returned an error: ' . $e->getMessage();
				exit;
			}
			
			// CODE TO GET THE PAGE ID FOR THE GIVEN PAGE NAME $_POST["url"]
			$pages = $fb->get('me/accounts/');

			$pages = $pages->getGraphEdge()->asArray();
			
			$start = microtime(true);
			
			foreach($pages as $page)
			{
				if($_POST["url"] == $page['name'])
				{
					// Sets limit for the number of posts
					//TODO: Need to change this to -7 to -37 days logic 
					$limit = 100;
					$count = 0;
					echo '<br>'.$page['name'].'<br>';
					$page_id = $page[id]; 
					// Get data older than 30 days and set until
					//TODO: Convert in into -7 to -37 days
					$day = strtotime('-30 day');
					//Write query to get 100 posts which are 30 days older
					$posts = $fb->get('/'.$page_id.'/posts?until='.$day.'&fields=id'.'&limit='.$limit);
					$posts = $posts->getGraphEdge()->asArray();
					
					echo "<center> <b>Page Posts Impressions(Scope Lifetime)</center> </b>";
					// Declare variables 
					$total_impressions_sum = 0;
					$total_impressions_unique_sum = 0;
					$total_impressions_paid_unique_sum = 0;
					$total_impressions_organic_unique_sum = 0;
					$total_impressions_organic_sum = 0;
					$total_impressions_viral_unique_sum = 0;
					$total_impressions_organic_max = 0;
					$total_impressions_organic_min = 0;
					$total_impressions_organic_unique_max = 0;
					$total_impressions_organic_unique_min = 0;

					//CODE TO GET ALL THE DATA FOR ALL THE POSTS
					//TODO MERGE ALL THE GET CALLS INTO A SINGLE CALL LIKE COMBO_CALLBACK.PHP LINE NUMBER 117
					// Traverse Posts
					foreach ($posts as $post)
					{
						$post_id = $post[id];

						// GET MESSAGE AND CREATED TIME OF A POST
						$impressions = $fb->get('/'.$post_id.'/?fields=message,created_time');
						$impressions = $impressions->getGraphNode()->asArray();
						//print_r($impressions);
						echo '<br>'."Post Message : ".$impressions['message']."  ".'<br>';
						print_r($impressions['created_time']);
						
						// GET IMPRESSIONS OF A POST
						$impressions = $fb->get('/'.$post_id.'/insights/post_impressions?fields=values{value}');
						$impressions = $impressions->getGraphEdge()->asArray();
						
						foreach ($impressions as $impression)
						{
							foreach($impression as $a)
							{
								foreach($a as $z)
								{
									$total_impressions = $z[value];
								}
							}
						}
						$total_impressions_sum += $total_impressions;
						echo '<br>'."Total Impressions : ".$total_impressions.'<br>';
						
						// GET UNIQUE IMPRESSIONS OF A POST
						$impressions = $fb->get('/'.$post_id.'/insights/post_impressions_unique?fields=values{value}');
						$impressions = $impressions->getGraphEdge()->asArray();
						
						foreach ($impressions as $impression)
						{
							foreach($impression as $a)
							{
								foreach($a as $z)
								{
									$total_impressions_unique = $z[value];
								}
							}
						}
						
						
						$total_impressions_unique_sum += $total_impressions_unique;
						echo '<br>'."Total Unique Impressions : ".$total_impressions_unique.'<br>';
						
						// GET PAID UNIQUE IMPRESSIONS OF A POST
						$impressions = $fb->get('/'.$post_id.'/insights/post_impressions_paid_unique?fields=values{value}');
						$impressions = $impressions->getGraphEdge()->asArray();
						
						foreach ($impressions as $impression)
						{
							foreach($impression as $a)
							{
								foreach($a as $z)
								{
									$total_impressions_paid_unique = $z[value];
								}
							}
						}
						
						$total_impressions_paid_unique_sum += $total_impressions_paid_unique;
						echo '<br>'."Total Paid and Unique Impressions : ".$total_impressions_paid_unique.'<br>';
						
						// GET ORGANIC UNIQUE IMPRESSIONS OF A POST
						$impressions = $fb->get('/'.$post_id.'/insights/post_impressions_organic_unique?fields=values{value}');
						$impressions = $impressions->getGraphEdge()->asArray();
						
						foreach ($impressions as $impression)
						{
							foreach($impression as $a)
							{
								foreach($a as $z)
								{
									$total_impressions_organic_unique = $z[value];
								}
							}
						}
						
						if($total_impressions_organic_unique > $total_impressions_organic_unique_max)
							$total_impressions_organic_unique_max = $total_impressions_organic_unique;
						if($total_impressions_organic_unique < $total_impressions_organic_unique_min)
							$total_impressions_organic_unique_min = $total_impressions_organic_unique;
						
						$total_impressions_organic_unique_sum += $total_impressions_organic_unique;
						echo '<br>'."Total Organic and Unique Impressions : ".$total_impressions_organic_unique.'<br>';
						
						// GET ORGANIC IMPRESSIONS OF A POST
						$impressions = $fb->get('/'.$post_id.'/insights/post_impressions_organic?fields=values{value}');
						$impressions = $impressions->getGraphEdge()->asArray();
						
						foreach ($impressions as $impression)
						{
							foreach($impression as $a)
							{
								foreach($a as $z)
								{
									$total_impressions_organic = $z[value];
								}
							}
						}
						
						$total_impressions_organic_sum += $total_impressions_organic;
						echo '<br>'."Total Organic Impressions : ".$total_impressions_organic.'<br>';
						
						if($total_impressions_organic > $total_impressions_organic_max)
							$total_impressions_organic_max = $total_impressions_unique;
						if($total_impressions_organic < $total_impressions_organic_min)
							$total_impressions_organic_min = $total_impressions_unique;
						
						// GET VIRAL UNIQUE IMPRESSIONS OF A POST						
						$impressions = $fb->get('/'.$post_id.'/insights/post_impressions_viral_unique?fields=values{value}');
						$impressions = $impressions->getGraphEdge()->asArray();
						
						foreach ($impressions as $impression)
						{
							foreach($impression as $a)
							{
								foreach($a as $z)
								{
									$total_impressions_viral_unique = $z[value];
								}
							}
						}
						$total_impressions_viral_unique_sum += $total_impressions_viral_unique;
						echo '<br>'."Total Viral and Unique Impressions : ".$total_impressions_viral_unique.'<br>';
						
						
						$count = $count + 1;
						if($count >= $limit)
							break;
						

					}	
					
					// CALCULATE AVERAGES AND PRINT THEM
					$avg_impressions = round(($total_impressions_sum/$count),2);
					$avg_impressions_unique = round(($total_impressions_unique_sum/$count),2);
					$avg_impressions_organic_unique = round(($total_impressions_organic_unique_sum/$count),2);
					$avg_impressions_organic = round(($total_impressions_organic_sum/$count),2);
					$avg_impressions_paid_unique = round(($total_impressions_paid_unique_sum/$count),2);
					$avg_impressions_viral_unique = round(($total_impressions_viral_unique_sum/$count),2);

					echo '<br>'."Average Impressions : ".$avg_impressions;
					echo '<br>'."Average Unique Impressions : ".$avg_impressions_unique;
					echo '<br>'."Average Organic and Unique Impressions : ".$avg_impressions_organic_unique;
					echo '<br>'."Average Organic Impressions : ".$avg_impressions_organic;
					echo '<br>'."Average Paid and Unique Impressions : ".$avg_impressions_paid_unique;
					echo '<br>'."Average Viral and Unique Impressions : ".$avg_impressions_viral_unique;
					echo '<br>'."Maximum Organic Impressions : ".$total_impressions_organic_max;
					echo '<br>'."Minimum Organic Impressions : ".$total_impressions_organic_min;
					echo '<br>'."Maximum Organic and Unique Impressions : ".$total_impressions_organic_unique_max;
					echo '<br>'."Minimum Organic and Unique Impressions : ".$total_impressions_organic_unique_min;
				}
				
				
				break;
				
			}
			$time_elapsed_secs = microtime(true) - $start;
			print '<br>'."Time : ".$time_elapsed_secs;
			
		}
   } 
?>


<html>
   <body id="submit_link">
      <form action = "<?php $_PHP_SELF ?>" method = "POST">
         Page: <input type = "text" name = "url" />
         <input id="submit_button" type = "submit" class="like" value="Submit Page Name" />
      </form>
   
   </body>
</html>