@if(session('success'))
<div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">{{ session('error') }}</div>
@endif
@if(session('info'))
<div class="mb-4 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-lg">{{ session('info') }}</div>
@endif
