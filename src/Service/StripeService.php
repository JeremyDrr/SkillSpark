<?php

namespace App\Service;

use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use Psr\Log\LoggerInterface;

class StripeService
{
    private $stripeClient;
    private $logger;

    public function __construct(string $stripeSecretKey, LoggerInterface $logger)
    {
        $this->stripeClient = new StripeClient($stripeSecretKey);
        $this->logger = $logger;
    }

    public function createProductAndPrice(string $name, string $description, int $amount, string $currency): array
    {
        try {
            $product = $this->stripeClient->products->create([
                'name' => $name,
                'description' => $description,
            ]);

            $price = $this->stripeClient->prices->create([
                'unit_amount' => $amount,
                'currency' => $currency,
                'product' => $product->id,
            ]);

            return ['product' => $product, 'price' => $price];
        } catch (ApiErrorException $e) {
            $this->logger->error('Stripe API error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateProductAndPrice(string $productId, string $priceId, string $name, string $description, int $amount, string $currency): array
    {
        try {
            $product = $this->stripeClient->products->update($productId, [
                'name' => $name,
                'description' => $description,
            ]);

            $price = $this->stripeClient->prices->create([
                'unit_amount' => $amount,
                'currency' => $currency,
                'product' => $productId,
            ]);

            return ['product' => $product, 'price' => $price];
        } catch (ApiErrorException $e) {
            $this->logger->error('Stripe API error: ' . $e->getMessage());
            throw $e;
        }
    }
}
