<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $type = (string) $request->query('type', 'all');
        if (! in_array($type, ['all', Invoice::TYPE_INVOICE, Invoice::TYPE_QUOTATION], true)) {
            $type = 'all';
        }

        $query = Invoice::with('createdBy');

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $invoices = $query->latest()->paginate(20)->withQueryString();

        return view('admin.invoices.index', compact('invoices', 'type'));
    }

    public function create(): View
    {
        return view('admin.invoices.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $invoice = Invoice::create([
            'type' => $data['type'],
            'reference' => Invoice::generateReference($data['type']),
            'title' => $data['title'],
            'bill_to_name' => $data['bill_to_name'],
            'bill_to_email' => $data['bill_to_email'] ?? null,
            'bill_to_phone' => $data['bill_to_phone'] ?? null,
            'bill_to_address' => $data['bill_to_address'] ?? null,
            'notes' => $data['notes'] ?? null,
            'total' => $this->itemsTotal($data['items']),
            'created_by' => $request->user()->id,
        ]);

        $this->syncItems($invoice, $data['items']);

        return redirect()->route('admin.invoices.show', $invoice)->with('success', ucfirst($data['type']) . ' created.');
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load('items');

        return view('admin.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice): View
    {
        $invoice->load('items');

        return view('admin.invoices.edit', compact('invoice'));
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $data = $this->validateData($request);

        $invoice->update([
            'type' => $data['type'],
            'title' => $data['title'],
            'bill_to_name' => $data['bill_to_name'],
            'bill_to_email' => $data['bill_to_email'] ?? null,
            'bill_to_phone' => $data['bill_to_phone'] ?? null,
            'bill_to_address' => $data['bill_to_address'] ?? null,
            'notes' => $data['notes'] ?? null,
            'total' => $this->itemsTotal($data['items']),
        ]);

        $invoice->items()->delete();
        $this->syncItems($invoice, $data['items']);

        return redirect()->route('admin.invoices.show', $invoice)->with('success', ucfirst($data['type']) . ' updated.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoice->delete();

        return redirect()->route('admin.invoices.index')->with('success', 'Document deleted.');
    }

    public function download(Invoice $invoice): Response
    {
        $invoice->load('items');

        $pdf = Pdf::loadView('invoices.document', compact('invoice'));

        return $pdf->download("{$invoice->reference}.pdf");
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'in:invoice,quotation'],
            'title' => ['required', 'string', 'max:255'],
            'bill_to_name' => ['required', 'string', 'max:255'],
            'bill_to_email' => ['nullable', 'email', 'max:255'],
            'bill_to_phone' => ['nullable', 'string', 'max:50'],
            'bill_to_address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:99999'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:9999999'],
        ]);
    }

    private function itemsTotal(array $items): float
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['quantity'] * $item['unit_price'];
        }

        return $total;
    }

    private function syncItems(Invoice $invoice, array $items): void
    {
        foreach ($items as $i => $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['quantity'] * $item['unit_price'],
                'sort_order' => $i + 1,
            ]);
        }
    }
}
