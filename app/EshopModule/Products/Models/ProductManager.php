<?php

namespace App\EshopModule\Products\Models;

use ItNetwork\Db;
use ItNetwork\Image;
use ItNetwork\UserException;
use PDOException;

/**
 * Správce produktů
 */
class ProductManager {

	/**
	 * Vrátí produkt podle URL
	 * @param string $url Url produktu
	 * @return array Produkt
	 */
	public function getProduct($url)
	{
		return Db::queryOne('SELECT * FROM product WHERE url = ? AND NOT hidden', array($url));
	}

	/**
	 * Vrátí produkt podle Id
	 * @param int $productId ID produktu
	 * @return array Produkt
	 */
	public function getProductFromId($productId)
	{
		return Db::queryOne('SELECT * FROM product WHERE product_id = ?', array($productId));
	}

	/**
	 * Uloží produkt tak, že vytvoří vždy nový a vrátí jeho Id. Pokud je zadáno product_id, pokusí se odstranit
	 * staré produkty, když nejsou použité v objednávkách
	 * @param array $product Produkt
	 * @return int ID Nového produktu
	 * @throws UserException
	 */
	public function saveProduct($product)
	{
		Db::beginTransaction();
		// Kontrola, zda již neexistuje neskrytý produkt s danou URL
		if (Db::querySingle('
				SELECT product_id
				FROM product
				WHERE (url = ? OR code = ?) AND NOT hidden AND product_id != ?
			', array($product['url'], $product['code'], $product['product_id'])))
			throw new UserException('Produkt s tímto URL nebo kódem již existuje, zadejte prosím unikátní URL a kód.');
		// Načtení dat starého produktu, která nejdou zadat do formuláře
		if ($product['product_id'])
			$oldProduct = Db::queryOne('SELECT ratings, rating_sum, stock FROM product WHERE product_id = ?', array($product['product_id']));
		else
			$oldProduct = null;
		$oldProductId = $product['product_id'];
		unset($product['product_id']);
		Db::insert('product', $product); // Vložíme vždy nový produkt, protože původní verze může být na fakturách
		$productId = Db::getLastId();
		if ($oldProduct) // Editace
		{
			// Přesunutí recenzí k novému produktu
			Db::query('UPDATE review SET product_id = ? WHERE product_id = ?', array($productId, $oldProductId));
			// Přeuložení dat ze starého produktu, která se nezadávají formulářem
			Db::update('product', array(
				'ratings' => $oldProduct['ratings'],
				'rating_sum' => $oldProduct['rating_sum'],
				'stock' => $oldProduct['stock'],
			), 'WHERE product_id = ?', array($productId));
			// Vyčištění starého produktu
			$this->cleanProduct($oldProductId);
		}
		Db::commit();
		return $productId;
	}

	/**
	 * Pokusí se odstranit produkt, pokud není vázaný na objednávku
	 * @param int $productId ID produktu k odstranění
	 * @param bool $removeImages Zda si přejeme odstranit i obrázky
	 */
	public function cleanProduct($productId, $removeImages = false)
	{
		$imagesCount = Db::querySingle('SELECT images_count FROM product WHERE product_id = ?', array($productId));
		try
		{
			// Pokusíme se starý produkt odstranit, pokud není navázaný na fakturu
			Db::query('DELETE FROM product WHERE product_id = ?', array($productId));
		}
		catch (PDOException $ex)
		{
			// Jinak ho ponecháme jako skrytý
			Db::query('UPDATE product SET hidden = 1 WHERE product_id = ?', array($productId));
		}
		// Odstraníme produkt z kategorií
		Db::query('DELETE FROM product_category WHERE product_id = ?', array($productId));
		if ($removeImages)
		{
			// Přesun obrázků
			for ($i = 0; $i < $imagesCount; $i++)
			{
				$path = 'images/products/' . (int)$productId . '_' . $i . '.jpg';
				if (file_exists($path))
					unlink($path);
			}
			// Miniatura
			if ($imagesCount)
			{
				$thumbnailPath = 'images/products/' . (int)$productId . '_thumb.png';
				if (file_exists($thumbnailPath))
					unlink($thumbnailPath);
			}
		}
	}

	/**
	 * Nahraje obrázky k danému produktu
	 * @param int $productId ID produktu, ke kterému nahráváme obrázky
	 * @param array $images Obrázky ve tvaru z $_FILE
	 * @param int $oldProductId ID staré verze produktu (každá editace vytvoří nový produkt)
	 * @param int $oldImagesCount Počet původních obrázků produktu
	 * @throws UserException
	 */
	public function saveProductImages($productId, $images, $oldProductId, $oldImagesCount)
	{
		// Přejmenování starých obrázků, pokud se změnilo ID produktu
		if ($oldProductId)
		{
			$imagesCount = $oldImagesCount;
			$this->renameProductImages($oldProductId, $productId, $imagesCount);
		}
		else
			$imagesCount = 0;
		$errors = array();
		// Nahraje další obrázky k produktu
		if ($images['name'][0])
		{
			for ($i = 0; $i < count($images['name']); $i++)
			{
				if (!Image::isImage($images['tmp_name'][$i]))
					$errors[] = 'Formát obrázku ' . $images['name'][$i] . ' není podporován';
				else
				{
					// První obrázek uložíme i jako miniaturu
					if (!$imagesCount)
					{
						$image = new Image($images['tmp_name'][$i]);
						$image->resizeToCoverEdge(260);
						$image->save('images/products/' . $productId . '_thumb.png', Image::IMAGETYPE_PNG);
					}
					$image = new Image($images['tmp_name'][$i]);
					$image->resizeToHeight(400);
					$image->save('images/products/' . $productId . '_' . $imagesCount . '.jpg', Image::IMAGETYPE_JPEG);
					$imagesCount++;
				}
			}
		}
		Db::query('UPDATE product SET images_count = ? WHERE product_id = ?', array($imagesCount, $productId));
		if ($errors)
			throw new UserException(implode(', ', $errors));
	}

	/**
	 * Přejmenuje obrázky daného produktu tak, aby patřily jinému produktu
	 * @param int $oldProductId ID produktu, jehož obrázky chceme přesunout
	 * @param int $productId ID produktu, ke kterému mají obrázky nově patřit
	 * @param int $imagesCount Počet obrázků
	 */
	private function renameProductImages($oldProductId, $productId, $imagesCount)
	{
		// Přesun miniatury
		$oldThumbnailPath = 'images/products/' . (int)$oldProductId . '_thumb.png';
		$newThumbnailPath = 'images/products/' . (int)$productId . '_thumb.png';
		if (file_exists($oldThumbnailPath))
			rename($oldThumbnailPath, $newThumbnailPath);
		// Přesun obrázků
		for ($i = 0; $i < $imagesCount; $i++)
		{
			$oldPath = 'images/products/' . (int)$oldProductId . '_' . $i . '.jpg';
			$newPath = 'images/products/' . (int)$productId . '_' . $i . '.jpg';
			if (file_exists($oldPath))
				rename($oldPath, $newPath);
		}
	}

	/**
	 * Odstraní obrázek produktu
	 * @param int $productId ID produktu
	 * @param int $imageIndex Index obrázku (0 je první)
	 */
	public function removeProductImage($productId, $imageIndex)
	{
		// Pokud je to první obrázek, mažeme i miniaturu
		if ($imageIndex == 0)
		{
			$thumbnailPath = 'images/products/' . (int)$productId . '_thumb.png';
			if (file_exists($thumbnailPath))
				unlink($thumbnailPath);
			// Snažíme se vytvořit novou miniaturu z druhého obrázku
			$secondImagePath = 'images/products/' . (int)$productId . '_1.jpg';
			if (file_exists($secondImagePath))
			{
				$image = new Image($secondImagePath);
				$image->resizeToHeight(320);
				$image->save($thumbnailPath, Image::IMAGETYPE_PNG);
			}
		}
		// Mažeme obrázek
		$path = 'images/products/' . (int)$productId . '_' . (int)$imageIndex . '.jpg';
		if (file_exists($path))
			unlink($path);
		$imagesCount = Db::querySingle('SELECT images_count FROM product WHERE product_id = ?', array($productId));
		// Přejmenování zbylých obrázků tak, aby šly za sebou
		for ($i = $imageIndex + 1; $i < $imagesCount; $i++)
		{
			$oldPath = 'images/products/' . (int)$productId . '_' . $i . '.jpg';
			$newPath = 'images/products/' . (int)$productId . '_' . ($i - 1) . '.jpg';
			if (file_exists($oldPath))
				rename($oldPath, $newPath);
		}
		Db::query('UPDATE product SET images_count = images_count - 1 WHERE product_id = ?', array($productId));
	}

	/**
	 * Sestaví vyhledávací dotaz na produktu podle zadaných parametrů
	 * @param bool $count Zda si přejeme vrátit počet řádku (true) nebo řádky (false)
	 * @param int $page Jakou stránku řádků z dotazu si přejeme vrátit
	 * @param null|string $category URL adresa kategorie, ve které vyhledáváme
	 * @param string $phrase Vyhledávaná fráze
	 * @param string $orderBy Sloupec, podle kterého řadíme
	 * @param int $startPrice Cena od
	 * @param int $endPrice Cena do
	 * @param bool $inStock Zda mají být nalezené produkty skladem
	 * @param int $perPage Počet produktů na stránku
	 * @return array Vyhledávací dotaz jako pole s klíči "query" a "params"
	 */
	private function buildSearchQuery($count = false, $page = 1, $category = null, $phrase = '', $orderBy = 'rating', $startPrice = 0, $endPrice = 0, $inStock = false, $perPage = 6)
	{
		$params = array();
		$orderByParts = array();
		$orderColumns = array(
			'rating' => '(rating_sum/ratings) DESC, product_id DESC', // Protože hodnocení mohou mít stejné, tak i podle ID
			'lowest_price' => 'price',
			'highest_price' => 'price DESC',
			'newest' => 'product_id DESC',
		);
		$condition = ' WHERE NOT product.hidden';
		// Výběr sloupců
		if ($count)
			$columns = 'SELECT COUNT(*)';
		else
			$columns = 'SELECT DISTINCT product_id, product.title, product.url, short_description, price, old_price, ratings, rating_sum, stock, images_count';
		// Řazení
		if (!isset($orderColumns[$orderBy]))
			$orderBy = 'rating';
		$orderByParts[] = $orderColumns[$orderBy];
		// Přidání fulltextového vyhledávání
		if ($phrase)
		{
			$columns .= ', MATCH(product.title, short_description) AGAINST (?) AS relevance,
						   MATCH(product.title) AGAINST (?) AS title_relevance';
			$condition .= ' AND MATCH(product.title, short_description) AGAINST (?)';
			$orderByParts[] = ' title_relevance DESC, relevance DESC';
			$params[] = $phrase;
			$params[] = $phrase;
			$params[] = $phrase;
		}
		// Výběr tabulek
		$tables = '
			FROM product
			JOIN product_category USING(product_id)
			JOIN category USING(category_id)
		';
		// Omezení kategorií
		if ($category)
		{
			$condition .= ' AND category.url = ?';
			$params[] = $category;
		}
		// Omezení cenou
		if ($startPrice)
		{
			$condition .= ' AND price >= ?';
			$params[] = $startPrice;
		}
		if ($endPrice)
		{
			$condition .= ' AND price <= ?';
			$params[] = $endPrice;
		}
		// Omezení skladem
		if ($inStock)
			$condition .= ' AND stock > 0';
		// Stránkování
		if ($count)
			$limit = '';
		else
		{
			$limit = ' LIMIT ?, ?';
			$params[] = ($page - 1) * $perPage;
			$params[] = $perPage;
		}
		// Složení a spuštění výsledného dotazu
		return array(
			'query' => $columns . $tables . $condition . ' ORDER BY ' . implode(', ', $orderByParts) . $limit,
			'params' => $params,
		);
	}

	/**
	 * Vrátí produkty odpovídající daným parametrům
	 * @param null|string $category URL adresa kategorie, ve které vyhledáváme
	 * @param int $page Jakou stránku řádků z dotazu si přejeme vrátit
	 * @param string $phrase Vyhledávaná fráze
	 * @param string $orderBy Sloupec, podle kterého řadíme
	 * @param int $startPrice Cena od
	 * @param int $endPrice Cena do
	 * @param bool $inStock Zda mají být nalezené produkty skladem
	 * @param int $perPage Počet produktů na stránku
	 * @return array Odpovídající produkty
	 */
	public function getProducts($category = null, $page = 1, $phrase = '', $orderBy = 'rating', $startPrice = 0, $endPrice = 0, $inStock = false, $perPage = 6)
	{
		$result = $this->buildSearchQuery(false, $page, $category, $phrase, $orderBy, $startPrice, $endPrice, $inStock, $perPage);
		return Db::queryAll($result['query'], $result['params']);
	}

	/**
	 * Vrátí počet stránek produktů, které odpovídají daným parametrům
	 * @param null|string $category URL adresa kategorie, ve které vyhledáváme
	 * @param string $phrase Vyhledávaná fráze
	 * @param string $orderBy Sloupec, podle kterého řadíme
	 * @param int $startPrice Cena od
	 * @param int $endPrice Cena do
	 * @param bool $inStock Zda mají být nalezené produkty skladem
	 * @param int $perPage Počet produktů na stránku
	 * @return int Počet stránek odpovídajících produktů
	 */
	public function getProductPagesCount($category = null, $phrase = '', $orderBy = 'rating', $startPrice = 0, $endPrice = 0, $inStock = false, $perPage = 6)
	{
		$result = $this->buildSearchQuery(true, 1, $category, $phrase, $orderBy, $startPrice, $endPrice, $inStock, $perPage);
		$products = Db::querySingle($result['query'], $result['params']);
		return ceil($products / $perPage);
	}

	/**
	 * Naskladní dané množství produktů
	 * @param int $productId ID produktu
	 * @param int $quantity Počet produktů
	 */
	public function addToStock($productId, $quantity)
	{
		Db::query('UPDATE product SET stock = stock + ? WHERE product_id = ?', array($quantity, $productId));
	}

	/**
	 * Vrátí seznam všech produktů pro formulář
	 * @return array Seznam všech produktů pro formulář
	 */
	public function getProductList()
	{
		return Db::queryPairs('
			SELECT title, product_id
			FROM product
			WHERE NOT hidden
			ORDER BY title
		', 'title', 'product_id');
	}

}
