<?php

namespace Potelo\GuPayment;

use Iugu_PaymentMethod as IuguCard;

class Card
{
    /**
     * The Iugu model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $owner;

    /**
     * The Iugu card instance.
     *
     * @var \Iugu_PaymentMethod
     */
    protected $card;

    /**
     * Create a new card instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $owner
     * @param  \Iugu_PaymentMethod  $card
     * @return void
     */
    public function __construct($owner, IuguCard $card)
    {
        $this->card = $card;
        $this->owner = $owner;
    }

    /**
     * Delete the card.
     *
     * @return \Iugu_PaymentMethod
     */
    public function delete()
    {
        return $this->card->delete();
    }

    /**
     * Get the Iugu card instance.
     *
     * @return \Iugu_PaymentMethod
     */
    public function asIuguCard()
    {
        return $this->card;
    }

    /**
     * Dynamically get values from the Iugu card.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->card->{$key};
    }
}
