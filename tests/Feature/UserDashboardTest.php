<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\ProviderProfile;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_stats_and_activity_for_authenticated_user(): void
    {
        $customer = $this->createUser('customer', 'Customer Test');
        $provider = $this->createUser('provider', 'Provider Test');

        $providerProfile = ProviderProfile::create([
            'provider_id' => (string) Str::uuid(),
            'user_id' => $provider->user_id,
            'business_name' => 'Provider Business',
            'bio' => 'Provider bio',
            'years_experience' => 3,
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
            'provider_name' => $provider->full_name,
            'title' => 'Home Deep Clean',
            'description' => 'Deep cleaning service',
            'base_price' => 500,
            'min_duration' => 120,
            'location' => 'Johannesburg',
            'rating' => 4.8,
            'reviews_count' => 12,
            'is_active' => true,
        ]);

        $booking = Booking::create([
            'id' => (string) Str::uuid(),
            'user_id' => $customer->user_id,
            'service_id' => $service->service_id,
            'booking_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'status' => Booking::STATUS_PENDING,
            'total_price' => 500,
            'address' => '123 Main Street',
            'notes' => 'Please be on time',
        ]);

        $conversation = Conversation::create([
            'user_id' => $customer->user_id,
            'provider_id' => $providerProfile->provider_id,
            'last_message_time' => now(),
        ]);

        Message::create([
            'conversation_id' => $conversation->conversation_id,
            'sender_id' => $provider->user_id,
            'sender_type' => 'provider',
            'message' => 'I can arrive earlier if needed.',
            'is_read' => false,
        ]);

        Review::create([
            'review_id' => (string) Str::uuid(),
            'booking_id' => $booking->id,
            'service_id' => $service->service_id,
            'to_user_id' => $provider->user_id,
            'from_user_id' => $customer->user_id,
            'rating' => 4,
            'comment' => 'Great service',
        ]);

        $this->actingAs($customer)
            ->get(route('users.dashboard'))
            ->assertOk()
            ->assertSee('Welcome back, Customer Test')
            ->assertSee('Bookings in Progress')
            ->assertSee('Unread Messages')
            ->assertSee('Average Rating')
            ->assertSee('1 unread')
            ->assertSee('Booking PENDING for Home Deep Clean');
    }

    public function test_dashboard_shows_empty_activity_state_when_no_data_exists(): void
    {
        $customer = $this->createUser('customer', 'Empty State User');

        $this->actingAs($customer)
            ->get(route('users.dashboard'))
            ->assertOk()
            ->assertSee('No activity yet')
            ->assertSee('Find Services');
    }

    private function createUser(string $role, string $fullName): User
    {
        return User::create([
            'user_id' => (string) Str::uuid(),
            'full_name' => $fullName,
            'email' => Str::lower(Str::random(10)) . '@example.com',
            'phone' => '27' . random_int(100000000, 999999999),
            'password' => Hash::make('password123'),
            'role' => $role,
        ]);
    }
}
