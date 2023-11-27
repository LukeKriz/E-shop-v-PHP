<?php


/**
 * Soubor pomocných metod pro formátování dat osob
 */
class PersonHelper
{

	/**
	 * Zformátuje adresu osoby
	 * @param array $person Osoba
	 * @return string Zformátovaná adresa
	 */
	public static function address($person)
	{
		$html = $person['street'] . ' ';
		if ($person['registry_number'] && $person['house_number'])
			$html .= $person['registry_number'] . '/' . $person['house_number'];
		else
			$html .= $person['registry_number'] ? $person['registry_number'] : $person['house_number'];
		$html .= '<br />';
		$html .= $person['city'] . '<br />';
		$html .= $person['zip'];
		return $html;
	}

	/**
	 * Zformátuje jméno fyzické osoby, případně název firmy (právnické osoby)
	 * @param array $person Osoba
	 * @return string Zformátované jméno osoby
	 */
	public static function name($person)
	{
		if ($person['company_name'])
			return $person['company_name'];
		if ($person['first_name'])
			return $person['first_name'] . ' ' . $person['last_name'];
		return 'Koncový zákazník';
	}

}