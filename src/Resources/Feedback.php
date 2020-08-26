<?php

namespace Etsy\Resources;

use Etsy\Resource;

/**
 * Feedback resource class. Represents feedback for either an Etsy seller or buyer.
 *
 * @link https://www.etsy.com/developers/documentation/reference/feedback
 * @author Rhys Hall hello@rhyshall.com
 */
class Feedback extends Resource {

  /**
   * @var array
   */
  protected $_associations = [
    'Buyer' => 'User',
    'Seller' => 'User',
    'Author' => 'User',
    'Subject' => 'User',
    'Transaction' => 'Transaction',
    'Listing' => 'Listing'
  ];

  /**
   * Gets the transaction the feedback is for.
   *
   * @return \Etsy\Resources\Transaction
   */
  public function getTransaction() {
    return $this->request(
        "GET",
        "/transactions/{$this->transaction_id}",
        "Transaction"
      )
      ->first();
  }

  /**
   * Gets the user the left the review.
   *
   * @return \Etsy\Resources\User
   */
  public function getAuthor() {
    return $this->request(
        "GET",
        "/users/{$this->creator_user_id}",
        "User"
      )
      ->first();
  }

  /**
   * Gets the user this feedback was left for.
   *
   * @return \Etsy\Resources\User
   */
  public function getTarget() {
    return $this->request(
        "GET",
        "/users/{$this->target_user_id}",
        "User"
      )
      ->first();
  }

}
