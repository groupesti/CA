@php
    $sortedChains = $chains->sortByDesc('depth');
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h3 class="text-sm font-semibold text-gray-700 mb-4">Certificate Chain</h3>
    <div class="space-y-0">
        @foreach($sortedChains as $index => $chain)
            <div class="flex items-start">
                <div class="flex flex-col items-center mr-4">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $loop->first ? 'bg-indigo-100 text-indigo-600' : ($loop->last ? 'bg-green-100 text-green-600' : 'bg-blue-100 text-blue-600') }} text-xs font-bold">
                        {{ $chain->depth }}
                    </div>
                    @if(!$loop->last)
                        <div class="w-0.5 h-8 bg-gray-300 my-1"></div>
                    @endif
                </div>
                <div class="flex-1 pb-4">
                    @if($chain->parentCertificate)
                        <a href="{{ route('ca.certificates.show', $chain->parentCertificate->uuid) }}"
                           class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                            {{ $chain->parentCertificate->subject_dn['CN'] ?? 'Certificate' }}
                        </a>
                        <p class="text-xs text-gray-500">
                            {{ $chain->parentCertificate->type->slug }}
                            @if($chain->depth === 0)
                                (Root)
                            @endif
                        </p>
                    @else
                        <span class="text-sm text-gray-500">Unknown certificate at depth {{ $chain->depth }}</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
