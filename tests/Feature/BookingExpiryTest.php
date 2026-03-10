<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingExpiryTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_expire_stale_bookings_command_uses_johannesburg_time_and_preserves_manual_cancellations(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 9, 10, 5, 0, 'UTC'));

        $fixture = $this->createServiceFixture();

        $pending = $this->createBooking($fixture['customer'], $fixture['service'], Booking::STATUS_PENDING, [
            'booking_date' => '2026-03-09',
            'start_time' => '11:00',
        ]);
        $confirmed = $this->createBooking($fixture['customer'], $fixture['service'], Booking::STATUS_CONFIRMED, [
            'booking_date' => '2026-03-09',
            'start_time' => '11:00',
        ]);
        $inProgress = $this->createBooking($fixture['customer'], $fixture['service'], Booking::STATUS_IN_PROGRESS, [
            'booking_date' => '2026-03-09',
            'start_time' => '11:00',
        ]);
        $completed = $this->createBooking($fixture['customer'], $fixture['service'], Booking::STATUS_COMPLETED, [
            'booking_date' => '2026-03-09',
            'start_time' => '11:00',
        ]);
        $manuallyCancelled = $this->createBooking($fixture['customer'], $fixture['service'], Booking::STATUS_CANCELLED, [
            'booking_date' => '2026-03-09',
            'start_time' => '11:00',
            'cancellation_reason' => Booking::CANCELLATION_REASON_PROVIDER,
            'cancelled_at' => now()->subHour(),
        ]);

        $this->artisan('bookings:expire-stale')->assertSuccessful();

        foreach ([$pending, $confirmed, $inProgress] as $booking) {
            $this->assertDatabaseHas('bookings', [
                'id' => $booking->id,
                'status' => Booking::STATUS_CANCELLED,
                'cancellation_reason' => Booking::CANCELLATION_REASON_EXPIRED,
            ]);
        }

        $this->assertDatabaseHas('bookings', [
            'id' => $completed->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $manuallyCancelled->id,
            'status' => Booking::STATUS_CANCELLED,
            'cancellation_reason' => Booking::CANCELLATION_REASON_PROVIDER,
        ]);
    }

    public function test_user_bookings_page_normalizes_and_displays_expired_bookings(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 9, 10, 5, 0, 'UTC'));

        $fixture = $this->createServiceFixture();
        $booking = $this->createBooking($fixture['customer'], $fixture['service'], Booking::STATUS_PENDING, [
            'booking_date' => '2026-03-09',
            'start_time' => '11:00',
        ]);

        $this->actingAs($fixture['customer'])
            ->get(route('users.bookings'))
            ->assertOk()
            ->assertSeeText('Expired');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => Booking::STATUS_CANCELLED,
            'cancellation_reason' => Booking::CANCELLATION_REASON_EXPIRED,
        ]);
    }

    public function test_provider_cannot_confirm_booking_after_it_has_expired(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 9, 10, 5, 0, 'UTC'));

        $fixture = $this->createServiceFixture();
        $booking = $this->createBooking($fixture['customer'], $fixture['service'], Booking::STATUS_PENDING, [
            'booking_date' => '2026-03-09',
            'start_time' => '11:00',
        ]);

        $this->actingAs($fixture['providerUser'])
            ->from(route('provider.bookings'))
            ->patch(route('provider.bookings.confirm', $booking->id))
            ->assertRedirect(route('provider.bookings'))
            ->assertSessionHas('error', 'Only pending bookings can be confirmed.');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => Booking::STATUS_CANCELLED,
            'cancellation_reason' => Booking::CANCELLATION_REASON_EXPIRED,
        ]);
    }

    public function test_expired_bookings_cannot_be_reviewed(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 9, 10, 5, 0, 'UTC'));

        $fixture = $this->createServiceFixture();
        $booking = $this->createBooking($fixture['customer'], $fixture['service'], Booking::STATUS_CONFIRMED, [
            'booking_date' => '2026-03-09',
            'start_time' => '11:00',
        ]);

        $this->actingAs($fixture['customer'])
            ->post(route('reviews.store'), [
                'booking_id' => $booking->id,
                'rating' => 5,
                'comment' => 'Trying to review an expired booking',
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Only completed bookings can be reviewed.');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => Booking::STATUS_CANCELLED,
            'cancellation_reason' => Booking::CANCELLATION_REASON_EXPIRED,
        ]);
    }

    public function test_api_service_request_creates_booking_that_follows_expiry_rules(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 9, 10, 5, 0, 'UTC'));

        $fixture = $this->createServiceFixture();

        $this->actingAs($fixture['customer'])
            ->postJson('/api/service-request', [
                'service_id' => $fixture['service']->service_id,
                'booking_date' => '2026-03-09',
                'start_time' => '11:00',
                'address' => '123 Test Street',
                'notes' => 'API booking',
            ])
            ->assertOk()
            ->assertJsonFragment([
                'message' => 'Service request sent successfully',
            ]);

        $booking = Booking::latest('created_at')->firstOrFail();

        $this->artisan('bookings:expire-stale')->assertSuccessful();

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => Booking::STATUS_CANCELLED,
            'cancellation_reason' => Booking::CANCELLATION_REASON_EXPIRED,
        ]);
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
            'bio' => 'Test profile',
            'years_experience' => 3,
        ]);

        $category = Category::create([
            'id' => (string) Str::uuid(),
            'name' => 'Home Services',
            'slug' => 'home-services-' . Str::lower(Str::random(6)),
            'icon' => 'fa-home',
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

    private function createBooking(User $customer, Service $service, string $status, array $overrides = []): Booking
    {
        return Booking::create(array_merge([
            'id' => (string) Str::uuid(),
            'user_id' => $customer->user_id,
            'service_id' => $service->service_id,
            'booking_date' => now()->toDateString(),
            'start_time' => '09:00',
            'status' => $status,
            'total_price' => $service->base_price,
            'notes' => 'Test booking',
            'address' => '123 Test Street',
            'cancellation_reason' => null,
            'cancelled_at' => null,
        ], $overrides));
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