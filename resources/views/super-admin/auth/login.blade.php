<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login Portal | {{ config('app.name', 'MediStore') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#5c67e8',
                            600: '#4b55c8',
                            700: '#3f49b8',
                        },
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .bg-login-gradient {
            background: linear-gradient(135deg, #8ba0f5 0%, #7d90ea 50%, #6f82df 100%);
        }
        .glass-card {
            box-shadow: 0 25px 60px -15px rgba(28, 38, 94, 0.35);
        }
    </style>
</head>
<body class="min-h-screen bg-login-gradient flex items-center justify-center p-4 sm:p-6 lg:p-8 antialiased selection:bg-[#4b55c8] selection:text-white">

    <!-- Main Container Card -->
    <div class="w-full max-w-4xl bg-white rounded-3xl lg:rounded-[2rem] glass-card overflow-hidden grid grid-cols-1 md:grid-cols-2 my-auto">

        <!-- Left Side: Brand & Isometric Illustration -->
        <div class="bg-gradient-to-br from-[#eef2fd] via-[#e7ecfc] to-[#dee5fa] p-8 sm:p-10 flex flex-col justify-between items-center text-center relative overflow-hidden border-b md:border-b-0 md:border-r border-slate-200/60">
            
            <!-- Top Branding -->
            <div class="z-10">
                <h1 class="text-xl sm:text-2xl font-black tracking-wider text-[#1e2746]">MEDISTORE</h1>
                <p class="text-xs text-[#616e92] font-medium mt-1">Smart Management for Modern Pharmacies</p>
            </div>

            <!-- Center Isometric 3D Illustration -->
            <div class="my-6 sm:my-8 relative w-full max-w-[300px] flex items-center justify-center z-10">
                <svg viewBox="0 0 320 280" class="w-full h-auto drop-shadow-md select-none" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Base Isometric Platform -->
                    <polygon points="160,260 290,195 290,175 160,240 30,175 30,195" fill="#c3ceee" />
                    <polygon points="160,240 290,175 160,110 30,175" fill="#dbe3fa" />
                    
                    <!-- Analytics Dashboard Window in Background -->
                    <g transform="translate(130, 40)">
                        <polygon points="80,10 160,50 160,140 80,100" fill="#ffffff" stroke="#c0cced" stroke-width="1.5" />
                        <polygon points="0,50 80,10 80,100 0,140" fill="#f4f7ff" stroke="#c0cced" stroke-width="1.5" />
                        <!-- Dashboard Top Bar -->
                        <polygon points="0,50 80,10 80,22 0,62" fill="#5c67e8" />
                        <circle cx="15" cy="53" r="2.5" fill="#f87171" />
                        <circle cx="23" cy="49" r="2.5" fill="#fbbf24" />
                        <circle cx="31" cy="45" r="2.5" fill="#34d399" />
                        
                        <!-- Dashboard Content Charts -->
                        <!-- Bar Chart -->
                        <line x1="20" y1="95" x2="20" y2="78" stroke="#5c67e8" stroke-width="4" stroke-linecap="round" />
                        <line x1="30" y1="100" x2="30" y2="70" stroke="#7d90ea" stroke-width="4" stroke-linecap="round" />
                        <line x1="40" y1="105" x2="40" y2="85" stroke="#a4b5f9" stroke-width="4" stroke-linecap="round" />
                        <line x1="50" y1="110" x2="50" y2="65" stroke="#4b55c8" stroke-width="4" stroke-linecap="round" />
                        <!-- Donut / Pie chart ring -->
                        <ellipse cx="120" cy="95" rx="16" ry="12" fill="none" stroke="#7d90ea" stroke-width="4" />
                        <ellipse cx="120" cy="95" rx="8" ry="6" fill="#f4f7ff" />
                    </g>

                    <!-- Large Isometric Medical Cross Badge Background -->
                    <g transform="translate(30, 60)">
                        <circle cx="45" cy="45" r="36" fill="#5c67e8" fill-opacity="0.9" />
                        <path d="M45 23 V67 M23 45 H67" stroke="white" stroke-width="8" stroke-linecap="round" />
                    </g>

                    <!-- Medicine Bottle 1 (Big Blue Capsule Bottle) -->
                    <g transform="translate(100, 105)">
                        <!-- Bottle Body -->
                        <polygon points="30,55 58,40 58,105 30,120 2,105 2,40" fill="#4b55c8" />
                        <polygon points="30,40 58,25 30,10 2,25" fill="#7d90ea" />
                        <!-- Label with Cross -->
                        <polygon points="30,68 54,55 54,95 30,108 6,95 6,55" fill="#ffffff" />
                        <path d="M30 75 V90 M22 82.5 H38" stroke="#5c67e8" stroke-width="3" stroke-linecap="round" />
                        <!-- Cap -->
                        <ellipse cx="30" cy="12" rx="14" ry="7" fill="#e0e7ff" stroke="#a5b4fc" stroke-width="1.5" />
                        <polygon points="16,12 44,12 44,22 16,22" fill="#c7d2fe" />
                    </g>

                    <!-- Medicine Bottle 2 (Small Liquid Bottle) -->
                    <g transform="translate(42, 125)">
                        <polygon points="25,45 46,33 46,85 25,97 4,85 4,33" fill="#ffffff" stroke="#c0cced" stroke-width="1.5" />
                        <polygon points="25,33 46,21 25,9 4,21" fill="#e8edf9" />
                        <!-- Liquid Level -->
                        <polygon points="25,58 44,47 44,83 25,94 6,83 6,47" fill="#7d90ea" fill-opacity="0.35" />
                        <!-- Cap -->
                        <ellipse cx="25" cy="11" rx="10" ry="5" fill="#94a3b8" />
                        <polygon points="15,11 35,11 35,18 15,18" fill="#64748b" />
                    </g>

                    <!-- Pill Capsules -->
                    <g transform="translate(90, 195)">
                        <ellipse cx="20" cy="10" rx="16" ry="6" transform="rotate(30 20 10)" fill="#f43f5e" />
                        <ellipse cx="24" cy="12" rx="8" ry="6" transform="rotate(30 24 12)" fill="#ffffff" />
                    </g>
                    <g transform="translate(125, 205)">
                        <ellipse cx="15" cy="8" rx="14" ry="5" transform="rotate(-25 15 8)" fill="#5c67e8" />
                        <ellipse cx="11" cy="6" rx="7" ry="5" transform="rotate(-25 11 6)" fill="#e0e7ff" />
                    </g>

                    <!-- Isometric Medical Boxes / Storage -->
                    <g transform="translate(190, 160)">
                        <polygon points="35,30 65,12 65,55 35,72 5,55 5,12" fill="#7d90ea" />
                        <polygon points="35,12 65,-6 35,-24 5,-6" fill="#a4b5f9" />
                        <polygon points="35,30 65,12 35,-6 5,12" fill="#5c67e8" />
                        <polygon points="5,12 35,30 35,72 5,55" fill="#434dbd" />
                    </g>
                    <g transform="translate(160, 200)">
                        <polygon points="25,20 45,8 45,35 25,47 5,35 5,8" fill="#a4b5f9" />
                        <polygon points="25,8 45,-4 25,-16 5,-4" fill="#c7d2fe" />
                        <polygon points="5,8 25,20 25,47 5,35" fill="#7d90ea" />
                    </g>
                </svg>
            </div>

            <!-- Bottom Branding -->
            <div class="z-10">
                <h2 class="text-xl sm:text-2xl font-black tracking-wider text-[#1e2746]">MEDISTORE</h2>
                <p class="text-xs text-[#616e92] font-medium mt-1">Smart Management for Modern Pharmacies</p>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="p-8 sm:p-12 lg:p-14 flex flex-col justify-between bg-white">
            
            <div>
                <!-- Top Portal Icon -->
                <div class="flex justify-center">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#4b55c8] to-[#6f7ef0] flex items-center justify-center shadow-lg shadow-[#4b55c8]/25 text-white">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v4m-2-2h4" />
                        </svg>
                    </div>
                </div>

                <!-- Titles -->
                <div class="text-center mt-3 mb-7">
                    <h3 class="text-2xl sm:text-[26px] font-bold text-[#1e2746] tracking-tight">Login Portal</h3>
                    <p class="text-xs sm:text-sm text-[#64748b] mt-1">Sign in to access your platform account</p>
                </div>

                <!-- Status Flash Message -->
                @if (session('status'))
                    <div class="mb-5 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs flex items-start gap-2">
                        <svg class="w-4 h-4 flex-shrink-0 text-emerald-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <!-- Error Flash Message -->
                @if (session('error'))
                    <div class="mb-5 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-start gap-2">
                        <svg class="w-4 h-4 flex-shrink-0 text-rose-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Login Form -->
                <form action="{{ route('super-admin.login.submit') }}" method="POST" class="space-y-4" novalidate>
                    @csrf

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-xs font-semibold text-[#334155] mb-1.5">
                            Email Address
                        </label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            placeholder="Enter your email address"
                            class="w-full px-3.5 py-2.5 bg-white border @error('email') border-rose-400 focus:border-rose-500 focus:ring-rose-500/10 @else border-[#d8e0ec] focus:border-[#4b55c8] focus:ring-[#4b55c8]/10 @enderror rounded-xl text-[#1e2746] placeholder-[#94a3b8] text-xs sm:text-sm focus:outline-none focus:ring-4 transition duration-150"
                        >
                        @error('email')
                            <p class="mt-1 text-xs text-rose-500 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-xs font-semibold text-[#334155] mb-1.5">
                            Password
                        </label>
                        <div class="relative">
                            <input
                                type="password"
                                name="password"
                                id="password"
                                required
                                placeholder="Enter your password"
                                class="w-full px-3.5 pr-10 py-2.5 bg-white border @error('password') border-rose-400 focus:border-rose-500 focus:ring-rose-500/10 @else border-[#d8e0ec] focus:border-[#4b55c8] focus:ring-[#4b55c8]/10 @enderror rounded-xl text-[#1e2746] placeholder-[#94a3b8] text-xs sm:text-sm focus:outline-none focus:ring-4 transition duration-150"
                            >
                            <button
                                type="button"
                                id="togglePassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer"
                                aria-label="Toggle password visibility"
                            >
                                <svg id="eyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-xs text-rose-500 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Remember & Forgot Password -->
                    <div class="flex items-center justify-between pt-1 text-xs">
                        <label class="flex items-center gap-2 cursor-pointer select-none text-[#475569]">
                            <input
                                type="checkbox"
                                name="remember"
                                id="remember"
                                class="w-4 h-4 rounded border-[#cbd5e1] text-[#4b55c8] focus:ring-[#4b55c8]/20 focus:ring-offset-0 transition cursor-pointer"
                            >
                            <span>Remember me</span>
                        </label>
                        <a href="javascript:void(0)" onclick="alert('For security reasons, password resets must be issued via system CLI or primary administrator.')" class="text-[#64748b] hover:text-[#4b55c8] font-medium transition">
                            Forgot password?
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button
                            type="submit"
                            id="submitBtn"
                            class="w-full py-2.5 px-4 bg-[#4b55c8] hover:bg-[#3f49b8] active:scale-[0.99] text-white font-semibold text-xs sm:text-sm rounded-xl shadow-md shadow-[#4b55c8]/25 transition flex items-center justify-center cursor-pointer"
                        >
                            <span>Sign In</span>
                        </button>
                    </div>

                    <!-- Secure Portal Badge -->
                    <div class="pt-3 flex justify-center">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#f1f5f9] text-[#475569] text-[11px] font-medium border border-[#e2e8f0]">
                            <svg class="w-3.5 h-3.5 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span>Secure Login Portal</span>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer Note -->
            <p class="text-center text-[11px] text-[#94a3b8] mt-8">
                &copy; {{ date('Y') }} Medistore. All rights reserved.
            </p>
        </div>

    </div>

    <!-- Password visibility toggle script -->
    <script>
        document.getElementById('togglePassword')?.addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        });
    </script>
</body>
</html>
