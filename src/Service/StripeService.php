<?php

namespace App\Service;

use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;

class StripeService
{
    private $stripeClient;
    private $logger;
    private $router;

    public function __construct(string $stripeSecretKey, LoggerInterface $logger, RouterInterface $router)
    {
        $this->stripeClient = new StripeClient($stripeSecretKey);
        $this->logger = $logger;
        $this->router = $router;
    }

    public function createProductAndPrice(string $name, string $description, int $amount, string $currency, bool $active): array
    {
        try {
            $product = $this->stripeClient->products->create([
                'name' => $name,
                'description' => $description,
                'active' => $active
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

    public function updateProductAndPrice(string $productId, string $priceId, string $name, string $description, int $amount, string $currency, bool $active): array
    {
        try {
            $product = $this->stripeClient->products->update($productId, [
                'name' => $name,
                'description' => $description,
                'active' => $active,
            ]);

            // Set the old price to inactive
            $this->stripeClient->prices->update($priceId, [
                'active' => false,
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

    /*
     *
     *
     * PAYMENT INTENT AND CHECKOUT
     *
     *
     */

    public function createCheckoutSession(array $lineItems, string $customerEmail): \Stripe\Checkout\Session
    {
        try {
            $session = $this->stripeClient->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [$lineItems],
                'mode' => 'payment',
                'customer_email' => $customerEmail,
                'success_url' => $this->router->generate('cart_success', [], RouterInterface::ABSOLUTE_URL),
                'cancel_url' => $this->router->generate('cart_cancel', [], RouterInterface::ABSOLUTE_URL),
            ]);

            return $session;
        } catch (ApiErrorException $e) {
            $this->logger->error('Stripe API error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createPaymentIntent($amount, $currency, $stripeAccountId)
    {
        try {
            $paymentIntent = $this->stripeClient->paymentIntents->create([
                'amount' => $amount,
                'currency' => $currency,
                'payment_method_types' => ['card'],
                'transfer_data' => [
                    'destination' => $stripeAccountId,
                ],
                'application_fee_amount' => (int)($amount * 0.3), // SkillSpark's 30% fee, capitalism yeh
            ]);

            return $paymentIntent;
        } catch (ApiErrorException $e) {
            $this->logger->error('Stripe API error: ' . $e->getMessage());
            throw $e;
        }
    }

    /*
     *
     *
     * STRIPE USER ACCOUNT
     *
     *
     */

    public function createConnectedAccount(): string
    {
        try {
            $account = $this->stripeClient->accounts->create([
                'type' => 'express',
                'country' => 'FR',
                'capabilities' => [
                    'transfers' => ['requested' => true],
                ],
            ]);

            return $account->id;
        } catch (ApiErrorException $e) {
            throw new \Exception('Stripe API error: ' . $e->getMessage());
        }
    }

    public function generateAccountLink(string $accountId, string $returnUrl, string $refreshUrl): string
    {
        try {
            $accountLink = $this->stripeClient->accountLinks->create([
                'account' => $accountId,
                'refresh_url' => $refreshUrl,
                'return_url' => $returnUrl,
                'type' => 'account_onboarding',
            ]);

            return $accountLink->url;
        } catch (ApiErrorException $e) {
            throw new \Exception('Stripe API error: ' . $e->getMessage());
        }
    }

    public function generateAccountLinkUrls(): array
    {
        $returnUrl = $this->router->generate('homepage', [], RouterInterface::ABSOLUTE_URL);
        $refreshUrl = $this->router->generate('signup', [], RouterInterface::ABSOLUTE_URL);

        return [
            'return_url' => $returnUrl,
            'refresh_url' => $refreshUrl,
        ];
    }
}
