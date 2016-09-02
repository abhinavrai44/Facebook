<?php
//NOTE: FOR PAGE DATA, USE GETGRAPHNODE, SINCE PAGE IS A NODE. 
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
// CODE TO GET THE PAGE DATA FOR THE ACCEPTED PAGE
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
			foreach($pages as $page)
			{
				if($_POST["url"] == $page['name'])
				{
					echo '<br>'.$page['name'].'<br>';
					$page_id = $page[id]; 
					// Create query to get fan data for the given page id using insights
					$fans = $fb->get('/'.$page_id.'/insights/page_fans,page_fans_locale,page_fans_city,page_fans_country,page_fans_gender_age/?fields=name,values{value}');
					$fans = $fans->getGraphEdge()->asArray();
					$page_fans_gender = array('M'=>0, 'F'=>0);
					$page_fans_age = array();
					

					// NOTE: The array contains one entry per field requested. So since we are asking for 5 attributes they size of array will be 5
					
					// Traverse through fans array and check which data is at which index
					foreach ($fans as $f)
					{	
						if($f['name'] == 'page_fans')
							$total_likes = $f['values'][0]['value'];
						else if($f['name'] == 'page_fans_locale')
							$page_fans_locale = $f['values'][2]['value'];
						else if($f['name'] == 'page_fans_city')
							$page_fans_city = $f['values'][2]['value'];
						else if($f['name'] == 'page_fans_country')
							$page_fans_country = $f['values'][2]['value'];

						// Since gender and age come together we need to split them
						// TODO needs to be optimized
						else if($f['name'] == 'page_fans_gender_age')
						{
							echo "<pre>";
							foreach($f as $a)
							{
								foreach($a as $b)
								{
									foreach($b as $z)
									{
										while (list($key, $val) = each($z)) 
										{
											$key = explode(".",$key);
											$page_fans_gender[$key[0]] += $val;

											if (array_key_exists($key[1], $page_fans_age)) 
											{
												$page_fans_age[$key[1]] += $val;
											}
											else
											{
												$page_fans_age[$key[1]] = $val;
											}
											
										}
										
									}
									
								}
							}
							echo "<pre>";
						}
							
					}
					// Print total likes
					echo "<center> <b>Page Fans(Scope Lifetime)</center> </b>";
					echo "Total Likes : ".$total_likes;
					
					// CODE TO GET PERCENTAGE DISTRIBUTION OF LOCALE
					echo '<br>'."Fan Percentage Distribution based on Locale".'<br>';
					arsort($page_fans_locale);
					foreach($page_fans_locale as &$value)
					{
						$value = round((($value / $total_likes) * 100),2);
					}
					echo "<pre>";print_r(($page_fans_locale));echo "<pre>";

					// CODE TO GET PERCENTAGE DISTRIBUTION OF CITY
					echo '<br>'."Fan Percentage Distribution based on City".'<br>';
					arsort($page_fans_city);
					foreach($page_fans_city as &$value)
					{
						$value = round((($value / $total_likes) * 100),2);
					}
					echo "<pre>";print_r($page_fans_city);echo "<pre>";
					// CODE TO GET PERCENTAGE DISTRIBUTION OF COUNTRY
					echo '<br>'."Fan Percentage Distribution based on Country".'<br>';
					arsort($page_fans_country);
					foreach($page_fans_country as &$value)
					{
						$value = round((($value / $total_likes) * 100),2);
					}
					print_r($page_fans_country);

					// CODE TO GET PERCENTAGE DISTRIBUTION OF GENDER AND AGE
					arsort($page_fans_gender);
					while (list($key, $value) = each($page_fans_gender))
					{
						$page_fans_gender[$key] = round((($value / ($total_likes*3)) * 100),2);
					}
					echo '<br>'."Fan Percentage Distribution based on Gender".'<br>';
					print_r($page_fans_gender);
					arsort($page_fans_age);
					while (list($key, $value) = each($page_fans_age))
					{
						$page_fans_age[$key] = round((($value / ($total_likes*3)) * 100),2);
					}
					echo '<br>'."Fan Percentage Distribution based on Age Groups".'<br>';
					print_r($page_fans_age);


					
					//CODE TO GET THE IMPRESSIONS AND ENGAGEMENTS FOR PAST 28 DAYS
					//TODO: CONVERT INTO -7 TO -37 DAY FORMAT

					//Query to get the data for the past 28 days 
					$fans = $fb->get('/'.$page_id.'/insights/page_engaged_users,page_impressions,page_impressions_unique,page_impressions_organic_unique,page_impressions_viral_unique,page_impressions_paid_unique,page_impressions_by_city_unique,page_impressions_by_country_unique,page_impressions_by_locale_unique,page_impressions_by_age_gender_unique?period=days_28&fields=name,values{value}');
					$fans = $fans->getGraphEdge()->asArray();

					$total_impressions_gender = array('M'=>0, 'F'=>0);
					$total_impressions_age = array();
					$total = 0;
			
					// NOTE: The array contains one entry per field requested. So since we are asking for 5 attributes they size of array will be 5
					
					// Traverse through fans array and check which data is at which index. Fans here is all the data					
					foreach ($fans as $f)
					{
						if($f['name'] == 'page_engaged_users')
							$total_engagements = $f['values'][0]['value'];
						else if($f['name'] == 'page_impressions')
							$total_impressions = $f['values'][2]['value'];
						else if($f['name'] == 'page_impressions_unique')
							$total_impressions_unique = $f['values'][2]['value'];
						else if($f['name'] == 'page_impressions_organic_unique')
							$page_impressions_organic_unique = $f['values'][2]['value'];
						else if($f['name'] == 'page_impressions_viral_unique')
							$page_impressions_viral_unique = $f['values'][2]['value'];
						else if($f['name'] == 'page_impressions_paid_unique')
							$page_impressions_paid_unique = $f['values'][2]['value'];
						else if($f['name'] == 'page_impressions_by_city_unique')
							$total_impressions_city = $f['values'][2]['value'];
						else if($f['name'] == 'page_impressions_by_country_unique')
							$total_impressions_country = $f['values'][2]['value'];
						else if($f['name'] == 'page_impressions_by_locale_unique')
							$total_impressions_locale = $f['values'][2]['value'];
						else if($f['name'] == 'page_impressions_by_age_gender_unique')
						{
						// Since gender and age come together we need to split them
						// TODO needs to be optimized
							echo "<pre>";
							foreach($f as $a)
							{
								foreach($a as $b)
								{
									foreach($b as $z)
									{
										while (list($key, $val) = each($z)) 
										{
											$key = explode(".",$key);
											$total += $val;
											$total_impressions_gender[$key[0]] += $val;

											if (array_key_exists($key[1], $total_impressions_age)) 
											{
												$total_impressions_age[$key[1]] += $val;
												
											}
											else
											{
												$total_impressions_age[$key[1]] = $val;
											
											}
											
										}
										
										
									}
									
								}
								
							}
							echo "<pre>";
						}

					}
					
					//PRINT ALL THE DATA
					echo "<center> <b>Page Engagements(Scope 28 days)</center> </b>";
					echo '<br>'."Total Engagements : ".$total_engagements.'<br>';
					echo "Average Engagements per day : ".round(($total_engagements/28),2).'<br>';
					
					echo "<center> <b>Page Impressions(Scope 28 days)</center> </b>";
					echo '<br>'."Total Impressions : ".$total_impressions.'<br>'.'<br>';
					echo "Average Impressions per day : ".round(($total_impressions/28),2).'<br>';
					
					echo "Average <b>Unique</b> Impressions per day : ".round(($total_impressions_unique/28),2).'<br>';
					
					echo "Average <b>Organic Unique</b> Impressions per day : ".round(($page_impressions_organic_unique/28),2).'<br>';
					
					echo "Average <b>Viral Unique</b> Impressions per day : ".round(($page_impressions_viral_unique/28),2).'<br>';
					
					echo "Average <b>Paid Unique </b>Impressions per day : ".round(($page_impressions_paid_unique/28),2).'<br>';
					
					
					
					// CODE TO GET PERCENTAGE DISTRIBUTION OF IMPRESSIONS OF CITY
					echo "<pre>";
					arsort($total_impressions_city);
					echo "Percentage Distribution of Impressions By City". '<br>';
					foreach($total_impressions_city as &$value)
					{
						$value = round((($value / $total_impressions_unique)*100),2);
					}
					print_r($total_impressions_city);
					
					// CODE TO GET PERCENTAGE DISTRIBUTION OF IMPRESSIONS OF COUNTRY
					echo "<pre>";
					arsort($total_impressions_country);
					echo "Percentage Distribution of Impressions By Country". '<br>';
					foreach($total_impressions_country as &$value)
					{
						$value = round((($value / $total_impressions_unique)*100),2);
					}
					print_r($total_impressions_country);
					
					// CODE TO GET PERCENTAGE DISTRIBUTION OF IMPRESSIONS OF LOCALE
					echo "<pre>";
					arsort($total_impressions_locale);
					echo "Percentage Distribution of Impressions By Locale". '<br>';
					foreach($total_impressions_locale as &$value)
					{
						$value = round((($value / $total_impressions_unique)*100),2);
					}
					print_r($total_impressions_locale);
					
					// CODE TO GET PERCENTAGE DISTRIBUTION OF IMPRESSIONS OF GENDER
					arsort($total_impressions_gender);
					while (list($key, $value) = each($total_impressions_gender))
					{
						$total_impressions_gender[$key] = round((($value / ($total)) * 100),2);
					}
					echo '<br>'."Percentage Distribution of Impressions By Gender".'<br>';
					print_r($total_impressions_gender);
					
					// CODE TO GET PERCENTAGE DISTRIBUTION OF IMPRESSIONS OF AGE
					arsort($total_impressions_age);
					while (list($key, $value) = each($total_impressions_age))
					{
						$total_impressions_age[$key] = round((($value / ($total)) * 100),2);
					}
					echo '<br>'."Percentage Distribution of Impressions By Age Groups".'<br>';
					print_r($total_impressions_age);
					
					
					break;
					
				}
				
				
				
				
			}
			
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