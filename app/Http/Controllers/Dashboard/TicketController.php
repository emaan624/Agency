<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::where('user_id', auth()->id())->latest()->paginate(10);
        return view('dashboard.tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('dashboard.tickets.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'priority' => 'in:low,normal,high,urgent',
        ]);

        $ticket = Ticket::create([
            'ticket_number' => 'TKT-' . strtoupper(Str::random(6)),
            'user_id' => auth()->id(),
            'subject' => $request->subject,
            'priority' => $request->priority ?? 'normal',
        ]);

        $ticket->messages()->create([
            'user_id' => auth()->id(),
            'body' => $request->body,
        ]);

        return redirect()->route('dashboard.tickets.show', $ticket)->with('success', 'Ticket created.');
    }

    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);
        $ticket->load('messages.user');
        return view('dashboard.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);
        $request->validate(['body' => 'required|string|max:5000']);
        $ticket->messages()->create(['user_id' => auth()->id(), 'body' => $request->body]);
        return back()->with('success', 'Reply sent.');
    }
}
