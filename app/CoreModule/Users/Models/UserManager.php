<?php



namespace App\CoreModule\Users\Models;

use ItNetwork\Db;
use ItNetwork\UserException;
use PDOException;

/**
 * Správce uživatelů redakčního systému
 */
class UserManager
{

	/**
	 * @var array|null Aktuálně přihlášený uživatel nebo null
	 */
	public static $user;

	/**
	 * Uloží aktuálně přihlášeného uživatele
	 */
	public function loadUser()
	{
		self::$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
	}

	/**
	 * Vrátí otisk hesla
	 * @param string $password Heslo
	 * @return string Otisk hesla
	 */
	public function computeHash($password)
	{
		return password_hash($password, PASSWORD_DEFAULT);
	}

	/**
	 * Registruje nového uživatele do systému
	 * @param string $name Uživatelské jméno
	 * @param string $password Heslo
	 * @param string $passwordRepeat Heslo znovu
	 * @param int $year Aktuální rok jako antispam
	 * @return int ID nově registrovaného uživatele
	 * @throws UserException
	 */
	public function register($name, $password, $passwordRepeat, $year)
	{
		if ($year != date('Y'))
			throw new UserException('Chybně vyplněný antispam.');
		if ($password != $passwordRepeat)
			throw new UserException('Hesla nesouhlasí.');
		$user = array(
			'name' => $name,
			'password' => $this->computeHash($password),
		);
		try
		{
			Db::insert('user', $user);
		}
		catch (PDOException $ex)
		{
			throw new UserException('Uživatel s tímto jménem je již zaregistrovaný.');
		}

		return Db::getLastId();
	}

	/**
	 * Přihlásí uživatele do systému
	 * @param string $email E-mail uživatele
	 * @param string $password Heslo
	 * @throws UserException
	 */
	public function login($email, $password)
	{
		$user = Db::queryOne('
			SELECT user_id, name, email, admin, password
			FROM user
			JOIN person USING (user_id)
			JOIN person_detail USING (person_detail_id)
			WHERE email = ?
		', array($email));
		if (!$user || !password_verify($password, $user['password']))
			throw new UserException('Neplatný email nebo heslo.');
		// Odstraníme heslo z pole s uživatelem, aby se nepředávalo na každé stránce webu
		unset($user['password']);
		$_SESSION['user'] = $user;
	}

	/**
	 * Odhlásí uživatele
	 */
	public function logout()
	{
		unset($_SESSION['user']);
	}

}
