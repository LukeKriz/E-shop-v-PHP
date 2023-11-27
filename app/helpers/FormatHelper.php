<?php



use ItNetwork\Utility\DateUtils;
use ItNetwork\Utility\StringUtils;

/**
 * Soubor metod pro formátování textu
 */
class FormatHelper
{

	/**
	 * Převede první písmeno textu na velké
	 * @param string $text Text k převedení
	 * @return string Převedený text
	 */
	public static function capitalize($text)
	{
		return StringUtils::capitalize($text);
	}

	/**
	 * Zkrátí text na požadovanou délku. U zkráceného textu zobrazuje tři tečky, které se vejdou do požadované délky
	 * @param string $text Text ke zkrácení
	 * @param int $length požadovaná délka textu
	 * @return string Zkrácený text
	 */
	public static function shorten($text, $length)
	{
		return StringUtils::shorten($text, $length);
	}

	/**
	 * Zformátuje datum z libovolné stringové podoby na tvar např. "Dnes"
	 * @param string $date Datum ke zformátování
	 * @param bool $time Zda chceme vrátit i čas
	 * @return string Zformátované datum
	 */
	public static function prettyDate($date, $time = true)
	{
		return DateUtils::prettyDate($date, $time);
	}

	/**
	 * Zformátuje datum a čas z libovolné stringové podoby na tvar např. "Dnes 15:21"
	 * @param string $date Datum ke zformátování
	 * @return string Zformátované datum
	 */
	public static function prettyDateTime($date)
	{
		return DateUtils::prettyDateTime($date);
	}

	/**
	 * Zformátuje datum na tvar d.m. Y
	 * @param string $date Datum
	 * @return string Datum ve tvaru d.m. Y
	 */
	public static function numericDate($date)
	{
		$dateTime = new DateTime($date);
		return $dateTime->format("j.n. Y");
	}

	/**
	 * Zformátuje částku na 2 desetinná místa a připojí danou měnu
	 * @param float $price Částka
	 * @param string $currency Měna (např. Kč)
	 * @return string Částka na 2 desetinná místa s měnou
	 */
	public static function currency($price, $currency = 'Kč')
	{
		return number_format($price, 2, ',', ' ') . ' ' . $currency;
	}

	/**
	 * Zformátuje boolean na tvar Ano/Ne
	 * @param bool $value Booleovská hodnota
	 * @return string Hodnota Ano nebo Ne
	 */
	public static function boolean($value)
	{
		return $value ? 'Ano' : 'Ne';
	}

} 