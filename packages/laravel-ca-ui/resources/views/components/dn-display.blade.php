@php
    $dnLabels = [
        'CN' => 'Common Name',
        'O' => 'Organization',
        'OU' => 'Organizational Unit',
        'C' => 'Country',
        'ST' => 'State/Province',
        'L' => 'Locality',
        'emailAddress' => 'Email',
        'serialNumber' => 'Serial Number',
    ];
@endphp

@if(!empty($dn))
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2">
        @foreach($dn as $key => $value)
            <div class="flex items-baseline">
                <dt class="text-xs font-medium text-gray-400 w-24 flex-shrink-0">{{ $dnLabels[$key] ?? $key }}</dt>
                <dd class="text-sm text-gray-900 ml-2">{{ $value }}</dd>
            </div>
        @endforeach
    </dl>
@else
    <p class="text-sm text-gray-500">No distinguished name information available.</p>
@endif
