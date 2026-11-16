<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Sistem Perpustakaan Digital' }} - Perpus Dupat</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-900 bg-slate-50 flex flex-col" x-data="{ mobileNav: false }">
    <!-- Top Navigation Bar (SMAN 24 Navy & Royal Blue Identity) -->
    <header class="bg-gradient-to-r from-[#102a54] via-[#163e75] to-[#1a4f8b] border-b border-[#102a54] text-white sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Left: Brand & Desktop Nav Links -->
                <div class="flex items-center">
                    <!-- Brand & Logo with SMAN 24 Seal -->
                    <a href="{{ url('/') }}" class="flex items-center gap-3 group shrink-0">
                        <img src="{{ asset('images/logo-sman24.jpg') }}" 
                             alt="Logo SMAN 24" 
                             class="w-9 h-9 rounded-full object-contain bg-white p-0.5 shadow-sm ring-1 ring-white/30 group-hover:scale-105 transition-transform duration-200 shrink-0">
                        <div>
                            <div class="font-bold text-sm tracking-wide text-white leading-tight">PERPUS DUPAT</div>
                            <div class="text-[11px] text-blue-200/90 font-normal leading-tight mt-0.5">SMA Negeri 24 Bandung</div>
                        </div>
                    </a>

                    <!-- Desktop Nav Links -->
                    <nav class="hidden md:flex items-center gap-6 lg:gap-8 ml-8 lg:ml-12 h-16">
                        @if(auth()->user()->isPustakawan())
                            <a href="{{ route('librarian.dashboard') }}" class="relative h-16 flex items-center text-sm font-medium transition {{ request()->routeIs('librarian.dashboard') ? 'text-white font-semibold' : 'text-blue-100/75 hover:text-white' }}">
                                <span>Dashboard</span>
                                @if(request()->routeIs('librarian.dashboard'))
                                    <span class="absolute bottom-2.5 left-1/2 -translate-x-1/2 w-6 h-0.5 bg-amber-400 rounded-full shadow-xs"></span>
                                @endif
                            </a>
                            <a href="{{ route('books.index') }}" class="relative h-16 flex items-center text-sm font-medium transition {{ request()->routeIs('books.*') ? 'text-white font-semibold' : 'text-blue-100/75 hover:text-white' }}">
                                <span>Katalog Buku</span>
                                @if(request()->routeIs('books.*'))
                                    <span class="absolute bottom-2.5 left-1/2 -translate-x-1/2 w-6 h-0.5 bg-amber-400 rounded-full shadow-xs"></span>
                                @endif
                            </a>
                            <a href="{{ route('loans.index') }}" class="relative h-16 flex items-center text-sm font-medium transition {{ request()->routeIs('loans.*') ? 'text-white font-semibold' : 'text-blue-100/75 hover:text-white' }}">
                                <span>Sirkulasi</span>
                                @if(request()->routeIs('loans.*'))
                                    <span class="absolute bottom-2.5 left-1/2 -translate-x-1/2 w-6 h-0.5 bg-amber-400 rounded-full shadow-xs"></span>
                                @endif
                            </a>
                            <a href="{{ route('visits.index') }}" class="relative h-16 flex items-center text-sm font-medium transition {{ request()->routeIs('visits.*') ? 'text-white font-semibold' : 'text-blue-100/75 hover:text-white' }}">
                                <span>Kunjungan Siswa</span>
                                @if(request()->routeIs('visits.*'))
                                    <span class="absolute bottom-2.5 left-1/2 -translate-x-1/2 w-6 h-0.5 bg-amber-400 rounded-full shadow-xs"></span>
                                @endif
                            </a>
                            <a href="{{ route('kiosk.index') }}" target="_blank" class="relative h-16 flex items-center text-sm font-medium text-blue-100/75 hover:text-white transition">
                                <span>Buka Kiosk Mandiri</span>
                            </a>
                        @else
                            <a href="{{ route('student.dashboard') }}" class="relative h-16 flex items-center text-sm font-medium transition {{ request()->routeIs('student.dashboard') ? 'text-white font-semibold' : 'text-blue-100/75 hover:text-white' }}">
                                <span>Beranda</span>
                                @if(request()->routeIs('student.dashboard'))
                                    <span class="absolute bottom-2.5 left-1/2 -translate-x-1/2 w-6 h-0.5 bg-amber-400 rounded-full shadow-xs"></span>
                                @endif
                            </a>
                            <a href="{{ route('books.index') }}" class="relative h-16 flex items-center text-sm font-medium transition {{ request()->routeIs('books.*') ? 'text-white font-semibold' : 'text-blue-100/75 hover:text-white' }}">
                                <span>Jelajah Buku</span>
                                @if(request()->routeIs('books.*'))
                                    <span class="absolute bottom-2.5 left-1/2 -translate-x-1/2 w-6 h-0.5 bg-amber-400 rounded-full shadow-xs"></span>
                                @endif
                            </a>
                            <a href="{{ route('loans.index') }}" class="relative h-16 flex items-center text-sm font-medium transition {{ request()->routeIs('loans.*') ? 'text-white font-semibold' : 'text-blue-100/75 hover:text-white' }}">
                                <span>Pinjaman Saya</span>
                                @if(request()->routeIs('loans.*'))
                                    <span class="absolute bottom-2.5 left-1/2 -translate-x-1/2 w-6 h-0.5 bg-amber-400 rounded-full shadow-xs"></span>
                                @endif
                            </a>
                            <a href="{{ route('visits.index') }}" class="relative h-16 flex items-center text-sm font-medium transition {{ request()->routeIs('visits.*') ? 'text-white font-semibold' : 'text-blue-100/75 hover:text-white' }}">
                                <span>Riwayat Kunjungan</span>
                                @if(request()->routeIs('visits.*'))
                                    <span class="absolute bottom-2.5 left-1/2 -translate-x-1/2 w-6 h-0.5 bg-amber-400 rounded-full shadow-xs"></span>
                                @endif
                            </a>
                        @endif
                    </nav>
                </div>

                <!-- Right Actions: User Menu & Mobile Toggle -->
                <div class="flex items-center gap-3 sm:gap-6">
                    <!-- User Menu Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-2.5 p-1 rounded-xl hover:bg-white/10 transition focus:outline-none">
                            <div class="w-8 h-8 rounded-full bg-[#102a54] text-white border border-white/20 flex items-center justify-center font-bold text-xs uppercase shrink-0 shadow-xs">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                            <div class="hidden sm:block text-left">
                                <div class="text-xs font-semibold text-white leading-none">{{ auth()->user()->name }}</div>
                                <div class="text-[11px] text-blue-200/90 capitalize mt-1 leading-none">
                                    {{ auth()->user()->isPustakawan() ? 'Pustakawan · Admin' : 'Siswa · ' . auth()->user()->nis_nip }}
                                </div>
                            </div>
                            <svg class="w-3.5 h-3.5 text-blue-200 ml-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open" x-transition.origin.top.right class="absolute right-0 mt-2 w-56 rounded-xl bg-white shadow-xl border border-slate-200 py-1.5 text-sm text-slate-700 z-50 divide-y divide-slate-100" style="display: none;">
                            <div class="px-4 py-2.5">
                                <div class="font-medium text-slate-900 text-xs truncate">{{ auth()->user()->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">NIS/NIP: {{ auth()->user()->nis_nip }}</div>
                                <span class="inline-flex mt-1.5 items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-[#1a4f8b] border border-blue-200">
                                    {{ strtoupper(auth()->user()->role) }}
                                </span>
                            </div>

                            <div class="py-1">
                                <a href="{{ auth()->user()->isPustakawan() ? route('librarian.dashboard') : route('student.dashboard') }}" class="w-full text-left px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 flex items-center gap-2 transition">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                    Dashboard
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 flex items-center gap-2 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Keluar (Logout)
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Hamburger Button -->
                    <button type="button"
                            @click="mobileNav = !mobileNav"
                            class="md:hidden inline-flex items-center justify-center p-2 rounded-lg text-white hover:bg-white/10 focus:outline-none transition active:scale-95"
                            aria-label="Toggle Navigation"
                            :aria-expanded="mobileNav">
                        <svg x-show="!mobileNav" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="mobileNav" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Slide-Down Drawer Navigation -->
        <div x-show="mobileNav"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             @click.outside="mobileNav = false"
             class="md:hidden border-t border-[#1a4f8b]/50 bg-[#102a54] px-4 pt-3 pb-4 space-y-1 shadow-xl">
            @if(auth()->user()->isPustakawan())
                <a href="{{ route('librarian.dashboard') }}"
                   @click="mobileNav = false"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('librarian.dashboard') ? 'bg-white/20 text-white font-semibold' : 'text-blue-100/80 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('books.index') }}"
                   @click="mobileNav = false"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('books.*') ? 'bg-white/20 text-white font-semibold' : 'text-blue-100/80 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span>Katalog Buku</span>
                </a>
                <a href="{{ route('loans.index') }}"
                   @click="mobileNav = false"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('loans.*') ? 'bg-white/20 text-white font-semibold' : 'text-blue-100/80 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span>Sirkulasi & Peminjaman</span>
                </a>
                <a href="{{ route('visits.index') }}"
                   @click="mobileNav = false"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('visits.*') ? 'bg-white/20 text-white font-semibold' : 'text-blue-100/80 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span>Kunjungan Siswa</span>
                </a>
            @else
                <a href="{{ route('student.dashboard') }}"
                   @click="mobileNav = false"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('student.dashboard') ? 'bg-white/20 text-white font-semibold' : 'text-blue-100/80 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Beranda</span>
                </a>
                <a href="{{ route('books.index') }}"
                   @click="mobileNav = false"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('books.*') ? 'bg-white/20 text-white font-semibold' : 'text-blue-100/80 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span>Jelajah Buku</span>
                </a>
                <a href="{{ route('loans.index') }}"
                   @click="mobileNav = false"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('loans.*') ? 'bg-white/20 text-white font-semibold' : 'text-blue-100/80 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span>Pinjaman Saya</span>
                </a>
                <a href="{{ route('visits.index') }}"
                   @click="mobileNav = false"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('visits.*') ? 'bg-white/20 text-white font-semibold' : 'text-blue-100/80 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span>Riwayat Kunjungan</span>
                </a>
            @endif

            @if(auth()->user()->isPustakawan())
                <div class="pt-2 border-t border-white/15">
                    <a href="{{ route('kiosk.index') }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-white/10 hover:bg-white/20 transition">
                        <svg class="w-3.5 h-3.5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>Buka Kiosk Mandiri</span>
                    </a>
                </div>
            @endif
        </div>
    </header>

    <!-- Main Content Container (Mobile pb-24 for bottom bar clearance) -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-24 md:pb-6">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 p-3.5 text-emerald-800 flex items-start gap-3 shadow-2xs">
                <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 p-3.5 text-rose-800 flex items-start gap-3 shadow-2xs">
                <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm font-medium">{{ session('error') }}</div>
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-5 rounded-lg border border-amber-200 bg-amber-50 p-3.5 text-amber-800 flex items-start gap-3 shadow-2xs">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <div class="text-sm font-medium">{{ session('warning') }}</div>
                    @if(session('import_errors'))
                        <div class="mt-2 text-xs bg-white p-2.5 rounded border border-amber-200 max-h-40 overflow-y-auto font-mono">
                            @foreach(session('import_errors') as $err)
                                <div>• Baris {{ $err['row'] }} (ISBN: {{ $err['isbn'] }}): {{ $err['message'] }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Global Toast Notification Widget (Alpine.js) -->
    <div x-cloak x-show="$store.toast.show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-2 scale-95" class="fixed bottom-20 md:bottom-5 right-5 z-50 max-w-sm w-[calc(100%-2.5rem)] md:w-full">
        <div class="rounded-xl shadow-lg p-4 flex items-center justify-between text-white border"
             :class="{
                'bg-emerald-700 border-emerald-800': $store.toast.type === 'success',
                'bg-rose-700 border-rose-800': $store.toast.type === 'error',
                'bg-amber-600 border-amber-700': $store.toast.type === 'warning',
                'bg-[#1a4f8b] border-[#102a54]': $store.toast.type === 'info'
             }">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm font-medium" x-text="$store.toast.message"></div>
            </div>
            <button @click="$store.toast.close()" class="text-white/80 hover:text-white p-1 rounded-md transition focus:outline-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Minimalist Clean Footer (Mobile pb accounted for bottom bar) -->
    <footer class="border-t border-slate-200 bg-white py-3.5 mb-16 md:mb-0 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
            <div>Sistem Perpustakaan Digital - SMA NEGERI 24 BANDUNG</div>
            <div>Server AMD A4 Low-Memory Optimized</div>
        </div>
    </footer>

    <!-- Fixed Bottom App Navigation Bar for Mobile (Native App Feel, 4 columns for both roles) -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 h-16 bg-white/95 backdrop-blur-md border-t border-slate-200 z-40 grid grid-cols-4 items-center px-1 shadow-sm">
        @if(auth()->user()->isPustakawan())
            <a href="{{ route('librarian.dashboard') }}" class="flex flex-col items-center justify-center py-1 transition {{ request()->routeIs('librarian.dashboard') ? 'text-[#1a4f8b]' : 'text-slate-500 hover:text-slate-900' }}">
                <div class="p-1 rounded-full {{ request()->routeIs('librarian.dashboard') ? 'bg-[#1a4f8b]/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
                <span class="text-[10px] font-medium leading-none mt-1">Dashboard</span>
            </a>
            <a href="{{ route('books.index') }}" class="flex flex-col items-center justify-center py-1 transition {{ request()->routeIs('books.*') ? 'text-[#1a4f8b]' : 'text-slate-500 hover:text-slate-900' }}">
                <div class="p-1 rounded-full {{ request()->routeIs('books.*') ? 'bg-[#1a4f8b]/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <span class="text-[10px] font-medium leading-none mt-1">Katalog</span>
            </a>
            <a href="{{ route('loans.index') }}" class="flex flex-col items-center justify-center py-1 transition {{ request()->routeIs('loans.*') ? 'text-[#1a4f8b]' : 'text-slate-500 hover:text-slate-900' }}">
                <div class="p-1 rounded-full {{ request()->routeIs('loans.*') ? 'bg-[#1a4f8b]/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <span class="text-[10px] font-medium leading-none mt-1">Sirkulasi</span>
            </a>
            <a href="{{ route('visits.index') }}" class="flex flex-col items-center justify-center py-1 transition {{ request()->routeIs('visits.*') ? 'text-[#1a4f8b]' : 'text-slate-500 hover:text-slate-900' }}">
                <div class="p-1 rounded-full {{ request()->routeIs('visits.*') ? 'bg-[#1a4f8b]/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <span class="text-[10px] font-medium leading-none mt-1">Kunjungan</span>
            </a>
        @else
            <a href="{{ route('student.dashboard') }}" class="flex flex-col items-center justify-center py-1 transition {{ request()->routeIs('student.dashboard') ? 'text-[#1a4f8b]' : 'text-slate-500 hover:text-slate-900' }}">
                <div class="p-1 rounded-full {{ request()->routeIs('student.dashboard') ? 'bg-[#1a4f8b]/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
                <span class="text-[10px] font-medium leading-none mt-1">Beranda</span>
            </a>
            <a href="{{ route('books.index') }}" class="flex flex-col items-center justify-center py-1 transition {{ request()->routeIs('books.*') ? 'text-[#1a4f8b]' : 'text-slate-500 hover:text-slate-900' }}">
                <div class="p-1 rounded-full {{ request()->routeIs('books.*') ? 'bg-[#1a4f8b]/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <span class="text-[10px] font-medium leading-none mt-1">Jelajah</span>
            </a>
            <a href="{{ route('loans.index') }}" class="flex flex-col items-center justify-center py-1 transition {{ request()->routeIs('loans.*') ? 'text-[#1a4f8b]' : 'text-slate-500 hover:text-slate-900' }}">
                <div class="p-1 rounded-full {{ request()->routeIs('loans.*') ? 'bg-[#1a4f8b]/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <span class="text-[10px] font-medium leading-none mt-1">Pinjaman</span>
            </a>
            <a href="{{ route('visits.index') }}" class="flex flex-col items-center justify-center py-1 transition {{ request()->routeIs('visits.*') ? 'text-[#1a4f8b]' : 'text-slate-500 hover:text-slate-900' }}">
                <div class="p-1 rounded-full {{ request()->routeIs('visits.*') ? 'bg-[#1a4f8b]/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <span class="text-[10px] font-medium leading-none mt-1">Riwayat</span>
            </a>
        @endif
    </nav>

    @stack('scripts')
</body>
</html>
