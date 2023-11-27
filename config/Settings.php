<?php



class Settings
{
	/**
	 * @var bool Zda systém běží v ladícím režimu (zobrazují se chyby, emaily se ukládají do souborů a podobně)
	 */
	public static $debug = false;
	/**
	 * @var string Doména projektu
	 */
	public static $domain = 'md99.wedos.net';
	/**
	 * @var array Přístupové údaje k databázi
	 */
	public static $db = array(
		'user' => "a265130_lp2wbu",
		'host' => "md99.wedos.net",
		'password' => "Lukas2610!",
		'database' => "d265130_lp2wbu",
	);
	/**
	 * @var string Email administrátora
	 */
	public static $email = 'opava@cerpadla-studny.cz';

}