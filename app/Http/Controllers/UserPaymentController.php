<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\BookingLifecycleService;
use App\Services\BookingPaymentService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserPaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $cards = PaymentMethod::query()
            ->where('user_id', $user->user_id)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        $payments = Payment::query()
            ->with(['booking.service', 'paymentMethod'])
            ->where('user_id', $user->user_id)
            ->latest('created_at')
            ->paginate(10);

        return view('Users.payments', compact('cards', 'payments'));
    }

    public function showCheckout(Request $request, Booking $booking, BookingLifecycleService $bookingLifecycleService)
    {
        $booking = $this->syncOwnedBooking($request, $booking, $bookingLifecycleService);

        if ($booking->status !== Booking::STATUS_CONFIRMED) {
            return redirect()->route('users.bookings')->with('error', 'Payment can only be completed for confirmed bookings.');
        }

        if ($booking->payment_status === Booking::PAYMENT_STATUS_PAID) {
            return redirect()->route('users.bookings')->with('success', 'This booking is already paid.');
        }

        $cards = PaymentMethod::query()
            ->where('user_id', $request->user()->user_id)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        $recentPayments = Payment::query()
            ->with(['booking.service', 'paymentMethod'])
            ->where('user_id', $request->user()->user_id)
            ->latest('created_at')
            ->limit(5)
            ->get();

        return view('Users.checkout', [
            'booking' => $booking->load(['service']),
            'cards' => $cards,
            'recentPayments' => $recentPayments,
            'methods' => [
                'card' => 'Card',
                'wallet' => 'Wallet',
                'eft' => 'Instant EFT',
            ],
        ]);
    }

    public function initiate(
        Request $request,
        Booking $booking,
        BookingPaymentService $bookingPaymentService,
        BookingLifecycleService $bookingLifecycleService
    ) {
        $booking = $this->syncOwnedBooking($request, $booking, $bookingLifecycleService);

        if ($booking->status !== Booking::STATUS_CONFIRMED) {
            return redirect()->route('users.bookings')->with('error', 'Payment can only be initiated for confirmed bookings.');
        }

        if ($booking->payment_status !== Booking::PAYMENT_STATUS_PAID) {
            $bookingPaymentService->markPaymentRequired($booking);
        }

        return redirect()->route('users.payments.checkout', $booking->id);
    }

    public function pay(
        Request $request,
        Booking $booking,
        BookingPaymentService $bookingPaymentService,
        BookingLifecycleService $bookingLifecycleService
    ) {
        $booking = $this->syncOwnedBooking($request, $booking, $bookingLifecycleService);

        if ($booking->status !== Booking::STATUS_CONFIRMED) {
            return redirect()->route('users.bookings')->with('error', 'Payment can only be completed for confirmed bookings.');
        }

        if ($booking->payment_status === Booking::PAYMENT_STATUS_PAID) {
            return redirect()->route('users.bookings')->with('success', 'This booking is already paid.');
        }

        $validated = $request->validate([
            'method' => 'required|in:card,wallet,eft',
        ]);

        $method = $validated['method'];
        $paymentMethod = null;
        $methodMetadata = [];

        if ($method === 'card') {
            [$paymentMethod, $methodMetadata] = $this->resolveCardSelection($request);
        }

        $bookingPaymentService->recordSuccessfulPayment(
            $booking,
            $method,
            $paymentMethod,
            $methodMetadata
        );

        return redirect()->route('users.bookings')->with('success', 'Payment completed successfully.');
    }

    public function simulateSuccess(
        Request $request,
        Booking $booking,
        BookingPaymentService $bookingPaymentService,
        BookingLifecycleService $bookingLifecycleService
    ) {
        return $this->pay($request, $booking, $bookingPaymentService, $bookingLifecycleService);
    }

    public function simulateFailure(
        Request $request,
        Booking $booking,
        BookingPaymentService $bookingPaymentService,
        BookingLifecycleService $bookingLifecycleService
    ) {
        $booking = $this->syncOwnedBooking($request, $booking, $bookingLifecycleService);

        if ($booking->status !== Booking::STATUS_CONFIRMED) {
            return redirect()->route('users.bookings')->with('error', 'Payment can only be processed for confirmed bookings.');
        }

        $validated = $request->validate([
            'method' => 'required|in:card,wallet,eft',
        ]);

        $method = $validated['method'];
        $paymentMethod = null;
        $methodMetadata = [];

        if ($method === 'card') {
            [$paymentMethod, $methodMetadata] = $this->resolveCardSelection($request);
        }

        $bookingPaymentService->recordFailedPayment(
            $booking,
            $method,
            $paymentMethod,
            $methodMetadata
        );

        return redirect()->route('users.payments.checkout', $booking->id)->with('error', 'Payment simulation failed. Please retry.');
    }

    public function storePaymentMethod(Request $request)
    {
        $request->validate([
            'set_as_default' => 'nullable|boolean',
        ]);

        $card = $this->validateAndNormalizeCardInput($request);
        $this->saveCard(
            $request->user()->user_id,
            $card,
            $request->boolean('set_as_default')
        );

        $bookingId = $request->input('booking_id');
        if ($bookingId) {
            return redirect()->route('users.payments.checkout', $bookingId)->with('success', 'Card added successfully.');
        }

        return redirect()->route('users.payments.index')->with('success', 'Card added successfully.');
    }

    public function setDefaultCard(Request $request, PaymentMethod $paymentMethod)
    {
        $this->ensureCardBelongsToUser($request, $paymentMethod);

        DB::transaction(function () use ($request, $paymentMethod) {
            PaymentMethod::query()
                ->where('user_id', $request->user()->user_id)
                ->update(['is_default' => false]);

            $paymentMethod->update(['is_default' => true]);
        });

        return redirect()->back()->with('success', 'Default card updated.');
    }

    public function destroyPaymentMethod(Request $request, PaymentMethod $paymentMethod)
    {
        $this->ensureCardBelongsToUser($request, $paymentMethod);

        $userId = $request->user()->user_id;
        $methodsCount = PaymentMethod::query()
            ->where('user_id', $userId)
            ->count();

        if ($methodsCount <= 1) {
            return redirect()->back()->with('error', 'You must keep at least one payment method.');
        }

        DB::transaction(function () use ($paymentMethod, $userId) {
            $wasDefault = (bool) $paymentMethod->is_default;
            $paymentMethod->delete();

            if (!$wasDefault) {
                return;
            }

            $nextDefault = PaymentMethod::query()
                ->where('user_id', $userId)
                ->latest('created_at')
                ->first();

            if ($nextDefault !== null) {
                $nextDefault->update(['is_default' => true]);
            }
        });

        return redirect()->back()->with('success', 'Card removed successfully.');
    }

    private function syncOwnedBooking(Request $request, Booking $booking, BookingLifecycleService $bookingLifecycleService): Booking
    {
        if ($booking->user_id !== $request->user()->user_id) {
            abort(403);
        }

        return $bookingLifecycleService->syncBooking($booking);
    }

    private function ensureCardBelongsToUser(Request $request, PaymentMethod $paymentMethod): void
    {
        if ($paymentMethod->user_id !== $request->user()->user_id) {
            abort(403);
        }
    }

    /**
     * @return array{0: PaymentMethod|null, 1: array<string, mixed>}
     */
    private function resolveCardSelection(Request $request): array
    {
        $userId = $request->user()->user_id;
        $paymentMethodId = (string) $request->input('payment_method_id', '');

        if ($paymentMethodId !== '') {
            $paymentMethod = PaymentMethod::query()
                ->where('payment_method_id', $paymentMethodId)
                ->where('user_id', $userId)
                ->first();

            if ($paymentMethod === null) {
                throw ValidationException::withMessages([
                    'payment_method_id' => 'Select a valid saved card.',
                ]);
            }

            return [$paymentMethod, []];
        }

        $card = $this->validateAndNormalizeCardInput($request);
        $saveCard = $request->boolean('save_card');
        $shouldPersist = $saveCard || PaymentMethod::query()->where('user_id', $userId)->doesntExist();

        $savedCard = null;
        if ($shouldPersist) {
            $savedCard = $this->saveCard($userId, $card, $request->boolean('set_as_default'));
        }

        return [
            $savedCard,
            [
                'card_brand' => $card['brand'],
                'card_last_four' => $card['last_four'],
                'card_holder_name' => $card['holder_name'],
            ],
        ];
    }

    /**
     * @return array{
     *     brand: string,
     *     holder_name: string,
     *     last_four: string,
     *     expiry_month: int,
     *     expiry_year: int,
     *     token: string
     * }
     */
    private function validateAndNormalizeCardInput(Request $request): array
    {
        $validated = $request->validate([
            'card_holder_name' => 'required|string|max:120',
            'card_number' => 'required|string|max:30',
            'expiry_month' => 'required|integer|min:1|max:12',
            'expiry_year' => 'required|integer|min:' . now()->year . '|max:' . (now()->year + 20),
            'cvv' => ['required', 'regex:/^\d{3,4}$/'],
        ]);

        $normalizedNumber = preg_replace('/\D+/', '', (string) $validated['card_number']) ?? '';
        if (strlen($normalizedNumber) < 12 || strlen($normalizedNumber) > 19) {
            throw ValidationException::withMessages([
                'card_number' => 'Enter a valid card number.',
            ]);
        }

        $expiryMonth = (int) $validated['expiry_month'];
        $expiryYear = (int) $validated['expiry_year'];

        $expiryDate = CarbonImmutable::create(
            $expiryYear,
            $expiryMonth,
            1,
            0,
            0,
            0,
            BookingLifecycleService::BUSINESS_TIMEZONE
        );

        if ($expiryDate->endOfMonth()->lt(CarbonImmutable::now(BookingLifecycleService::BUSINESS_TIMEZONE))) {
            throw ValidationException::withMessages([
                'expiry_year' => 'This card has expired.',
            ]);
        }

        return [
            'brand' => $this->detectCardBrand($normalizedNumber),
            'holder_name' => trim((string) $validated['card_holder_name']),
            'last_four' => substr($normalizedNumber, -4),
            'expiry_month' => $expiryMonth,
            'expiry_year' => $expiryYear,
            'token' => 'tok_' . Str::lower(Str::random(24)),
        ];
    }

    /**
     * @param  array{
     *     brand: string,
     *     holder_name: string,
     *     last_four: string,
     *     expiry_month: int,
     *     expiry_year: int,
     *     token: string
     * }  $card
     */
    private function saveCard(string $userId, array $card, bool $setAsDefault = false): PaymentMethod
    {
        return DB::transaction(function () use ($userId, $card, $setAsDefault) {
            $existingCard = PaymentMethod::query()
                ->where('user_id', $userId)
                ->where('brand', $card['brand'])
                ->where('last_four', $card['last_four'])
                ->where('expiry_month', $card['expiry_month'])
                ->where('expiry_year', $card['expiry_year'])
                ->first();

            $isFirstCard = PaymentMethod::query()->where('user_id', $userId)->doesntExist();
            $shouldBeDefault = $setAsDefault || $isFirstCard;

            if ($shouldBeDefault) {
                PaymentMethod::query()
                    ->where('user_id', $userId)
                    ->update(['is_default' => false]);
            }

            if ($existingCard !== null) {
                $existingCard->update([
                    'holder_name' => $card['holder_name'],
                    'token' => $card['token'],
                    'is_default' => $shouldBeDefault ? true : $existingCard->is_default,
                ]);

                return $existingCard->fresh() ?? $existingCard;
            }

            return PaymentMethod::create([
                'user_id' => $userId,
                'brand' => $card['brand'],
                'holder_name' => $card['holder_name'],
                'last_four' => $card['last_four'],
                'expiry_month' => $card['expiry_month'],
                'expiry_year' => $card['expiry_year'],
                'token' => $card['token'],
                'is_default' => $shouldBeDefault,
                'metadata' => [
                    'simulated' => true,
                ],
            ]);
        });
    }

    private function detectCardBrand(string $cardNumber): string
    {
        if (preg_match('/^4/', $cardNumber) === 1) {
            return 'visa';
        }

        if (preg_match('/^(5[1-5]|2[2-7])/', $cardNumber) === 1) {
            return 'mastercard';
        }

        if (preg_match('/^3[47]/', $cardNumber) === 1) {
            return 'amex';
        }

        if (preg_match('/^(6011|65|64[4-9])/', $cardNumber) === 1) {
            return 'discover';
        }

        return 'card';
    }
}
