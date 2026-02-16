<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::query()
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"))
            ->when($request->status === 'active', fn($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn($q) => $q->where('is_active', false))
            ->latest()
            ->paginate(25);

        return view('master.clients.index', compact('clients'));
    }

    public function create()
    {
        return view('master.clients.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:clients',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:50',
            'npwp' => 'nullable|string|max:50',
            'payment_terms' => 'required|integer|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only([
            'code', 'name', 'address', 'email', 'phone',
            'pic_name', 'pic_phone', 'npwp',
            'latitude', 'longitude', 'payment_terms', 'credit_limit',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('clients', 'public');
        }

        Client::create($data);

        return redirect()->route('clients.index')->with('success', 'Client berhasil ditambahkan.');
    }

    public function show(Client $client)
    {
        $client->load(['purchaseOrders' => fn($q) => $q->latest()->limit(10), 'invoices' => fn($q) => $q->latest()->limit(10)]);
        $outstandingAmount = $client->getOutstandingAmount();
        return view('master.clients.show', compact('client', 'outstandingAmount'));
    }

    public function edit(Client $client)
    {
        return view('master.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:clients,code,' . $client->id,
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:50',
            'npwp' => 'nullable|string|max:50',
            'payment_terms' => 'required|integer|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only([
            'code', 'name', 'address', 'email', 'phone',
            'pic_name', 'pic_phone', 'npwp',
            'latitude', 'longitude', 'payment_terms', 'credit_limit',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('logo')) {
            if ($client->logo) {
                Storage::disk('public')->delete($client->logo);
            }
            $data['logo'] = $request->file('logo')->store('clients', 'public');
        }

        $client->update($data);

        return redirect()->route('clients.index')->with('success', 'Client berhasil diperbarui.');
    }

    public function destroy(Client $client)
    {
        if ($client->purchaseOrders()->count() > 0) {
            return back()->with('error', 'Tidak bisa menghapus client yang memiliki transaksi.');
        }

        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client berhasil dihapus.');
    }
}
