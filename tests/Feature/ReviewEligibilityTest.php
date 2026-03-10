<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\ProviderProfile;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReviewEligibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_review_non_completed_booking(): void
    {
        $fixture = $this->createBookingFixture('confirmed');

        $response = $this->actingAs($fixture['customer'])
            ->post(route('reviews.store'), [
                'booking_id' => $fixture['booking']->id,
                'rating' => 4,
                'comment' => 'Great service',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Only completed bookings can be reviewed.');

        $this->assertDatabaseMissing('service_reviews', [
            'booking_id' => $fixture['booking']->id,
            'from_user_id' => $fixture['customer']->user_id,
        ]);
    }

    public function test_customer_cannot_review_another_users_completed_booking_via_api(): void
    {
        $owner = $this->createUser('customer');
        $attacker = $this->createUser('customer');
        $fixture = $this->createBookingFixture('completed', $owner);

        $response = $this->actingAs($attacker, 'sanctum')
            ->postJson('/api/reviews', [
                'booking_id' => $fixture['booking']->id,
                'rating' => 2,
                'comment' => 'Attempted unauthorized review',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'You can only review your own completed bookings.',
            ]);

        $this->assertDatabaseMissing('service_reviews', [
            'booking_id' => $fixture['booking']->id,
        ]);
    }

    public function test_customer_can_create_and_update_review_for_own_completed_booking(): void
    {
        $fixture = $this->createBookingFixture('completed');

        $this->actingAs($fixture['customer'])
            ->post(route('reviews.store'), [
                'booking_id' => $fixture['booking']->id,
                'rating' => 4,
                'comment' => 'Great service',
            ])->assertRedirect();

        $this->assertDatabaseHas('service_reviews', [
            'booking_id' => $fixture['booking']->id,
            'service_id' => $fixture['service']->service_id,
            'to_user_id' => $fixture['providerUser']->user_id,
            'from_user_id' => $fixture['customer']->user_id,
            'rating' => 4,
            'comment' => 'Great service',
        ]);

        $this->actingAs($fixture['customer'])
            ->post(route('reviews.store'), [
                'booking_id' => $fixture['booking']->id,
                'rating' => 5,
                'comment' => 'Updated review',
            ])->assertRedirect();

        $this->assertDatabaseCount('service_reviews', 1);

        $this->assertDatabaseHas('service_reviews', [
            'booking_id' => $fixture['booking']->id,
            'from_user_id' => $fixture['customer']->user_id,
            'rating' => 5,
            'comment' => 'Updated review',
        ]);
    }

    public function test_completed_booking_review_link_contains_booking_query_param(): void
    {
        $fixture = $this->createBookingFixture('completed');

        $this->actingAs($fixture['customer'])
            ->get(route('users.bookings'))
            ->assertOk()
            ->assertSee(route('reviews.reviews', ['booking' => $fixture['booking']->id]), false);

        $this->actingAs($fixture['customer'])
            ->get(route('reviews.reviews'))
            ->assertOk()
            ->assertSeeText('To leave a review, open a completed booking and click Review.');
    }

    public function test_api_review_endpoints_require_authentication(): void
    {
        $fixture = $this->createBookingFixture('completed');

        $this->postJson('/api/reviews', [
            'booking_id' => $fixture['booking']->id,
            'rating' => 5,
            'comment' => 'No auth',
        ])->assertStatus(401);

        $this->deleteJson('/api/reviews/' . (string) Str::uuid())
            ->assertStatus(401);
    }

    public function test_only_review_author_can_delete_review(): void
    {
        $fixture = $this->createBookingFixture('completed');
        $otherCustomer = $this->createUser('customer');

        $review = Review::create([
            'booking_id' => $fixture['booking']->id,
            'service_id' => $fixture['service']->service_id,
            'to_user_id' => $fixture['providerUser']->user_id,
            'from_user_id' => $fixture['customer']->user_id,
            'rating' => 5,
            'comment' => 'Author review',
        ]);

        $this->actingAs($otherCustomer, 'sanctum')
            ->deleteJson('/api/reviews/' . $review->review_id)
            ->assertStatus(403)
            ->assertJson([
                'message' => 'You cannot delete this review.',
            ]);

        $this->actingAs($fixture['customer'], 'sanctum')
            ->deleteJson('/api/reviews/' . $review->review_id)
            ->assertOk()
            ->assertJson([
                'message' => 'Review deleted successfully.',
            ]);

        $this->assertDatabaseMissing('service_reviews', [
            'review_id' => $review->review_id,
        ]);
    }

    /**
     * @return array{customer: User, providerUser: User, providerProfile: ProviderProfile, category: Category, service: Service, booking: Booking}
     */
    private function createBookingFixture(string $status = 'completed', ?User $bookingOwner = null): array
    {
        $customer = $bookingOwner ?? $this->createUser('customer');
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
            'min_duration' => 90,
            'location' => 'Johannesburg',
            'is_active' => true,
        ]);

        $booking = Booking::create([
            'id' => (string) Str::uuid(),
            'user_id' => $customer->user_id,
            'service_id' => $service->service_id,
            'booking_date' => now()->toDateString(),
            'start_time' => '09:00',
            'status' => $status,
            'total_price' => $service->base_price,
            'notes' => 'Test booking',
            'address' => '123 Test Street',
        ]);

        return compact('customer', 'providerUser', 'providerProfile', 'category', 'service', 'booking');
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

