<?php

/*  _____ _______         _                      _
 * |_   _|__   __|       | |                    | |
 *   | |    | |_ __   ___| |___      _____  _ __| | __  ___ ____
 *   | |    | | '_ \ / _ \ __\ \ /\ / / _ \| '__| |/ / / __|_  /
 *  _| |_   | | | | |  __/ |_ \ V  V / (_) | |  |   < | (__ / /
 * |_____|  |_|_| |_|\___|\__| \_/\_/ \___/|_|  |_|\_(_)___/___|
 *                                _
 *              ___ ___ ___ _____|_|_ _ _____
 *             | . |  _| -_|     | | | |     |
 *             |  _|_| |___|_|_|_|_|___|_|_|_|
 *             |_|                          _ _ _        LICENCE
 *        ___ ___    ___    ___ ___ ___ ___| | |_|___ ___
 *       |   | . |  |___|  |  _| -_|_ -| -_| | | |   | . |
 *       |_|_|___|         |_| |___|___|___|_|_|_|_|_|_  |
 *                                                   |___|
 *
 * IT ZPRAVODAJSTVÍ  <>  PROGRAMOVÁNÍ  <>  HW A SW  <>  KOMUNITA
 *
 * Tento zdrojový kód je součástí výukových seriálů na
 * IT sociální síti WWW.ITNETWORK.CZ
 *
 * Kód spadá pod licenci prémiového obsahu s omezeným
 * přeprodáváním a vznikl díky podpoře našich členů. Je určen
 * pouze pro osobní užití a nesmí být šířen. Může být použit
 * v jednom uzavřeném komerčním projektu, pro širší využití je
 * dostupná licence Premium commercial. Více informací na
 * http://www.itnetwork.cz/licence
 */

namespace App\EshopModule\Persons\Models;

use ItNetwork\Db;
use ItNetwork\Utility\ArrayUtils;
use PDOException;

/**
 * Spravuje osoby v e-shopu
 */
class PersonManager
{

