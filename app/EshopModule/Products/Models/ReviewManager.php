<?php

namespace App\EshopModule\Products\Models;

use App\CoreModule\Users\Models\UserManager;
use ItNetwork\Db;
use ItNetwork\UserException;
use ItNetwork\Utility\DateUtils;
use PDOException;

/**
 * Správce recenzí na produkty
 */
class ReviewManager {

	/**
	 * Vrátí recenze k danému produktu
	 * @param int $productId ID produktu
	 * @return array Recenze k danému produktu
	 */
	public function getReviews($productId)
	{
		return Db::queryAll('
            SELECT content, user_id, CONCAT(COALESCE(first_name, ""), " ", COALESCE(last_name, ""), " ", COALESCE(company_name, "")) AS name, rating, sent
            FROM review
            JOIN person USING (user_id)
            JOIN person_detail USING (person_detail_id)
            WHERE product_id=?
            ORDER BY review_id DESC
        ', array($productId));
	}

	/**
	 * Přidá k produktu recenzi
	 * @param array $review Recenze
	 * @throws UserException
	 */
	public function addReview($review)
	{
		try
		{
			$review['user_id'] = UserManager::$user['user_id'];
			$review['sent'] = DateUtils::dbNow();
			Db::insert('review', $review);
			Db::query('
				UPDATE product
				SET ratings = ratings + 1, rating_sum = rating_sum + ?
				WHERE product_id = ?
			', array($review['rating'], $review['product_id']));
		}
		catch (PDOException $ex)
		{
			throw new UserException('Tento produkt jsi již hodnotil.');
		}
	}

}