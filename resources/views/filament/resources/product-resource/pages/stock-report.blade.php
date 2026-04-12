<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Stock Level Summary
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-left divide-y divide-gray-200 dark:divide-white/5">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-white/5">
                            <th class="px-4 py-3 font-semibold">Product Name</th>
                            <th class="px-4 py-3 font-semibold">SKU</th>
                            <th class="px-4 py-3 font-semibold text-center">UOM</th>
                            <th class="px-4 py-3 font-semibold text-right">Dus</th>
                            <th class="px-4 py-3 font-semibold text-right">Set/Pcs</th>
                            <th class="px-4 py-3 font-semibold text-right">Total Stock</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @foreach(\App\Models\Product::all() as $product)
                        <tr>
                            <td class="px-4 py-3">{{ $product->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $product->sku }}</td>
                            <td class="px-4 py-3 text-center">{{ $product->uom }}</td>
                            <td class="px-4 py-3 text-right">
                                {{ $product->isi > 0 ? floor($product->stock / $product->isi) : 0 }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ $product->isi > 0 ? ($product->stock % $product->isi) : $product->stock }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-primary-600">
                                {{ number_format($product->stock) }}
                            </td>
                            <td class="px-4 py-3">
                                @if($product->stock <= 5)
                                    <span class="px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full dark:bg-red-700/10 dark:text-red-400">Low Stock</span>
                                @elseif($product->stock <= 20)
                                    <span class="px-2 py-1 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded-full dark:bg-yellow-700/10 dark:text-yellow-400">Medium</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full dark:bg-green-700/10 dark:text-green-400">Healthy</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
