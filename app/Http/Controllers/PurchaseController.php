<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Purchase;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Display user's purchase history.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = $user->purchases()
            ->with(['package.images'])
            ->latest();

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $purchases = $query->paginate(15);

        return Inertia::render('Purchases/Index', [
            'purchases' => $purchases,
            'filters' => $request->only(['status', 'start_date', 'end_date']),
        ]);
    }

    /**
     * Initiate purchase process.
     */
    public function create(Package $package)
    {
        $user = auth()->user();

        // Check if package is active
        if (!$package->is_active) {
            return back()->with('error', 'This package is not available for purchase.');
        }

        // Get current price
        $currentPrice = $package->current_price;
        if (!$currentPrice) {
            return back()->with('error', 'Price not available for this package.');
        }

        return Inertia::render('Purchases/Create', [
            'package' => $package->load(['images']),
            'currentPrice' => $currentPrice,
            'user' => $user,
        ]);
    }

    /**
     * Process purchase and redirect to Stripe checkout.
     */
    public function store(Request $request, Package $package)
    {
        $user = auth()->user();

        $request->validate([
            'purchase_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        // Get price for the selected date or today
        $purchaseDate = $request->purchase_date ? \Carbon\Carbon::parse($request->purchase_date) : now();
        $currentPrice = $package->current_price;

        if (!$currentPrice) {
            return back()->with('error', 'Price not available for the selected date.');
        }

        // Create purchase record (pending)
        $purchase = Purchase::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'amount' => $currentPrice->price,
            'status' => 'pending',
            'payment_details' => [
                'notes' => $request->notes,
                'purchase_date' => $purchaseDate->toDateString(),
                'selected_price_type' => $currentPrice->price_type,
                'original_price' => $currentPrice->price,
            ],
        ]);

        // Create Stripe checkout session
        try {
            $session = $this->stripeService->createCheckoutSession($package, $user, $purchase);

            // Update purchase with Stripe session ID
            $purchase->update([
                'stripe_session_id' => $session->id,
            ]);

            // Redirect to Stripe checkout
            return Inertia::location($session->url);

        } catch (\Exception $e) {
            // Update purchase status to failed
            $purchase->update([
                'status' => 'failed',
                'payment_details' => array_merge(
                    $purchase->payment_details ?? [],
                    ['error' => $e->getMessage()]
                ),
            ]);

            return back()->with('error', 'Failed to initiate payment: ' . $e->getMessage());
        }
    }

    /**
     * Handle Stripe payment success.
     */
    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            return redirect()->route('packages.index')
                ->with('error', 'Invalid payment session.');
        }

        try {
            // Retrieve the session from Stripe
            $session = $this->stripeService->retrieveSession($sessionId);

            // Find the purchase
            $purchase = Purchase::where('stripe_session_id', $sessionId)->first();

            if (!$purchase) {
                return redirect()->route('packages.index')
                    ->with('error', 'Purchase not found.');
            }

            // Update purchase with payment details
            $purchase->update([
                'stripe_payment_id' => $session->payment_intent,
                'status' => $session->payment_status === 'paid' ? 'completed' : 'pending',
                'payment_details' => array_merge(
                    $purchase->payment_details ?? [],
                    [
                        'stripe_session' => $session->toArray(),
                        'completed_at' => now()->toDateTimeString(),
                    ]
                ),
            ]);

            // Load purchase with package
            $purchase->load(['package.images']);

            return Inertia::render('Purchases/Success', [
                'purchase' => $purchase,
            ]);

        } catch (\Exception $e) {
            return redirect()->route('packages.index')
                ->with('error', 'Payment verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle Stripe payment cancellation.
     */
    public function cancel(Request $request)
    {
        $sessionId = $request->get('session_id');

        if ($sessionId) {
            // Find and update the purchase
            $purchase = Purchase::where('stripe_session_id', $sessionId)->first();
            if ($purchase) {
                $purchase->update([
                    'status' => 'failed',
                    'payment_details' => array_merge(
                        $purchase->payment_details ?? [],
                        ['cancelled_at' => now()->toDateTimeString()]
                    ),
                ]);
            }
        }

        return redirect()->route('packages.index')
            ->with('error', 'Payment was cancelled.');
    }

    /**
     * Display purchase details.
     */
    public function show(Purchase $purchase)
    {
        $user = auth()->user();

        // Check if user owns this purchase or is admin
        if ($purchase->user_id !== $user->id && !$user->hasRole(['Admin', 'Super Admin'])) {
            abort(403, 'Unauthorized access.');
        }

        $purchase->load(['package.images', 'package.prices', 'user']);

        return Inertia::render('Purchases/Show', [
            'purchase' => $purchase,
        ]);
    }

    /**
     * Handle Stripe webhook.
     */
    public function webhook(Request $request)
    {
        // This endpoint should be configured in Stripe dashboard
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                
                // Find purchase
                $purchase = Purchase::where('stripe_session_id', $session->id)->first();
                
                if ($purchase) {
                    $purchase->update([
                        'stripe_payment_id' => $session->payment_intent,
                        'status' => 'completed',
                        'payment_details' => array_merge(
                            $purchase->payment_details ?? [],
                            [
                                'webhook_received_at' => now()->toDateTimeString(),
                                'stripe_event' => $event->toArray(),
                            ]
                        ),
                    ]);
                }
                break;

            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                
                // Find purchase by payment intent
                $purchase = Purchase::where('stripe_payment_id', $paymentIntent->id)->first();
                
                if ($purchase) {
                    $purchase->update([
                        'status' => 'failed',
                        'payment_details' => array_merge(
                            $purchase->payment_details ?? [],
                            [
                                'failure_reason' => $paymentIntent->last_payment_error->message ?? 'Unknown',
                                'webhook_received_at' => now()->toDateTimeString(),
                            ]
                        ),
                    ]);
                }
                break;

            // Add more event handlers as needed
        }

        return response()->json(['status' => 'success']);
    }
}