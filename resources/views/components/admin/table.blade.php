@props([
    'headers' => [],
    'striped' => false,
])

<div class="overflow-x-auto w-full rounded-lg">
    <table {{ $attributes->merge(['class' => 'w-full text-left text-sm text-slate-700']) }}>
        @if(count($headers) > 0 || isset($thead))
            <thead class="bg-slate-50/80 text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                @if(isset($thead))
                    {{ $thead }}
                @else
                    <tr>
                        @foreach($headers as $header)
                            <th scope="col" class="px-5 py-3.5">{{ $header }}</th>
                        @endforeach
                    </tr>
                @endif
            </thead>
        @endif
        <tbody class="divide-y divide-slate-100 {{ $striped ? '[&>tr:nth-child(even)]:bg-slate-50/50' : '' }}">
            {{ $slot }}
        </tbody>
    </table>
</div>
