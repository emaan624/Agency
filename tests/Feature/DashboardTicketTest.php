<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTicketTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_tickets_page_loads(): void
    {
        $this->actingAs($this->user)
            ->get(route('dashboard.tickets.index'))
            ->assertOk();
    }

    public function test_create_ticket_page_loads(): void
    {
        $this->actingAs($this->user)
            ->get(route('dashboard.tickets.create'))
            ->assertOk();
    }

    public function test_user_can_create_ticket(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('dashboard.tickets.store'), [
                'subject'  => 'Issue with my order',
                'body'     => 'I cannot see my order in the list.',
                'priority' => 'normal',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'user_id' => $this->user->id,
            'subject' => 'Issue with my order',
        ]);
    }

    public function test_user_can_view_own_ticket(): void
    {
        $ticket = Ticket::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->get(route('dashboard.tickets.show', $ticket))
            ->assertOk()
            ->assertSee($ticket->subject);
    }

    public function test_user_cannot_view_another_users_ticket(): void
    {
        $otherTicket = Ticket::factory()->create();

        $this->actingAs($this->user)
            ->get(route('dashboard.tickets.show', $otherTicket))
            ->assertForbidden();
    }

    public function test_user_can_reply_to_open_ticket(): void
    {
        $ticket = Ticket::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->post(route('dashboard.tickets.reply', $ticket), [
                'body' => 'This is my follow-up message.',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('messages', [
            'ticket_id' => $ticket->id,
            'user_id'   => $this->user->id,
        ]);
    }

    public function test_user_cannot_reply_to_closed_ticket(): void
    {
        $ticket = Ticket::factory()->closed()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->post(route('dashboard.tickets.reply', $ticket), [
                'body' => 'Trying to reply.',
            ])
            ->assertForbidden();
    }
}
