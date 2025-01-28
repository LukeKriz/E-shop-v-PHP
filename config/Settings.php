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
	public static $domain = '';
	/**
	 * @var array Přístupové údaje k databázi
	 */
	public static $db = array(
		'user' => "",
		'host' => "",
		'password' => "",
		'database' => "",
	);
	/**
	 * @var string Email administrátora
	 */
	public static $email = '';

}
