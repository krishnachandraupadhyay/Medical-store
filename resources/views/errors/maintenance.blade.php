<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Maintenance - {{ setting('app_name', 'MEDISTORE') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-[#f0f4ff] via-white to-[#f8faff] min-h-screen flex items-center justify-center p-6 text-[#1e2746]">
    <div class="max-w-lg w-full bg-white rounded-3xl p-8 sm:p-10 shadow-xl border border-slate-200 text-center relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-blue-600 via-indigo-600 to-sky-500"></div>

        <div class="w-20 h-20 bg-blue-50 border border-blue-100 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-inner text-[#4b55c8]">
            <svg class="w-10 h-10 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
        </div>

        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 mb-3">
            System Maintenance In Progress
        </span>

        <h1 class="text-2xl font-bold tracking-tight text-[#1e2746] mb-3">
            {{ setting('app_name', 'MEDISTORE') }}
        </h1>

        <p class="text-slate-600 text-sm leading-relaxed mb-6">
            {{ $message ?? 'The system is undergoing scheduled maintenance upgrades to improve performance and security. Please check back shortly.' }}
        </p>

        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 text-xs text-slate-500 space-y-1.5 text-left mb-6">
            <div class="flex items-center justify-between">
                <span class="font-medium text-slate-700">Support Desk:</span>
                <span>{{ setting('support_email', 'support@medistore.io') }}</span>
            </div>
            @if(setting('support_phone'))
            <div class="flex items-center justify-between">
                <span class="font-medium text-slate-700">Phone Helpline:</span>
                <span>{{ setting('support_phone') }}</span>
            </div>
            @endif
        </div>

        <div class="flex items-center justify-center gap-3">
            <a href="{{ route('super-admin.login') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-semibold text-[#4b55c8] bg-blue-50 hover:bg-blue-100 transition-colors">
                Super Admin Access →
            </a>
        </div>
    </div>
</body>
</html>
