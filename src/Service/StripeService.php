<?php

namespace App\Service;

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
            'description' => $course->getIntroduction(),
            'images' => [$course->getThumbnail()],
            'active' => $course->isActive()

        ]);
    }

    /**
     * @param Course $course
     * @return Product
     * @throws ApiErrorException
     */
    public function updateProduct(Course $course): Product
    {

        $productData = [
            'name' => $course->getTitle(),
            'description' => $course->getIntroduction(),
            'currencies' => ['eur'],
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $course->getPrice() * 100, // Stripe prices are in cents
                'product_data' => [
                    'name' => $course->getTitle(),
                    'description' => $course->getIntroduction(),
                ],
            ],
        ];


        return $this->getStripe()->products->update($course->getStripePriceId(), [
            'name' => $course->getTitle(),
            'description' => $course->getIntroduction(),
            'images' => [$course->getThumbnail()],
            'active' => $course->isActive()

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
            'unit_amount' => $course->getPrice()*100,
            'currency' => 'eur',
            'product' => $course->getStripeProductId(),

        ]);
    }

    /**
     * @param Course $course
     * @return Price
     * @throws ApiErrorException
     */
    public function updatePrice(Course $course): Price
    {
        $stripe = $this->getStripe();
        $newPrice = $stripe->prices->create([
            'product' => $course->getStripeProductId(),
            'unit_amount' => $course->getPrice() * 100, // Convert to cents
            'currency' => 'eur',
            'active' => $course->isActive(),
        ]);

        // Deactivate the old price (optional)
        $stripe->prices->update($course->getStripePriceId(), ['active' => false]);

        // Update course with new price ID (optional)
        $course->setStripePriceId($newPrice->id);


        return $newPrice;
    }


}