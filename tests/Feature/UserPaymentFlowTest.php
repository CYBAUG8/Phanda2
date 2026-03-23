<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserPaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_pay_with_new_card_and_save_it(): void
    {
        $fixture = $this->createServiceFixture();
        $booking = $this->createConfirmedBooking($fixture['customer'], $fixture['service']);

        $this->actingAs($fixture['customer'])
            ->post(route('users.payments.initiate', $booking->id))
            ->assertRedirect(route('users.payments.checkout', $booking->id));

        $response = $this->actingAs($fixture['customer'])
            ->post(route('users.payments.pay', $booking->id), [
                'method' => 'card',
                'payment_method_id' => '',
                'card_holder_name' => 'Customer User',
                'card_number' => '4111 1111 1111 1111',
                'expiry_month' => 12,
                'expiry_year' => now()->addYears(2)->year,
                'cvv' => '123',
                'save_card' => '1',
                'set_as_default' => '1',
            ]);

        $response
            ->assertRedirect(route('users.bookings'))
            ->assertSessionHas('success', 'Payment completed successfully.');

        $booking->refresh();
        $this->assertSame(Booking::PAYMENT_STATUS_PAID, $booking->payment_status);

        $this->assertDatabaseHas('payment_methods', [
            'user_id' => $fixture['customer']->user_id,
            'brand' => 'visa',
            'last_four' => '1111',
            'is_default' => 1,
        ]);

        $payment = Payment::query()
            ->where('booking_id', $booking->id)
            ->latest('created_at')
            ->firstOrFail();

        $this->assertSame('paid', $payment->status);
        $this->assertSame('card', $payment->method);
        $this->assertNotNull($payment->payment_method_id);
        $this->assertSame('1111', data_get($payment->metadata, 'card_last_four'));
    }

    public function test_user_can_choose_saved_card_for_payment(): void
    {
        $fixture = $this->createServiceFixture();
        $booking = $this->createConfirmedBooking($fixture['customer'], $fixture['service']);

        $savedCard = PaymentMethod::create([
            'user_id' => $fixture['customer']->user_id,
            'brand' => 'mastercard',
            'holder_name' => 'Customer User',
            'last_four' => '4444',
            'expiry_month' => 8,
            'expiry_year' => now()->addYears(1)->year,
            'token' => 'tok_existing_card',
            'is_default' => true,
        ]);

        $this->actingAs($fixture['customer'])
            ->post(route('users.payments.pay', $booking->id), [
                'method' => 'card',
                'payment_method_id' => $savedCard->payment_method_id,
            ])
            ->assertRedirect(route('users.bookings'))
            ->assertSessionHas('success', 'Payment completed successfully.');

        $payment = Payment::query()
            ->where('booking_id', $booking->id)
            ->latest('created_at')
            ->firstOrFail();

        $this->assertSame($savedCard->payment_method_id, $payment->payment_method_id);
        $this->assertSame('4444', data_get($payment->metadata, 'card_last_four'));
        $this->assertSame('mastercard', data_get($payment->metadata, 'card_brand'));
    }

    /**
     * @return array{customer: User, providerUser: User, providerProfile: ProviderProfile, category: Category, service: Service}
     */
    private function createServiceFixture(): array
    {
        $customer = $this->createUser('customer');
        $providerUser = $this->createUser('provider');

        $providerProfile = ProviderProfile::create([
            'provider_id' => (string) Str::uuid(),
            'user_id' => $providerUser->user_id,
            'business_name' => 'Provider Business',
            'bio' => 'Provider profile',
            'years_experience' => 4,
            'service_area' => 'Johannesburg',
        ]);

        $category = Category::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cleaning',
            'slug' => 'cleaning-' . Str::lower(Str::random(6)),
            'icon' => 'fa-broom',
        ]);

        $service = Service::create([
            'service_id' => (string) Str::uuid(),
            'category_id' => $category->id,
            'provider_id' => $providerProfile->provider_id,
            'provider_name' => $providerUser->full_name,
            'title' => 'Deep Cleaning',
            'description' => 'A complete home cleaning service.',
            'base_price' => 450,
            'min_duration' => 60,
            'location' => 'Johannesburg',
            'is_active' => true,
        ]);

        return compact('customer', 'providerUser', 'providerProfile', 'category', 'service');
    }

    private function createConfirmedBooking(User $customer, Service $service): Booking
    {
        return Booking::create([
            'id' => (string) Str::uuid(),
            'user_id' => $customer->user_id,
            'service_id' => $service->service_id,
            'booking_date' => now()->addDay()->toDateString(),
            'start_time' => '10:00',
            'status' => Booking::STATUS_CONFIRMED,
            'payment_status' => Booking::PAYMENT_STATUS_REQUIRED,
            'payment_due_at' => now()->addHours(6),
            'total_price' => $service->base_price,
            'notes' => 'Test booking',
            'address' => '123 Test Street',
        ]);
    }

    private function createUser(string $role = 'customer'): User
    {
        return User::create([
            'user_id' => (string) Str::uuid(),
            'full_name' => ucfirst($role) . ' User ' . Str::lower(Str::random(5)),
            'email' => Str::lower(Str::random(10)) . '@example.com',
            'phone' => '27' . random_int(100000000, 999999999),
            'password' => Hash::make('password123'),
            'role' => $role,
        ]);
    }
}
