<?php


require 'src/facebook.php';

$today = date('Y-m-d');
$recent_date = date('Y-m-d', strtotime('-0 days', strtotime($today)));
$recent_time = strtotime($recent_date);
$config = array(
'appId' => '692015450839686',
'secret' => '2312e7e3bb4b3941617f406cdd365815',
);

	
$facebook = new Facebook($config);

$user = $facebook->getUser();

if ($user) {
  $logoutUrl = $facebook->getLogoutUrl();
} else {
	$params = array('scope' => 'user_likes,friends_likes,read_stream' );
  $loginUrl = $facebook->getLoginUrl($params);
}

if ($user) {
  try {
   
    $user_profile = $facebook->api('/me');
    $user_likes = $facebook->api('/me/likes');
    $user_status = $facebook->api('/me/statuses');
  } catch (FacebookApiException $e) {
    error_log($e);
    $user = null;
  }
}





$con = mysql_connect("localhost","773409","lister_123");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }



mysql_select_db("773409") or die(mysql_error());

mysql_query("DROP TABLE current_page") 
or die(mysql_error());

  mysql_query("CREATE TABLE current_page(
category VARCHAR(200), 
 category_topic VARCHAR(200),
 page_name VARCHAR(200))")
 or die(mysql_error());  


mysql_query("DROP TABLE like_page") 
or die(mysql_error());

  mysql_query("CREATE TABLE like_page(
category VARCHAR(200), 
 category_topic VARCHAR(200),
 page_name VARCHAR(200))")
 or die(mysql_error());  



?>

<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    
    <style>
      body {
        font-family: 'Lucida Grande', Verdana, Arial, sans-serif;
      }
      h1 a {
        text-decoration: none;
        color: #3b5998;
      }
      h1 a:hover {
        text-decoration: underline;
      }
    </style>
  </head>
  <body>
    
<h1>Know Your Interest Graph</h1>
    <?php if ($user): ?>
     
    <?php else: ?>

      <div>
        <a href="<?php echo $loginUrl; ?>">Login with Facebook to use Know Your Interest Graph App</a>
      </div>
    <?php endif ?>

    

    <?php if ($user): ?>
      <h3><?php echo $user_profile['name']?></h3>
      <img src="https://graph.facebook.com/<?php echo $user; ?>/picture">
      <font size="6" color="blue">
      <b>
      <pre>
   We just found out who you are!!!!
                         What kind of a person you are!!!
                                   And what things interests you!!!!
</pre>
       </b>
      </font>
     <?php
     	  	$i=0;  
    		foreach ($user_likes["data"] as $value)
  		{
 		 	$like_category[$i] = $value["category"];
   			$i++;
  		}
     		$unique_category = array_unique($like_category);
     		$distinct_category[0] = current($unique_category);
  		for ($i=1; $i<count($unique_category); $i++)
  		{
 			$distinct_category[$i] = next($unique_category);
 		}
 		foreach ($user_likes["data"] as $value)
  		{		
  			  	
 		 	for ($i=0; $i<count($distinct_category); $i++)
 			 {
 				 if ($distinct_category[$i] == $value["category"])
 				 {
 				 	$time = substr($value["created_time"],0,10);
 				 	$my_date = date('Y-m-d', strtotime($time));
 				 	
					$expire_time = strtotime($my_date);

					if ($expire_time < $recent_time)
 				 	{
 				 	$likes[$distinct_category[$i]][count($likes[$distinct_category[$i]])]["name"] = $value["name"];
 				 	$likes[$distinct_category[$i]][count($likes[$distinct_category[$i]])-1]["date"] = $my_date;
 				 	$likes[$distinct_category[$i]][count($likes[$distinct_category[$i]])-1]["category"] = $value["category"];
 				 	
 				 	
 				 	 				  				 	
 				 	}
 				 	else
 				 	{
 				 	$recent_likes[$distinct_category[$i]][count($recent_likes[$distinct_category[$i]])]["name"] = $value["name"];
 				 	$recent_likes[$distinct_category[$i]][count($recent_likes[$distinct_category[$i]])-1]["date"] = $my_date;
 				 	$recent_likes[$distinct_category[$i]][count($recent_likes[$distinct_category[$i]])-1]["category"] = $value["category"];
 				 	
			 	}
 				 }
			  }
   			
  		}
 		
 		
 		
 		
 		
 		for ($i=0; $i<count($distinct_category); $i++)
 			 {
 			 $con = mysql_connect("localhost","773409","lister_123");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }

