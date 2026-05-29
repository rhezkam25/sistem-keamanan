@php
$colors = [
    'blue'   => 'bg-blue-50 border-blue-200 text-blue-700',
    'green'  => 'bg-green-50 border-green-200 text-green-700',
    'yellow' => 'bg-yellow-50 border-yellow-200 text-yellow-700',
    'red'    => 'bg-red-50 border-red-200 text-red-700',
    'purple' => 'bg-purple-50 border-purple-200 text-purple-700',
];
$cls = $colors[$color] ?? $colors['blue'];
@endphp
<div class="bg-white border rounded-lg p-5 shadow-sm">
    <p class="text-sm text-gray-500">{{ $label }}</p>
    <p class="text-3xl font-bold mt-1 {{ explode(' ', $cls)[2] }}">{{ $value }}</p>
</div>
