<?php


namespace App\CoreModule\Articles\Models;

use ItNetwork\Db;
use ItNetwork\UserException;
use PDOException;

/**
 * Třída poskytuje metody pro správu článků v redakčním systému
 */
class ArticleManager
{

	/**
	 * @var array Aktuálně načtený článek
	 */
	public static $article;

	/**
	 * Načte článek z DB a uloží do statické vlastnosti $article
	 * @param string $url URL článku
	 */
	public function loadArticle($url)
	{
		self::$article = $this->getArticle($url);
	}

	/**
	 * Vrátí článek z databáze podle jeho URL
	 * @param string $url URl článku
	 * @return mixed Pole s článkem nebo false při neúspěchu
	 */
	public function getArticle($url)
	{
		return Db::queryOne('
			SELECT `article_id`, `title`, `content`, `url`, `description`, `controller`
			FROM `article`
			WHERE `url` = ?
		', array($url));
	}

	/**
	 * Uloží článek do systému. Pokud je ID false, vloží nový, jinak provede editaci.
	 * @param array $article Pole s článkem
	 * @throws UserException
	 */
	public function saveArticle($article)
	{
		if (!$article['article_id'])
		{
			try
			{
				// Aby se provedl autoincrement, hodnota musí být NULL, nebo sloupeček z dotazu musíme vynechat
				unset($article['article_id']);
				Db::insert('article', $article);
			}
			catch (PDOException $ex)
			{
				throw new UserException('Článek s touto URL adresou již existuje.');
			}
		}
		else
			Db::update('article', $article, 'WHERE article_id = ?', array($article['article_id']));
	}

	/**
	 * Vrátí seznam článků v databázi
	 * @return mixed Seznam článků
	 */
	public function getArticles()
	{
		return Db::queryAll('
			SELECT `article_id`, `title`, `url`, `description`
			FROM `article`
			ORDER BY `article_id` DESC
		');
	}

	/**
	 * Odstraní článek
	 * @param string $url URL článku
	 */
	public function removeArticle($url)
	{
		Db::query('DELETE FROM article WHERE url = ?', array($url));
	}

}
