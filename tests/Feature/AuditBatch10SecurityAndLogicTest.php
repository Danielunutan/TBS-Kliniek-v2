<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureUserIsOwner;
use App\Http\Middleware\EnsureUserIsStaff;
use App\Http\Middleware\SerializeCharacterActions;
use App\Models\Character;
use App\Models\User;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuditBatch10SecurityAndLogicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify that all recurring scheduled console commands have mutex overlap protection enabled.
     */
    public function test_all_scheduled_console_commands_have_mutex_without_overlapping_protection(): void
    {
        /** @var Schedule $schedule */
        $schedule = app(Schedule::class);

        /** @var Event[] $events */
        $events = $schedule->events();

        $this->assertNotEmpty($events, 'The console schedule must contain scheduled events.');

        $expectedCommands = [
            'gang:pay-wages',
            'battle:settle',
            'patients:pay-allowances',
            'vault:evening-drop',
            'event:resolve-cash-hunt',
            'lottery:draw',
        ];

        foreach ($expectedCommands as $commandName) {
            $matchingEvents = array_filter($events, function (Event $event) use ($commandName): bool {
                return Str::contains($event->command ?? '', $commandName);
            });

            $this->assertNotEmpty(
                $matchingEvents,
                "Command '{$commandName}' must be registered in the console schedule."
            );

            foreach ($matchingEvents as $event) {
                $this->assertTrue(
                    $event->withoutOverlapping,
                    "Command '{$commandName}' must have withoutOverlapping protection enabled to prevent concurrent executions."
                );
            }
        }
    }

    /**
     * Verify that administrative routes deny unauthenticated guests and regular users.
     */
    public function test_admin_routes_deny_unauthenticated_guests_and_regular_players(): void
    {
        $guestResponse = $this->get(route('admin.dashboard'));
        $guestResponse->assertRedirect(route('login'));

        $regularUser = User::factory()->create([
            'is_staff' => false,
            'role' => 'user',
        ]);
        Character::create([
            'user_id' => $regularUser->id,
            'name' => 'NormaleSpeler',
            'level' => 1,
            'cash' => 500,
            'bank' => 0,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
        ]);

        $userResponse = $this->actingAs($regularUser)->get(route('admin.dashboard'));
        $userResponse->assertForbidden();

        $auditResponse = $this->actingAs($regularUser)->get(route('admin.audit_log.index'));
        $auditResponse->assertForbidden();
    }

    /**
     * Verify that admin panel routes are guarded by EnsureUserIsStaff middleware.
     */
    public function test_admin_route_group_enforces_staff_middleware(): void
    {
        $adminRoute = Route::getRoutes()->getByName('admin.dashboard');

        $this->assertNotNull($adminRoute, "Route 'admin.dashboard' must exist.");
        $this->assertContains(
            EnsureUserIsStaff::class,
            $adminRoute->gatherMiddleware(),
            'Admin routes must enforce the EnsureUserIsStaff middleware.'
        );
    }

    /**
     * Verify that admin mail routes are guarded by EnsureUserIsOwner middleware.
     */
    public function test_admin_mail_routes_enforce_owner_middleware(): void
    {
        $mailRoute = Route::getRoutes()->getByName('admin.mail.index');

        $this->assertNotNull($mailRoute, "Route 'admin.mail.index' must exist.");
        $this->assertContains(
            EnsureUserIsOwner::class,
            $mailRoute->gatherMiddleware(),
            'Admin mail routes must enforce the EnsureUserIsOwner middleware.'
        );
    }

    /**
     * Verify that financial and game mutating routes enforce character serialization middleware.
     */
    public function test_game_action_routes_enforce_character_serialization(): void
    {
        $serializedRoutes = [
            'theft.steal',
            'fight.attack',
            'radio.shoutout',
            'shop.buy',
        ];

        foreach ($serializedRoutes as $routeName) {
            $route = Route::getRoutes()->getByName($routeName);
            $this->assertNotNull($route, "Route '{$routeName}' must exist.");
            $this->assertContains(
                SerializeCharacterActions::class,
                $route->gatherMiddleware(),
                "Route '{$routeName}' must include SerializeCharacterActions middleware."
            );
        }
    }

    /**
     * Verify that public legal pages remain open for unauthenticated guests.
     */
    public function test_public_legal_pages_are_accessible_without_authentication(): void
    {
        $this->get(route('legal.terms'))->assertOk();
        $this->get(route('legal.privacy'))->assertOk();
        $this->get(route('legal.cookies'))->assertOk();
        $this->get(route('legal.disclaimer'))->assertOk();
    }
}
