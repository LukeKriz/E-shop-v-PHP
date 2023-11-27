<?php



namespace App\EshopModule\Products\Models;

use ItNetwork\Db;
use ItNetwork\UserException;
use ItNetwork\Utility\ArrayUtils;
use PDOException;
use ProductHelper;

/**
 * Správce kategorií pro produkty
 */
class CategoryManager
{

	/**
	 * Vrátí URL adresu první kategorie, do které produkt spadá
	 * @param int $productId ID produktu
	 * @return string URL adresa první kategorie, do které produkt spadá
	 */
	public function getProductCategory($productId)
	{
		return Db::querySingle('
			SELECT category.url
			FROM category
			JOIN product_category USING (category_id)
			JOIN product USING (product_id)
			WHERE product_id = ?
		', array($productId));
	}

	/**
	 * Vrátí ty kategorie, které v sobě již neobsahují další podkategorie (listy stromu kategorií)
	 * @return array Kategorie, které v sobě již neobsahují další podkategorie (listy stromu kategorií)
	 */
	public function getCategoryLeafs()
	{
		return Db::queryPairs('
            SELECT category_id, title
            FROM category
            WHERE category_id NOT IN (
                SELECT parent_category_id as category_id
                FROM category
                WHERE parent_category_id IS NOT NULL
            )
        ', 'title', 'category_id');
	}

	/**
	 * Vrátí ID kategorií, ve kterých je zařazený daný produkt
	 * @param int $productId ID produktu
	 * @return array ID kategorií, ve kterých je zařazený daný produkt
	 */
	public function getProductCategories($productId)
	{
		$categories = Db::queryAll('SELECT category_id FROM product_category WHERE product_id = ?', array($productId));
		return ArrayUtils::mapSingles($categories, 'category_id');
	}

	/**
	 * Aktualizuje zařazení produktu do kategorií
	 * @param int $productId ID produktu, který zařazujeme
	 * @param array $categories ID kategorií, do kterých má být produkt zařazen
	 */
	public function updateProductCategories($productId, $categories)
	{
		Db::query('DELETE FROM product_category WHERE product_id = ?', array($productId));
		$rows = array();
		foreach ($categories as $category)
		{
			$rows[] = array(
				'product_id' => $productId,
				'category_id' => $category,
			);
		}
		Db::insertAll('product_category', $rows);
	}

	/**
	 * Zformátuje pole kategorií z databáze rekurzivně do stromu
	 * Kód z http://www.jugbit.com/php/php-recursive-menu-with-1-query/
	 * @param array $items
	 * @param int $parentId ID rodičovské kategorie
	 * @return array Kategorie ve stromové podobě
	 */
	private function formatTree($items, $parentId)
	{
		// Vytvoříme prázdný strom
		$tree = array();
		// Pokusíme se najít položky, které patří do rodičovské kategorie ($parentId)
		foreach ($items as $item)
		{
			if ($item['parent_category_id'] == $parentId)
			{
				// Položku přidáme do nového stromu
				$tree[$item['category_id']] = $item;
				// A rekurzivně přidáme strom podpoložek
				$tree[$item['category_id']]['subcategories'] = $this->formatTree($items, $item['category_id']);
			}
		}
		return $tree; // Vrátíme hotový strom
	}

	/**
	 * Vrátí kategorie produktů v podobě stromu
	 * @param bool $showAll Zda chceme zobrazovat i skryté kategorie
	 * @return array Kategorie produktů v podobě stromu
	 */
	public function getCategories($showAll = false)
	{
		$query = 'SELECT * FROM category';
		if (!$showAll)
			$query .= ' WHERE NOT hidden';
		$query .= ' ORDER BY order_no';
		$categories = Db::queryAll($query);
		return $this->formatTree($categories, null);
	}

	/**
	 * Získá titulek kategorie podle jejího URL
	 * @param string $url Url kategorie
	 * @return string Titulek kategorie
	 */
	public function getTitle($url)
	{
		return Db::querySingle('SELECT title FROM category WHERE url = ?', array($url));
	}

	/**
	 * Vrátí platební metody, které jsou realizované jako produkty ve skryté kategorii
	 * @param array $settings Účetní nastavení
	 * @return array Platební metody, které jsou realizované jako produkty ve skryté kategorii
	 */
	public function getPaymentMethods($settings)
	{
		$products = Db::queryAll('
			SELECT product.title, price, product_id
			FROM product
			JOIN product_category USING (product_id)
			JOIN category USING (category_id)
			WHERE category.url = ?
		', array('zpusoby-dopravy'));
		$methods = array();
		foreach ($products as $product)
		{
			$key = $product['title'] . ' ' . ProductHelper::price($product['price'], $settings['vat_payer'], $settings['vat']);
			$methods[$key] = $product['product_id'];
		}
		return $methods;
	}

	/**
	 * Vrátí JSON s kategoriemi produktů
	 * @return string JSON s kategoriemi produktů
	 */
	public function getCategoriesJson()
	{
		$categories = Db::queryAll('SELECT * FROM category ORDER BY order_no');
		return json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
	}

	/**
	 * Aktualizuje kategorie produktů
	 * @param array $categories Nové kategorie produktů
	 * @throws UserException
	 */
	public function saveCategories($categories)
	{
		$filtered = array();
		foreach ($categories as $category)
		{
			$filtered[] = array(
				'category_id' => $category['category_id'],
				'url' => $category['url'],
				'title' => $category['title'],
				'order_no' => $category['order_no'],
				'hidden' => $category['hidden'],
				'parent_category_id' => isset($category['parent_category_id']) ? $category['parent_category_id'] : null,
			);
		}
		// Zbavíme se cizího klíče, aby odstranění kategorií nevyvolalo i odstranění navázaných produktů
		Db::query('ALTER TABLE product_category DROP FOREIGN KEY product_category_ibfk_2');
		try
		{
			Db::beginTransaction();
			Db::query('DELETE FROM category'); // Smaže všechny kategorie
			Db::insertAll('category', $filtered);
			DB::commit();
		}
		catch (PDOException $ex)
		{
			Db::rollBack();
			// Vrátíme cizí klíč
			Db::query('
				ALTER TABLE product_category
				ADD CONSTRAINT product_category_ibfk_2 FOREIGN KEY (category_id) REFERENCES category(category_id)
				ON DELETE CASCADE ON UPDATE CASCADE
			');
			throw new UserException('Chyba při uložení kategorií, pravděpodobně jste zadali shodná URL.');
		}
		// Produkty, jejichž kategorie nyní neexistuje, přesuneme do kategorie nezařazeno
		Db::query('
			UPDATE product_category
			SET category_id = (SELECT category_id FROM category WHERE url="nezarazeno" AND hidden)
			WHERE category_id NOT IN (SELECT category_id FROM category)
		');
		// Vrátíme cizí klíč
		Db::query('
			ALTER TABLE product_category
			ADD CONSTRAINT product_category_ibfk_2 FOREIGN KEY (category_id) REFERENCES category(category_id)
			ON DELETE CASCADE ON UPDATE CASCADE
		');
	}

}