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

class ProviderPortalIndexFiltersTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_provider_services_index_supports_filters_metrics_and_pagination(): void
    {
        $fixture = $this->createProviderFixture();
        $provider = $fixture['provider'];
        $profile = $fixture['profile'];
        $categoryA = $fixture['categoryA'];
        $categoryB = $fixture['categoryB'];

        $activeMatch = $this->createService($profile->provider_id, $categoryA->id, [
            'title' => 'Deep Clean Prime',
            'is_active' => true,
            'base_price' => 900,
        ]);

        $this->createService($profile->provider_id, $categoryA->id, [
            'title' => 'Deep Repair',
            'is_active' => false,
            'base_price' => 320,
        ]);

        $this->createService($profile->provider_id, $categoryB->id, [
            'title' => 'Garden Setup',
            'is_active' => true,
            'base_price' => 540,
        ]);

        for ($index = 1; $index <= 11; $index++) {
            $this->createService($profile->provider_id, $categoryA->id, [
                'title' => 'Service Filler ' . $index,
                'is_active' => true,
                'base_price' => 100 + $index,
            ]);
        }

        $archived = $this->createService($profile->provider_id, $categoryA->id, [
            'title' => 'Deep Legacy',
            'is_active' => false,
            'base_price' => 200,
        ]);
        $archived->delete();

        $otherProvider = $this->createProviderFixture()['profile'];
        $this->createService($otherProvider->provider_id, $categoryA->id, [
            'title' => 'Other Provider Service',
            'is_active' => true,
        ]);

        $response = $this->actingAs($provider)
            ->get(route('provider.services.index', [
                'q' => 'Deep',
                'category' => $categoryA->id,
                'status' => 'active',
                'sort' => 'price_high',
            ]))
            ->assertOk();

        /** @var \Illuminate\Pagination\LengthAwarePaginator $filteredServices */
        $filteredServices = $response->viewData('services');
        $metrics = $response->viewData('serviceMetrics');

        $this->assertSame(1, $filteredServices->total());
        $this->assertSame($activeMatch->service_id, $filteredServices->items()[0]->service_id);
        $this->assertSame(14, $metrics['total']);
        $this->assertSame(13, $metrics['active']);
        $this->assertSame(1, $metrics['paused']);
        $this->assertSame(1, $metrics['archived']);

        $pagedResponse = $this->actingAs($provider)
            ->get(route('provider.services.index', ['page' => 2]))
            ->assertOk();
        /** @var \Illuminate\Pagination\LengthAwarePaginator $pagedServices */
        $pagedServices = $pagedResponse->viewData('services');
        $this->assertSame(2, $pagedServices->currentPage());
        $this->assertSame(14, $pagedServices->total());

        $archivedResponse = $this->actingAs($provider)
            ->get(route('provider.services.index', ['archived' => 1]))
            ->assertOk();
        /** @var \Illuminate\Pagination\LengthAwarePaginator $archivedServices */
        $archivedServices = $archivedResponse->viewData('services');
        $this->assertSame(1, $archivedServices->total());
        $this->assertSame($archived->service_id, $archivedServices->items()[0]->service_id);
    }

    public function test_provider_bookings_index_supports_status_payment_schedule_and_summary_metrics(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 24, 10, 0, 0, 'Africa/Johannesburg'));

        $fixture = $this->createProviderFixture();
        $provider = $fixture['provider'];
        $service = $this->createService($fixture['profile']->provider_id, $fixture['categoryA']->id, [
            'title' => 'Premium Cleaning',
            'is_active' => true,
            'base_price' => 650,
        ]);
        $customer = $this->createUser('customer');

        $today = Carbon::now('Africa/Johannesburg')->toDateString();
        $tomorrow = Carbon::now('Africa/Johannesburg')->addDay()->toDateString();
        $yesterday = Carbon::now('Africa/Johannesburg')->subDay()->toDateString();

        $this->createBooking($customer, $service, [
            'status' => Booking::STATUS_PENDING,
            'booking_date' => $tomorrow,
            'payment_status' => Booking::PAYMENT_STATUS_UNPAID,
            'address' => 'Pending Street',
        ]);

        $confirmedAwaiting = $this->createBooking($customer, $service, [
            'status' => Booking::STATUS_CONFIRMED,
            'booking_date' => $tomorrow,
            'payment_status' => Booking::PAYMENT_STATUS_REQUIRED,
            'address' => 'Awaiting Street',
            'notes' => 'Awaiting customer payment',
        ]);

        $this->createBooking($customer, $service, [
            'status' => Booking::STATUS_CONFIRMED,
            'booking_date' => $tomorrow,
            'payment_status' => Booking::PAYMENT_STATUS_PAID,
            'address' => 'Paid Street',
        ]);

        $this->createBooking($customer, $service, [
            'status' => Booking::STATUS_IN_PROGRESS,
            'booking_date' => $today,
            'payment_status' => Booking::PAYMENT_STATUS_PAID,
            'address' => 'In Progress Street',
        ]);

        $this->createBooking($customer, $service, [
            'status' => Booking::STATUS_COMPLETED,
            'booking_date' => $yesterday,
            'payment_status' => Booking::PAYMENT_STATUS_PAID,
            'address' => 'Completed Street',
        ]);

        $this->createBooking($customer, $service, [
            'status' => Booking::STATUS_CANCELLED,
            'booking_date' => $yesterday,
            'payment_status' => Booking::PAYMENT_STATUS_REQUIRED,
            'cancellation_reason' => Booking::CANCELLATION_REASON_EXPIRED,
            'cancelled_at' => now(),
            'address' => 'Expired Street',
        ]);

        for ($index = 1; $index <= 11; $index++) {
            $this->createBooking($customer, $service, [
                'status' => Booking::STATUS_PENDING,
                'booking_date' => $tomorrow,
                'payment_status' => Booking::PAYMENT_STATUS_UNPAID,
                'address' => 'Extra Pending ' . $index,
                'notes' => 'Pending booking ' . $index,
            ]);
        }

        $response = $this->actingAs($provider)
            ->get(route('provider.bookings.index', [
                'status' => 'confirmed',
                'payment' => 'required',
                'scheduled_for' => 'upcoming',
                'q' => 'Awaiting',
            ]))
            ->assertOk();

        /** @var \Illuminate\Pagination\LengthAwarePaginator $filteredBookings */
        $filteredBookings = $response->viewData('bookings');
        $statusCounts = $response->viewData('statusCounts');
        $bookingMetrics = $response->viewData('bookingMetrics');

        $this->assertSame(1, $filteredBookings->total());
        $this->assertSame($confirmedAwaiting->id, $filteredBookings->items()[0]->id);
        $this->assertSame(12, $statusCounts['pending']);
        $this->assertSame(2, $statusCounts['confirmed']);
        $this->assertSame(1, $statusCounts['in_progress']);
        $this->assertSame(1, $statusCounts['completed']);
        $this->assertSame(1, $statusCounts['cancelled']);
        $this->assertSame(12, $bookingMetrics['pending']);
        $this->assertSame(2, $bookingMetrics['confirmed_upcoming']);
        $this->assertSame(1, $bookingMetrics['in_progress']);
        $this->assertSame(1, $bookingMetrics['awaiting_payment']);

        $pendingPageTwo = $this->actingAs($provider)
            ->get(route('provider.bookings.index', ['status' => 'pending', 'page' => 2]))
            ->assertOk();
        /** @var \Illuminate\Pagination\LengthAwarePaginator $pendingBookings */
        $pendingBookings = $pendingPageTwo->viewData('bookings');
        $this->assertSame(12, $pendingBookings->total());
        $this->assertSame(2, $pendingBookings->currentPage());
    }

    /**
     * @return array{provider: User, profile: ProviderProfile, categoryA: Category, categoryB: Category}
     */
    private function createProviderFixture(): array
    {
        $provider = $this->createUser('provider');

        $profile = ProviderProfile::create([
            'provider_id' => (string) Str::uuid(),
            'user_id' => $provider->user_id,
            'business_name' => 'Provider ' . Str::lower(Str::random(5)),
            'bio' => 'Provider profile',
            'service_area' => 'Johannesburg',
            'years_experience' => 4,
        ]);

        $categoryA = Category::create([
            'id' => (string) Str::uuid(),
            'name' => 'Home Services ' . Str::lower(Str::random(4)),
            'slug' => 'home-services-' . Str::lower(Str::random(8)),
            'icon' => 'fa-home',
        ]);

        $categoryB = Category::create([
            'id' => (string) Str::uuid(),
            'name' => 'Maintenance ' . Str::lower(Str::random(4)),
            'slug' => 'maintenance-' . Str::lower(Str::random(8)),
            'icon' => 'fa-wrench',
        ]);

        return compact('provider', 'profile', 'categoryA', 'categoryB');
    }

    private function createService(string $providerId, string $categoryId, array $overrides = []): Service
    {
        return Service::create(array_merge([
            'service_id' => (string) Str::uuid(),
            'category_id' => $categoryId,
            'provider_id' => $providerId,
            'provider_name' => 'Provider Name',
            'title' => 'Service ' . Str::lower(Str::random(5)),
            'description' => 'Service description',
            'base_price' => 250,
            'min_duration' => 60,
            'location' => 'Johannesburg',
            'is_active' => true,
        ], $overrides));
    }

    private function createBooking(User $customer, Service $service, array $overrides = []): Booking
    {
        return Booking::create(array_merge([
            'id' => (string) Str::uuid(),
            'user_id' => $customer->user_id,
            'service_id' => $service->service_id,
            'booking_date' => Carbon::now('Africa/Johannesburg')->toDateString(),
            'start_time' => '14:00',
            'status' => Booking::STATUS_PENDING,
            'total_price' => $service->base_price,
            'notes' => 'Test booking',
            'address' => '123 Test Street',
            'payment_status' => Booking::PAYMENT_STATUS_UNPAID,
            'cancellation_reason' => null,
            'cancelled_at' => null,
        ], $overrides));
    }

    private function createUser(string $role): User
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