	/**
	 * Vrátí dodací adresu, ve které mají klíče prefix delivery_
	 * @param int $deliveryAddressId ID adresy
	 * @return array Dodací adresa
	 */
	public function getDeliveryAddress($deliveryAddressId)
	{
		return ArrayUtils::addPrefix('delivery_', Db::queryOne('
			SELECT * FROM address WHERE address_id = ?
		', array($deliveryAddressId)));
	}

	/**
	 * Vrátí osobu
	 * @param int $personId ID Osoby
	 * @return array Osoba
	 */
	public function getPerson($personId)
	{
		$person = Db::queryOne('
			SELECT *
			FROM person
			JOIN address USING (address_id)
			JOIN person_detail USING (person_detail_id)
			LEFT JOIN bank_account USING (bank_account_id)
			LEFT JOIN bank_code USING (bank_code)
			WHERE person_id = ?
		', array($personId));
		if ($person && $person['delivery_address_id']) // Připojení dodací adresy
			$person = array_merge($person, $this->getDeliveryAddress($person['delivery_address_id']));
		return $person;
	}

	/**
	 * Vrátí státy
	 * @return array Státy
	 */
	public function getCountries()
	{
		return Db::queryPairs('SELECT country_id, title FROM country ORDER BY title', 'title', 'country_id');
	}

	/**
	 * Vrátí administrátory
	 * @return array Administrátoři
	 */
	public function getAdmins()
	{
		return Db::queryAll('
			SELECT person_id, person_detail_id,
			CONCAT(COALESCE(first_name, ""), " ", COALESCE(last_name, ""), " ", COALESCE(company_name, "")) AS name
			FROM person
			JOIN user USING (user_id)
			JOIN person_detail USING (person_detail_id)
			WHERE admin
		');
	}

	/**
	 * Vrátí ID osoby, přiřazené k daného uživatelskému účtu
	 * @param int $userId ID uživatele
	 * @return int ID osoby
	 */
	public function getPersonId($userId)
	{
		return Db::querySingle('SELECT person_id FROM person WHERE user_id = ?', array($userId));
	}

	/**
	 * Zjisti, zda-li již existuje zaregistrovaný uživatel s předaným emailem
	 * @param string $email Email k validaci
	 * @return bool Zda-li už existuje osoba s daným emailem
	 */
	public function doesPersonWithEmailExist($email)
	{
		return (bool)Db::querySingle('
			SELECT COUNT(*)
			FROM `person`
			JOIN `person_detail` USING (`person_detail_id`)
			WHERE `person_detail`.`email` = ? AND `person`.`user_id` IS NOT NULL
		', array($email));
	}

	/**
	 * Uloží osobu (vytvoří novou nebo updatuje existující podle toho, zda je zadaný klíč person_id)
	 * Jednotlivé součásti osoby jsou vždy vytvořené znovu a staré jsou vymazány, pokud nejsou použity v existujících
	 * objednávkách.
	 * @param array $person Osoba
	 * @param int|null $userId ID uživatele, ke kterému se má vytvořená osoba přiřadit
	 * @param bool $admin Zda je osoba administrátor (ukládáme i bankovní účet)
	 * @return int V případě vytvoření nové osoby vrací její ID, jinak null
	 */
	public function savePerson($person, $userId = null, $admin = false)
	{
		Db::beginTransaction();

		$personData = array();
		// Detail
		$detailData = ArrayUtils::filterKeys($person, array('first_name', 'last_name', 'company_name', 'phone', 'fax', 'email', 'tax_number', 'identification_number', 'registry_entry'));
		if (!$detailData['identification_number'])
			$detailData['identification_number'] = null;
		Db::insert('person_detail', $detailData);
		$personData['person_detail_id'] = Db::getLastId();
		if ($detailData['company_name']) // Právnická osoba
		{
			$detailData['first_name'] = null;
			$detailData['last_name'] = null;
		}
		else
			$detailData['company_name'] = null;
		// Adresa
		$addressData = ArrayUtils::filterKeys($person, array('street', 'registry_number', 'house_number', 'city', 'zip', 'country_id'));
		// Jelikož číslo domu není povinné, musíme za náš formulářový framework ohlídat, zda-li není hodnota vyplněná a popřípadě ji nastavit na výchozí
		$addressData['house_number'] = $addressData['house_number'] ?: 0;
		Db::insert('address', $addressData);
		$personData['address_id'] = Db::getLastId();
		// Dodací adresa
		// Pokud je adresa vyplněna, vložíme ji do databáze
		$deliveryAddressData = ArrayUtils::filterKeys($person, array('delivery_street', 'delivery_registry_number', 'delivery_house_number', 'delivery_city', 'delivery_zip', 'delivery_country_id'));
		if (!$person['omit_delivery_address'] && $deliveryAddressData['delivery_city'] && $deliveryAddressData['delivery_zip'] && $deliveryAddressData['delivery_house_number'])
		{
			Db::insert('address', ArrayUtils::removePrefix('delivery_', $deliveryAddressData));
			$personData['delivery_address_id'] = Db::getLastId();
		}
		else
			$personData['delivery_address_id'] = null;
		// Platební údaje
		if ($admin)
		{
			$accountData = ArrayUtils::filterKeys($person, array('bank_code', 'account_number'));
			if (array_filter($accountData)) // Pokud je účet vyplněný, vložíme nový
			{
				Db::insert('bank_account', $accountData);
				$personData['bank_account_id'] = Db::getLastId();
			}
		}
		// Osoba
		if (!$person['person_id']) // Vkládáme
		{
			Db::insert('person', $personData);
			$personId = Db::getLastId();
			if ($userId) // Přiřazení osoby k určitému účtu
				Db::query('UPDATE person SET user_id = ? WHERE person_id = ?', array($userId, $personId));
		}
		else
		{
			$oldPerson = Db::queryOne('SELECT * FROM person WHERE person_id = ?', array($person['person_id']));
			Db::update('person', $personData, 'WHERE person_id = ?', array($person['person_id']));
			// Vyčištění původních záznamů
			$this->cleanPersonDetail($oldPerson['person_detail_id']);
			$this->cleanAddress($oldPerson['address_id']);
			$this->cleanAddress($oldPerson['delivery_address_id']);
			if ($admin)
				$this->cleanBankAccount($oldPerson['bank_account_id']);
		}

		Db::commit();
		if (!$person['person_id'])
			return $personId;
		return null;
	}

	/**
	 * Odstraní daný detail osoby v případě, že není použitý v žádné objednávce
	 * @param int $personDetailId ID detailu osoby
	 */
	public function cleanPersonDetail($personDetailId)
	{
		try
		{
			Db::query('DELETE FROM person_detail WHERE person_detail_id = ?', array($personDetailId));
		}
		catch (PDOException $ex)
		{
			// Položku se nepodařilo odstranit, jelikož je napojená na objednávky
		}
	}

	/**
	 * Odstraní danou adresu osoby v případě, že není použita v žádné objednávce
	 * @param int $addressId ID adresy osoby
	 */
	public function cleanAddress($addressId)
	{
		try
		{
			Db::query('DELETE FROM address WHERE address_id = ?', array($addressId));
		}
		catch (PDOException $ex)
		{
			// Položku se nepodařilo odstranit, jelikož je napojená na objednávky
		}
	}

	/**
	 * Odstraní daný bankovní účet v případě, že není použitý v žádné objednávce
	 * @param int $bankAccountId ID bankovního účtu
	 */
	public function cleanBankAccount($bankAccountId)
	{
		try
		{
			Db::query('DELETE FROM bank_account	WHERE bank_account_id = ?', array($bankAccountId));
		}
		catch (PDOException $ex)
		{
			// Položku se nepodařilo odstranit, jelikož je napojená na objednávky
		}
	}

	/**
	 * Vrátí osobu sestavenou z jednotlivých součástí podle jejich ID. Používá se u objednávek, kde se může adresa
	 * lišit od té, kterou má aktuálně uloženou daná osoba.
	 * @param int $personId ID osoby
	 * @param int $detailId ID detailu osoby
	 * @param int $addressId ID adresy
	 * @param int $deliveryAddressId ID dodací adresy
	 * @param int $accountId ID bankovního účtu
	 * @return array Sestavená osoba
	 */
	public function getCustomPerson($personId, $detailId, $addressId, $deliveryAddressId, $accountId)
	{
		$person = Db::queryOne('
			SELECT *, ? AS person_id
			FROM person_detail
			JOIN address ON address_id = ?
			LEFT JOIN bank_account ON bank_account_id = ?
			LEFT JOIN bank_code USING (bank_code)
			WHERE person_detail_id = ?
		', array($personId, $addressId, $accountId, $detailId));
		if ($person && $deliveryAddressId) // Připojení dodací adresy
			$person = array_merge($person, $this->getDeliveryAddress($deliveryAddressId));
		return $person;
	}

	/**
	 * Vrátí detail osoby s daným Id
	 * @param int $detailId ID detailu osoby
	 * @return array Detail osoby
	 */
	public function getPersonDetail($detailId)
	{
		return Db::queryOne('SELECT * FROM person_detail WHERE person_detail_id = ?', array($detailId));
	}

	/**
	 * Odstraní danou osobu
	 * @param int $personId ID osoby
	 */
	public function deletePerson($personId)
	{
		Db::query('DELETE FROM person WHERE person_id = ?', array($personId));
	}

}
