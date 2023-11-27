<?php



use ItNetwork\HtmlBuilder;

/**
 * Soubor formátovacích metod pro navigační menu
 */
class MenuHelper
{
	/**
	 * Vyrenderuje menu ze stromu jako vnořené seznamy
	 * @param array $categories Strom kategorií
	 * @param string $parentUrl URL rodičovské kategorie (pro rekurzi)
	 * @return string Výsledné HTML
	 */
	public static function renderCategories($categories, $parentUrl = 'produkty/index')
	{
		$builder = new HtmlBuilder();

		$builder->startElement('ul', array('class' => 'nav nav-list tree'));

		foreach ($categories as $category)
		{
			$url = $parentUrl . '/' . $category['url'];
			if ($category['subcategories'])
			{
				$builder->startElement('li');
				$builder->addValueElement('label', $category['title'], array(
					'class' => 'tree-toggler nav-header',
					'data-path' => $url,
				));
				$builder->addValue(self::renderCategories($category['subcategories'], $url), true);
				$builder->endElement();
			}
			else
			{
				$builder->startElement('li');
				$builder->addValueElement('a', $category['title'], array(
					'href' => '' . $url,
					'data-path' => $url,
				));
				$builder->endElement();
			}
		}

		$builder->endElement();

		return $builder->render();
	}
}
