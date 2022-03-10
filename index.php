<? require('config.php') ?>
<!DOCTYPE html>
<head>
<title>Tus Hoteles en Foursquare</title>
<meta content="text/html; charset=UTF-8" http-equiv="content-type" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<style>
	body { font-family: Arial }
</style>
</head>

<body>
<h1>Tus Hoteles en Foursquare</h1>
<p>Un simple listado de todos los hoteles en los que hiciste checkin con Foursquare.</p>
<p>Comentarios e ideas: <a class="twitter-follow-button"
  href="https://twitter.com/marceloruiz"
  data-show-count="false"
  data-size="large">
Follow @twitter
</a>
<script>window.twttr=(function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],t=window.twttr||{};if(d.getElementById(id))return;js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);t._e=[];t.ready=function(f){t._e.push(f);};return t;}(document,"script","twitter-wjs"));</script></p>
<? 
if (isset($_GET['code'])) 
{
	$response = file_get_contents('https://foursquare.com/oauth2/access_token?client_id='. CLIENT_ID .'&client_secret='. CLIENT_SECRET .'&grant_type=authorization_code&redirect_uri=http://centraldev.net/4sqtest/index.php&code='.$_GET['code']); 
	$obj = json_decode($response);
	echo '<p><a href="?page=checkins&access_token='. $obj->{'access_token'} .'">Ver checkins</a></p>';
}
elseif  ($_GET['page'] == 'checkins') 
{ 
	$mostrados = array();
	$ultFecha = time();
	$checkinCount = 0;
	$i = 0;
	do
	{
		$response = ObtenerCheckins($_GET['access_token'], $ultFecha);
		$obj = json_decode($response);
		$totalCheckins = $obj->{'response'}->{'checkins'}->{'count'};
		
		$checkinCountBefore = $checkinCount;
		$ultFecha = MostrarCheckins($obj->{'response'}->{'checkins'}->{'items'}, $mostrados);
		if ($checkinCount == $checkinCountBefore)
			$checkinCount = $totalCheckins;
		
		$i++;
	} while($checkinCount+1 <= $totalCheckins);
	
	$countHoteles = count($mostrados);
	if ($countHoteles > 4)
		echo "<p><strong>¡Felicitaciones! Hiciste checkin en {$countHoteles} hoteles en Foursquare.</strong></p>";
	elseif ($countHoteles <= 4)
		echo "<p><strong>Solo hiciste checkin en unos pocos hoteles con Foursquare. ¡Sigue viajando!</strong></p>";
	else
		echo "<p><strong>No hiciste checkin en ningún hotel con Foursquare. ¡Viaja más!</strong></p>";

} else { ?>

<p><a href="https://foursquare.com/oauth2/authenticate?client_id=<?=CLIENT_ID?>&response_type=code&redirect_uri=http://centraldev.net/4sqtest/index.php"><img src="https://ss1.4sqi.net/img/connectTo-990dd166e85a12426a6f57634875256c.png" border="0"/></a></p>

<? } ?>

<p><strong>Declaración de privacidad:</strong> esta aplicación es meramente didáctica. No se guardan datos personales en ninguna base de datos, ni cookies, ni se persiste cualquier otro tipo de información. (<a href="index.php.zip">Código fuente</a>).</p>

</body>
</html>

<?
// funciones

function ObtenerCheckins($access_token, $beforeTimestamp)
{
	$url = 'https://api.foursquare.com/v2/users/self/checkins?beforeTimestamp='. $beforeTimestamp .'&limit=100&v=20150128&oauth_token='. $access_token;
	return file_get_contents($url); 
}

function MostrarCheckins($checkins, $mostrados)
{
	global $ultFecha;
	global $checkinCount;
	global $mostrados;
	
	foreach ($checkins as $item)
	{
		
		$ultFecha = $item->{'createdAt'};
		$checkinCount++;
		
		if ($item->{'venue'} == null)
			continue;
			
		foreach ($item->{'venue'}->{'categories'} as $category)
		{
			if ($category->{'name'} == 'Hotel' 
				|| $category->{'name'} == 'Bed & Breakfast'
				|| $category->{'name'} == 'Resort'
				|| $category->{'name'} == 'Hotel Pool') 
			{
				if (in_array($item->{'venue'}->{'id'}, $mostrados))
					continue;
				
				echo '<p><a href="https://es.foursquare.com/v/'.$item->{'venue'}->{'id'}.'">'. $item->{'venue'}->{'name'} .'</a> ('. 
				$item->{'venue'}->{'location'}->{'city'} .', '. $item->{'venue'}->{'location'}->{'country'} .') - '. 
				date('M Y', $item->{'createdAt'}) .'</p>';
				
				$mostrados[] = $item->{'venue'}->{'id'};
			}
		}
		
	}
	
	return $ultFecha;
}
?>