mysql_select_db("773409") or die(mysql_error());

$result = mysql_query("SELECT * FROM category_list") 
or die(mysql_error());  

 			 while($row = mysql_fetch_array( $result )) {
						
						if ($row['category'] == $distinct_category[$i])
							{
							
							
							$category=$row['category'];
							$category_topic=$row['category_topic'];
							$count=count($likes[$distinct_category[$i]]);
							for ($j=0; $j<$count; $j++)
 			 {
 			 				$name=$likes[$distinct_category[$i]][$j]["name"];
 			 				$name = str_replace(' ', '_', $name);
 			 				$name = str_replace('"', '', $name);
 			 				$name = str_replace("'", "", $name);
								mysql_query("INSERT INTO current_page VALUES('".$category ."','".$category_topic ."','".$name ."')") 
or die(mysql_error());
}
							}
	 
										    }
										    mysql_close($con);
										    
										    
 			 }
 			 
   		$con = mysql_connect("localhost","773409","lister_123");
		if (!$con)
  		{
		  die('Could not connect: ' . mysql_error());
  		}

mysql_select_db("773409") or die(mysql_error());
$rel = mysql_query("SELECT DISTINCT category FROM common_category") 
or die(mysql_error());  

 			 while($ro = mysql_fetch_array( $rel )) {
 			 $res = mysql_query("SELECT * FROM current_page") 
or die(mysql_error());  

 			 while($row = mysql_fetch_array( $res )) {
if($ro['category'] == $row['category_topic'])
{

$url = "http://en.wikipedia.org/wiki/".$row['page_name'];
try {
$crl = curl_init();
        $timeout = 5;
        
        curl_setopt ($crl, CURLOPT_URL,$url);
        curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
        $ret = curl_exec($crl);
        curl_close($crl);
        
$max=0;        
       $max = strlen($ret);
       
       echo "<br>";
        $pos = strpos($ret, "<p>");
        $endpos = strpos($ret, "</p>");
        $cat=$ro['category'];
        }
         catch (Exception $e) {
   echo "hi";
}
        $count=0;
        $rel1 = mysql_query("SELECT * FROM common_category WHERE category='".$cat."'") 
or die(mysql_error());  

 			 while($ro1 = mysql_fetch_array( $rel1 )) {
	if($max > 120)
	{
	
        
        $temp = substr_count($ret,$ro1['keyword'] , 0, $max);
        
        if($temp > $count)
        {
        $count = $temp;
        $cat_top = $ro1['keyword'];
        }
        if($count != 0)
        {
         mysql_query("UPDATE current_page SET category='".$cat_top."'
WHERE page_name='".$row['page_name']."'") 
or die(mysql_error());  

        }
}
}
}
 			 }
}

$rs = mysql_query("SELECT * FROM current_page WHERE (category_topic='Public Figure' OR category_topic='Entertainer')") 
or die(mysql_error()); 
while($ro2 = mysql_fetch_array( $rs )) {
$rs1 = mysql_query("SELECT * FROM category_list WHERE category='".$ro2['category']."' ") 
or die(mysql_error()); 
$rs2 = mysql_fetch_array( $rs1 );
mysql_query("UPDATE current_page SET category_topic='".$rs2['category_topic']."'
WHERE page_name='".$ro2['page_name']."'")
or die(mysql_error());
}
$rs = mysql_query("SELECT * FROM current_page WHERE (category_topic='Public Figure' OR category_topic='Entertainer')") 
or die(mysql_error()); 
while($ro2 = mysql_fetch_array( $rs )) {
$rs1 = mysql_query("SELECT * FROM category_list WHERE category='".$ro2['category']."' ") 
or die(mysql_error()); 
$rs2 = mysql_fetch_array( $rs1 );
mysql_query("UPDATE current_page SET category_topic='".$rs2['category_topic']."'
WHERE page_name='".$ro2['page_name']."'")
or die(mysql_error());
}
mysql_close($con);

 		
 		
 		
 		
 		
 		
 		
 		
 		
 		
 		
 		
  		
 		
 		
 		
 		
 		
 		
 		
 		
 		
 		
 		
 		

 		
 		
 		
 		for ($i=0; $i<count($distinct_category); $i++)
 			 {
 			 $con = mysql_connect("localhost","773409","lister_123");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }

mysql_select_db("773409") or die(mysql_error());

$result = mysql_query("SELECT * FROM category_list") 
or die(mysql_error());  

 			 while($row = mysql_fetch_array( $result )) {
						
						if ($row['category'] == $distinct_category[$i])
							{
							
							
							$category=$row['category'];
							$category_topic=$row['category_topic'];
							$count=count($recent_likes[$distinct_category[$i]]);
							for ($j=0; $j<$count; $j++)
 			 {
 			 				$name=$recent_likes[$distinct_category[$i]][$j]["name"];
 			 				$name = str_replace(' ', '_', $name);
 			 				$name = str_replace('"', '', $name);
 			 				$name = str_replace("'", "", $name);
								mysql_query("INSERT INTO like_page VALUES('".$category ."','".$category_topic ."','".$name ."')") 
or die(mysql_error());
}
							}
	 
										    }
										    mysql_close($con);
										    
										    
 			 }
 			 
   		$con = mysql_connect("localhost","773409","lister_123");
		if (!$con)
  		{
		  die('Could not connect: ' . mysql_error());
  		}

mysql_select_db("773409") or die(mysql_error());
$rel = mysql_query("SELECT DISTINCT category FROM common_category") 
or die(mysql_error());  

 			 while($ro = mysql_fetch_array( $rel )) {
 			 $res = mysql_query("SELECT * FROM like_page") 
or die(mysql_error());  

 			 while($row = mysql_fetch_array( $res )) {
 	
if($ro['category'] == $row['category_topic'])
{

$url = "http://en.wikipedia.org/wiki/".$row['page_name'];
		 
try {
$crl = curl_init();
        $timeout = 5;
        
        curl_setopt ($crl, CURLOPT_URL,$url);
        curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
        $ret = curl_exec($crl);
        curl_close($crl);
        
        
       $max = strlen($ret);
        $pos = strpos($ret, "<p>");
        $endpos = strpos($ret, "</p>");
        $cat=$ro['category'];
        }
         catch (Exception $e) {
   echo "hi";
}
if($row['page_name'] == 'Shruti_Hassan')
	{
        echo $max;
        }
        $count=0;
        $rel1 = mysql_query("SELECT * FROM common_category WHERE category='".$cat."'") 
or die(mysql_error());  

 			 while($ro1 = mysql_fetch_array( $rel1 )) {
	if($max > 120)
	{
	
        $temp = substr_count($ret,$ro1['keyword'] , 0, $max);
        if($temp > $count)
        {
        $count = $temp;
        $cat_top = $ro1['keyword'];
        }
        if($count != 0)
        {
         mysql_query("UPDATE like_page SET category='".$cat_top."'
WHERE page_name='".$row['page_name']."'") 
or die(mysql_error());  

        }
}

}
}
 			 }
}

$rs = mysql_query("SELECT * FROM like_page WHERE (category_topic='Public Figure' OR category_topic='Entertainer')") 
or die(mysql_error()); 
while($ro2 = mysql_fetch_array( $rs )) {
$rs1 = mysql_query("SELECT * FROM category_list WHERE category='".$ro2['category']."' ") 
or die(mysql_error()); 
$rs2 = mysql_fetch_array( $rs1 );
mysql_query("UPDATE like_page SET category_topic='".$rs2['category_topic']."'
WHERE page_name='".$ro2['page_name']."'")
or die(mysql_error());
}
mysql_close($con);

     ?>
    <?php
$i3=-1;
$con = mysql_connect("localhost","773409","lister_123");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }
mysql_select_db("773409") or die(mysql_error());

$sth = mysql_query("SELECT category_topic, COUNT(category) FROM like_page GROUP BY category_topic");


$rows = array();
//flag is not needed
$flag = true;
$table = array();
$table['cols'] = array(

    //Labels your chart, this represent the column title
    //note that one column is in "string" format and another one is in "number" format as pie chart only required "numbers" for calculating percentage And string will be used for column title
    array('label' => 'category', 'type' => 'string'),
    array('label' => 'interest', 'type' => 'number')

);

$rows = array();
while($r = mysql_fetch_array($sth)) {
    $temp = array();
    // the following line will used to slice the Pie chart
    $temp[] = array('v' => (string) $r['category_topic']); 


    $temp[] = array('v' => (int)  $r['COUNT(category)'] ); 
    $rows[] = array('c' => $temp);
}

$table['rows'] = $rows;
$jsonTable = json_encode($table);
//echo $jsonTable;



$sth1 = mysql_query("SELECT category_topic, COUNT(category) FROM current_page GROUP BY category_topic");



$rows1 = array();
//flag is not needed
$flag = true;
$table1 = array();
$table1['cols'] = array(

    //Labels your chart, this represent the column title
    //note that one column is in "string" format and another one is in "number" format as pie chart only required "numbers" for calculating percentage And string will be used for column title
    array('label' => 'category', 'type' => 'string'),
    array('label' => 'interest', 'type' => 'number')

);

$rows1 = array();
while($r = mysql_fetch_array($sth1)) {
    $temp = array();
    // the following line will used to slice the Pie chart
    $temp[] = array('v' => (string) $r['category_topic']); 


    $temp[] = array('v' => (int)  $r['COUNT(category)'] ); 
    $rows1[] = array('c' => $temp);
}

$table1['rows'] = $rows1;
$jsonTable1 = json_encode($table1);

$i=0;


mysql_close($con);
?>


<html>
  <head>
 <font size='6' color='brown'><b>
Check out your Interest Chart</font><font size='6' color='blue'> <?php echo $user_profile['name']?> </font></b>
    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script type="text/javascript">

    // Load the Visualization API and the piechart package.
    google.load('visualization', '1', {'packages':['corechart']});

    // Set a callback to run when the Google Visualization API is loaded.
    
    google.setOnLoadCallback(drawChart1);
    
    
    function drawChart1() {

      // Create our data table out of JSON data loaded from server.
      var data = new google.visualization.DataTable(<?=$jsonTable1?>);
      var options = {
           title: 'Like History Based User Profile',
          is3D: 'true',
          width: 800,
          height: 600
        };
      // Instantiate and draw our chart, passing in some options.
      //do not forget to check ur div ID
      var chart = new google.visualization.PieChart(document.getElementById('chart_div1'));
      chart.draw(data, options);
    }

  
    
    
    
    </script>
  </head>

  <body>
    <!--Div that will hold the pie chart-->
    <div id="chart_div"></div>
      <div id="chart_div1"></div>
      <div id="chart_div2"></div>
      
  </body>
</html>

<?php
echo "<pre><font size='7' color='black'><b>Your Interest Chart Dissected</b></pre>";
$con = mysql_connect("localhost","773409","lister_123");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }
mysql_select_db("773409") or die(mysql_error());
$s = mysql_query("SELECT DISTINCT category_topic FROM current_page");
while($r = mysql_fetch_array($s)) {



$sth = mysql_query("SELECT COUNT(page_name) FROM current_page WHERE category_topic = '".$r['category_topic']."'");
$r2 = mysql_fetch_array($sth);
$st = mysql_query("SELECT category, COUNT(page_name) FROM current_page WHERE category_topic = '".$r['category_topic']."' GROUP BY category ");
echo "<pre><font size='5' color='blue'><b>".$r['category_topic']."</b></font></pre><ul>";
while($r1 = mysql_fetch_array($st)) {
$cot=($r1['COUNT(page_name)']/$r2['COUNT(page_name)'])*100;
echo "<li><font size='3' color='brown'><b>".$r1['category']."</b></font><font size='2'>-- ".$cot."%</font></li>";
}
echo "</ul>";
}



?>
     
    
    <?php else: ?>
      <strong><em>You are not Connected.</em></strong>
    <?php endif ?>

   

  </body>
</html>					