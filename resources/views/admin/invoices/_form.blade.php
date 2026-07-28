@php
    $isEdit = isset($invoice);
    $existingItems = $isEdit
        ? $invoice->items->map(fn ($item) => ['description' => $item->description, 'quantity' => (float) $item->quantity, 'unit_price' => (float) $item->unit_price])->all()
        : [['description' => '', 'quantity' => 1, 'unit_price' => 0]];
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.invoices.update', $invoice) : route('admin.invoices.store') }}" class="mt-6 space-y-6"
      x-data="{
          submitting: false,
          items: @js(old('items', $existingItems)),
          discount: @js((float) old('discount', $isEdit ? $invoice->discount : 0)),
          addItem() { this.items.push({ description: '', quantity: 1, unit_price: 0 }) },
          removeItem(i) { if (this.items.length > 1) this.items.splice(i, 1) },
          lineTotal(item) { return (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0) },
          get subtotal() { return this.items.reduce((sum, item) => sum + this.lineTotal(item), 0) },
          get discountAmount() { return Math.min(parseFloat(this.discount) || 0, this.subtotal) },
          get total() { return this.subtotal - this.discountAmount },
      }"
      @submit="submitting = true">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex gap-4">
            <label class="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                <input type="radio" name="type" value="invoice" {{ old('type', $isEdit ? $invoice->type : 'invoice') === 'invoice' ? 'checked' : '' }} class="h-4 w-4 border-slate-300 text-brand-600 focus:ring-brand-200">
                Invoice
            </label>
            <label class="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                <input type="radio" name="type" value="quotation" {{ old('type', $isEdit ? $invoice->type : 'invoice') === 'quotation' ? 'checked' : '' }} class="h-4 w-4 border-slate-300 text-brand-600 focus:ring-brand-200">
                Quotation
            </label>
        </div>

        <div class="mt-5">
            <label for="title" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Title</label>
            <input id="title" name="title" type="text" required value="{{ old('title', $isEdit ? $invoice->title : '') }}"
                placeholder="e.g. Construction Materials Quote"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Shown instead of the reference number when browsing documents, and printed on the PDF.</p>
            <x-field-error name="title" />
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <div>
                <label for="bill_to_name" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Customer name</label>
                <input id="bill_to_name" name="bill_to_name" type="text" required value="{{ old('bill_to_name', $isEdit ? $invoice->bill_to_name : '') }}"
                    class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                <x-field-error name="bill_to_name" />
            </div>
            <div>
                <label for="bill_to_phone" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Phone <span class="text-slate-400">(optional)</span></label>
                <input id="bill_to_phone" name="bill_to_phone" type="text" value="{{ old('bill_to_phone', $isEdit ? $invoice->bill_to_phone : '') }}"
                    class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
            <div>
                <label for="bill_to_email" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Email <span class="text-slate-400">(optional)</span></label>
                <input id="bill_to_email" name="bill_to_email" type="email" value="{{ old('bill_to_email', $isEdit ? $invoice->bill_to_email : '') }}"
                    class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
            <div>
                <label for="bill_to_address" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Address <span class="text-slate-400">(optional)</span></label>
                <input id="bill_to_address" name="bill_to_address" type="text" value="{{ old('bill_to_address', $isEdit ? $invoice->bill_to_address : '') }}"
                    class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Line items</h2>

        <div class="mt-4 space-y-4">
            <template x-for="(item, index) in items" :key="index">
                <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400" x-text="'Item ' + (index + 1)"></span>
                        <button type="button" x-show="items.length > 1" @click="removeItem(index)" class="text-xs font-semibold text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">Remove</button>
                    </div>

                    <div class="mt-3 grid gap-4 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-200">Description</label>
                            <input type="text" :name="'items[' + index + '][description]'" x-model="item.description" required
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-200">Qty</label>
                            <input type="number" min="0.01" step="0.01" :name="'items[' + index + '][quantity]'" x-model="item.quantity" required
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-200">Unit price (Rs.)</label>
                            <input type="number" min="0" step="0.01" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" required
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                        </div>
                    </div>
                    <p class="mt-2 text-right text-xs font-semibold text-slate-500 dark:text-slate-400">Line total: Rs. <span x-text="lineTotal(item).toLocaleString()"></span></p>
                </div>
            </template>
        </div>

        <button type="button" @click="addItem()" class="mt-4 inline-flex items-center gap-1.5 rounded-lg border border-dashed border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-brand-300 hover:text-brand-700 dark:border-slate-700 dark:text-slate-300">
            + Add line item
        </button>

        <div class="mt-5 space-y-3 border-t border-slate-100 pt-4 dark:border-slate-800">
            <div class="flex items-center justify-end gap-3">
                <label for="discount" class="text-sm font-medium text-slate-700 dark:text-slate-200">Discount <span class="text-slate-400">(Rs., optional)</span></label>
                <input id="discount" name="discount" type="number" min="0" step="0.01" x-model="discount"
                    class="w-36 rounded-lg border border-slate-300 px-3 py-2 text-right text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
            <x-field-error name="discount" />

            <template x-if="discountAmount > 0">
                <div class="flex items-center justify-end gap-3 text-sm">
                    <span class="text-slate-500 dark:text-slate-400">Subtotal</span>
                    <span class="w-28 text-right text-slate-700 dark:text-slate-300">Rs. <span x-text="subtotal.toLocaleString()"></span></span>
                </div>
            </template>
            <template x-if="discountAmount > 0">
                <div class="flex items-center justify-end gap-3 text-sm">
                    <span class="text-red-600 dark:text-red-400">Discount</span>
                    <span class="w-28 text-right text-red-600 dark:text-red-400">&minus; Rs. <span x-text="discountAmount.toLocaleString()"></span></span>
                </div>
            </template>

            <div class="flex items-center justify-end gap-3">
                <span class="text-sm text-slate-500 dark:text-slate-400">Total</span>
                <span class="font-display text-xl font-extrabold text-slate-900 dark:text-white">Rs. <span x-text="total.toLocaleString()"></span></span>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <label for="notes" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Notes <span class="text-slate-400">(optional)</span></label>
        <textarea id="notes" name="notes" rows="3" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('notes', $isEdit ? $invoice->notes : '') }}</textarea>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" :disabled="submitting" class="rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
            <span x-show="!submitting">{{ $isEdit ? 'Save changes' : 'Save document' }}</span>
            <span x-show="submitting" x-cloak>Saving…</span>
        </button>
        <a href="{{ $isEdit ? route('admin.invoices.show', $invoice) : route('admin.invoices.index') }}" class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">Cancel</a>
    </div>
</form>
