@php
    $colorClasses = match($color ?? 'gray') {
        'indigo' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-600', 'icon' => 'text-indigo-500'],
        'green' => ['bg' => 'bg-green-100', 'text' => 'text-green-600', 'icon' => 'text-green-500'],
        'yellow' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600', 'icon' => 'text-yellow-500'],
        'red' => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'icon' => 'text-red-500'],
        'blue' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'icon' => 'text-blue-500'],
        default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'icon' => 'text-gray-500'],
    };

    // Allowlist of safe SVG path data attributes for icons
    $allowedIcons = [
        'certificate' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />',
        'key' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />',
        'shield' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />',
        'clock' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
        'server' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 17.25v-.228a4.5 4.5 0 0 0-.12-1.03l-2.268-9.64a3.375 3.375 0 0 0-3.285-2.602H7.923a3.375 3.375 0 0 0-3.285 2.602l-2.268 9.64a4.5 4.5 0 0 0-.12 1.03v.228m19.5 0a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3m19.5 0a3 3 0 0 0-3-3H5.25a3 3 0 0 0-3 3m16.5 0h.008v.008h-.008v-.008Zm-3 0h.008v.008h-.008v-.008Z" />',
        'chart' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />',
        'exclamation' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />',
        'check' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
    ];

    $iconName = $icon ?? '';
    $iconSvg = $allowedIcons[$iconName] ?? $allowedIcons['certificate'] ?? '';
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="flex items-center">
        <div class="flex-shrink-0 {{ $colorClasses['bg'] }} rounded-lg p-3">
            <svg class="h-6 w-6 {{ $colorClasses['icon'] }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                {{-- Safe: $iconSvg is sourced from the hardcoded $allowedIcons allowlist above, not from user input --}}
                {!! $iconSvg !!}
            </svg>
        </div>
        <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">{{ $label ?? 'Label' }}</p>
            <p class="text-2xl font-bold {{ $colorClasses['text'] }}">{{ $value ?? 0 }}</p>
        </div>
    </div>
</div>
