<?php

namespace App\Services;

use App\Entity\Course;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeService
{
    private StripeClient $stripe;

    public function getStripe(){
        return $this->stripe ??= new StripeClient($_ENV['STRIPE_PRIVATE_KEY']);
    }

    /**
     * @param Course $course
     * @return Product
     * @throws ApiErrorException
     */
    public function createProduct(Course $course): Product
    {
        return $this->getStripe()->products->create([
           'name' => $course->getTitle(),
            'description' => $course->getIntroduction()
        ]);
    }

    /**
     * @param Course $course
     * @return Price
     * @throws ApiErrorException
     */
    public function createPrice(Course $course): Price
    {
        return $this->getStripe()->prices->create([
            'unit_amount' => $course->getPrice(),
            'currency' => 'eur',
            'product' => $course->getStripeProductId()
        ]);
    }
}