<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ServiceArchivalTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_service_destroy_soft_deletes_and_hides_from_default_list(): void
    {
        $fixture = $this->createProviderServiceFixture();

        $this->actingAs($fixture['providerUser'])
            ->delete(route('provider.services.destroy', $fixture['service']->service_id))
            ->assertRedirect()
            ->assertSessionHas('success', 'Service archived.');

        $this->assertSoftDeleted('services', [
            'service_id' => $fixture['service']->service_id,
        ]);

        $this->assertDatabaseHas('services', [
            'service_id' => $fixture['service']->service_id,
            'is_active' => 0,
        ]);

        $this->actingAs($fixture['providerUser'])
            ->get(route('provider.services.index'))
            ->assertOk()
            ->assertDontSeeText($fixture['service']->title);

        $this->actingAs($fixture['providerUser'])
            ->get(route('provider.services.index', ['archived' => 1]))
            ->assertOk()
            ->assertSeeText($fixture['service']->title)
            ->assertSeeText('Archived services are read-only.');
    }

    public function test_archived_service_cannot_be_booked_or_requested(): void
    {
        $fixture = $this->createProviderServiceFixture();
        $customer = $this->createUser('customer');

        $fixture['service']->update(['is_active' => false]);
        $fixture['service']->delete();

        $this->actingAs($customer)
            ->postJson('/api/service-request', [
                'service_id' => $fixture['service']->service_id,
                'booking_date' => now()->addDay()->toDateString(),
                'start_time' => '10:00',
                'address' => '123 Test Street',
                'notes' => 'Please confirm',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['service_id']);

        $this->actingAs($customer)
            ->post(route('users.bookings.store'), [
                'service_id' => $fixture['service']->service_id,
                'booking_date' => now()->addDay()->toDateString(),
                'start_time' => '10:00',
                'address' => '123 Test Street',
                'notes' => 'Please confirm',
            ])
            ->assertSessionHasErrors(['service_id']);
    }

    public function test_provider_account_delete_soft_deletes_user_profile_and_services(): void
    {
        $fixture = $this->createProviderServiceFixture();

        $this->actingAs($fixture['providerUser'])
            ->delete('/account', [
                'password' => 'password123',
            ])
            ->assertOk()
            ->assertJson([
                'message' => 'Account archived successfully',
            ]);

        $this->assertSoftDeleted('users', [
            'user_id' => $fixture['providerUser']->user_id,
        ]);

        $this->assertSoftDeleted('provider_profiles', [
            'provider_id' => $fixture['providerProfile']->provider_id,
        ]);

        $this->assertSoftDeleted('services', [
            'service_id' => $fixture['service']->service_id,
        ]);

        $this->assertDatabaseHas('services', [
            'service_id' => $fixture['service']->service_id,
            'is_active' => 0,
        ]);
    }

    /**
     * @return array{providerUser: User, providerProfile: ProviderProfile, category: Category, service: Service}
     */
    private function createProviderServiceFixture(): array
    {
        $providerUser = $this->createUser('provider');

        $providerProfile = ProviderProfile::create([
            'provider_id' => (string) Str::uuid(),
            'user_id' => $providerUser->user_id,
            'business_name' => 'Provider Business',
            'bio' => 'Test profile',
            'years_experience' => 3,
            'service_area' => 'Johannesburg',
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

        return compact('providerUser', 'providerProfile', 'category', 'service');
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
