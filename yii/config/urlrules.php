<?php
// if (defined('YII_DEBUG') && YII_DEBUG)
	//return array();

return array(
	'/' => 'site/index',
	'/inloggen'=>'site/login',
	'/uitloggen'=>'site/logout',

	'/gebruikers'=>'user/admin',
	'/gebruiker/toevoegen'=>'user/create',
	'/gebruiker/bewerken/<id:\d+>'=>'user/update',

	'/wachtwoord-vergeten'=>'user/forgotpass',
	'/wachtwoord-wijzigen'=>'user/changepass',

	'/brieven'=>'letter/admin',
	'/brief/invullen/<id>/<description>/pagina/<page>'=>'letter/fill',
	'/brief/invullen/<id>/<description>'=>'letter/fill',
	'/brief/invullen/<id>/<description>/<do:outro>'=>'letter/fill',
	'/brief/details/<id>/<description>/pagina/<page>'=>'letter/view',
	'/brief/pdf/<id>/<description>/pagina/<page>'=>'letter/downloadPdf',
	'/brief/bewerken/<id>/<description>'=>'letter/update',
	'/brief/toevoegen'=>'letter/create',

	'/paginas/<letterId:\d+>'=>'page/admin',
	'/pagina/toevoegen/<letterId:\d+>'=>'page/create',
	'/pagina/bewerken/<id:\d+>'=>'page/update',

	'/notities'=>'memo/admin',
	'/notitie/toevoegen'=>'memo/create',
	'/notitie/bewerken/<id:\d+>'=>'memo/update',
	
	'/deelnemers/<letterId>/<letterDescription>'=>'participant/admin',
	'/deelnemer/details/<id>/<name>'=>'participant/view',
	'/deelnemer/toevoegen/<letterId>'=>'participant/create',
	'/deelnemer/bewerken/<id>/<name>'=>'participant/update',
);