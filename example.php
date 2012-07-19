<?php

require_once 'Dropbox.class.php';

$dropbox = new Dropbox();

$userArray = $dropbox->call('https://api.dropbox.com/1/account/info');

?>


<html>
	<head>
		<title>Dropbox API</title>
	</head>
	<body>
	<h1>Dropbox API</h1>
	
	<?php 
	if($dropbox->hasAccess())
	{
		echo '
		<h2>User Info</h2>
			<ul>	
				<li>'.$userArray->display_name.'</li>
				<li>'.$userArray->email.'</li>
			</ul>';
	}
	else
	{
		echo '
		<h2>Login</h2>
			<a href="'.$dropbox->getAccessURL().'">Login to Dropbox</a>';
	}
	?>
	
	</body>
</html>