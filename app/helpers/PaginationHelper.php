<?php



use ItNetwork\HtmlBuilder;

/**
 * Soubor formátovacích metod pro paginaci
 */
class PaginationHelper
{
	/**
	 * Nahradí podřetězec "{page}" v URL adrese zadaným číslem stránky
	 * @param string $url URL adresa
	 * @param int $page Číslo stránky
	 * @return string Výsledná URL adresa
	 */
	private static function pageUrl($url, $page)
	{
		return str_replace('{page}', $page, $url);
	}

	/**
	 * Vygeneruje widget s paginací
	 * @param int $page Aktuální strana
	 * @param int $pages Celkový počet stran
	 * @param string $url URL adresa pro přechod na jednotlivé stránky s placeholderem {page} místo čísla strany
	 * @return string Widget s paginací
	 */
	public static function pagination($page, $pages, $url)
	{
		$radius = 5; // Poloměr oblasti kolem aktuální stránky

		$builder = new HtmlBuilder();

		$builder->startElement('nav');
		$builder->startElement('ul', array('class' => 'pagination'));

		// Šipka vlevo
		$params = array();
		if ($page <= 1)
			$params['class'] = 'disabled';
		$builder->startElement('li', $params);
		$builder->startElement('span');
		$builder->startElement('span', array('aria-hidden' => 'true'));
		if ($page > 1)
			$builder->addValueElement('a', '&laquo;', array('href' => self::pageUrl($url, $page - 1)), true);
		else
			$builder->addValue('&laquo;', true);
		$builder->endElement();
		$builder->endElement();
		$builder->endElement();

		$left = $page - $radius >= 1 ? $page - $radius : 1;
		$right = $page + $radius <= $pages ? $page + $radius : $pages;

		// Umístění jedničky
		if ($left > 1)
		{
			$builder->startElement('li');
			$builder->startElement('span');
			$builder->addValueElement('a', 1, array('href' => self::pageUrl($url, 1)), true);
			$builder->endElement();
			$builder->endElement();
		}
		// Tečky vlevo
		if ($left > 2)
		{
			$builder->startElement('li', array('class' => 'disabled'));
			$builder->addValueElement('span', '&hellip;', array(), true);
			$builder->endElement();
		}

		// Stránky v radiusu
		for ($i = $left; $i <= $right; $i++)
		{
			$params = array();
			if ($i == $page)
				$params['class'] = 'active';
			$builder->startElement('li', $params);
			if ($i == $page) // Aktivní stránka
			{
				$builder->startElement('span');
				$builder->addValue($i);
				$builder->addValueElement('span', '(current)', array('class' => 'sr-only'));
				$builder->endElement();
			}
			else
				$builder->addValueElement('a', $i, array('href' => self::pageUrl($url, $i)), true);
			$builder->endElement();
		}

		// Tečky vpravo
		if ($right < $pages - 1)
		{
			$builder->startElement('li', array('class' => 'disabled'));
			$builder->addValueElement('span', '&hellip;', array(), true);
			$builder->endElement();
		}

		// Umístění poslední stránky
		if ($right < $pages)
		{
			$builder->startElement('li');
			$builder->startElement('span');
			$builder->addValueElement('a', $pages, array('href' => self::pageUrl($url, $pages)), true);
			$builder->endElement();
			$builder->endElement();
		}

		// Šipka vpravo
		$params = array();
		if ($page >= $pages)
			$params['class'] = 'disabled';
		$builder->startElement('li', $params);
		$builder->startElement('span');
		$builder->startElement('span', array('aria-hidden' => 'true'));
		if ($page < $pages)
			$builder->addValueElement('a', '&raquo;', array('href' => self::pageUrl($url, $page + 1)), true);
		else
			$builder->addValue('&laquo;', true);

		$builder->endElement();
		$builder->endElement();
		$builder->endElement();

		$builder->endElement();
		$builder->endElement();
		return $builder->render();
	}
}